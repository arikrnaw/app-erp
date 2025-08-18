<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BankAccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BankAccount::query();

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")
                  ->orWhere('account_name', 'like', "%{$search}%")
                  ->orWhere('bank_name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('is_active') && $request->is_active !== 'all') {
            $query->where('is_active', $request->is_active === 'true');
        }

        // Account type filter
        if ($request->has('account_type') && $request->account_type !== 'all') {
            $query->where('account_type', $request->account_type);
        }

        $bankAccounts = $query->orderBy('bank_name')->orderBy('account_name')->paginate(15);

        return response()->json($bankAccounts);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_number' => 'required|string|max:255|unique:bank_accounts,account_number',
            'account_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_type' => 'required|in:checking,savings,credit',
            'opening_balance' => 'required|numeric|min:0',
            'current_balance' => 'required|numeric',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $bankAccount = BankAccount::create([
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
                'bank_name' => $request->bank_name,
                'account_type' => $request->account_type,
                'opening_balance' => $request->opening_balance,
                'current_balance' => $request->current_balance,
                'is_active' => $request->is_active ?? true,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Bank account created successfully',
                'bank_account' => $bankAccount
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating bank account: ' . $e->getMessage()], 500);
        }
    }

    public function show(BankAccount $bankAccount): JsonResponse
    {
        $bankAccount->load(['bankTransactions' => function ($query) {
            $query->orderBy('transaction_date', 'desc')->limit(10);
        }]);

        return response()->json(['bank_account' => $bankAccount]);
    }

    public function update(Request $request, BankAccount $bankAccount): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_number' => 'required|string|max:255|unique:bank_accounts,account_number,' . $bankAccount->id,
            'account_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_type' => 'required|in:checking,savings,credit',
            'opening_balance' => 'required|numeric|min:0',
            'current_balance' => 'required|numeric',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $bankAccount->update([
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
                'bank_name' => $request->bank_name,
                'account_type' => $request->account_type,
                'opening_balance' => $request->opening_balance,
                'current_balance' => $request->current_balance,
                'is_active' => $request->is_active,
            ]);

            return response()->json([
                'message' => 'Bank account updated successfully',
                'bank_account' => $bankAccount
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating bank account: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(BankAccount $bankAccount): JsonResponse
    {
        try {
            // Check if bank account has transactions
            if ($bankAccount->bankTransactions()->exists()) {
                return response()->json(['message' => 'Cannot delete bank account that has transactions'], 422);
            }

            // Check if bank account has reconciliations
            if ($bankAccount->bankReconciliations()->exists()) {
                return response()->json(['message' => 'Cannot delete bank account that has reconciliations'], 422);
            }

            $bankAccount->delete();
            return response()->json(['message' => 'Bank account deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting bank account: ' . $e->getMessage()], 500);
        }
    }

    public function getActive(): JsonResponse
    {
        $bankAccounts = BankAccount::where('is_active', true)
            ->orderBy('bank_name')
            ->orderBy('account_name')
            ->get();
        return response()->json(['bank_accounts' => $bankAccounts]);
    }

    public function getBalance(BankAccount $bankAccount): JsonResponse
    {
        $currentBalance = $bankAccount->current_balance;
        $unreconciledTransactions = $bankAccount->bankTransactions()
            ->where('is_reconciled', false)
            ->get();

        $unreconciledDebits = $unreconciledTransactions->sum('debit_amount');
        $unreconciledCredits = $unreconciledTransactions->sum('credit_amount');

        return response()->json([
            'current_balance' => $currentBalance,
            'unreconciled_debits' => $unreconciledDebits,
            'unreconciled_credits' => $unreconciledCredits,
            'book_balance' => $currentBalance + $unreconciledCredits - $unreconciledDebits,
            'unreconciled_transactions_count' => $unreconciledTransactions->count()
        ]);
    }
}
