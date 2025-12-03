<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Finance\BankAccount;
use App\Exports\BankAccountExport;
use App\Exports\BankAccountTemplateExport;
use App\Imports\BankAccountImport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('bank_name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Account type filter
        if ($request->has('account_type') && $request->account_type !== 'all') {
            $query->where('account_type', $request->account_type);
        }

        $bankAccounts = $query->orderBy('bank_name')->orderBy('name')->paginate(15);

        return response()->json($bankAccounts);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'account_number' => 'required|string|max:100|unique:bank_accounts,account_number',
            'description' => 'nullable|string|max:1000',
            'bank_name' => 'required|string|max:255',
            'bank_branch' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:11',
            'iban' => 'nullable|string|max:50',
            'currency' => 'required|string|max:3',
            'opening_balance' => 'nullable|numeric|min:0',
            'opening_date' => 'nullable|date',
            'account_type' => 'required|in:checking,savings,time_deposit,investment',
            'status' => 'nullable|in:active,inactive',
            'reconcile_automatically' => 'boolean',
            'allow_overdraft' => 'boolean',
            'include_in_cash_flow' => 'boolean',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $bankAccount = BankAccount::create([
                'name' => $request->name,
                'account_number' => $request->account_number,
                'description' => $request->description,
                'bank_name' => $request->bank_name,
                'bank_branch' => $request->bank_branch,
                'swift_code' => $request->swift_code,
                'iban' => $request->iban,
                'currency' => $request->currency,
                'opening_balance' => $request->opening_balance ?? 0,
                'opening_date' => $request->opening_date,
                'account_type' => $request->account_type,
                'status' => $request->status ?? 'active',
                'reconcile_automatically' => $request->reconcile_automatically ?? false,
                'allow_overdraft' => $request->allow_overdraft ?? false,
                'include_in_cash_flow' => $request->include_in_cash_flow ?? true,
                'notes' => $request->notes,
                'balance' => $request->opening_balance ?? 0,
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
            'name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_type' => 'required|in:checking,savings,credit',
            'opening_balance' => 'required|numeric|min:0',
            'balance' => 'required|numeric',
            'status' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $bankAccount->update([
                'account_number' => $request->account_number,
                'name' => $request->name,
                'bank_name' => $request->bank_name,
                'account_type' => $request->account_type,
                'opening_balance' => $request->opening_balance,
                'balance' => $request->balance,
                'status' => $request->status,
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
        $bankAccounts = BankAccount::where('status', 'active')
            ->orderBy('bank_name')
            ->orderBy('name')
            ->get();
        return response()->json(['bank_accounts' => $bankAccounts]);
    }

    public function getBalance(BankAccount $bankAccount): JsonResponse
    {
        $currentBalance = $bankAccount->balance;
        $unreconciledTransactions = $bankAccount->bankTransactions()
            ->where('is_reconciled', false)
            ->get();

        $unreconciledDebits = $unreconciledTransactions->sum('debit_amount');
        $unreconciledCredits = $unreconciledTransactions->sum('credit_amount');

        return response()->json([
            'balance' => $currentBalance,
            'unreconciled_debits' => $unreconciledDebits,
            'unreconciled_credits' => $unreconciledCredits,
            'book_balance' => $currentBalance + $unreconciledCredits - $unreconciledDebits,
            'unreconciled_transactions_count' => $unreconciledTransactions->count()
        ]);
    }

    /**
     * Export bank accounts to Excel
     */
    public function export(Request $request): BinaryFileResponse
    {
        $filters = $request->only(['search', 'status', 'account_type']);
        
        $fileName = 'bank_accounts_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new BankAccountExport($filters), $fileName);
    }

    /**
     * Import bank accounts from Excel
     */
    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240' // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $import = new BankAccountImport();
            Excel::import($import, $request->file('file'));

            DB::commit();

            return response()->json([
                'message' => 'Bank accounts imported successfully',
                'imported_count' => $import->getImportedCount(),
                'skipped_count' => $import->getSkippedCount(),
                'errors' => $import->getErrors()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error importing bank accounts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download template for import
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        $fileName = 'bank_accounts_template_' . now()->format('Y-m-d') . '.xlsx';
        
        return Excel::download(new BankAccountTemplateExport(), $fileName);
    }
}
