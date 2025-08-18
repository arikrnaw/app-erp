<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prospect;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProspectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Prospect::with(['assignedUser', 'company'])
            ->where('company_id', Auth::user()->company_id);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority !== '') {
            $query->where('priority', $request->priority);
        }

        // Filter by assigned user
        if ($request->has('assigned_to') && $request->assigned_to !== '') {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by source
        if ($request->has('source') && $request->source !== '') {
            $query->where('source', $request->source);
        }

        // Search by name, email, or company name
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $prospects = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $prospects->items(),
            'pagination' => [
                'current_page' => $prospects->currentPage(),
                'last_page' => $prospects->lastPage(),
                'per_page' => $prospects->perPage(),
                'total' => $prospects->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'status' => 'required|in:new,contacted,qualified,proposal,negotiation,won,lost',
            'priority' => 'required|in:low,medium,high,urgent',
            'estimated_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'next_follow_up_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $prospect = Prospect::create([
            'company_id' => Auth::user()->company_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company_name' => $request->company_name,
            'position' => $request->position,
            'industry' => $request->industry,
            'source' => $request->source,
            'status' => $request->status,
            'priority' => $request->priority,
            'estimated_value' => $request->estimated_value,
            'notes' => $request->notes,
            'assigned_to' => $request->assigned_to,
            'next_follow_up_date' => $request->next_follow_up_date,
        ]);

        $prospect->load(['assignedUser', 'company']);

        return response()->json([
            'message' => 'Prospect created successfully',
            'data' => $prospect,
        ], 201);
    }

    public function show(Prospect $prospect): JsonResponse
    {
        if ($prospect->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $prospect->load(['assignedUser', 'company', 'followUps', 'activities']);

        return response()->json(['data' => $prospect]);
    }

    public function update(Request $request, Prospect $prospect): JsonResponse
    {
        if ($prospect->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'status' => 'sometimes|required|in:new,contacted,qualified,proposal,negotiation,won,lost',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'estimated_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'next_follow_up_date' => 'nullable|date',
            'last_contact_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $prospect->update($request->only([
            'name', 'email', 'phone', 'company_name', 'position', 'industry',
            'source', 'status', 'priority', 'estimated_value', 'notes',
            'assigned_to', 'next_follow_up_date', 'last_contact_date'
        ]));

        $prospect->load(['assignedUser', 'company']);

        return response()->json([
            'message' => 'Prospect updated successfully',
            'data' => $prospect,
        ]);
    }

    public function destroy(Prospect $prospect): JsonResponse
    {
        if ($prospect->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $prospect->delete();

        return response()->json(['message' => 'Prospect deleted successfully']);
    }

    public function statistics(): JsonResponse
    {
        $companyId = Auth::user()->company_id;

        $stats = DB::table('prospects')
            ->where('company_id', $companyId)
            ->selectRaw('
                COUNT(*) as total_prospects,
                COUNT(CASE WHEN status = "new" THEN 1 END) as new_prospects,
                COUNT(CASE WHEN status = "contacted" THEN 1 END) as contacted_prospects,
                COUNT(CASE WHEN status = "qualified" THEN 1 END) as qualified_prospects,
                COUNT(CASE WHEN status = "proposal" THEN 1 END) as proposal_prospects,
                COUNT(CASE WHEN status = "negotiation" THEN 1 END) as negotiation_prospects,
                COUNT(CASE WHEN status = "won" THEN 1 END) as won_prospects,
                COUNT(CASE WHEN status = "lost" THEN 1 END) as lost_prospects,
                SUM(estimated_value) as total_estimated_value
            ')
            ->first();

        return response()->json(['data' => $stats]);
    }

    public function getAssignedUsers(): JsonResponse
    {
        $users = User::where('company_id', Auth::user()->company_id)
            ->select('id', 'name', 'email')
            ->get();

        return response()->json(['data' => $users]);
    }

    public function getSources(): JsonResponse
    {
        $sources = Prospect::where('company_id', Auth::user()->company_id)
            ->whereNotNull('source')
            ->distinct()
            ->pluck('source');

        return response()->json(['data' => $sources]);
    }

    public function getIndustries(): JsonResponse
    {
        $industries = Prospect::where('company_id', Auth::user()->company_id)
            ->whereNotNull('industry')
            ->distinct()
            ->pluck('industry');

        return response()->json(['data' => $industries]);
    }
}
