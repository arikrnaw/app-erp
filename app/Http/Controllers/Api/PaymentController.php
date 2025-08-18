<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with(['customer', 'invoice', 'created_by_user'])
            ->where('company_id', Auth::user()->company_id);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'ilike', "%{$search}%")
                  ->orWhere('reference_number', 'ilike', "%{$search}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($search) {
                      $customerQuery->where('name', 'ilike', "%{$search}%");
                  })
                  ->orWhereHas('invoice', function ($invoiceQuery) use ($search) {
                      $invoiceQuery->where('invoice_number', 'ilike', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->where('payment_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        $payments = $query->orderBy('payment_date', 'desc')
                         ->orderBy('id', 'desc')
                         ->paginate(15);

        // Calculate summary
        $summary = $this->calculateSummary($request);

        return response()->json([
            'data' => $payments->items(),
            'meta' => $payments->toArray(),
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
            'invoice_id' => 'nullable|exists:invoices,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,credit_card,check,other',
            'reference_number' => 'nullable|string|max:50',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $payment = Payment::create([
                'company_id' => Auth::user()->company_id,
                'payment_number' => Payment::generatePaymentNumber(Auth::user()->company_id),
                'customer_id' => $request->customer_id,
                'invoice_id' => $request->invoice_id,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'amount' => $request->amount,
                'notes' => $request->notes,
                'status' => 'completed',
                'created_by' => Auth::id(),
            ]);

            // Update invoice if payment is linked to an invoice
            if ($request->invoice_id) {
                $invoice = Invoice::find($request->invoice_id);
                if ($invoice && $invoice->company_id === Auth::user()->company_id) {
                    $invoice->paid_amount += $request->amount;
                    $invoice->balance_amount = $invoice->total_amount - $invoice->paid_amount;
                    
                    // Update invoice status
                    if ($invoice->balance_amount <= 0) {
                        $invoice->status = 'paid';
                    } elseif ($invoice->due_date < now()->toDateString()) {
                        $invoice->status = 'overdue';
                    } else {
                        $invoice->status = 'sent';
                    }
                    
                    $invoice->save();
                }
            }

            DB::commit();

            $payment->load(['customer', 'invoice', 'created_by_user']);

            return response()->json([
                'message' => 'Payment created successfully',
                'payment' => $payment
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment): JsonResponse
    {
        // Check if the payment belongs to the user's company
        if ($payment->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $payment->load(['customer', 'invoice', 'created_by_user']);

        return response()->json([
            'data' => $payment
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment): JsonResponse
    {
        // Check if the payment belongs to the user's company
        if ($payment->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        // Only allow updates for pending payments
        if ($payment->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot update non-pending payment'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,credit_card,check,other',
            'reference_number' => 'nullable|string|max:50',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $payment->update([
                'customer_id' => $request->customer_id,
                'invoice_id' => $request->invoice_id,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'amount' => $request->amount,
                'notes' => $request->notes,
            ]);

            DB::commit();

            $payment->load(['customer', 'invoice', 'created_by_user']);

            return response()->json([
                'message' => 'Payment updated successfully',
                'payment' => $payment
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment): JsonResponse
    {
        // Check if the payment belongs to the user's company
        if ($payment->company_id !== Auth::user()->company_id) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        // Only allow deletion for pending payments
        if ($payment->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot delete non-pending payment'
            ], 422);
        }

        $payment->delete();

        return response()->json([
            'message' => 'Payment deleted successfully'
        ]);
    }

    /**
     * Calculate summary statistics
     */
    private function calculateSummary(Request $request): array
    {
        $query = Payment::where('company_id', Auth::user()->company_id);

        // Apply same filters as main query
        if ($request->has('status') && $request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->where('payment_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        $totalPayments = $query->count();
        $completedPayments = $query->where('status', 'completed')->count();
        $pendingPayments = $query->where('status', 'pending')->count();
        $totalAmount = $query->sum('amount');

        return [
            'total_payments' => $totalPayments,
            'completed_payments' => $completedPayments,
            'pending_payments' => $pendingPayments,
            'total_amount' => $totalAmount,
        ];
    }
}
