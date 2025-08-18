<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ChartOfAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ChartOfAccount::with(['parent', 'children', 'created_by_user'])
            ->where('company_id', Auth::user()->company_id);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('account_code', 'ilike', "%{$search}%")
                  ->orWhere('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->has('type') && $request->type && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $accounts = $query->orderBy('account_code')->paginate(15);

        return response()->json($accounts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_code' => 'required|string|max:50|unique:chart_of_accounts,account_code,NULL,id,company_id,' . Auth::user()->company_id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'balance' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $account = ChartOfAccount::create([
            'company_id' => Auth::user()->company_id,
            'account_code' => $request->account_code,
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'parent_id' => $request->parent_id ?: null,
            'balance' => $request->balance ?: 0,
            'status' => $request->status,
            'created_by' => Auth::id(),
        ]);

        $account->load(['parent', 'children', 'created_by_user']);

        return response()->json([
            'message' => 'Chart of account created successfully',
            'chart_of_account' => $account
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ChartOfAccount $chartOfAccount): JsonResponse
    {
        // Check if the account belongs to the user's company
        if ($chartOfAccount->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        $chartOfAccount->load(['parent', 'children', 'created_by_user']);

        return response()->json([
            'data' => $chartOfAccount
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChartOfAccount $chartOfAccount): JsonResponse
    {
        // Check if the account belongs to the user's company
        if ($chartOfAccount->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'account_code' => 'required|string|max:50|unique:chart_of_accounts,account_code,' . $chartOfAccount->id . ',id,company_id,' . Auth::user()->company_id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Prevent circular reference
        if ($request->parent_id == $chartOfAccount->id) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => ['parent_id' => ['An account cannot be its own parent']]
            ], 422);
        }

        $chartOfAccount->update([
            'account_code' => $request->account_code,
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'parent_id' => $request->parent_id ?: null,
            'status' => $request->status,
        ]);

        $chartOfAccount->load(['parent', 'children', 'created_by_user']);

        return response()->json([
            'message' => 'Chart of account updated successfully',
            'chart_of_account' => $chartOfAccount
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChartOfAccount $chartOfAccount): JsonResponse
    {
        // Check if the account belongs to the user's company
        if ($chartOfAccount->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        // Check if account has children
        if ($chartOfAccount->children()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete account with child accounts'
            ], 422);
        }

        // Check if account has transactions (you might want to add this check)
        // if ($chartOfAccount->journalEntries()->count() > 0) {
        //     return response()->json([
        //         'message' => 'Cannot delete account with existing transactions'
        //     ], 422);
        // }

        $chartOfAccount->delete();

        return response()->json([
            'message' => 'Chart of account deleted successfully'
        ]);
    }
}
