<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaxTransaction;
use App\Models\TaxRate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TaxTransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TaxTransaction::with(['taxRate']);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('taxRate', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Transaction type filter
        if ($request->has('transaction_type') && $request->transaction_type !== 'all') {
            $query->where('transaction_type', $request->transaction_type);
        }

        // Date filter
        if ($request->has('date')) {
            $query->whereDate('transaction_date', $request->date);
        }

        // Date range filter
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
        }

        $taxTransactions = $query->orderBy('transaction_date', 'desc')->paginate(15);

        return response()->json($taxTransactions);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'transaction_type' => 'required|in:sale,purchase,adjustment',
            'reference_type' => 'required|string',
            'reference_id' => 'required|integer',
            'tax_rate_id' => 'required|exists:tax_rates,id',
            'taxable_amount' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'status' => 'in:pending,filed,paid',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Generate transaction number
            $transactionNumber = 'TAX-' . date('Y') . '-' . str_pad(TaxTransaction::whereYear('created_at', date('Y'))->count() + 1, 4, '0', STR_PAD_LEFT);

            $taxTransaction = TaxTransaction::create([
                'transaction_number' => $transactionNumber,
                'transaction_type' => $request->transaction_type,
                'reference_type' => $request->reference_type,
                'reference_id' => $request->reference_id,
                'tax_rate_id' => $request->tax_rate_id,
                'taxable_amount' => $request->taxable_amount,
                'tax_amount' => $request->tax_amount,
                'transaction_date' => $request->transaction_date,
                'status' => $request->status ?? 'pending',
                'notes' => $request->notes,
            ]);

            DB::commit();

            $taxTransaction->load('taxRate');

            return response()->json([
                'message' => 'Tax transaction created successfully',
                'tax_transaction' => $taxTransaction
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating tax transaction: ' . $e->getMessage()], 500);
        }
    }

    public function show(TaxTransaction $taxTransaction): JsonResponse
    {
        $taxTransaction->load('taxRate');
        return response()->json(['tax_transaction' => $taxTransaction]);
    }

    public function update(Request $request, TaxTransaction $taxTransaction): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'transaction_type' => 'required|in:sale,purchase,adjustment',
            'reference_type' => 'required|string',
            'reference_id' => 'required|integer',
            'tax_rate_id' => 'required|exists:tax_rates,id',
            'taxable_amount' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'status' => 'in:pending,filed,paid',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $taxTransaction->update([
                'transaction_type' => $request->transaction_type,
                'reference_type' => $request->reference_type,
                'reference_id' => $request->reference_id,
                'tax_rate_id' => $request->tax_rate_id,
                'taxable_amount' => $request->taxable_amount,
                'tax_amount' => $request->tax_amount,
                'transaction_date' => $request->transaction_date,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            $taxTransaction->load('taxRate');

            return response()->json([
                'message' => 'Tax transaction updated successfully',
                'tax_transaction' => $taxTransaction
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating tax transaction: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(TaxTransaction $taxTransaction): JsonResponse
    {
        try {
            $taxTransaction->delete();
            return response()->json(['message' => 'Tax transaction deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting tax transaction: ' . $e->getMessage()], 500);
        }
    }

    public function getTaxRates(): JsonResponse
    {
        $taxRates = TaxRate::where('is_active', true)->orderBy('name')->get();
        return response()->json(['tax_rates' => $taxRates]);
    }

    public function getSummary(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $summary = TaxTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('
                transaction_type,
                status,
                COUNT(*) as transaction_count,
                SUM(taxable_amount) as total_taxable_amount,
                SUM(tax_amount) as total_tax_amount
            ')
            ->groupBy('transaction_type', 'status')
            ->get();

        $totalTaxAmount = TaxTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('tax_amount');

        return response()->json([
            'summary' => $summary,
            'total_tax_amount' => $totalTaxAmount,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ]);
    }
}
