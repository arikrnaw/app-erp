<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_of_material_id',
        'product_id',
        'item_name',
        'description',
        'quantity_required',
        'unit',
        'unit_cost',
        'total_cost',
        'sequence',
        'is_critical',
        'notes',
    ];

    protected $casts = [
        'quantity_required' => 'decimal:4',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'is_critical' => 'boolean',
    ];

    // Relationships
    public function billOfMaterial(): BelongsTo
    {
        return $this->belongsTo(BillOfMaterial::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Methods
    public function calculateTotalCost(): float
    {
        return $this->quantity_required * $this->unit_cost;
    }

    public function isCritical(): bool
    {
        return $this->is_critical;
    }
}
