<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'purchase_request_id',
        'supplier_id',
        'warehouse_id',
        'po_number',
        'reference_number',
        'order_date',
        'expected_delivery_date',
        'payment_terms',
        'shipping_address',
        'billing_address',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_cost',
        'handling_cost',
        'total_amount',
        'delivery_method',
        'carrier',
        'tracking_number',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
        'approval_notes',
        'updated_by'
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'approved_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'handling_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    protected $appends = [
        'formatted_total_amount',
        'formatted_order_date',
        'formatted_expected_delivery_date'
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('po_number', 'like', "%{$search}%")
              ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                  $supplierQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('code', 'like', "%{$search}%");
              });
        });
    }

    public function scopeStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    public function scopeDateFilter($query, $filter)
    {
        return match ($filter) {
            'today' => $query->whereDate('order_date', today()),
            'week' => $query->whereBetween('order_date', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereMonth('order_date', now()->month)->whereYear('order_date', now()->year),
            'year' => $query->whereYear('order_date', now()->year),
            default => $query
        };
    }

    // Accessors
    public function getFormattedTotalAmountAttribute(): string
    {
        return number_format($this->total_amount, 2);
    }

    public function getFormattedOrderDateAttribute(): string
    {
        return $this->order_date?->format('M d, Y') ?? 'N/A';
    }

    public function getFormattedExpectedDeliveryDateAttribute(): string
    {
        return $this->expected_delivery_date?->format('M d, Y') ?? 'N/A';
    }

    // Methods
    public function calculateTotalAmount(): void
    {
        $this->total_amount = $this->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
        $this->save();
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'confirmed']);
    }

    public function canBeDeleted(): bool
    {
        return $this->status === 'draft';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'confirmed']);
    }

    public function canBeReceived(): bool
    {
        return $this->status === 'confirmed';
    }
}
