<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillOfMaterial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'bom_number',
        'name',
        'description',
        'product_id',
        'quantity_per_unit',
        'unit',
        'total_cost',
        'status',
        'effective_date',
        'expiry_date',
        'created_by',
        'approved_by',
        'approved_at',
        'approval_notes',
        'notes',
    ];

    protected $casts = [
        'quantity_per_unit' => 'decimal:4',
        'total_cost' => 'decimal:2',
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BomItem::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function productionPlans(): HasMany
    {
        return $this->hasMany(ProductionPlan::class);
    }

    // Methods
    public function generateBomNumber(): string
    {
        $prefix = 'BOM';
        $year = date('Y');
        $month = date('m');
        
        $lastBom = self::where('bom_number', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('bom_number', 'desc')
            ->first();

        if ($lastBom) {
            $lastNumber = (int) substr($lastBom->bom_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s%s%s%04d', $prefix, $year, $month, $newNumber);
    }

    public function calculateTotalCost(): float
    {
        return $this->items()->sum('total_cost');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isApproved(): bool
    {
        return $this->status === 'active' && $this->approved_at !== null;
    }
}
