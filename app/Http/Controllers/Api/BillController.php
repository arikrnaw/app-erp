<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BillController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Bill::with(['supplier', 'items.product']);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bill_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Date filter
        if ($request->has('date')) {
            $query->whereDate('bill_date', $request->date);
        }

        // Supplier filter
        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $bills = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($bills);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:bill_date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Generate bill number
            $billNumber = 'BILL-' . date('Y') . '-' . str_pad(Bill::whereYear('created_at', date('Y'))->count() + 1, 4, '0', STR_PAD_LEFT);

            $bill = Bill::create([
                'bill_number' => $billNumber,
                'supplier_id' => $request->supplier_id,
                'bill_date' => $request->bill_date,
                'due_date' => $request->due_date,
                'status' => 'draft',
                'notes' => $request->notes,
            ]);

            $subtotal = 0;
            $taxAmount = 0;
            $discountAmount = 0;

            foreach ($request->items as $item) {
                $quantity = $item['quantity'];
                $unitPrice = $item['unit_price'];
                $discountPercent = $item['discount_percentage'] ?? 0;
                $taxPercent = $item['tax_percentage'] ?? 0;

                $lineSubtotal = $quantity * $unitPrice;
                $lineDiscount = $lineSubtotal * ($discountPercent / 100);
                $lineTax = ($lineSubtotal - $lineDiscount) * ($taxPercent / 100);
                $lineTotal = $lineSubtotal - $lineDiscount + $lineTax;

                $bill->items()->create([
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_percentage' => $discountPercent,
                    'tax_percentage' => $taxPercent,
                    'total_amount' => $lineTotal,
                ]);

                $subtotal += $lineSubtotal;
                $discountAmount += $lineDiscount;
                $taxAmount += $lineTax;
            }

            $totalAmount = $subtotal - $discountAmount + $taxAmount;

            $bill->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'balance_amount' => $totalAmount,
            ]);

            DB::commit();

            $bill->load(['supplier', 'items.product']);

            return response()->json([
                'message' => 'Bill created successfully',
                'bill' => $bill
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating bill: ' . $e->getMessage()], 500);
        }
    }

    public function show(Bill $bill): JsonResponse
    {
        $bill->load(['supplier', 'items.product']);
        return response()->json(['bill' => $bill]);
    }

    public function update(Request $request, Bill $bill): JsonResponse
    {
        if ($bill->status !== 'draft') {
            return response()->json(['message' => 'Only draft bills can be updated'], 422);
        }

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:bill_date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $bill->update([
                'supplier_id' => $request->supplier_id,
                'bill_date' => $request->bill_date,
                'due_date' => $request->due_date,
                'notes' => $request->notes,
            ]);

            // Delete existing items
            $bill->items()->delete();

            $subtotal = 0;
            $taxAmount = 0;
            $discountAmount = 0;

            foreach ($request->items as $item) {
                $quantity = $item['quantity'];
                $unitPrice = $item['unit_price'];
                $discountPercent = $item['discount_percentage'] ?? 0;
                $taxPercent = $item['tax_percentage'] ?? 0;

                $lineSubtotal = $quantity * $unitPrice;
                $lineDiscount = $lineSubtotal * ($discountPercent / 100);
                $lineTax = ($lineSubtotal - $lineDiscount) * ($taxPercent / 100);
                $lineTotal = $lineSubtotal - $lineDiscount + $lineTax;

                $bill->items()->create([
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_percentage' => $discountPercent,
                    'tax_percentage' => $taxPercent,
                    'total_amount' => $lineTotal,
                ]);

                $subtotal += $lineSubtotal;
                $discountAmount += $lineDiscount;
                $taxAmount += $lineTax;
            }

            $totalAmount = $subtotal - $discountAmount + $taxAmount;

            $bill->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'balance_amount' => $totalAmount - $bill->paid_amount,
            ]);

            DB::commit();

            $bill->load(['supplier', 'items.product']);

            return response()->json([
                'message' => 'Bill updated successfully',
                'bill' => $bill
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating bill: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Bill $bill): JsonResponse
    {
        if ($bill->status !== 'draft') {
            return response()->json(['message' => 'Only draft bills can be deleted'], 422);
        }

        try {
            $bill->delete();
            return response()->json(['message' => 'Bill deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting bill: ' . $e->getMessage()], 500);
        }
    }

    public function getSuppliers(): JsonResponse
    {
        $suppliers = Supplier::where('is_active', true)->get();
        return response()->json(['suppliers' => $suppliers]);
    }

    public function getProducts(): JsonResponse
    {
        $products = Product::where('is_active', true)->get();
        return response()->json(['products' => $products]);
    }
}
