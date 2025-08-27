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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

        // Calculate summary data
        $summary = [
            'total_bills' => Bill::count(),
            'total_amount' => Bill::sum('total_amount'),
            'overdue_amount' => Bill::where('due_date', '<', now())
                ->where('status', '!=', 'paid')
                ->where('status', '!=', 'cancelled')
                ->sum('balance_amount'),
            'paid_amount' => Bill::sum('paid_amount')
        ];

        return response()->json([
            'data' => $bills->items(),
            'pagination' => [
                'current_page' => $bills->currentPage(),
                'last_page' => $bills->lastPage(),
                'per_page' => $bills->perPage(),
                'total' => $bills->total(),
                'from' => $bills->firstItem(),
                'to' => $bills->lastItem(),
            ],
            'summary' => $summary
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after:bill_date',
            'description' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.description' => 'required|string',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_price' => 'required|numeric|min:0.01',
            'lines.*.tax_rate' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Generate bill number
            $billNumber = 'BILL-' . date('Y') . '-' . str_pad(Bill::whereYear('created_at', date('Y'))->count() + 1, 4, '0', STR_PAD_LEFT);

            $bill = Bill::create([
                'company_id' => Auth::user()->company_id,
                'bill_number' => $billNumber,
                'supplier_id' => $request->supplier_id,
                'bill_date' => $request->bill_date,
                'due_date' => $request->due_date,
                'status' => 'draft',
                'notes' => $request->description,
                'subtotal' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
                'paid_amount' => 0,
                'balance_amount' => 0,
                'created_by' => Auth::id(),
            ]);

            $subtotal = 0;
            $taxAmount = 0;
            $discountAmount = 0;

            foreach ($request->lines as $line) {
                $quantity = $line['quantity'];
                $unitPrice = $line['unit_price'];
                $taxRate = $line['tax_rate'] ?? 0;

                $lineSubtotal = $quantity * $unitPrice;
                $lineTax = $lineSubtotal * ($taxRate / 100);
                $lineTotal = $lineSubtotal + $lineTax;

                $bill->items()->create([
                    'bill_id' => $bill->id,
                    'description' => $line['description'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax_percentage' => $taxRate,
                    'tax_amount' => $lineTax,
                    'total_amount' => $lineTotal,
                ]);

                $subtotal += $lineSubtotal;
                $taxAmount += $lineTax;
            }

            $totalAmount = $subtotal + $taxAmount;

            $bill->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'balance_amount' => $totalAmount,
            ]);

            DB::commit();

            $bill->load(['supplier', 'items']);

            return response()->json([
                'success' => true,
                'message' => 'Bill created successfully',
                'data' => $bill
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating bill: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all(),
                'user_id' => Auth::id(),
                'company_id' => Auth::user()->company_id ?? 'null'
            ]);
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
            'items.*.product_id' => 'nullable|exists:products,id',
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
                    'product_id' => $item['product_id'] ?? null,
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

    public function postBill(Bill $bill): JsonResponse
    {
        // Add detailed logging
        Log::info('PostBill called', [
            'bill_id' => $bill->id,
            'bill_status' => $bill->status,
            'user_id' => Auth::id(),
            'company_id' => Auth::user()->company_id ?? 'null'
        ]);

        if ($bill->status !== 'draft') {
            Log::warning('PostBill failed - invalid status', [
                'bill_id' => $bill->id,
                'current_status' => $bill->status,
                'expected_status' => 'draft'
            ]);
            return response()->json(['message' => 'Only draft bills can be posted'], 422);
        }

        try {
            DB::beginTransaction();

            // Update bill status to posted
            $bill->update([
                'status' => 'posted',
                'posted_at' => now()
            ]);

            DB::commit();

            Log::info('PostBill successful', [
                'bill_id' => $bill->id,
                'new_status' => $bill->status,
                'posted_at' => $bill->posted_at
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bill posted successfully',
                'data' => $bill->load(['supplier', 'items'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PostBill failed with exception', [
                'bill_id' => $bill->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error posting bill: ' . $e->getMessage()
            ], 500);
        }
    }

    public function recordPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'bill_id' => 'required|exists:bills,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $bill = Bill::findOrFail($request->bill_id);

            // Check if bill can receive payment
            if ($bill->status !== 'posted') {
                return response()->json(['message' => 'Only posted bills can receive payments'], 422);
            }

            // Check if payment amount exceeds balance
            if ($request->amount > $bill->balance_amount) {
                return response()->json(['message' => 'Payment amount cannot exceed balance due'], 422);
            }

            // Generate payment number
            $paymentNumber = 'BP-' . date('Ymd') . '-' . str_pad(\App\Models\BillPayment::count() + 1, 4, '0', STR_PAD_LEFT);

            // Create payment record
            $payment = \App\Models\BillPayment::create([
                'payment_number' => $paymentNumber,
                'company_id' => Auth::user()->company_id,
                'bill_id' => $request->bill_id,
                'supplier_id' => $bill->supplier_id,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'amount' => $request->amount,
                'notes' => $request->description,
                'created_by' => Auth::id(),
                'status' => 'completed',
            ]);

            // Update bill paid amount and status
            $bill->increment('paid_amount', $request->amount);
            $bill->update(['balance_amount' => $bill->total_amount - $bill->paid_amount]);

            // Update bill status based on payment
            if ($bill->paid_amount >= $bill->total_amount) {
                $bill->update(['status' => 'paid']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'data' => $payment->load(['supplier', 'bill'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error recording payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testBill(Bill $bill): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Bill found successfully',
            'data' => [
                'id' => $bill->id,
                'bill_number' => $bill->bill_number,
                'status' => $bill->status,
                'company_id' => $bill->company_id,
                'created_by' => $bill->created_by,
                'posted_at' => $bill->posted_at,
                'updated_at' => $bill->updated_at
            ]
        ]);
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
