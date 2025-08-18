<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Project::with(['projectManager', 'client', 'company']);

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        // Filter by project manager
        if ($request->has('project_manager_id') && $request->project_manager_id) {
            $query->where('project_manager_id', $request->project_manager_id);
        }

        // Filter by client
        if ($request->has('client_id') && $request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $projects = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $projects->items(),
            'pagination' => [
                'current_page' => $projects->currentPage(),
                'last_page' => $projects->lastPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
            ],
        ]);
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:projects,code',
            'description' => 'nullable|string',
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'budget' => 'nullable|numeric|min:0',
            'project_manager_id' => 'required|exists:users,id',
            'client_id' => 'nullable|exists:customers,id',
            'company_id' => 'required|exists:companies,id',
            'location' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
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

            $project = Project::create($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Project created successfully',
                'data' => $project->load(['projectManager', 'client', 'company']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create project',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project): JsonResponse
    {
        $project->load([
            'projectManager',
            'client',
            'company',
            'tasks',
            'resources',
            'costs',
            'milestones',
            'team.user',
        ]);

        return response()->json([
            'success' => true,
            'data' => $project,
        ]);
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, Project $project): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:50|unique:projects,code,' . $project->id,
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:planning,active,on_hold,completed,cancelled',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date',
            'budget' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'progress_percentage' => 'nullable|numeric|min:0|max:100',
            'project_manager_id' => 'sometimes|required|exists:users,id',
            'client_id' => 'nullable|exists:customers,id',
            'location' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
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

            $project->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Project updated successfully',
                'data' => $project->load(['projectManager', 'client', 'company']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update project',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Project $project): JsonResponse
    {
        try {
            $project->delete();

            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete project',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get project statistics.
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'completed_projects' => Project::where('status', 'completed')->count(),
            'overdue_projects' => Project::where('end_date', '<', now())
                ->where('status', '!=', 'completed')
                ->count(),
            'total_budget' => Project::sum('budget'),
            'total_actual_cost' => Project::sum('actual_cost'),
            'average_progress' => Project::avg('progress_percentage'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get project managers.
     */
    public function getProjectManagers(): JsonResponse
    {
        $managers = User::select('id', 'name', 'email')
            ->whereHas('projects', function ($query) {
                $query->where('project_manager_id', 'users.id');
            })
            ->orWhere('id', function ($query) {
                $query->select('project_manager_id')
                    ->from('projects')
                    ->whereNotNull('project_manager_id');
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => $managers,
        ]);
    }

    /**
     * Get clients.
     */
    public function getClients(): JsonResponse
    {
        $clients = Customer::select('id', 'name', 'email', 'company')
            ->whereHas('projects')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $clients,
        ]);
    }

    /**
     * Generate project code.
     */
    public function generateCode(): JsonResponse
    {
        $prefix = 'PRJ';
        $year = date('Y');
        $month = date('m');
        
        $lastProject = Project::where('code', 'like', $prefix . $year . $month . '%')
            ->orderBy('code', 'desc')
            ->first();

        if ($lastProject) {
            $lastNumber = (int) substr($lastProject->code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $code = $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        return response()->json([
            'success' => true,
            'data' => ['code' => $code],
        ]);
    }
}
