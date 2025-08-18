<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankTransaction;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BankTransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BankTransaction::with(['bankAccount']);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('bankAccount', function ($sq) use ($search) {
                      $sq->where('account_name', 'like', "%{$search}%")
                         ->orWhere('bank_name', 'like', "%{$search}%");
                  });
            });
        }

        // Bank account filter
        if ($request->has('bank_account_id')) {
            $query->where('bank_account_id', $request->bank_account_id);
        }

        // Reconciliation status filter
        if ($request->has('is_reconciled') && $request->is_reconciled !== 'all') {
            $query->where('is_reconciled', $request->is_reconciled === 'true');
        }

        // Date filter
        if ($request->has('date')) {
            $query->whereDate('transaction_date', $request->date);
        }

        // Date range filter
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
        }

        $bankTransactions = $query->orderBy('transaction_date', 'desc')->paginate(15);

        return response()->json($bankTransactions);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'transaction_date' => 'required|date',
            'description' => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'debit_amount' => 'required_without:credit_amount|numeric|min:0',
            'credit_amount' => 'required_without:debit_amount|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Ensure only one of debit or credit amount is provided
        if ($request->debit_amount > 0 && $request->credit_amount > 0) {
            return response()->json(['message' => 'Cannot have both debit and credit amounts'], 422);
        }

        try {
            DB::beginTransaction();

            // Generate transaction number
            $transactionNumber = 'BANK-' . date('Y') . '-' . str_pad(BankTransaction::whereYear('created_at', date('Y'))->count() + 1, 4, '0', STR_PAD_LEFT);

            $bankAccount = BankAccount::findOrFail($request->bank_account_id);
            
            // Calculate new balance
            $debitAmount = $request->debit_amount ?? 0;
            $creditAmount = $request->credit_amount ?? 0;
            $newBalance = $bankAccount->current_balance + $creditAmount - $debitAmount;

            $bankTransaction = BankTransaction::create([
                'transaction_number' => $transactionNumber,
                'bank_account_id' => $request->bank_account_id,
                'transaction_date' => $request->transaction_date,
                'description' => $request->description,
                'reference_number' => $request->reference_number,
                'debit_amount' => $debitAmount,
                'credit_amount' => $creditAmount,
                'balance' => $newBalance,
                'notes' => $request->notes,
            ]);

            // Update bank account balance
            $bankAccount->update(['current_balance' => $newBalance]);

            DB::commit();

            $bankTransaction->load('bankAccount');

            return response()->json([
                'message' => 'Bank transaction created successfully',
                'bank_transaction' => $bankTransaction
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating bank transaction: ' . $e->getMessage()], 500);
        }
    }

    public function show(BankTransaction $bankTransaction): JsonResponse
    {
        $bankTransaction->load('bankAccount');
        return response()->json(['bank_transaction' => $bankTransaction]);
    }

    public function update(Request $request, BankTransaction $bankTransaction): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'transaction_date' => 'required|date',
            'description' => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'debit_amount' => 'required_without:credit_amount|numeric|min:0',
            'credit_amount' => 'required_without:debit_amount|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Ensure only one of debit or credit amount is provided
        if ($request->debit_amount > 0 && $request->credit_amount > 0) {
            return response()->json(['message' => 'Cannot have both debit and credit amounts'], 422);
        }

        try {
            DB::beginTransaction();

            $oldBankAccount = $bankTransaction->bankAccount;
            $newBankAccount = BankAccount::findOrFail($request->bank_account_id);

            // Revert old transaction from old account balance
            $oldBankAccount->update([
                'current_balance' => $oldBankAccount->current_balance - $bankTransaction->credit_amount + $bankTransaction->debit_amount
            ]);

            // Calculate new balance for new account
            $debitAmount = $request->debit_amount ?? 0;
            $creditAmount = $request->credit_amount ?? 0;
            $newBalance = $newBankAccount->current_balance + $creditAmount - $debitAmount;

            $bankTransaction->update([
                'bank_account_id' => $request->bank_account_id,
                'transaction_date' => $request->transaction_date,
                'description' => $request->description,
                'reference_number' => $request->reference_number,
                'debit_amount' => $debitAmount,
                'credit_amount' => $creditAmount,
                'balance' => $newBalance,
                'notes' => $request->notes,
            ]);

            // Update new bank account balance
            $newBankAccount->update(['current_balance' => $newBalance]);

            DB::commit();

            $bankTransaction->load('bankAccount');

            return response()->json([
                'message' => 'Bank transaction updated successfully',
                'bank_transaction' => $bankTransaction
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating bank transaction: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(BankTransaction $bankTransaction): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Revert transaction from bank account balance
            $bankAccount = $bankTransaction->bankAccount;
            $bankAccount->update([
                'current_balance' => $bankAccount->current_balance - $bankTransaction->credit_amount + $bankTransaction->debit_amount
            ]);

            $bankTransaction->delete();

            DB::commit();

            return response()->json(['message' => 'Bank transaction deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error deleting bank transaction: ' . $e->getMessage()], 500);
        }
    }

    public function getBankAccounts(): JsonResponse
    {
        $bankAccounts = BankAccount::where('is_active', true)
            ->orderBy('bank_name')
            ->orderBy('account_name')
            ->get();
        return response()->json(['bank_accounts' => $bankAccounts]);
    }

    public function getUnreconciled(Request $request): JsonResponse
    {
        $query = BankTransaction::with('bankAccount')
            ->where('is_reconciled', false);

        if ($request->has('bank_account_id')) {
            $query->where('bank_account_id', $request->bank_account_id);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();

        return response()->json(['transactions' => $transactions]);
    }
}
