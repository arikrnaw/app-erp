<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'order_number',
        'order_date',
        'expected_delivery_date',
        'subtotal',
        'tax_amount',
        'shipping_cost',
        'total_amount',
        'status',
        'notes',
        'terms_conditions',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'draft',
    ];

    // Relationships
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function approvalRequest(): MorphOne
    {
        return $this->morphOne(\App\Models\Finance\ApprovalRequest::class, 'approvable');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('order_date', [$startDate, $endDate]);
    }

    public function scopeByAmountRange($query, $minAmount, $maxAmount)
    {
        return $query->whereBetween('total_amount', [$minAmount, $maxAmount]);
    }

    // Helper methods
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isPendingApproval()
    {
        return $this->status === 'pending_approval';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function canEdit()
    {
        return in_array($this->status, ['draft', 'pending_approval']);
    }

    public function canCancel()
    {
        return in_array($this->status, ['draft', 'pending_approval']);
    }

    public function canApprove()
    {
        return $this->isPendingApproval() && $this->approvalRequest;
    }

    public function getFormattedOrderNumber()
    {
        return 'PO-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getFormattedTotalAmount()
    {
        return number_format($this->total_amount, 2);
    }

    public function getFormattedOrderDate()
    {
        return $this->order_date->format('M d, Y');
    }

    public function getFormattedExpectedDeliveryDate()
    {
        return $this->expected_delivery_date->format('M d, Y');
    }

    public function getStatusBadgeVariant()
    {
        return match($this->status) {
            'draft' => 'secondary',
            'pending_approval' => 'warning',
            'approved' => 'default',
            'rejected' => 'destructive',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    public function getStatusLabel()
    {
        return match($this->status) {
            'draft' => 'Draft',
            'pending_approval' => 'Pending Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
            default => 'Unknown',
        };
    }

    // Events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($purchaseOrder) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $purchaseOrder->created_by = \Illuminate\Support\Facades\Auth::id();
            }
            
            // Generate order number
            $purchaseOrder->order_number = 'PO-' . str_pad(static::max('id') + 1, 6, '0', STR_PAD_LEFT);
        });

        static::updating(function ($purchaseOrder) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $purchaseOrder->updated_by = \Illuminate\Support\Facades\Auth::id();
            }
        });
    }
}
