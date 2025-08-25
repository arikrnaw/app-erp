<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\FixedAsset;
use App\Models\Finance\AssetCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class FixedAssetsController extends Controller
{
    /**
     * Display a listing of fixed assets
     */
    public function index()
    {
        return Inertia::render('Finance/FixedAssets/Index');
    }

    /**
     * Show the form for creating a new fixed asset
     */
    public function create()
    {
        return Inertia::render('Finance/FixedAssets/Create');
    }

    /**
     * Store a newly created fixed asset
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'tag_number' => 'required|string|max:100|unique:fixed_assets',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'required|exists:asset_categories,id',
            'purchase_value' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'useful_life' => 'required|integer|min:1',
            'salvage_value' => 'nullable|numeric|min:0',
            'depreciation_method' => 'required|string|in:straight_line,declining_balance,sum_of_years,units_of_production',
            'location' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'warranty_expiry' => 'nullable|date',
            'insurance_info' => 'nullable|string|max:1000',
            'status' => 'required|string|in:active,disposed,maintenance',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $assetData = $request->all();
            $assetData['company_id'] = Auth::check() ? Auth::user()->company_id : 1;
            $assetData['created_by'] = Auth::check() ? Auth::id() : 1;
            $assetData['useful_life_years'] = $request->useful_life; // Map to the expected field name

            $asset = FixedAsset::create($assetData);

            DB::commit();

            return redirect()->route('finance.fixed-assets.show', $asset->id)
                ->with('success', 'Fixed asset created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error creating fixed asset: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified fixed asset
     */
    public function show($id)
    {
        return Inertia::render('Finance/FixedAssets/Show', ['id' => $id]);
    }

    /**
     * Show the form for editing the specified fixed asset
     */
    public function edit($id)
    {
        return Inertia::render('Finance/FixedAssets/Edit', ['id' => $id]);
    }

    /**
     * Update the specified fixed asset
     */
    public function update(Request $request, $id)
    {
        $asset = FixedAsset::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'tag_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('fixed_assets')->ignore($id)
            ],
            'description' => 'nullable|string|max:1000',
            'category_id' => 'required|exists:asset_categories,id',
            'purchase_value' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'useful_life' => 'required|integer|min:1',
            'salvage_value' => 'nullable|numeric|min:0',
            'depreciation_method' => 'required|string|in:straight_line,declining_balance,sum_of_years,units_of_production',
            'location' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'warranty_expiry' => 'nullable|date',
            'insurance_info' => 'nullable|string|max:1000',
            'status' => 'required|string|in:active,disposed,maintenance',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $updateData = $request->all();
            $updateData['updated_by'] = Auth::check() ? Auth::id() : 1;
            $updateData['useful_life_years'] = $request->useful_life; // Map to the expected field name

            $asset->update($updateData);

            DB::commit();

            return redirect()->route('finance.fixed-assets.show', $asset->id)
                ->with('success', 'Fixed asset updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error updating fixed asset: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified fixed asset
     */
    public function destroy($id)
    {
        try {
            $asset = FixedAsset::findOrFail($id);
            $asset->delete();

            return redirect()->route('finance.fixed-assets.index')
                ->with('success', 'Fixed asset deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error deleting fixed asset: ' . $e->getMessage()]);
        }
    }
}
