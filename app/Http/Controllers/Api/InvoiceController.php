<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::with(['customer', 'created_by_user', 'items'])
            ->where('company_id', Auth::user()->company_id);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'ilike', "%{$search}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($search) {
                      $customerQuery->where('name', 'ilike', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->where('invoice_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('invoice_date', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('invoice_date', 'desc')
                         ->orderBy('id', 'desc')
                         ->paginate(15);

        // Calculate summary
        $summary = $this->calculateSummary($request);

        return response()->json([
            'data' => $invoices->items(),
            'meta' => $invoices->toArray(),
            'summary' => $summary
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'reference_type' => 'nullable|string|max:50',
            'reference_id' => 'nullable|integer',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Calculate totals
            $subtotal = 0;
            $taxAmount = 0;
            $discountAmount = 0;

            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $discount = $lineTotal * ($item['discount_rate'] ?? 0) / 100;
                $taxableAmount = $lineTotal - $discount;
                $tax = $taxableAmount * ($item['tax_rate'] ?? 0) / 100;

                $subtotal += $lineTotal;
                $discountAmount += $discount;
                $taxAmount += $tax;
            }

            $totalAmount = $subtotal - $discountAmount + $taxAmount;

            $invoice = Invoice::create([
                'company_id' => Auth::user()->company_id,
                'invoice_number' => Invoice::generateInvoiceNumber(Auth::user()->company_id),
                'customer_id' => $request->customer_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'reference_type' => $request->reference_type,
                'reference_id' => $request->reference_id,
                'description' => $request->description,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'balance_amount' => $totalAmount,
                'status' => 'draft',
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            // Create invoice items
            foreach ($request->items as $index => $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $discount = $lineTotal * ($item['discount_rate'] ?? 0) / 100;
                $taxableAmount = $lineTotal - $discount;
                $tax = $taxableAmount * ($item['tax_rate'] ?? 0) / 100;
                $total = $lineTotal - $discount + $tax;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $tax,
                    'discount_rate' => $item['discount_rate'] ?? 0,
                    'discount_amount' => $discount,
                    'total_amount' => $total,
                    'line_number' => $index + 1,
                ]);
            }

            DB::commit();

            $invoice->load(['customer', 'created_by_user', 'items']);

            return response()->json([
                'message' => 'Invoice created successfully',
                'invoice' => $invoice
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice): JsonResponse
    {
        // Check if the invoice belongs to the user's company
        if ($invoice->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $invoice->load(['customer', 'created_by_user', 'items.product', 'payments']);

        return response()->json([
            'data' => $invoice
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        // Check if the invoice belongs to the user's company
        if ($invoice->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        // Only allow updates for draft invoices
        if ($invoice->status !== 'draft') {
            return response()->json([
                'message' => 'Cannot update non-draft invoice'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'reference_type' => 'nullable|string|max:50',
            'reference_id' => 'nullable|integer',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Calculate totals
            $subtotal = 0;
            $taxAmount = 0;
            $discountAmount = 0;

            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $discount = $lineTotal * ($item['discount_rate'] ?? 0) / 100;
                $taxableAmount = $lineTotal - $discount;
                $tax = $taxableAmount * ($item['tax_rate'] ?? 0) / 100;

                $subtotal += $lineTotal;
                $discountAmount += $discount;
                $taxAmount += $tax;
            }

            $totalAmount = $subtotal - $discountAmount + $taxAmount;
            $balanceAmount = $totalAmount - $invoice->paid_amount;

            $invoice->update([
                'customer_id' => $request->customer_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'reference_type' => $request->reference_type,
                'reference_id' => $request->reference_id,
                'description' => $request->description,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'balance_amount' => $balanceAmount,
                'notes' => $request->notes,
            ]);

            // Delete existing items
            $invoice->items()->delete();

            // Create new invoice items
            foreach ($request->items as $index => $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $discount = $lineTotal * ($item['discount_rate'] ?? 0) / 100;
                $taxableAmount = $lineTotal - $discount;
                $tax = $taxableAmount * ($item['tax_rate'] ?? 0) / 100;
                $total = $lineTotal - $discount + $tax;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $tax,
                    'discount_rate' => $item['discount_rate'] ?? 0,
                    'discount_amount' => $discount,
                    'total_amount' => $total,
                    'line_number' => $index + 1,
                ]);
            }

            DB::commit();

            $invoice->load(['customer', 'created_by_user', 'items']);

            return response()->json([
                'message' => 'Invoice updated successfully',
                'invoice' => $invoice
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        // Check if the invoice belongs to the user's company
        if ($invoice->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        // Only allow deletion for draft invoices
        if ($invoice->status !== 'draft') {
            return response()->json([
                'message' => 'Cannot delete non-draft invoice'
            ], 422);
        }

        $invoice->delete();

        return response()->json([
            'message' => 'Invoice deleted successfully'
        ]);
    }

    /**
     * Calculate summary statistics
     */
    private function calculateSummary(Request $request): array
    {
        $query = Invoice::where('company_id', Auth::user()->company_id);

        // Apply same filters as main query
        if ($request->has('status') && $request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->where('invoice_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('invoice_date', '<=', $request->date_to);
        }

        $totalInvoices = $query->count();
        $totalAmount = $query->sum('total_amount');
        $outstandingAmount = $query->whereNotIn('status', ['paid', 'cancelled'])->sum('balance_amount');
        $overdueAmount = $query->overdue()->sum('balance_amount');

        return [
            'total_invoices' => $totalInvoices,
            'total_amount' => $totalAmount,
            'outstanding_amount' => $outstandingAmount,
            'overdue_amount' => $overdueAmount,
        ];
    }
}
