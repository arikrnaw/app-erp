<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'work_order_id',
        'production_tracking_id',
        'cost_type',
        'cost_category',
        'description',
        'quantity',
        'unit',
        'unit_cost',
        'total_cost',
        'cost_date',
        'recorded_by',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'cost_date' => 'date',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function productionTracking(): BelongsTo
    {
        return $this->belongsTo(ProductionTracking::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // Methods
    public function calculateTotalCost(): float
    {
        return $this->quantity * $this->unit_cost;
    }

    public function isMaterialCost(): bool
    {
        return $this->cost_type === 'material';
    }

    public function isLaborCost(): bool
    {
        return $this->cost_type === 'labor';
    }

    public function isOverheadCost(): bool
    {
        return $this->cost_type === 'overhead';
    }

    public function isMachineCost(): bool
    {
        return $this->cost_type === 'machine';
    }
}
