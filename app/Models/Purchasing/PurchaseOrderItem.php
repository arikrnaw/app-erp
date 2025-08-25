<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'unit_price',
        'tax_rate',
        'total_price',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    // Relationships
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    // Helper methods
    public function getFormattedUnitPrice()
    {
        return number_format($this->unit_price, 2);
    }

    public function getFormattedTotalPrice()
    {
        return number_format($this->total_price, 2);
    }

    public function getFormattedTaxRate()
    {
        return number_format($this->tax_rate, 2) . '%';
    }

    public function getTaxAmount()
    {
        return $this->total_price * ($this->tax_rate / 100);
    }

    public function getFormattedTaxAmount()
    {
        return number_format($this->getTaxAmount(), 2);
    }

    // Events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            // Calculate total price
            $item->total_price = $item->quantity * $item->unit_price;
        });

        static::updating(function ($item) {
            // Recalculate total price if quantity or unit price changed
            if ($item->isDirty(['quantity', 'unit_price'])) {
                $item->total_price = $item->quantity * $item->unit_price;
            }
        });
    }
}
