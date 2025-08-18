<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProjectTask;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProjectTaskController extends Controller
{
    /**
     * Display a listing of project tasks.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProjectTask::with(['project', 'assignedTo', 'createdBy', 'parentTask']);

        // Filter by project
        if ($request->has('project_id') && $request->project_id) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by assigned user
        if ($request->has('assigned_to') && $request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter overdue tasks
        if ($request->has('overdue') && $request->overdue) {
            $query->where('due_date', '<', now())->where('status', '!=', 'completed');
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $tasks = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $tasks->items(),
            'pagination' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
            ],
        ]);
    }

    /**
     * Store a newly created project task.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,in_progress,review,testing,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'type' => 'required|in:feature,bug,improvement,documentation,testing,other',
            'project_id' => 'required|exists:projects,id',
            'parent_task_id' => 'nullable|exists:project_tasks,id',
            'assigned_to' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'estimated_hours' => 'nullable|integer|min:0',
            'dependencies' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $task = ProjectTask::create(array_merge($request->all(), [
                'created_by' => auth()->id(),
            ]));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'data' => $task->load(['project', 'assignedTo', 'createdBy', 'parentTask']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified project task.
     */
    public function show(ProjectTask $task): JsonResponse
    {
        $task->load([
            'project',
            'assignedTo',
            'createdBy',
            'parentTask',
            'subtasks',
        ]);

        return response()->json([
            'success' => true,
            'data' => $task,
        ]);
    }

    /**
     * Update the specified project task.
     */
    public function update(Request $request, ProjectTask $task): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:todo,in_progress,review,testing,completed,cancelled',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'type' => 'sometimes|required|in:feature,bug,improvement,documentation,testing,other',
            'parent_task_id' => 'nullable|exists:project_tasks,id',
            'assigned_to' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date',
            'estimated_hours' => 'nullable|integer|min:0',
            'actual_hours' => 'nullable|integer|min:0',
            'progress_percentage' => 'nullable|numeric|min:0|max:100',
            'dependencies' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $task->update($request->all());

            // Update parent task progress if this is a subtask
            if ($task->parent_task_id) {
                $task->parentTask->updateProgress();
            }

            // Update project progress
            $task->project->updateProgress();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully',
                'data' => $task->load(['project', 'assignedTo', 'createdBy', 'parentTask']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified project task.
     */
    public function destroy(ProjectTask $task): JsonResponse
    {
        try {
            DB::beginTransaction();

            $task->delete();

            // Update parent task progress if this was a subtask
            if ($task->parent_task_id) {
                $task->parentTask->updateProgress();
            }

            // Update project progress
            $task->project->updateProgress();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get task statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = ProjectTask::query();

        if ($request->has('project_id') && $request->project_id) {
            $query->where('project_id', $request->project_id);
        }

        $stats = [
            'total_tasks' => $query->count(),
            'todo_tasks' => (clone $query)->where('status', 'todo')->count(),
            'in_progress_tasks' => (clone $query)->where('status', 'in_progress')->count(),
            'completed_tasks' => (clone $query)->where('status', 'completed')->count(),
            'overdue_tasks' => (clone $query)->where('due_date', '<', now())
                ->where('status', '!=', 'completed')
                ->count(),
            'high_priority_tasks' => (clone $query)->where('priority', 'high')->count(),
            'urgent_tasks' => (clone $query)->where('priority', 'urgent')->count(),
            'total_estimated_hours' => (clone $query)->sum('estimated_hours'),
            'total_actual_hours' => (clone $query)->sum('actual_hours'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get assigned users.
     */
    public function getAssignedUsers(): JsonResponse
    {
        $users = User::select('id', 'name', 'email')
            ->whereHas('assignedTasks')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Update task status.
     */
    public function updateStatus(Request $request, ProjectTask $task): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:todo,in_progress,review,testing,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $task->update([
                'status' => $request->status,
                'actual_start_date' => $request->status === 'in_progress' && !$task->actual_start_date 
                    ? now() 
                    : $task->actual_start_date,
                'actual_end_date' => $request->status === 'completed' 
                    ? now() 
                    : $task->actual_end_date,
            ]);

            // Update parent task progress if this is a subtask
            if ($task->parent_task_id) {
                $task->parentTask->updateProgress();
            }

            // Update project progress
            $task->project->updateProgress();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task status updated successfully',
                'data' => $task->load(['project', 'assignedTo', 'createdBy']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update task status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
