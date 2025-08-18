<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'product_id',
        'item_name',
        'description',
        'specifications',
        'quantity',
        'unit',
        'estimated_unit_price',
        'estimated_total_price',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'estimated_unit_price' => 'decimal:2',
        'estimated_total_price' => 'decimal:2',
    ];

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
