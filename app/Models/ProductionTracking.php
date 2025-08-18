<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionTracking extends Model
{
    use HasFactory;

    protected $table = 'production_tracking';

    protected $fillable = [
        'company_id',
        'work_order_id',
        'work_center_id',
        'operator_id',
        'operation_type',
        'start_time',
        'end_time',
        'duration_minutes',
        'quantity_produced',
        'quantity_rejected',
        'rejection_reason',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_minutes' => 'decimal:2',
        'quantity_produced' => 'decimal:4',
        'quantity_rejected' => 'decimal:4',
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

    public function workCenter(): BelongsTo
    {
        return $this->belongsTo(WorkCenter::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function productionCosts(): HasMany
    {
        return $this->hasMany(ProductionCost::class);
    }

    // Methods
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function getDurationHours(): float
    {
        return $this->duration_minutes / 60;
    }

    public function getEfficiency(): float
    {
        if ($this->quantity_produced <= 0) {
            return 0;
        }

        $totalQuantity = $this->quantity_produced + $this->quantity_rejected;
        return ($this->quantity_produced / $totalQuantity) * 100;
    }

    public function getRejectionRate(): float
    {
        $totalQuantity = $this->quantity_produced + $this->quantity_rejected;
        
        if ($totalQuantity <= 0) {
            return 0;
        }

        return ($this->quantity_rejected / $totalQuantity) * 100;
    }
}
