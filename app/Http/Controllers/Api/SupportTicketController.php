<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SupportTicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = SupportTicket::with(['assignedUser', 'company', 'customer', 'createdByUser'])
            ->where('company_id', Auth::user()->company_id);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority !== '') {
            $query->where('priority', $request->priority);
        }

        // Filter by category
        if ($request->has('category') && $request->category !== '') {
            $query->where('category', $request->category);
        }

        // Filter by assigned user
        if ($request->has('assigned_to') && $request->assigned_to !== '') {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by customer
        if ($request->has('customer_id') && $request->customer_id !== '') {
            $query->where('customer_id', $request->customer_id);
        }

        // Search by ticket number, subject, or customer name
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $tickets = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $tickets->items(),
            'pagination' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:open,in_progress,waiting_for_customer,resolved,closed',
            'category' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'estimated_resolution_time' => 'nullable|integer|min:1',
            'internal_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Generate ticket number
        $ticketNumber = 'TKT-' . date('Y') . '-' . str_pad(SupportTicket::whereYear('created_at', date('Y'))->count() + 1, 4, '0', STR_PAD_LEFT);

        $ticket = SupportTicket::create([
            'company_id' => Auth::user()->company_id,
            'ticket_number' => $ticketNumber,
            'customer_id' => $request->customer_id,
            'subject' => $request->subject,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => $request->status,
            'category' => $request->category,
            'assigned_to' => $request->assigned_to,
            'created_by' => Auth::id(),
            'due_date' => $request->due_date,
            'estimated_resolution_time' => $request->estimated_resolution_time,
            'internal_notes' => $request->internal_notes,
        ]);

        $ticket->load(['assignedUser', 'company', 'customer', 'createdByUser']);

        return response()->json([
            'message' => 'Support ticket created successfully',
            'data' => $ticket,
        ], 201);
    }

    public function show(SupportTicket $supportTicket): JsonResponse
    {
        if ($supportTicket->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $supportTicket->load([
            'assignedUser', 'company', 'customer', 'createdByUser', 'resolvedByUser',
            'responses.createdByUser', 'attachments'
        ]);

        return response()->json(['data' => $supportTicket]);
    }

    public function update(Request $request, SupportTicket $supportTicket): JsonResponse
    {
        if ($supportTicket->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'customer_id' => 'sometimes|required|exists:customers,id',
            'subject' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'status' => 'sometimes|required|in:open,in_progress,waiting_for_customer,resolved,closed',
            'category' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'estimated_resolution_time' => 'nullable|integer|min:1',
            'internal_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $supportTicket->update($request->only([
            'customer_id', 'subject', 'description', 'priority', 'status', 'category',
            'assigned_to', 'due_date', 'estimated_resolution_time', 'internal_notes'
        ]));

        $supportTicket->load(['assignedUser', 'company', 'customer', 'createdByUser']);

        return response()->json([
            'message' => 'Support ticket updated successfully',
            'data' => $supportTicket,
        ]);
    }

    public function destroy(SupportTicket $supportTicket): JsonResponse
    {
        if ($supportTicket->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $supportTicket->delete();

        return response()->json(['message' => 'Support ticket deleted successfully']);
    }

    public function resolve(Request $request, SupportTicket $supportTicket): JsonResponse
    {
        if ($supportTicket->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'customer_satisfaction_rating' => 'nullable|integer|min:1|max:5',
            'internal_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $supportTicket->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => Auth::id(),
            'actual_resolution_time' => $supportTicket->created_at->diffInMinutes(now()),
            'customer_satisfaction_rating' => $request->customer_satisfaction_rating,
            'internal_notes' => $request->internal_notes,
        ]);

        $supportTicket->load(['assignedUser', 'company', 'customer', 'createdByUser', 'resolvedByUser']);

        return response()->json([
            'message' => 'Support ticket resolved successfully',
            'data' => $supportTicket,
        ]);
    }

    public function close(SupportTicket $supportTicket): JsonResponse
    {
        if ($supportTicket->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $supportTicket->update([
            'status' => 'closed',
        ]);

        $supportTicket->load(['assignedUser', 'company', 'customer', 'createdByUser']);

        return response()->json([
            'message' => 'Support ticket closed successfully',
            'data' => $supportTicket,
        ]);
    }

    public function statistics(): JsonResponse
    {
        $companyId = Auth::user()->company_id;

        $stats = DB::table('support_tickets')
            ->where('company_id', $companyId)
            ->selectRaw('
                COUNT(*) as total_tickets,
                COUNT(CASE WHEN status = "open" THEN 1 END) as open_tickets,
                COUNT(CASE WHEN status = "in_progress" THEN 1 END) as in_progress_tickets,
                COUNT(CASE WHEN status = "waiting_for_customer" THEN 1 END) as waiting_tickets,
                COUNT(CASE WHEN status = "resolved" THEN 1 END) as resolved_tickets,
                COUNT(CASE WHEN status = "closed" THEN 1 END) as closed_tickets,
                COUNT(CASE WHEN priority = "urgent" THEN 1 END) as urgent_tickets,
                COUNT(CASE WHEN priority = "high" THEN 1 END) as high_priority_tickets,
                AVG(customer_satisfaction_rating) as avg_satisfaction,
                AVG(actual_resolution_time) as avg_resolution_time
            ')
            ->first();

        return response()->json(['data' => $stats]);
    }

    public function getOpenTickets(): JsonResponse
    {
        $tickets = SupportTicket::with(['assignedUser', 'customer'])
            ->where('company_id', Auth::user()->company_id)
            ->whereIn('status', ['open', 'in_progress'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        return response()->json(['data' => $tickets]);
    }

    public function getUrgentTickets(): JsonResponse
    {
        $tickets = SupportTicket::with(['assignedUser', 'customer'])
            ->where('company_id', Auth::user()->company_id)
            ->where('priority', 'urgent')
            ->whereIn('status', ['open', 'in_progress'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['data' => $tickets]);
    }

    public function getAssignedUsers(): JsonResponse
    {
        $users = User::where('company_id', Auth::user()->company_id)
            ->select('id', 'name', 'email')
            ->get();

        return response()->json(['data' => $users]);
    }

    public function getCategories(): JsonResponse
    {
        $categories = SupportTicket::where('company_id', Auth::user()->company_id)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return response()->json(['data' => $categories]);
    }
}

