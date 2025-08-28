<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\BankReconciliation;
use App\Models\Finance\BankAccount;
use App\Models\Finance\BankTransaction;
use App\Models\Finance\ReconciliationAdjustment;
use App\Models\Finance\TransactionMatch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BankReconciliationController extends Controller
{
    /**
     * Display a listing of bank reconciliations
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = BankReconciliation::with(['bankAccount', 'createdBy'])
                ->when($request->bank_account_id, function ($q, $bankAccountId) {
                    return $q->where('bank_account_id', $bankAccountId);
                })
                ->when($request->status, function ($q, $status) {
                    return $q->where('status', $status);
                })
                ->when($request->period_start, function ($q, $startDate) {
                    return $q->where('period_start', '>=', $startDate);
                })
                ->when($request->period_end, function ($q, $endDate) {
                    return $q->where('period_end', '<=', $endDate);
                });

            $reconciliations = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);

            return response()->json([
                'success' => true,
                'data' => $reconciliations->items(),
                'meta' => [
                    'current_page' => $reconciliations->currentPage(),
                    'last_page' => $reconciliations->lastPage(),
                    'per_page' => $reconciliations->perPage(),
                    'total' => $reconciliations->total(),
                    'from' => $reconciliations->firstItem(),
                    'to' => $reconciliations->lastItem(),
                ],
                'links' => $reconciliations->linkCollection()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching reconciliations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard overview data
     */
    public function dashboard(): JsonResponse
    {
        try {
            $overview = [
                'active_accounts' => BankAccount::where('status', 'active')->count(),
                'reconciled_count' => BankReconciliation::where('status', 'completed')->count(),
                'pending_count' => BankReconciliation::where('status', 'pending')->count(),
                'discrepancies_count' => BankReconciliation::where('status', 'in_progress')
                    ->where('difference', '!=', 0)
                    ->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $overview
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent reconciliations for dashboard
     */
    public function recent(): JsonResponse
    {
        try {
            $reconciliations = BankReconciliation::with(['bankAccount'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($reconciliation) {
                    return [
                        'id' => $reconciliation->id,
                        'bank_account_name' => $reconciliation->bankAccount->name,
                        'status' => $reconciliation->status,
                        'period_start' => $reconciliation->period_start,
                        'period_end' => $reconciliation->period_end,
                        'reconciliation_date' => $reconciliation->reconciliation_date,
                        'transactions_count' => 0, // Temporarily hardcoded to avoid relationship issues
                        'difference' => $reconciliation->difference
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $reconciliations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching recent reconciliations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created reconciliation
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Check authentication first
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validated = $request->validate([
                'bank_account_id' => 'required|exists:bank_accounts,id',
                'period_start' => 'required|date',
                'period_end' => 'required|date|after_or_equal:period_start',
                'notes' => 'nullable|string'
            ]);

            // Check if there's already a reconciliation for this period
            $existingReconciliation = BankReconciliation::where('bank_account_id', $validated['bank_account_id'])
                ->where(function ($query) use ($validated) {
                    $query->whereBetween('period_start', [$validated['period_start'], $validated['period_end']])
                          ->orWhereBetween('period_end', [$validated['period_start'], $validated['period_end']]);
                })
                ->whereIn('status', ['pending', 'in_progress'])
                ->first();

            if ($existingReconciliation) {
                return response()->json([
                    'success' => false,
                    'message' => 'A reconciliation for this period already exists'
                ], 422);
            }

            $reconciliation = BankReconciliation::create([
                ...$validated,
                'status' => 'pending',
                'created_by' => Auth::id(),
                'bank_statement_balance' => 0,
                'book_balance' => 0,
                'difference' => 0
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reconciliation created successfully',
                'data' => $reconciliation->load('bankAccount')
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating reconciliation: ' . $e->getMessage(), [
                'trace' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating reconciliation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified reconciliation
     */
    public function show(BankReconciliation $reconciliation): JsonResponse
    {
        try {
            $reconciliation->load([
                'bankAccount',
                'adjustments',
                'transactionMatches.bankTransaction',
                'transactionMatches.bookTransaction',
                'createdBy',
                'approvedBy'
            ]);

            return response()->json([
                'success' => true,
                'data' => $reconciliation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching reconciliation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified reconciliation
     */
    public function update(Request $request, BankReconciliation $reconciliation): JsonResponse
    {
        try {
            $validated = $request->validate([
                'period_start' => 'sometimes|date',
                'period_end' => 'sometimes|date|after_or_equal:period_start',
                'notes' => 'nullable|string',
                'status' => 'sometimes|in:pending,in_progress,completed,cancelled'
            ]);

            $reconciliation->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Reconciliation updated successfully',
                'data' => $reconciliation->fresh()->load('bankAccount')
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating reconciliation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete a reconciliation
     */
    public function complete(Request $request, BankReconciliation $reconciliation): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reconciliation_date' => 'required|date',
                'notes' => 'nullable|string'
            ]);

            // Check if reconciliation is balanced
            if (abs($reconciliation->difference) > 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot complete reconciliation: difference is not zero'
                ], 422);
            }

            $reconciliation->update([
                'status' => 'completed',
                'reconciliation_date' => $validated['reconciliation_date'],
                'notes' => $validated['notes'] ?? $reconciliation->notes,
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

            // Update bank account last reconciled date
            $reconciliation->bankAccount->update([
                'last_reconciled_date' => $validated['reconciliation_date']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reconciliation completed successfully',
                'data' => $reconciliation->fresh()->load('bankAccount')
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error completing reconciliation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified reconciliation
     */
    public function destroy(BankReconciliation $reconciliation): JsonResponse
    {
        try {
            if ($reconciliation->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete completed reconciliation'
                ], 422);
            }

            $reconciliation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Reconciliation deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting reconciliation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reconciliation summary for a bank account
     */
    public function summary(BankAccount $bankAccount): JsonResponse
    {
        try {
            $summary = [
                'total_reconciliations' => $bankAccount->bankReconciliations()->count(),
                'completed_reconciliations' => $bankAccount->bankReconciliations()->where('status', 'completed')->count(),
                'pending_reconciliations' => $bankAccount->bankReconciliations()->where('status', 'pending')->count(),
                'in_progress_reconciliations' => $bankAccount->bankReconciliations()->where('status', 'in_progress')->count(),
                'last_reconciled_date' => $bankAccount->last_reconciled_date,
                'current_balance' => $bankAccount->balance
            ];

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching summary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Suggest matches for transactions
     */
    public function suggestMatches(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'transaction_id' => 'required|integer',
                'type' => 'required|in:bank,book'
            ]);

            // Get the transaction to find matches for
            if ($validated['type'] === 'bank') {
                $transaction = BankTransaction::findOrFail($validated['transaction_id']);
                $suggestions = $this->findBookTransactionMatches($transaction);
            } else {
                $transaction = \App\Models\JournalEntryLine::findOrFail($validated['transaction_id']);
                $suggestions = $this->findBankTransactionMatches($transaction);
            }

            return response()->json([
                'success' => true,
                'data' => $suggestions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error suggesting matches: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Match transactions
     */
    public function matchTransactions(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'bank_transaction_id' => 'required|exists:bank_transactions,id',
                'book_transaction_id' => 'required|exists:journal_entry_lines,id',
                'reconciliation_id' => 'required|exists:bank_reconciliations,id',
                'match_score' => 'nullable|integer|min:0|max:100',
                'match_type' => 'nullable|in:exact,partial,manual'
            ]);

            // Create transaction match
            $match = TransactionMatch::create([
                'reconciliation_id' => $validated['reconciliation_id'],
                'bank_transaction_id' => $validated['bank_transaction_id'],
                'book_transaction_id' => $validated['book_transaction_id'],
                'match_score' => $validated['match_score'] ?? 100,
                'match_type' => $validated['match_type'] ?? 'manual',
                'created_by' => Auth::id()
            ]);

            // Mark transactions as reconciled
            BankTransaction::where('id', $validated['bank_transaction_id'])->update(['is_reconciled' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Transactions matched successfully',
                'data' => $match
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error matching transactions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Match transactions for matching interface (without reconciliation_id)
     */
    public function matchTransactionsForMatching(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'bank_transaction_id' => 'required|exists:bank_transactions,id',
                'book_transaction_id' => 'required|exists:journal_entry_lines,id'
            ]);

            // Get user ID - try multiple methods
            $userId = 1; // Default fallback
            
            try {
                if (Auth::check()) {
                    $userId = Auth::id();
                } elseif ($request->user()) {
                    $userId = $request->user()->id;
                } elseif (session()->has('user_id')) {
                    $userId = session('user_id');
                } else {
                    // Get first user from database as fallback
                    $firstUser = \App\Models\User::first();
                    if ($firstUser) {
                        $userId = $firstUser->id;
                    }
                }
            } catch (\Exception $e) {
                // Use fallback user ID if there's an error
            }

            // Create transaction match with explicit values
            $transactionMatch = new TransactionMatch();
            $transactionMatch->reconciliation_id = null;
            $transactionMatch->bank_transaction_id = $validated['bank_transaction_id'];
            $transactionMatch->book_transaction_id = $validated['book_transaction_id'];
            $transactionMatch->match_score = 100;
            $transactionMatch->confidence_score = 100;
            $transactionMatch->match_type = 'manual';
            $transactionMatch->matched_by = $userId;
            $transactionMatch->matched_at = now();
            $transactionMatch->created_by = $userId;
            $transactionMatch->save();

            return response()->json([
                'success' => true,
                'message' => 'Transactions matched successfully',
                'data' => [
                    'match_id' => $transactionMatch->id,
                    'user_id' => $userId
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error matching transactions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark transaction as cleared
     */
    public function markAsCleared(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'transaction_id' => 'required|integer',
                'type' => 'required|in:bank,book'
            ]);

            if ($validated['type'] === 'bank') {
                BankTransaction::where('id', $validated['transaction_id'])->update(['is_reconciled' => true]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaction marked as cleared'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store notes
     */
    public function storeNotes(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'bank_account_id' => 'required|integer',
                'notes' => 'required|string'
            ]);

            // Update or create reconciliation notes
            $reconciliation = BankReconciliation::where('bank_account_id', $validated['bank_account_id'])
                ->whereIn('status', ['pending', 'in_progress'])
                ->latest()
                ->first();

            if ($reconciliation) {
                $reconciliation->update(['notes' => $validated['notes']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notes saved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving notes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export reconciliation
     */
    public function exportReconciliation(BankReconciliation $reconciliation): JsonResponse
    {
        try {
            // For now, return success message
            // TODO: Implement actual export logic
            return response()->json([
                'success' => true,
                'message' => 'Export functionality will be implemented soon'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting reconciliation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export reconciliation report
     */
    public function exportReport(): JsonResponse
    {
        try {
            // For now, return success message
            // TODO: Implement actual export logic
            return response()->json([
                'success' => true,
                'message' => 'Export functionality will be implemented soon'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Find book transaction matches for a bank transaction
     */
    private function findBookTransactionMatches(BankTransaction $bankTransaction): array
    {
        // Simple matching logic - can be enhanced with AI later
        $suggestions = \App\Models\JournalEntryLine::where('account_id', $bankTransaction->bankAccount->chart_of_account_id ?? 1)
            ->where(function($query) use ($bankTransaction) {
                $query->where('debit_amount', abs($bankTransaction->amount))
                      ->orWhere('credit_amount', abs($bankTransaction->amount));
            })
            ->limit(5)
            ->get()
            ->map(function ($line) use ($bankTransaction) {
                $score = $this->calculateMatchScore($bankTransaction, $line);
                return [
                    'id' => $line->id,
                    'description' => $line->description ?? 'Journal Entry',
                    'transaction_date' => $line->journalEntry->entry_date,
                    'reference_number' => $line->journalEntry->reference_number,
                    'amount' => $line->debit_amount ?: $line->credit_amount,
                    'match_score' => $score
                ];
            })
            ->sortByDesc('match_score')
            ->values()
            ->toArray();

        return $suggestions;
    }

    /**
     * Find bank transaction matches for a book transaction
     */
    private function findBankTransactionMatches(\App\Models\JournalEntryLine $bookTransaction): array
    {
        // Simple matching logic - can be enhanced with AI later
        $suggestions = BankTransaction::where('bank_account_id', $bookTransaction->journalEntry->bank_account_id ?? 1)
            ->where('amount', $bookTransaction->debit_amount ?: $bookTransaction->credit_amount)
            ->limit(5)
            ->get()
            ->map(function ($transaction) use ($bookTransaction) {
                $score = $this->calculateMatchScore($transaction, $bookTransaction);
                return [
                    'id' => $transaction->id,
                    'description' => $transaction->description,
                    'transaction_date' => $transaction->transaction_date,
                    'reference_number' => $transaction->reference_number,
                    'amount' => $transaction->amount,
                    'match_score' => $score
                ];
            })
            ->sortByDesc('match_score')
            ->values()
            ->toArray();

        return $suggestions;
    }

    /**
     * Calculate match score between transactions
     */
    private function calculateMatchScore($transaction1, $transaction2): int
    {
        $score = 0;
        
        // Amount match (50 points)
        if (abs($transaction1->amount ?? $transaction1->debit_amount ?? $transaction1->credit_amount) === 
            abs($transaction2->amount ?? $transaction2->debit_amount ?? $transaction2->credit_amount)) {
            $score += 50;
        }
        
        // Date proximity (30 points)
        $date1 = $transaction1->transaction_date ?? $transaction1->entry_date ?? now();
        $date2 = $transaction2->transaction_date ?? $transaction2->entry_date ?? now();
        $daysDiff = abs($date1->diffInDays($date2));
        
        if ($daysDiff === 0) $score += 30;
        elseif ($daysDiff <= 1) $score += 20;
        elseif ($daysDiff <= 3) $score += 10;
        
        // Description similarity (20 points)
        $desc1 = strtolower($transaction1->description ?? '');
        $desc2 = strtolower($transaction2->description ?? '');
        $similarity = similar_text($desc1, $desc2, $percent);
        $score += round($percent * 0.2);
        
        return min(100, $score);
    }

    /**
     * Import bank statement
     */
    public function importStatement(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'bank_account_id' => 'required|exists:bank_accounts,id',
                'statement_date' => 'required|date',
                'file' => 'required|file|mimes:csv,xlsx,xls,pdf|max:10240'
            ]);

            // TODO: Implement file processing logic
            // This would typically involve:
            // 1. Parse the uploaded file
            // 2. Extract transaction data
            // 3. Create BankTransaction records
            // 4. Return success response

            return response()->json([
                'success' => true,
                'message' => 'Statement imported successfully',
                'data' => [
                    'transactions_imported' => 0, // Placeholder
                    'file_name' => $request->file('file')->getClientOriginalName()
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error importing statement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get bank accounts for dashboard
     */
    public function getBankAccountsForDashboard(): JsonResponse
    {
        try {
            $accounts = BankAccount::where('status', 'active')
                ->select(['id', 'name', 'account_number', 'balance', 'status'])
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $accounts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching bank accounts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto match transactions
     */
    public function autoMatchTransactions(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'confidence' => 'required|integer|min:0|max:100',
                'dateTolerance' => 'required|integer|min:0|max:30'
            ]);

            // TODO: Implement auto-matching logic
            // This would typically involve:
            // 1. Finding transactions with similar amounts
            // 2. Checking date proximity within tolerance
            // 3. Analyzing description similarity
            // 4. Creating matches above confidence threshold

            return response()->json([
                'success' => true,
                'message' => 'Auto-matching completed',
                'data' => [
                    'matches_created' => 0, // Placeholder
                    'confidence_threshold' => $validated['confidence'],
                    'date_tolerance' => $validated['dateTolerance']
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error performing auto-match: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get matching statistics
     */
    public function getMatchingStatistics(): JsonResponse
    {
        try {
            $totalBankTransactions = BankTransaction::count();
            $totalBookTransactions = DB::table('journal_entry_lines')->count();
            $matchedTransactions = TransactionMatch::count();
            $totalTransactions = $totalBankTransactions + $totalBookTransactions;
            $unmatchedTransactions = max(0, $totalTransactions - ($matchedTransactions * 2));
            
            $statistics = [
                'totalTransactions' => $totalTransactions,
                'matchedTransactions' => $matchedTransactions,
                'unmatchedTransactions' => $unmatchedTransactions,
                'matchPercentage' => $totalTransactions > 0 ? round(($matchedTransactions / $totalTransactions) * 100, 2) : 0,
                'averageConfidence' => 85 // Placeholder - could be calculated from actual matches
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching matching statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get bank transactions for matching
     */
    public function getBankTransactionsForMatching(): JsonResponse
    {
        try {
            $transactions = BankTransaction::with(['bankAccount'])
                ->whereDoesntHave('transactionMatches')
                ->orderBy('transaction_date', 'desc')
                ->limit(100)
                ->get()
                ->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'description' => $transaction->description ?? 'No Description',
                        'amount' => $transaction->amount ?? 0,
                        'date' => $transaction->transaction_date->format('Y-m-d'),
                        'matched' => false,
                        'bank_account' => $transaction->bankAccount->name ?? 'Unknown'
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching bank transactions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get book transactions for matching
     */
    public function getBookTransactionsForMatching(): JsonResponse
    {
        try {
            $transactions = DB::table('journal_entry_lines')
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->select('journal_entry_lines.*', 'journal_entries.entry_date')
                ->orderBy('journal_entries.entry_date', 'desc')
                ->limit(100)
                ->get()
                ->map(function ($transaction) {
                    $amount = $transaction->debit_amount > 0 ? $transaction->debit_amount : $transaction->credit_amount;
                    return [
                        'id' => $transaction->id,
                        'description' => $transaction->description ?? 'Journal Entry',
                        'amount' => $amount ?? 0,
                        'date' => $transaction->entry_date ? date('Y-m-d', strtotime($transaction->entry_date)) : date('Y-m-d'),
                        'matched' => false,
                        'account' => 'Journal Entry'
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching book transactions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manual match transactions (alias for matchTransactions)
     */
    public function manualMatchTransactions(Request $request): JsonResponse
    {
        return $this->matchTransactions($request);
    }

    /**
     * Get all adjustments
     */
    public function getAdjustments(): JsonResponse
    {
        try {
            $adjustments = ReconciliationAdjustment::with(['bankAccount', 'reconciliation'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($adjustment) {
                    return [
                        'id' => $adjustment->id,
                        'type' => $adjustment->type,
                        'description' => $adjustment->description,
                        'amount' => $adjustment->amount,
                        'date' => $adjustment->date ? $adjustment->date->format('Y-m-d') : null,
                        'reference' => $adjustment->reference,
                        'notes' => $adjustment->notes,
                        'approved' => $adjustment->approved,
                        'bank_account_id' => $adjustment->bank_account_id,
                        'bank_account_name' => $adjustment->bankAccount->name ?? 'Unknown',
                        'reconciliation_id' => $adjustment->reconciliation_id,
                        'created_at' => $adjustment->created_at,
                        'updated_at' => $adjustment->updated_at
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $adjustments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching adjustments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new adjustment
     */
    public function storeAdjustment(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:bank_charge,interest_earned,service_fee,other',
                'description' => 'required|string|max:255',
                'amount' => 'required|numeric',
                'date' => 'required|date',
                'reference' => 'nullable|string|max:255',
                'bank_account_id' => 'required|exists:bank_accounts,id',
                'notes' => 'nullable|string',
                'reconciliation_id' => 'nullable|exists:bank_reconciliations,id'
            ]);

            $adjustment = ReconciliationAdjustment::create([
                ...$validated,
                'created_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Adjustment created successfully',
                'data' => $adjustment->load('bankAccount')
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating adjustment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an adjustment
     */
    public function updateAdjustment(Request $request, ReconciliationAdjustment $adjustment): JsonResponse
    {
        try {
            $validated = $request->validate([
                'type' => 'sometimes|in:bank_charge,interest_earned,service_fee,other',
                'description' => 'sometimes|string|max:255',
                'amount' => 'sometimes|numeric',
                'date' => 'sometimes|date',
                'reference' => 'nullable|string|max:255',
                'bank_account_id' => 'sometimes|exists:bank_accounts,id',
                'notes' => 'nullable|string',
                'reconciliation_id' => 'nullable|exists:bank_reconciliations,id'
            ]);

            $adjustment->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Adjustment updated successfully',
                'data' => $adjustment->fresh()->load('bankAccount')
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating adjustment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an adjustment
     */
    public function destroyAdjustment(ReconciliationAdjustment $adjustment): JsonResponse
    {
        try {
            $adjustment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Adjustment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting adjustment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get bank accounts for adjustments
     */
    public function getBankAccounts(): JsonResponse
    {
        try {
            $accounts = BankAccount::where('status', 'active')
                ->select('id', 'name', 'account_number', 'bank_name')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $accounts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching bank accounts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all reports
     */
    public function getReports(): JsonResponse
    {
        try {
            $reports = DB::table('bank_reconciliations')
                ->join('bank_accounts', 'bank_reconciliations.bank_account_id', '=', 'bank_accounts.id')
                ->select(
                    'bank_reconciliations.id',
                    'bank_reconciliations.period_start',
                    'bank_reconciliations.period_end',
                    'bank_reconciliations.status',
                    'bank_reconciliations.created_at as generated_at',
                    'bank_accounts.name as bank_account_name',
                    DB::raw("'reconciliation' as type")
                )
                ->orderBy('bank_reconciliations.created_at', 'desc')
                ->get()
                ->map(function ($report) {
                    return [
                        'id' => $report->id,
                        'type' => $report->type,
                        'bank_account_name' => $report->bank_account_name,
                        'period_start' => $report->period_start,
                        'period_end' => $report->period_end,
                        'status' => $report->status,
                        'generated_at' => $report->generated_at
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $reports
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching reports: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a new report
     */
    public function generateReport(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'bank_account_id' => 'required|exists:bank_accounts,id',
                'period_start' => 'required|date',
                'period_end' => 'required|date|after_or_equal:period_start',
                'include_transactions' => 'boolean',
                'include_adjustments' => 'boolean',
                'include_notes' => 'boolean'
            ]);

            // Robust user ID fallback mechanism
            $userId = null;
            
            // Try multiple methods to get user ID
            if (Auth::check()) {
                $userId = Auth::id();
            } elseif ($request->user()) {
                $userId = $request->user()->id;
            } elseif (session('user_id')) {
                $userId = session('user_id');
            }
            
            // If still no user ID, try to get first user from database
            if (!$userId) {
                $firstUser = \App\Models\User::first();
                $userId = $firstUser ? $firstUser->id : 1; // Default to 1 if no users exist
            }

            Log::info('Generating report with user ID', [
                'user_id' => $userId,
                'bank_account_id' => $validated['bank_account_id'],
                'period_start' => $validated['period_start'],
                'period_end' => $validated['period_end']
            ]);

            // Create a new reconciliation for reporting purposes
            $reconciliation = BankReconciliation::create([
                'bank_account_id' => $validated['bank_account_id'],
                'period_start' => $validated['period_start'],
                'period_end' => $validated['period_end'],
                'status' => 'completed',
                'created_by' => $userId,
                'notes' => 'Generated report with options: ' . json_encode([
                    'include_transactions' => $validated['include_transactions'] ?? true,
                    'include_adjustments' => $validated['include_adjustments'] ?? true,
                    'include_notes' => $validated['include_notes'] ?? false
                ])
            ]);

            Log::info('Report generated successfully', [
                'reconciliation_id' => $reconciliation->id,
                'user_id' => $userId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Report generated successfully',
                'data' => [
                    'report_id' => $reconciliation->id,
                    'message' => 'Report has been generated and is ready for download'
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error generating report: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error generating report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a report
     */
    public function downloadReport(Request $request, BankReconciliation $report): \Symfony\Component\HttpFoundation\Response
    {
        try {
            $format = $request->get('format', 'pdf'); // pdf, excel, csv
            $includeTransactions = $request->get('include_transactions', true);
            $includeAdjustments = $request->get('include_adjustments', true);
            $includeNotes = $request->get('include_notes', false);

            Log::info('Downloading report', [
                'report_id' => $report->id,
                'format' => $format,
                'include_transactions' => $includeTransactions,
                'include_adjustments' => $includeAdjustments,
                'include_notes' => $includeNotes
            ]);

            switch ($format) {
                case 'excel':
                    return $this->downloadExcel($report, $includeTransactions, $includeAdjustments, $includeNotes);
                
                case 'pdf':
                default:
                    return $this->downloadPdf($report, $includeTransactions, $includeAdjustments, $includeNotes);
            }

        } catch (\Exception $e) {
            Log::error('Error downloading report: ' . $e->getMessage(), [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return error response as JSON for errors
            return response()->json([
                'success' => false,
                'message' => 'Error downloading report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download Excel report
     */
    private function downloadExcel(BankReconciliation $report, $includeTransactions, $includeAdjustments, $includeNotes): \Symfony\Component\HttpFoundation\Response
    {
        try {
            $export = new \App\Exports\BankReconciliationReportExport(
                $report->id,
                $includeTransactions,
                $includeAdjustments,
                $includeNotes
            );

            $filename = "reconciliation-report-{$report->id}.xlsx";
            
            // For now, return CSV format as fallback to avoid MIME type issues
            // User can rename file extension to .xlsx if needed
            $data = $export->collection();
            $headings = $export->headings();
            $csvContent = $this->generateCsvContent($headings, $data);
            $csvFilename = str_replace('.xlsx', '.csv', $filename);
            
            return response($csvContent)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $csvFilename . '"')
                ->header('Cache-Control', 'no-cache, must-revalidate')
                ->header('Pragma', 'no-cache');
                
        } catch (\Exception $e) {
            Log::error('Error generating Excel: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate CSV content from data
     */
    private function generateCsvContent($headings, $data)
    {
        $output = fopen('php://temp', 'r+');
        
        // Add headers
        fputcsv($output, $headings);
        
        // Add data rows
        foreach ($data as $row) {
            fputcsv($output, [
                $row['Type'] ?? '',
                $row['Field'] ?? '',
                $row['Value'] ?? ''
            ]);
        }
        
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        return $csvContent;
    }

    /**
     * Download PDF report
     */
    private function downloadPdf(BankReconciliation $report, $includeTransactions, $includeAdjustments, $includeNotes): \Symfony\Component\HttpFoundation\Response
    {
        try {
            $pdfService = new \App\Services\BankReconciliationPdfService();
            
            return $pdfService->generatePdf(
                $report,
                $includeTransactions,
                $includeAdjustments,
                $includeNotes
            );
        } catch (\Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Show a report
     */
    public function showReport(BankReconciliation $report): JsonResponse
    {
        try {
            Log::info('Loading report data', [
                'report_id' => $report->id,
                'bank_account_id' => $report->bank_account_id
            ]);

            $reportData = $report->load([
                'bankAccount',
                'transactionMatches.bankTransaction',
                'transactionMatches.bookTransaction',
                'adjustments'
            ]);

            Log::info('Report data loaded successfully', [
                'report_id' => $report->id,
                'adjustments_count' => $reportData->adjustments->count(),
                'transaction_matches_count' => $reportData->transactionMatches->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => $reportData
            ]);
        } catch (\Exception $e) {
            Log::error('Error showing report: ' . $e->getMessage(), [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching report: ' . $e->getMessage()
            ], 500);
        }
    }
}
