<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ServiceTicketController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceTicket::where('company_id', Auth::user()->company_id)
            ->with(['customer', 'assignedTo', 'escalatedTo', 'category', 'creator']);

        // Filtering
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('ticket_code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('ticket_type')) {
            $query->where('ticket_type', $request->ticket_type);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('sla_breached')) {
            $query->where('sla_breached', $request->sla_breached);
        }

        // Statistics
        $statistics = [
            'total' => ServiceTicket::where('company_id', Auth::user()->company_id)->count(),
            'open' => ServiceTicket::where('company_id', Auth::user()->company_id)->open()->count(),
            'resolved' => ServiceTicket::where('company_id', Auth::user()->company_id)->resolved()->count(),
            'sla_breached' => ServiceTicket::where('company_id', Auth::user()->company_id)->slaBreached()->count(),
        ];

        $tickets = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $tickets,
            'statistics' => $statistics,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'ticket_type' => 'required|in:technical_support,billing_support,product_support,account_support,feature_request,bug_report,general_inquiry,escalation,other',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:open,assigned,in_progress,waiting_customer,resolved,closed',
            'source' => 'required|in:phone,email,chat,web_form,social_media,in_person',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_company' => 'nullable|string|max:255',
            'issue_details' => 'required|string',
            'steps_to_reproduce' => 'nullable|string',
            'error_messages' => 'nullable|string',
            'affected_product' => 'nullable|string|max:255',
            'affected_version' => 'nullable|string|max:255',
            'attachments' => 'nullable|array',
            'assigned_to' => 'nullable|exists:users,id',
            'category_id' => 'nullable|exists:service_categories,id',
            'tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $ticket = ServiceTicket::create([
            ...$request->validated(),
            'created_by' => Auth::id(),
            'company_id' => Auth::user()->company_id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $ticket->load(['customer', 'assignedTo', 'escalatedTo', 'category', 'creator']);

        return response()->json([
            'success' => true,
            'message' => 'Service ticket created successfully',
            'data' => $ticket,
        ], 201);
    }

    public function show($id)
    {
        $ticket = ServiceTicket::where('company_id', Auth::user()->company_id)
            ->with(['customer', 'assignedTo', 'escalatedTo', 'category', 'creator', 'company'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $ticket,
        ]);
    }

    public function update(Request $request, $id)
    {
        $ticket = ServiceTicket::where('company_id', Auth::user()->company_id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'ticket_type' => 'sometimes|required|in:technical_support,billing_support,product_support,account_support,feature_request,bug_report,general_inquiry,escalation,other',
            'priority' => 'sometimes|required|in:low,medium,high,critical',
            'status' => 'sometimes|required|in:open,assigned,in_progress,waiting_customer,resolved,closed',
            'source' => 'sometimes|required|in:phone,email,chat,web_form,social_media,in_person',
            'customer_name' => 'sometimes|required|string|max:255',
            'customer_email' => 'sometimes|required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_company' => 'nullable|string|max:255',
            'issue_details' => 'sometimes|required|string',
            'steps_to_reproduce' => 'nullable|string',
            'error_messages' => 'nullable|string',
            'affected_product' => 'nullable|string|max:255',
            'affected_version' => 'nullable|string|max:255',
            'attachments' => 'nullable|array',
            'assigned_to' => 'nullable|exists:users,id',
            'escalated_to' => 'nullable|exists:users,id',
            'escalation_date' => 'nullable|date',
            'escalation_reason' => 'nullable|string',
            'escalation_level' => 'nullable|integer|min:0',
            'resolution_notes' => 'nullable|string',
            'solution_provided' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'first_response_date' => 'nullable|date',
            'resolution_date' => 'nullable|date',
            'response_time_hours' => 'nullable|integer|min:0',
            'resolution_time_hours' => 'nullable|integer|min:0',
            'sla_due_date' => 'nullable|date',
            'sla_breached' => 'nullable|boolean',
            'sla_breach_hours' => 'nullable|integer|min:0',
            'satisfaction_rating' => 'nullable|in:very_dissatisfied,dissatisfied,neutral,satisfied,very_satisfied',
            'customer_feedback' => 'nullable|string',
            'customer_responded' => 'nullable|boolean',
            'tags' => 'nullable|array',
            'category_id' => 'nullable|exists:service_categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $ticket->update($request->validated());
        $ticket->load(['customer', 'assignedTo', 'escalatedTo', 'category', 'creator']);

        return response()->json([
            'success' => true,
            'message' => 'Service ticket updated successfully',
            'data' => $ticket,
        ]);
    }

    public function destroy($id)
    {
        $ticket = ServiceTicket::where('company_id', Auth::user()->company_id)->findOrFail($id);
        $ticket->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service ticket deleted successfully',
        ]);
    }

    public function statistics()
    {
        $companyId = Auth::user()->company_id;

        $statistics = [
            'total_tickets' => ServiceTicket::where('company_id', $companyId)->count(),
            'open_tickets' => ServiceTicket::where('company_id', $companyId)->open()->count(),
            'resolved_tickets' => ServiceTicket::where('company_id', $companyId)->resolved()->count(),
            'sla_breached_tickets' => ServiceTicket::where('company_id', $companyId)->slaBreached()->count(),
            'escalated_tickets' => ServiceTicket::where('company_id', $companyId)->escalated()->count(),
            'average_response_time' => ServiceTicket::where('company_id', $companyId)
                ->whereNotNull('response_time_hours')
                ->avg('response_time_hours'),
            'average_resolution_time' => ServiceTicket::where('company_id', $companyId)
                ->whereNotNull('resolution_time_hours')
                ->avg('resolution_time_hours'),
            'satisfaction_breakdown' => ServiceTicket::where('company_id', $companyId)
                ->whereNotNull('satisfaction_rating')
                ->selectRaw('satisfaction_rating, COUNT(*) as count')
                ->groupBy('satisfaction_rating')
                ->get(),
            'tickets_by_type' => ServiceTicket::where('company_id', $companyId)
                ->selectRaw('ticket_type, COUNT(*) as count')
                ->groupBy('ticket_type')
                ->get(),
            'tickets_by_source' => ServiceTicket::where('company_id', $companyId)
                ->selectRaw('source, COUNT(*) as count')
                ->groupBy('source')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }
}
