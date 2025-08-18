<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaxRate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TaxRateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TaxRate::query();

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('is_active') && $request->is_active !== 'all') {
            $query->where('is_active', $request->is_active === 'true');
        }

        $taxRates = $query->orderBy('name')->paginate(15);

        return response()->json($taxRates);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:tax_rates,name',
            'rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $taxRate = TaxRate::create([
                'name' => $request->name,
                'rate' => $request->rate,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
            ]);

            return response()->json([
                'message' => 'Tax rate created successfully',
                'tax_rate' => $taxRate
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating tax rate: ' . $e->getMessage()], 500);
        }
    }

    public function show(TaxRate $taxRate): JsonResponse
    {
        return response()->json(['tax_rate' => $taxRate]);
    }

    public function update(Request $request, TaxRate $taxRate): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:tax_rates,name,' . $taxRate->id,
            'rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $taxRate->update([
                'name' => $request->name,
                'rate' => $request->rate,
                'description' => $request->description,
                'is_active' => $request->is_active,
            ]);

            return response()->json([
                'message' => 'Tax rate updated successfully',
                'tax_rate' => $taxRate
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating tax rate: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(TaxRate $taxRate): JsonResponse
    {
        try {
            // Check if tax rate is being used in transactions
            if ($taxRate->taxTransactions()->exists()) {
                return response()->json(['message' => 'Cannot delete tax rate that is being used in transactions'], 422);
            }

            $taxRate->delete();
            return response()->json(['message' => 'Tax rate deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting tax rate: ' . $e->getMessage()], 500);
        }
    }

    public function getActive(): JsonResponse
    {
        $taxRates = TaxRate::where('is_active', true)->orderBy('name')->get();
        return response()->json(['tax_rates' => $taxRates]);
    }
}
