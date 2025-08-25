<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\BankAccount;
use App\Models\Finance\PettyCashFund;
use App\Models\Finance\CashTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CashManagementController extends Controller
{
    /**
     * Get cash management dashboard data
     */
    public function dashboard(): JsonResponse
    {
        try {
            $bankAccounts = BankAccount::where('status', 'active')->get();
            $pettyCashFunds = PettyCashFund::where('status', 'active')->get();
            $recentTransactions = CashTransaction::with(['bankAccount', 'pettyCashFund'])
                ->latest()
                ->take(10)
                ->get();

            $totalCashBalance = $bankAccounts->sum('balance') + $pettyCashFunds->sum('balance');
            $totalCashInflow = $recentTransactions->where('amount', '>', 0)->sum('amount');
            $totalCashOutflow = $recentTransactions->where('amount', '<', 0)->sum('amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'total_cash_balance' => $totalCashBalance,
                    'total_cash_inflow' => $totalCashInflow,
                    'total_cash_outflow' => abs($totalCashOutflow),
                    'net_cash_flow' => $totalCashInflow - abs($totalCashOutflow),
                    'bank_accounts' => $bankAccounts,
                    'petty_cash_funds' => $pettyCashFunds,
                    'recent_transactions' => $recentTransactions
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all bank accounts with pagination
     */
    public function getBankAccounts(Request $request): JsonResponse
    {
        try {
            $query = BankAccount::query();

            // Apply filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('account_number', 'like', "%{$search}%")
                      ->orWhere('bank_name', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('currency')) {
                $query->where('currency', $request->currency);
            }

            $bankAccounts = $query->paginate($request->get('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => $bankAccounts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching bank accounts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new bank account
     */
    public function createBankAccount(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'account_number' => 'required|string|max:100|unique:bank_accounts',
                'description' => 'nullable|string|max:1000',
                'bank_name' => 'required|string|max:255',
                'bank_branch' => 'nullable|string|max:255',
                'swift_code' => 'nullable|string|max:11',
                'iban' => 'nullable|string|max:50',
                'currency' => 'required|string|max:3',
                'opening_balance' => 'nullable|numeric|min:0',
                'opening_date' => 'nullable|date',
                'account_type' => 'required|string|in:checking,savings,time_deposit,investment',
                'status' => 'nullable|string|in:active,inactive',
                'reconcile_automatically' => 'boolean',
                'allow_overdraft' => 'boolean',
                'include_in_cash_flow' => 'boolean',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $bankAccount = BankAccount::create($request->all());

            // Create opening balance transaction if provided
            if ($request->filled('opening_balance') && $request->opening_balance > 0) {
                CashTransaction::create([
                    'bank_account_id' => $bankAccount->id,
                    'type' => 'opening_balance',
                    'amount' => $request->opening_balance,
                    'description' => 'Opening balance',
                    'date' => $request->opening_date ?? now(),
                    'status' => 'completed',
                    'reference_number' => 'OB-' . $bankAccount->id
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bank account created successfully',
                'data' => $bankAccount->load('transactions')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating bank account: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific bank account
     */
    public function getBankAccount($id): JsonResponse
    {
        try {
            $bankAccount = BankAccount::with(['transactions' => function ($query) {
                $query->latest()->take(50);
            }])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $bankAccount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bank account not found'
            ], 404);
        }
    }

    /**
     * Update a bank account
     */
    public function updateBankAccount(Request $request, $id): JsonResponse
    {
        try {
            $bankAccount = BankAccount::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'account_number' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('bank_accounts')->ignore($id)
                ],
                'description' => 'nullable|string|max:1000',
                'bank_name' => 'sometimes|required|string|max:255',
                'bank_branch' => 'nullable|string|max:255',
                'swift_code' => 'nullable|string|max:11',
                'iban' => 'nullable|string|max:50',
                'currency' => 'sometimes|required|string|max:3',
                'account_type' => 'sometimes|required|string|in:checking,savings,time_deposit,investment',
                'status' => 'nullable|string|in:active,inactive',
                'reconcile_automatically' => 'boolean',
                'allow_overdraft' => 'boolean',
                'include_in_cash_flow' => 'boolean',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $bankAccount->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Bank account updated successfully',
                'data' => $bankAccount->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating bank account: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle bank account status
     */
    public function toggleBankAccountStatus(Request $request, $id): JsonResponse
    {
        try {
            $bankAccount = BankAccount::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $bankAccount->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Bank account status updated successfully',
                'data' => $bankAccount->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating bank account status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get petty cash funds
     */
    public function getPettyCashFunds(): JsonResponse
    {
        try {
            $pettyCashFunds = PettyCashFund::where('status', 'active')->get();

            return response()->json([
                'success' => true,
                'data' => $pettyCashFunds
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching petty cash funds: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create petty cash fund
     */
    public function createPettyCashFund(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'custodian' => 'required|string|max:255',
                'initial_amount' => 'required|numeric|min:0',
                'currency' => 'required|string|max:3',
                'description' => 'nullable|string|max:1000',
                'location' => 'nullable|string|max:255',
                'status' => 'nullable|string|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $pettyCashFund = PettyCashFund::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Petty cash fund created successfully',
                'data' => $pettyCashFund
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating petty cash fund: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent cash transactions
     */
    public function getRecentTransactions(): JsonResponse
    {
        try {
            $transactions = CashTransaction::with(['bankAccount', 'pettyCashFund'])
                ->latest()
                ->take(20)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching recent transactions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create cash transaction
     */
    public function createTransaction(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|string|in:deposit,withdrawal,transfer,expense,opening_balance',
                'amount' => 'required|numeric',
                'description' => 'required|string|max:1000',
                'date' => 'required|date',
                'bank_account_id' => 'nullable|exists:bank_accounts,id',
                'petty_cash_fund_id' => 'nullable|exists:petty_cash_funds,id',
                'reference_number' => 'nullable|string|max:100',
                'status' => 'nullable|string|in:completed,pending,cancelled',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Ensure either bank_account_id or petty_cash_fund_id is provided
            if (!$request->bank_account_id && !$request->petty_cash_fund_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Either bank account or petty cash fund must be specified'
                ], 422);
            }

            DB::beginTransaction();

            $transaction = CashTransaction::create($request->all());

            // Update account balance
            if ($request->bank_account_id) {
                $bankAccount = BankAccount::find($request->bank_account_id);
                $bankAccount->increment('balance', $request->amount);
            } elseif ($request->petty_cash_fund_id) {
                $pettyCashFund = PettyCashFund::find($request->petty_cash_fund_id);
                $pettyCashFund->increment('balance', $request->amount);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction created successfully',
                'data' => $transaction->load(['bankAccount', 'pettyCashFund'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export cash flow report
     */
    public function exportCashFlow(Request $request): JsonResponse
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth());
            $endDate = $request->get('end_date', now()->endOfMonth());

            $transactions = CashTransaction::with(['bankAccount', 'pettyCashFund'])
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date')
                ->get();

            // Group by month for cash flow statement
            $cashFlow = $transactions->groupBy(function ($transaction) {
                return $transaction->date->format('Y-m');
            })->map(function ($monthTransactions) {
                return [
                    'inflow' => $monthTransactions->where('amount', '>', 0)->sum('amount'),
                    'outflow' => abs($monthTransactions->where('amount', '<', 0)->sum('amount')),
                    'net_flow' => $monthTransactions->sum('amount')
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ],
                    'cash_flow' => $cashFlow,
                    'transactions' => $transactions
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting cash flow: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export bank accounts
     */
    public function exportBankAccounts(): JsonResponse
    {
        try {
            $bankAccounts = BankAccount::with('transactions')->get();

            return response()->json([
                'success' => true,
                'data' => $bankAccounts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting bank accounts: ' . $e->getMessage()
            ], 500);
        }
    }
}
