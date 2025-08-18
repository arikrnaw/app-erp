<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FollowUpController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = FollowUp::with(['assignedUser', 'company', 'prospect', 'customer'])
            ->where('company_id', Auth::user()->company_id);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        // Filter by assigned user
        if ($request->has('assigned_to') && $request->assigned_to !== '') {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by prospect
        if ($request->has('prospect_id') && $request->prospect_id !== '') {
            $query->where('prospect_id', $request->prospect_id);
        }

        // Filter by customer
        if ($request->has('customer_id') && $request->customer_id !== '') {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by scheduled date range
        if ($request->has('scheduled_from') && $request->scheduled_from !== '') {
            $query->where('scheduled_date', '>=', $request->scheduled_from);
        }

        if ($request->has('scheduled_to') && $request->scheduled_to !== '') {
            $query->where('scheduled_date', '<=', $request->scheduled_to);
        }

        // Search by subject
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where('subject', 'like', "%{$search}%");
        }

        // Sort
        $sortBy = $request->get('sort_by', 'scheduled_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $followUps = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $followUps->items(),
            'pagination' => [
                'current_page' => $followUps->currentPage(),
                'last_page' => $followUps->lastPage(),
                'per_page' => $followUps->perPage(),
                'total' => $followUps->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prospect_id' => 'nullable|exists:prospects,id',
            'customer_id' => 'nullable|exists:customers,id',
            'type' => 'required|in:call,email,meeting,presentation,demo,proposal,other',
            'method' => 'required|in:phone,email,in_person,video_call,other',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled,no_show',
            'scheduled_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'outcome' => 'nullable|in:positive,neutral,negative,no_response',
            'next_action' => 'nullable|string|max:255',
            'next_follow_up_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Ensure either prospect_id or customer_id is provided
        if (!$request->prospect_id && !$request->customer_id) {
            return response()->json(['errors' => ['prospect_id' => ['Either prospect or customer must be selected.']]], 422);
        }

        $followUp = FollowUp::create([
            'company_id' => Auth::user()->company_id,
            'prospect_id' => $request->prospect_id,
            'customer_id' => $request->customer_id,
            'type' => $request->type,
            'method' => $request->method,
            'subject' => $request->subject,
            'description' => $request->description,
            'status' => $request->status,
            'scheduled_date' => $request->scheduled_date,
            'assigned_to' => $request->assigned_to,
            'notes' => $request->notes,
            'outcome' => $request->outcome,
            'next_action' => $request->next_action,
            'next_follow_up_date' => $request->next_follow_up_date,
        ]);

        $followUp->load(['assignedUser', 'company', 'prospect', 'customer']);

        return response()->json([
            'message' => 'Follow-up created successfully',
            'data' => $followUp,
        ], 201);
    }

    public function show(FollowUp $followUp): JsonResponse
    {
        if ($followUp->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $followUp->load(['assignedUser', 'company', 'prospect', 'customer']);

        return response()->json(['data' => $followUp]);
    }

    public function update(Request $request, FollowUp $followUp): JsonResponse
    {
        if ($followUp->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'prospect_id' => 'nullable|exists:prospects,id',
            'customer_id' => 'nullable|exists:customers,id',
            'type' => 'sometimes|required|in:call,email,meeting,presentation,demo,proposal,other',
            'method' => 'sometimes|required|in:phone,email,in_person,video_call,other',
            'subject' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:scheduled,in_progress,completed,cancelled,no_show',
            'scheduled_date' => 'nullable|date',
            'completed_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'outcome' => 'nullable|in:positive,neutral,negative,no_response',
            'next_action' => 'nullable|string|max:255',
            'next_follow_up_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $followUp->update($request->only([
            'prospect_id', 'customer_id', 'type', 'method', 'subject', 'description',
            'status', 'scheduled_date', 'completed_date', 'assigned_to', 'notes',
            'outcome', 'next_action', 'next_follow_up_date'
        ]));

        $followUp->load(['assignedUser', 'company', 'prospect', 'customer']);

        return response()->json([
            'message' => 'Follow-up updated successfully',
            'data' => $followUp,
        ]);
    }

    public function destroy(FollowUp $followUp): JsonResponse
    {
        if ($followUp->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $followUp->delete();

        return response()->json(['message' => 'Follow-up deleted successfully']);
    }

    public function complete(Request $request, FollowUp $followUp): JsonResponse
    {
        if ($followUp->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'outcome' => 'required|in:positive,neutral,negative,no_response',
            'notes' => 'nullable|string',
            'next_action' => 'nullable|string|max:255',
            'next_follow_up_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $followUp->update([
            'status' => 'completed',
            'completed_date' => now(),
            'outcome' => $request->outcome,
            'notes' => $request->notes,
            'next_action' => $request->next_action,
            'next_follow_up_date' => $request->next_follow_up_date,
        ]);

        $followUp->load(['assignedUser', 'company', 'prospect', 'customer']);

        return response()->json([
            'message' => 'Follow-up completed successfully',
            'data' => $followUp,
        ]);
    }

    public function statistics(): JsonResponse
    {
        $companyId = Auth::user()->company_id;

        $stats = DB::table('follow_ups')
            ->where('company_id', $companyId)
            ->selectRaw('
                COUNT(*) as total_follow_ups,
                COUNT(CASE WHEN status = "scheduled" THEN 1 END) as scheduled_follow_ups,
                COUNT(CASE WHEN status = "in_progress" THEN 1 END) as in_progress_follow_ups,
                COUNT(CASE WHEN status = "completed" THEN 1 END) as completed_follow_ups,
                COUNT(CASE WHEN status = "cancelled" THEN 1 END) as cancelled_follow_ups,
                COUNT(CASE WHEN outcome = "positive" THEN 1 END) as positive_outcomes,
                COUNT(CASE WHEN outcome = "neutral" THEN 1 END) as neutral_outcomes,
                COUNT(CASE WHEN outcome = "negative" THEN 1 END) as negative_outcomes
            ')
            ->first();

        return response()->json(['data' => $stats]);
    }

    public function getUpcoming(): JsonResponse
    {
        $followUps = FollowUp::with(['assignedUser', 'prospect', 'customer'])
            ->where('company_id', Auth::user()->company_id)
            ->where('status', 'scheduled')
            ->where('scheduled_date', '>=', now())
            ->orderBy('scheduled_date', 'asc')
            ->limit(10)
            ->get();

        return response()->json(['data' => $followUps]);
    }

    public function getOverdue(): JsonResponse
    {
        $followUps = FollowUp::with(['assignedUser', 'prospect', 'customer'])
            ->where('company_id', Auth::user()->company_id)
            ->where('status', 'scheduled')
            ->where('scheduled_date', '<', now())
            ->orderBy('scheduled_date', 'asc')
            ->get();

        return response()->json(['data' => $followUps]);
    }

    public function getAssignedUsers(): JsonResponse
    {
        $users = User::where('company_id', Auth::user()->company_id)
            ->select('id', 'name', 'email')
            ->get();

        return response()->json(['data' => $users]);
    }
}

