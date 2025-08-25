<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\FixedAsset;
use App\Models\Finance\AssetCategory;
use App\Models\Finance\AssetDepreciation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FixedAssetsController extends Controller
{
    /**
     * Get fixed assets dashboard data
     */
    public function dashboard(): JsonResponse
    {
        try {
            $companyId = Auth::check() ? Auth::user()->company_id : 1;
            $assets = FixedAsset::with('category')->where('company_id', $companyId)->get();
            $categories = AssetCategory::withCount('fixedAssets')->where('company_id', $companyId)->get();
            $monthlyDepreciation = AssetDepreciation::with('asset')
                ->where('company_id', $companyId)
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->get();

            $totalAssetValue = $assets->sum('purchase_value');
            $totalDepreciation = $assets->sum('accumulated_depreciation');
            $netBookValue = $totalAssetValue - $totalDepreciation;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_assets' => $assets->count(),
                    'total_value' => $totalAssetValue,
                    'total_depreciation' => $totalDepreciation,
                    'net_book_value' => $netBookValue,
                    'asset_categories' => $categories,
                    'monthly_depreciation' => $monthlyDepreciation
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all fixed assets with pagination
     */
    public function getAssets(Request $request): JsonResponse
    {
        try {
            $companyId = Auth::check() ? Auth::user()->company_id : 1;
            $query = FixedAsset::with(['category', 'depreciations'])
                ->where('company_id', $companyId);

            // Apply filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('tag_number', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%");
                });
            }

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $assets = $query->paginate($request->get('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => $assets
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching fixed assets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new fixed asset
     */
    public function createAsset(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'tag_number' => 'required|string|max:100|unique:fixed_assets',
                'description' => 'nullable|string|max:1000',
                'category_id' => 'required|exists:asset_categories,id',
                'purchase_value' => 'required|numeric|min:0',
                'purchase_date' => 'required|date',
                'useful_life_years' => 'required|integer|min:1',
                'salvage_value' => 'nullable|numeric|min:0',
                'depreciation_method' => 'required|string|in:straight_line,declining_balance,units_of_production',
                'location' => 'nullable|string|max:255',
                'supplier' => 'nullable|string|max:255',
                'warranty_expiry' => 'nullable|date',
                'status' => 'nullable|string|in:active,disposed,maintenance',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()                                                                                                                                                                                                                    
                ], 422);
            }

            DB::beginTransaction();

            $assetData = $request->all();
            $assetData['company_id'] = Auth::check() ? Auth::user()->company_id : 1; // Default to company ID 1 if not set
            $assetData['created_by'] = Auth::check() ? Auth::id() : 1; // Default to user ID 1 if not set

            $asset = FixedAsset::create($assetData);

            // Calculate initial depreciation
            $this->calculateInitialDepreciation($asset);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fixed asset created successfully',
                'data' => $asset->load(['category', 'depreciations'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating fixed asset: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific fixed asset
     */
    public function getAsset($id): JsonResponse
    {
        try {
            $asset = FixedAsset::with(['category', 'depreciations' => function ($query) {
                $query->orderBy('date', 'desc');
            }])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $asset
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fixed asset not found'
            ], 404);
        }
    }

    /**
     * Update a fixed asset
     */
    public function updateAsset(Request $request, $id): JsonResponse
    {
        try {
            $asset = FixedAsset::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'tag_number' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('fixed_assets')->ignore($id)
                ],
                'description' => 'nullable|string|max:1000',
                'category_id' => 'sometimes|required|exists:asset_categories,id',
                'purchase_value' => 'sometimes|required|numeric|min:0',
                'purchase_date' => 'sometimes|required|date',
                'useful_life_years' => 'sometimes|required|integer|min:1',
                'salvage_value' => 'nullable|numeric|min:0',
                'depreciation_method' => 'sometimes|required|string|in:straight_line,declining_balance,units_of_production',
                'location' => 'nullable|string|max:255',
                'supplier' => 'nullable|string|max:255',
                'warranty_expiry' => 'nullable|date',
                'status' => 'nullable|string|in:active,disposed,maintenance',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = $request->all();
            $updateData['updated_by'] = Auth::check() ? Auth::id() : 1;

            $asset->update($updateData);

            // Recalculate depreciation if necessary values changed
            if ($request->hasAny(['purchase_value', 'useful_life_years', 'salvage_value', 'depreciation_method'])) {
                $this->recalculateDepreciation($asset);
            }

            return response()->json([
                'success' => true,
                'message' => 'Fixed asset updated successfully',
                'data' => $asset->fresh()->load(['category', 'depreciations'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating fixed asset: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get asset categories
     */
    public function getCategories(): JsonResponse
    {
        try {
            $companyId = Auth::check() ? Auth::user()->company_id : 1;
            $categories = AssetCategory::withCount('fixedAssets')->where('company_id', $companyId)->get();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching asset categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create asset category
     */
    public function createCategory(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:asset_categories',
                'description' => 'nullable|string|max:1000',
                'depreciation_rate' => 'nullable|numeric|min:0|max:100',
                'useful_life_years' => 'nullable|integer|min:1',
                'status' => 'nullable|string|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $categoryData = $request->all();
            $categoryData['company_id'] = Auth::check() ? Auth::user()->company_id : 1;
            $categoryData['created_by'] = Auth::check() ? Auth::id() : 1;

            $category = AssetCategory::create($categoryData);

            return response()->json([
                'success' => true,
                'message' => 'Asset category created successfully',
                'data' => $category
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating asset category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get monthly depreciation
     */
    public function getMonthlyDepreciation(): JsonResponse
    {
        try {
            $companyId = Auth::check() ? Auth::user()->company_id : 1;
            $depreciation = AssetDepreciation::with(['asset.category'])
                ->where('company_id', $companyId)
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $depreciation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching monthly depreciation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate depreciation for an asset
     */
    public function calculateDepreciation(Request $request, $id): JsonResponse
    {
        try {
            $asset = FixedAsset::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'date' => 'required|date',
                'force_recalculate' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $depreciationAmount = $this->calculateDepreciationAmount($asset, $request->date);
            
            // Check if depreciation already exists for this date
            $existingDepreciation = AssetDepreciation::where('asset_id', $asset->id)
                ->whereDate('date', $request->date)
                ->first();

            if ($existingDepreciation && !$request->force_recalculate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Depreciation already calculated for this date'
                ], 422);
            }

            if ($existingDepreciation) {
                $existingDepreciation->update([
                    'amount' => $depreciationAmount,
                    'accumulated_depreciation' => $asset->accumulated_depreciation + $depreciationAmount
                ]);
            } else {
                AssetDepreciation::create([
                    'asset_id' => $asset->id,
                    'date' => $request->date,
                    'amount' => $depreciationAmount,
                    'accumulated_depreciation' => $asset->accumulated_depreciation + $depreciationAmount
                ]);
            }

            // Update asset's accumulated depreciation
            $asset->increment('accumulated_depreciation', $depreciationAmount);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Depreciation calculated successfully',
                'data' => [
                    'depreciation_amount' => $depreciationAmount,
                    'accumulated_depreciation' => $asset->fresh()->accumulated_depreciation
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error calculating depreciation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export fixed assets
     */
    public function exportAssets(): JsonResponse
    {
        try {
            $assets = FixedAsset::with(['category', 'depreciations'])->get();

            return response()->json([
                'success' => true,
                'data' => $assets
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting fixed assets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate initial depreciation for a new asset
     */
    private function calculateInitialDepreciation(FixedAsset $asset): void
    {
        $depreciationAmount = $this->calculateDepreciationAmount($asset, $asset->purchase_date);
        
        AssetDepreciation::create([
            'asset_id' => $asset->id,
            'date' => $asset->purchase_date,
            'amount' => $depreciationAmount,
            'accumulated_depreciation' => $depreciationAmount
        ]);

        $asset->update(['accumulated_depreciation' => $depreciationAmount]);
    }

    /**
     * Recalculate depreciation for an asset
     */
    private function recalculateDepreciation(FixedAsset $asset): void
    {
        // Delete existing depreciation records
        $asset->depreciations()->delete();
        
        // Reset accumulated depreciation
        $asset->update(['accumulated_depreciation' => 0]);
        
        // Recalculate from purchase date
        $this->calculateInitialDepreciation($asset);
    }

    /**
     * Calculate depreciation amount for an asset
     */
    private function calculateDepreciationAmount(FixedAsset $asset, $date): float
    {
        $monthsSincePurchase = $asset->purchase_date->diffInMonths($date);
        
        if ($monthsSincePurchase <= 0) {
            return 0;
        }

        $depreciableAmount = $asset->purchase_value - ($asset->salvage_value ?? 0);
        $monthlyDepreciation = $depreciableAmount / ($asset->useful_life * 12);

        return min($monthlyDepreciation, $depreciableAmount - $asset->accumulated_depreciation);
    }
}
