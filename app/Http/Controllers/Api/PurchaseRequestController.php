<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseRequest::with(['requestedBy', 'department', 'approvedBy', 'items.product'])
            ->where('company_id', auth()->user()->company_id);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhereHas('requestedBy', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
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

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->where('request_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('request_date', '<=', $request->end_date);
        }

        $purchaseRequests = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($purchaseRequests);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_date' => 'required|date',
            'required_date' => 'required|date|after_or_equal:request_date',
            'priority' => 'required|in:low,medium,high,urgent',
            'department_id' => 'nullable|exists:departments,id',
            'purpose' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,submitted,approved,rejected,cancelled',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'nullable|string',
            'items.*.specifications' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit' => 'required|string|max:50',
            'items.*.estimated_unit_price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $purchaseRequest = PurchaseRequest::create([
                'company_id' => auth()->user()->company_id,
                'request_number' => $request->request_number ?: $this->generateRequestNumber(),
                'requested_by' => auth()->id(),
                'department_id' => $request->department_id,
                'request_date' => $request->request_date,
                'required_date' => $request->required_date,
                'priority' => $request->priority,
                'status' => $request->status,
                'purpose' => $request->purpose,
                'notes' => $request->notes,
                'total_estimated_cost' => $request->total_estimated_cost ?: 0,
                'approved_by' => $request->status === 'approved' ? auth()->id() : null,
                'approved_at' => $request->status === 'approved' ? now() : null,
                'approval_notes' => $request->approval_notes,
            ]);

            // Create items
            foreach ($request->items as $item) {
                PurchaseRequestItem::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'specifications' => $item['specifications'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'estimated_unit_price' => $item['estimated_unit_price'] ?? 0,
                    'estimated_total_price' => ($item['quantity'] ?? 0) * ($item['estimated_unit_price'] ?? 0),
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            $purchaseRequest->load(['requestedBy', 'department', 'approvedBy', 'items.product']);

            return response()->json([
                'message' => 'Purchase request created successfully',
                'purchase_request' => $purchaseRequest
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating purchase request: ' . $e->getMessage()], 500);
        }
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->load(['requestedBy', 'department', 'approvedBy', 'items.product', 'purchaseOrders']);
        
        return response()->json([
            'data' => $purchaseRequest
        ]);
    }

    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        $validator = Validator::make($request->all(), [
            'request_date' => 'required|date',
            'required_date' => 'required|date|after_or_equal:request_date',
            'priority' => 'required|in:low,medium,high,urgent',
            'department_id' => 'nullable|exists:departments,id',
            'purpose' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,submitted,approved,rejected,cancelled',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'nullable|string',
            'items.*.specifications' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit' => 'required|string|max:50',
            'items.*.estimated_unit_price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $purchaseRequest->update([
                'department_id' => $request->department_id,
                'request_date' => $request->request_date,
                'required_date' => $request->required_date,
                'priority' => $request->priority,
                'status' => $request->status,
                'purpose' => $request->purpose,
                'notes' => $request->notes,
                'total_estimated_cost' => $request->total_estimated_cost ?: 0,
                'approved_by' => $request->status === 'approved' ? auth()->id() : $purchaseRequest->approved_by,
                'approved_at' => $request->status === 'approved' ? now() : $purchaseRequest->approved_at,
                'approval_notes' => $request->approval_notes,
            ]);

            // Delete existing items
            $purchaseRequest->items()->delete();

            // Create new items
            foreach ($request->items as $item) {
                PurchaseRequestItem::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'specifications' => $item['specifications'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'estimated_unit_price' => $item['estimated_unit_price'] ?? 0,
                    'estimated_total_price' => ($item['quantity'] ?? 0) * ($item['estimated_unit_price'] ?? 0),
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            $purchaseRequest->load(['requestedBy', 'department', 'approvedBy', 'items.product']);

            return response()->json([
                'message' => 'Purchase request updated successfully',
                'purchase_request' => $purchaseRequest
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating purchase request: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(PurchaseRequest $purchaseRequest)
    {
        try {
            DB::beginTransaction();
            
            // Delete items first
            $purchaseRequest->items()->delete();
            
            // Delete the purchase request
            $purchaseRequest->delete();
            
            DB::commit();

            return response()->json(['message' => 'Purchase request deleted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error deleting purchase request: ' . $e->getMessage()], 500);
        }
    }

    public function generateNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $lastRequest = PurchaseRequest::where('request_number', 'like', "PR{$year}{$month}%")
            ->orderBy('request_number', 'desc')
            ->first();

        if ($lastRequest) {
            $lastNumber = intval(substr($lastRequest->request_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $requestNumber = "PR{$year}{$month}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        return response()->json([
            'request_number' => $requestNumber
        ]);
    }

    private function generateRequestNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $lastRequest = PurchaseRequest::where('request_number', 'like', "PR{$year}{$month}%")
            ->orderBy('request_number', 'desc')
            ->first();

        if ($lastRequest) {
            $lastNumber = intval(substr($lastRequest->request_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "PR{$year}{$month}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
