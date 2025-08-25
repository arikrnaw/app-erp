<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\ApprovalWorkflow;
use App\Models\Finance\ApprovalRequest;
use App\Models\Finance\ApprovalRule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ApprovalWorkflowController extends Controller
{
    /**
     * Get approval workflows dashboard
     */
    public function dashboard(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Get pending approvals for current user
            $pendingApprovals = ApprovalRequest::where('approver_id', $user->id)
                ->where('status', 'pending')
                ->with(['requestor', 'workflow', 'approvable'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            // Get approval statistics
            $stats = [
                'pending' => ApprovalRequest::where('status', 'pending')->count(),
                'approved' => ApprovalRequest::where('status', 'approved')->count(),
                'rejected' => ApprovalRequest::where('status', 'rejected')->count(),
                'escalated' => ApprovalRequest::where('status', 'escalated')->count(),
                'my_pending' => $pendingApprovals->count(),
                'total_workflows' => ApprovalWorkflow::count(),
                'active_rules' => ApprovalRule::where('is_active', true)->count(),
            ];
            
            // Get recent approval activities
            $recentActivities = ApprovalRequest::with(['requestor', 'approver', 'workflow'])
                ->orderBy('updated_at', 'desc')
                ->limit(20)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'pendingApprovals' => $pendingApprovals,
                    'recentActivities' => $recentActivities,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading dashboard: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get approval workflows
     */
    public function getWorkflows(Request $request): JsonResponse
    {
        try {
            $query = ApprovalWorkflow::with(['rules', 'approvers']);
            
            // Apply filters
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }
            
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            $workflows = $query->paginate($request->get('per_page', 15));
            
            return response()->json([
                'success' => true,
                'data' => $workflows
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading workflows: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create approval workflow
     */
    public function createWorkflow(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|string|in:purchase_order,expense,budget,asset_purchase,general',
                'description' => 'nullable|string',
                'threshold_amount' => 'required|numeric|min:0',
                'approval_levels' => 'required|array|min:1',
                'approval_levels.*.level' => 'required|integer|min:1',
                'approval_levels.*.approver_role' => 'required|string',
                'approval_levels.*.approver_id' => 'nullable|exists:users,id',
                'approval_levels.*.escalation_hours' => 'nullable|integer|min:1',
                'is_active' => 'boolean',
                'auto_escalate' => 'boolean',
                'require_all_levels' => 'boolean',
            ]);
            
            DB::beginTransaction();
            
            $workflow = ApprovalWorkflow::create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'description' => $validated['description'],
                'threshold_amount' => $validated['threshold_amount'],
                'is_active' => $validated['is_active'] ?? true,
                'auto_escalate' => $validated['auto_escalate'] ?? false,
                'require_all_levels' => $validated['require_all_levels'] ?? false,
                'created_by' => Auth::id(),
            ]);
            
            // Create approval levels
            foreach ($validated['approval_levels'] as $levelData) {
                $workflow->levels()->create([
                    'level' => $levelData['level'],
                    'approver_role' => $levelData['approver_role'],
                    'approver_id' => $levelData['approver_id'],
                    'escalation_hours' => $levelData['escalation_hours'] ?? 24,
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Approval workflow created successfully',
                'data' => $workflow->load('levels')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating workflow: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update approval workflow
     */
    public function updateWorkflow(Request $request, int $id): JsonResponse
    {
        try {
            $workflow = ApprovalWorkflow::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'threshold_amount' => 'sometimes|numeric|min:0',
                'is_active' => 'boolean',
                'auto_escalate' => 'boolean',
                'require_all_levels' => 'boolean',
            ]);
            
            $workflow->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Approval workflow updated successfully',
                'data' => $workflow->load('levels')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating workflow: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get approval requests
     */
    public function getApprovalRequests(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $query = ApprovalRequest::with(['requestor', 'workflow', 'approvable']);
            
            // Filter by user role
            if ($request->has('my_approvals') && $request->my_approvals) {
                $query->where('approver_id', $user->id);
            }
            
            if ($request->has('my_requests') && $request->my_requests) {
                $query->where('requestor_id', $user->id);
            }
            
            // Apply other filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->has('type')) {
                $query->whereHas('workflow', function ($q) use ($request) {
                    $q->where('type', $request->type);
                });
            }
            
            $requests = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));
            
            return response()->json([
                'success' => true,
                'data' => $requests
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading approval requests: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Approve or reject request
     */
    public function processApproval(Request $request, int $id): JsonResponse
    {
        try {
            $approvalRequest = ApprovalRequest::findOrFail($id);
            $user = Auth::user();
            
            // Check if user can approve this request
            if ($approvalRequest->approver_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to approve this request'
                ], 403);
            }
            
            $validated = $request->validate([
                'action' => 'required|in:approve,reject',
                'comments' => 'nullable|string',
                'next_approver_id' => 'nullable|exists:users,id',
            ]);
            
            DB::beginTransaction();
            
            if ($validated['action'] === 'approve') {
                $approvalRequest->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approver_comments' => $validated['comments'],
                ]);
                
                // Check if workflow is complete
                $this->checkWorkflowCompletion($approvalRequest);
                
            } else {
                $approvalRequest->update([
                    'status' => 'rejected',
                    'rejected_at' => now(),
                    'approver_comments' => $validated['comments'],
                ]);
                
                // Notify requestor of rejection
                $this->notifyRejection($approvalRequest);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Request ' . $validated['action'] . 'd successfully',
                'data' => $approvalRequest->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error processing approval: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create approval request
     */
    public function createApprovalRequest(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'workflow_id' => 'required|exists:approval_workflows,id',
                'approvable_type' => 'required|string',
                'approvable_id' => 'required|integer',
                'amount' => 'required|numeric|min:0',
                'description' => 'required|string',
                'priority' => 'nullable|in:low,medium,high,urgent',
                'due_date' => 'nullable|date|after:today',
            ]);
            
            $workflow = ApprovalWorkflow::findOrFail($validated['workflow_id']);
            
            // Check if amount meets threshold
            if ($validated['amount'] < $workflow->threshold_amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Amount does not meet approval threshold'
                ], 400);
            }
            
            DB::beginTransaction();
            
            // Create approval request
            $approvalRequest = ApprovalRequest::create([
                'workflow_id' => $workflow->id,
                'requestor_id' => Auth::id(),
                'approvable_type' => $validated['approvable_type'],
                'approvable_id' => $validated['approvable_id'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'priority' => $validated['priority'] ?? 'medium',
                'due_date' => $validated['due_date'],
                'status' => 'pending',
            ]);
            
            // Assign first level approver
            $firstLevel = $workflow->levels()->orderBy('level')->first();
            if ($firstLevel) {
                $approvalRequest->update([
                    'approver_id' => $firstLevel->approver_id,
                    'current_level' => $firstLevel->level,
                ]);
                
                // Notify approver
                $this->notifyApprover($approvalRequest);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Approval request created successfully',
                'data' => $approvalRequest->load(['workflow', 'approver'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating approval request: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get approval rules
     */
    public function getApprovalRules(Request $request): JsonResponse
    {
        try {
            $query = ApprovalRule::with(['workflow']);
            
            if ($request->has('type')) {
                $query->whereHas('workflow', function ($q) use ($request) {
                    $q->where('type', $request->type);
                });
            }
            
            $rules = $query->paginate($request->get('per_page', 15));
            
            return response()->json([
                'success' => true,
                'data' => $rules
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading approval rules: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Export approval data
     */
    public function exportApprovals(Request $request): JsonResponse
    {
        try {
            $query = ApprovalRequest::with(['requestor', 'approver', 'workflow']);
            
            // Apply filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->has('date_from')) {
                $query->where('created_at', '>=', $request->date_from);
            }
            
            if ($request->has('date_to')) {
                $query->where('created_at', '<=', $request->date_to);
            }
            
            $approvals = $query->get();
            
            // Return data for export (frontend will handle Excel/PDF generation)
            return response()->json([
                'success' => true,
                'data' => $approvals,
                'message' => 'Data ready for export'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error preparing export: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check if workflow is complete and proceed to next level
     */
    private function checkWorkflowCompletion(ApprovalRequest $request): void
    {
        $workflow = $request->workflow;
        $currentLevel = $request->current_level;
        
        // Get next level
        $nextLevel = $workflow->levels()
            ->where('level', '>', $currentLevel)
            ->orderBy('level')
            ->first();
        
        if ($nextLevel) {
            // Move to next level
            $request->update([
                'approver_id' => $nextLevel->approver_id,
                'current_level' => $nextLevel->level,
                'status' => 'pending',
            ]);
            
            // Notify next approver
            $this->notifyApprover($request);
        } else {
            // Workflow complete
            $request->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            
            // Notify requestor of completion
            $this->notifyCompletion($request);
        }
    }
    
    /**
     * Notify approver of new request
     */
    private function notifyApprover(ApprovalRequest $request): void
    {
        // Implementation for notification system
        // This could be email, push notification, or in-app notification
    }
    
    /**
     * Notify requestor of rejection
     */
    private function notifyRejection(ApprovalRequest $request): void
    {
        // Implementation for rejection notification
    }
    
    /**
     * Notify requestor of completion
     */
    private function notifyCompletion(ApprovalRequest $request): void
    {
        // Implementation for completion notification
    }
}
