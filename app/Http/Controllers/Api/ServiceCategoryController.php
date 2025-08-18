<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ServiceCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceCategory::where('company_id', Auth::user()->company_id)
            ->with(['parent', 'defaultAssignee', 'creator', 'children']);

        // Filtering
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        $categories = $query->ordered()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'parent_id' => 'nullable|exists:service_categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'sla_hours' => 'nullable|integer|min:1',
            'escalation_hours' => 'nullable|integer|min:1',
            'auto_assignment_rules' => 'nullable|array',
            'default_assignee' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $category = ServiceCategory::create([
            ...$request->validated(),
            'created_by' => Auth::id(),
            'company_id' => Auth::user()->company_id,
        ]);

        $category->load(['parent', 'defaultAssignee', 'creator', 'children']);

        return response()->json([
            'success' => true,
            'message' => 'Service category created successfully',
            'data' => $category,
        ], 201);
    }

    public function show($id)
    {
        $category = ServiceCategory::where('company_id', Auth::user()->company_id)
            ->with(['parent', 'defaultAssignee', 'creator', 'children', 'serviceTickets'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    public function update(Request $request, $id)
    {
        $category = ServiceCategory::where('company_id', Auth::user()->company_id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'parent_id' => 'nullable|exists:service_categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'sla_hours' => 'nullable|integer|min:1',
            'escalation_hours' => 'nullable|integer|min:1',
            'auto_assignment_rules' => 'nullable|array',
            'default_assignee' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $category->update($request->validated());
        $category->load(['parent', 'defaultAssignee', 'creator', 'children']);

        return response()->json([
            'success' => true,
            'message' => 'Service category updated successfully',
            'data' => $category,
        ]);
    }

    public function destroy($id)
    {
        $category = ServiceCategory::where('company_id', Auth::user()->company_id)->findOrFail($id);
        
        // Check if category has children
        if ($category->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with subcategories',
            ], 422);
        }

        // Check if category has tickets
        if ($category->serviceTickets()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with associated tickets',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service category deleted successfully',
        ]);
    }

    public function tree()
    {
        $categories = ServiceCategory::where('company_id', Auth::user()->company_id)
            ->active()
            ->root()
            ->with(['children' => function ($query) {
                $query->active()->ordered();
            }])
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }
}
