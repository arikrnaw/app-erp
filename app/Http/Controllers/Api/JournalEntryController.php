<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = JournalEntry::with(['lines.account', 'created_by_user'])
            ->where('company_id', Auth::user()->company_id);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('entry_number', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->has('date') && $request->date) {
            $query->where('entry_date', $request->date);
        }

        $entries = $query->orderBy('entry_date', 'desc')
                        ->orderBy('id', 'desc')
                        ->paginate(15);

        return response()->json($entries);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'entry_date' => 'required|date',
            'reference_type' => 'nullable|string|max:50',
            'reference_id' => 'nullable|integer',
            'description' => 'nullable|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.description' => 'nullable|string',
            'lines.*.debit_amount' => 'required_without:lines.*.credit_amount|numeric|min:0',
            'lines.*.credit_amount' => 'required_without:lines.*.debit_amount|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate that debit equals credit
        $totalDebit = collect($request->lines)->sum('debit_amount');
        $totalCredit = collect($request->lines)->sum('credit_amount');

        if ($totalDebit != $totalCredit) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => ['lines' => ['Total debit must equal total credit']]
            ], 422);
        }

        try {
            DB::beginTransaction();

            $entry = JournalEntry::create([
                'company_id' => Auth::user()->company_id,
                'entry_number' => JournalEntry::generateEntryNumber(Auth::user()->company_id),
                'entry_date' => $request->entry_date,
                'reference_type' => $request->reference_type,
                'reference_id' => $request->reference_id,
                'description' => $request->description,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            // Create journal entry lines
            foreach ($request->lines as $index => $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit_amount' => $line['debit_amount'] ?? 0,
                    'credit_amount' => $line['credit_amount'] ?? 0,
                    'line_number' => $index + 1,
                ]);
            }

            DB::commit();

            $entry->load(['lines.account', 'created_by_user']);

            return response()->json([
                'message' => 'Journal entry created successfully',
                'journal_entry' => $entry
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create journal entry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(JournalEntry $journalEntry): JsonResponse
    {
        // Check if the entry belongs to the user's company
        if ($journalEntry->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Journal entry not found'], 404);
        }

        $journalEntry->load(['lines.account', 'created_by_user']);

        return response()->json([
            'data' => $journalEntry
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        // Check if the entry belongs to the user's company
        if ($journalEntry->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Journal entry not found'], 404);
        }

        // Only allow updates for draft entries
        if ($journalEntry->status !== 'draft') {
            return response()->json([
                'message' => 'Cannot update posted or cancelled journal entry'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'entry_date' => 'required|date',
            'reference_type' => 'nullable|string|max:50',
            'reference_id' => 'nullable|integer',
            'description' => 'nullable|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.description' => 'nullable|string',
            'lines.*.debit_amount' => 'required_without:lines.*.credit_amount|numeric|min:0',
            'lines.*.credit_amount' => 'required_without:lines.*.debit_amount|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate that debit equals credit
        $totalDebit = collect($request->lines)->sum('debit_amount');
        $totalCredit = collect($request->lines)->sum('credit_amount');

        if ($totalDebit != $totalCredit) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => ['lines' => ['Total debit must equal total credit']]
            ], 422);
        }

        try {
            DB::beginTransaction();

            $journalEntry->update([
                'entry_date' => $request->entry_date,
                'reference_type' => $request->reference_type,
                'reference_id' => $request->reference_id,
                'description' => $request->description,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            // Delete existing lines
            $journalEntry->lines()->delete();

            // Create new journal entry lines
            foreach ($request->lines as $index => $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit_amount' => $line['debit_amount'] ?? 0,
                    'credit_amount' => $line['credit_amount'] ?? 0,
                    'line_number' => $index + 1,
                ]);
            }

            DB::commit();

            $journalEntry->load(['lines.account', 'created_by_user']);

            return response()->json([
                'message' => 'Journal entry updated successfully',
                'journal_entry' => $journalEntry
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update journal entry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JournalEntry $journalEntry): JsonResponse
    {
        // Check if the entry belongs to the user's company
        if ($journalEntry->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Journal entry not found'], 404);
        }

        // Only allow deletion for draft entries
        if ($journalEntry->status !== 'draft') {
            return response()->json([
                'message' => 'Cannot delete posted or cancelled journal entry'
            ], 422);
        }

        $journalEntry->delete();

        return response()->json([
            'message' => 'Journal entry deleted successfully'
        ]);
    }

    /**
     * Post a journal entry.
     */
    public function post(JournalEntry $journalEntry): JsonResponse
    {
        // Check if the entry belongs to the user's company
        if ($journalEntry->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Journal entry not found'], 404);
        }

        // Only allow posting draft entries
        if ($journalEntry->status !== 'draft') {
            return response()->json([
                'message' => 'Only draft entries can be posted'
            ], 422);
        }

        // Validate that entry is balanced
        if (!$journalEntry->isBalanced()) {
            return response()->json([
                'message' => 'Journal entry must be balanced before posting'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $journalEntry->update([
                'status' => 'posted',
                'posted_at' => now(),
            ]);

            // Update account balances
            foreach ($journalEntry->lines as $line) {
                $account = $line->account;
                if ($line->debit_amount > 0) {
                    $account->increment('balance', $line->debit_amount);
                } else {
                    $account->decrement('balance', $line->credit_amount);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Journal entry posted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to post journal entry',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
