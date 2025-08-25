<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\Expense;
use App\Models\Finance\ApprovalWorkflow;
use App\Models\Finance\ApprovalRequest;
use App\Models\Finance\Budget;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    /**
     * Get expenses with approval status
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Expense::with(['category', 'department', 'approvalRequest'])
                ->when($request->has('status'), function ($q) use ($request) {
                    $q->where('status', $request->status);
                })
                ->when($request->has('category_id'), function ($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                })
                ->when($request->has('department_id'), function ($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                })
                ->when($request->has('date_from'), function ($q) use ($request) {
                    $q->where('expense_date', '>=', $request->date_from);
                })
                ->when($request->has('date_to'), function ($q) use ($request) {
                    $q->where('expense_date', '<=', $request->date_to);
                });

            $expenses = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $expenses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading expenses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create expense with approval workflow and budget checking
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'category_id' => 'required|exists:expense_categories,id',
                'department_id' => 'required|exists:departments,id',
                'expense_date' => 'required|date',
                'description' => 'required|string|max:500',
                'amount' => 'required|numeric|min:0',
                'tax_amount' => 'nullable|numeric|min:0',
                'total_amount' => 'required|numeric|min:0',
                'payment_method' => 'required|string|in:cash,bank_transfer,credit_card,check',
                'vendor_name' => 'nullable|string|max:255',
                'invoice_number' => 'nullable|string|max:100',
                'receipt_attachment' => 'nullable|string',
                'notes' => 'nullable|string',
                'is_recurring' => 'boolean',
                'recurring_frequency' => 'nullable|string|in:weekly,monthly,quarterly,yearly',
            ]);

            DB::beginTransaction();

            // Check budget availability
            $budgetCheck = $this->checkBudgetAvailability(
                $validated['category_id'],
                $validated['department_id'],
                $validated['total_amount'],
                $validated['expense_date']
            );

            if (!$budgetCheck['available']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Budget exceeded: ' . $budgetCheck['message'],
                    'budget_info' => $budgetCheck
                ], 400);
            }

            // Create expense
            $expense = Expense::create([
                'category_id' => $validated['category_id'],
                'department_id' => $validated['department_id'],
                'expense_date' => $validated['expense_date'],
                'description' => $validated['description'],
                'amount' => $validated['amount'],
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'total_amount' => $validated['total_amount'],
                'payment_method' => $validated['payment_method'],
                'vendor_name' => $validated['vendor_name'],
                'invoice_number' => $validated['invoice_number'],
                'receipt_attachment' => $validated['receipt_attachment'],
                'notes' => $validated['notes'],
                'is_recurring' => $validated['is_recurring'] ?? false,
                'recurring_frequency' => $validated['recurring_frequency'],
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            // Check if approval is required
            $this->checkApprovalRequired($expense, $validated['total_amount']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Expense created successfully',
                'data' => $expense->load(['category', 'department', 'approvalRequest']),
                'budget_info' => $budgetCheck
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating expense: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update expense
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $expense = Expense::findOrFail($id);

            // Check if expense can be updated
            if ($expense->approvalRequest && $expense->approvalRequest->status === 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update expense while approval is pending'
                ], 400);
            }

            $validated = $request->validate([
                'category_id' => 'sometimes|exists:expense_categories,id',
                'department_id' => 'sometimes|exists:departments,id',
                'expense_date' => 'sometimes|date',
                'description' => 'sometimes|string|max:500',
                'amount' => 'sometimes|numeric|min:0',
                'tax_amount' => 'nullable|numeric|min:0',
                'total_amount' => 'sometimes|numeric|min:0',
                'payment_method' => 'sometimes|string|in:cash,bank_transfer,credit_card,check',
                'vendor_name' => 'nullable|string|max:255',
                'invoice_number' => 'nullable|string|max:100',
                'receipt_attachment' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $expense->update($validated);

            // Recalculate total if amount changed
            if ($request->has('amount') || $request->has('tax_amount')) {
                $totalAmount = ($validated['amount'] ?? $expense->amount) + ($validated['tax_amount'] ?? $expense->tax_amount);
                $expense->update(['total_amount' => $totalAmount]);
            }

            // Check if approval is still required
            $this->checkApprovalRequired($expense, $expense->total_amount);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Expense updated successfully',
                'data' => $expense->load(['category', 'department', 'approvalRequest'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating expense: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit expense for approval
     */
    public function submitForApproval(int $id): JsonResponse
    {
        try {
            $expense = Expense::findOrFail($id);

            // Check if already submitted
            if ($expense->approvalRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Expense already submitted for approval'
                ], 400);
            }

            // Check if approval is required
            $workflow = $this->getApprovalWorkflow($expense->total_amount);
            if (!$workflow) {
                // Auto-approve if no workflow found
                $expense->update(['status' => 'approved']);
                return response()->json([
                    'success' => true,
                    'message' => 'Expense auto-approved',
                    'data' => $expense
                ]);
            }

            // Create approval request
            $approvalRequest = ApprovalRequest::create([
                'workflow_id' => $workflow->id,
                'requestor_id' => Auth::id(),
                'approvable_type' => Expense::class,
                'approvable_id' => $expense->id,
                'amount' => $expense->total_amount,
                'description' => "Expense: {$expense->description} - {$expense->category->name}",
                'priority' => $this->determinePriority($expense->total_amount),
                'due_date' => now()->addDays(2),
                'status' => 'pending',
            ]);

            // Assign first level approver
            $firstLevel = $workflow->levels()->orderBy('level')->first();
            if ($firstLevel) {
                $approvalRequest->update([
                    'approver_id' => $firstLevel->approver_id,
                    'current_level' => $firstLevel->level,
                ]);
            }

            // Update expense status
            $expense->update(['status' => 'pending_approval']);

            return response()->json([
                'success' => true,
                'message' => 'Expense submitted for approval',
                'data' => $expense->load(['category', 'department', 'approvalRequest'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting for approval: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get expense with approval details
     */
    public function show(int $id): JsonResponse
    {
        try {
            $expense = Expense::with([
                'category', 
                'department', 
                'approvalRequest.workflow',
                'approvalRequest.approver',
                'approvalRequest.requestor'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $expense
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading expense: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel expense
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $expense = Expense::findOrFail($id);

            // Check if can be cancelled
            if (!in_array($expense->status, ['draft', 'pending_approval'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Expense cannot be cancelled in current status'
                ], 400);
            }

            DB::beginTransaction();

            // Cancel approval request if exists
            if ($expense->approvalRequest) {
                $expense->approvalRequest->update([
                    'status' => 'cancelled',
                    'approver_comments' => 'Cancelled by requestor'
                ]);
            }

            // Update expense status
            $expense->update(['status' => 'cancelled']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Expense cancelled successfully',
                'data' => $expense
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling expense: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get expenses pending approval
     */
    public function pendingApproval(Request $request): JsonResponse
    {
        try {
            $query = Expense::with(['category', 'department', 'approvalRequest.workflow'])
                ->whereHas('approvalRequest', function ($q) {
                    $q->where('status', 'pending');
                });

            $expenses = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $expenses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading pending approvals: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get expense analytics
     */
    public function analytics(Request $request): JsonResponse
    {
        try {
            $dateFrom = $request->get('date_from', now()->startOfMonth());
            $dateTo = $request->get('date_to', now()->endOfMonth());

            $analytics = [
                'total_expenses' => Expense::whereBetween('expense_date', [$dateFrom, $dateTo])->sum('total_amount'),
                'pending_approval' => Expense::whereHas('approvalRequest', function ($q) {
                    $q->where('status', 'pending');
                })->sum('total_amount'),
                'approved_expenses' => Expense::where('status', 'approved')
                    ->whereBetween('expense_date', [$dateFrom, $dateTo])
                    ->sum('total_amount'),
                'by_category' => Expense::with('category')
                    ->whereBetween('expense_date', [$dateFrom, $dateTo])
                    ->selectRaw('category_id, SUM(total_amount) as total')
                    ->groupBy('category_id')
                    ->get(),
                'by_department' => Expense::with('department')
                    ->whereBetween('expense_date', [$dateFrom, $dateTo])
                    ->selectRaw('department_id, SUM(total_amount) as total')
                    ->groupBy('department_id')
                    ->get(),
                'budget_variance' => $this->getBudgetVariance($dateFrom, $dateTo),
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check budget availability
     */
    private function checkBudgetAvailability(int $categoryId, int $departmentId, float $amount, string $expenseDate): array
    {
        $expenseDate = \Carbon\Carbon::parse($expenseDate);
        $year = $expenseDate->year;
        $month = $expenseDate->month;

        // Get budget for this category and department
        $budget = Budget::where('category_id', $categoryId)
            ->where('department_id', $departmentId)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if (!$budget) {
            return [
                'available' => true,
                'message' => 'No budget set for this category and period',
                'budget_amount' => 0,
                'spent_amount' => 0,
                'remaining_amount' => 0,
            ];
        }

        // Get total spent in this category and department for the month
        $spentAmount = Expense::where('category_id', $categoryId)
            ->where('department_id', $departmentId)
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->where('status', 'approved')
            ->sum('total_amount');

        $remainingAmount = $budget->amount - $spentAmount;
        $available = $remainingAmount >= $amount;

        return [
            'available' => $available,
            'message' => $available ? 'Budget available' : 'Budget exceeded',
            'budget_amount' => $budget->amount,
            'spent_amount' => $spentAmount,
            'remaining_amount' => $remainingAmount,
            'requested_amount' => $amount,
        ];
    }

    /**
     * Get budget variance
     */
    private function getBudgetVariance(string $dateFrom, string $dateTo): array
    {
        $expenses = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
            ->where('status', 'approved')
            ->with(['category', 'department'])
            ->get();

        $variance = [];
        foreach ($expenses as $expense) {
            $budget = Budget::where('category_id', $expense->category_id)
                ->where('department_id', $expense->department_id)
                ->where('year', $expense->expense_date->year)
                ->where('month', $expense->expense_date->month)
                ->first();

            if ($budget) {
                $variance[] = [
                    'category' => $expense->category->name,
                    'department' => $expense->department->name,
                    'budgeted' => $budget->amount,
                    'actual' => $expense->total_amount,
                    'variance' => $budget->amount - $expense->total_amount,
                    'variance_percentage' => (($budget->amount - $expense->total_amount) / $budget->amount) * 100,
                ];
            }
        }

        return $variance;
    }

    /**
     * Check if approval is required and create approval request
     */
    private function checkApprovalRequired(Expense $expense, float $totalAmount): void
    {
        $workflow = $this->getApprovalWorkflow($totalAmount);
        
        if ($workflow) {
            // Create approval request
            $approvalRequest = ApprovalRequest::create([
                'workflow_id' => $workflow->id,
                'requestor_id' => Auth::id(),
                'approvable_type' => Expense::class,
                'approvable_id' => $expense->id,
                'amount' => $totalAmount,
                'description' => "Expense: {$expense->description} - {$expense->category->name}",
                'priority' => $this->determinePriority($totalAmount),
                'due_date' => now()->addDays(2),
                'status' => 'pending',
            ]);

            // Assign first level approver
            $firstLevel = $workflow->levels()->orderBy('level')->first();
            if ($firstLevel) {
                $approvalRequest->update([
                    'approver_id' => $firstLevel->approver_id,
                    'current_level' => $firstLevel->level,
                ]);
            }

            // Update expense status
            $expense->update(['status' => 'pending_approval']);
        } else {
            // Auto-approve if no workflow found
            $expense->update(['status' => 'approved']);
        }
    }

    /**
     * Get appropriate approval workflow based on amount
     */
    private function getApprovalWorkflow(float $amount): ?ApprovalWorkflow
    {
        return ApprovalWorkflow::active()
            ->byType('expense')
            ->byThreshold($amount)
            ->first();
    }

    /**
     * Determine priority based on amount
     */
    private function determinePriority(float $amount): string
    {
        if ($amount >= 50000) return 'urgent';
        if ($amount >= 25000) return 'high';
        if ($amount >= 10000) return 'medium';
        return 'low';
    }
}
