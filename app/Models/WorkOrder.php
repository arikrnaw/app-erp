<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'work_order_number',
        'name',
        'description',
        'production_plan_id',
        'product_id',
        'bill_of_material_id',
        'planned_quantity',
        'completed_quantity',
        'unit',
        'start_date',
        'due_date',
        'priority',
        'status',
        'estimated_hours',
        'actual_hours',
        'estimated_cost',
        'actual_cost',
        'warehouse_id',
        'work_center_id',
        'assigned_to',
        'created_by',
        'approved_by',
        'approved_at',
        'started_at',
        'completed_at',
        'approval_notes',
        'notes',
    ];

    protected $casts = [
        'planned_quantity' => 'decimal:4',
        'completed_quantity' => 'decimal:4',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'start_date' => 'date',
        'due_date' => 'date',
        'approved_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function productionPlan(): BelongsTo
    {
        return $this->belongsTo(ProductionPlan::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function billOfMaterial(): BelongsTo
    {
        return $this->belongsTo(BillOfMaterial::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function workCenter(): BelongsTo
    {
        return $this->belongsTo(WorkCenter::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function productionTracking(): HasMany
    {
        return $this->hasMany(ProductionTracking::class);
    }

    public function productionCosts(): HasMany
    {
        return $this->hasMany(ProductionCost::class);
    }

    // Methods
    public function generateWorkOrderNumber(): string
    {
        $prefix = 'WO';
        $year = date('Y');
        $month = date('m');
        
        $lastWorkOrder = self::where('work_order_number', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('work_order_number', 'desc')
            ->first();

        if ($lastWorkOrder) {
            $lastNumber = (int) substr($lastWorkOrder->work_order_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s%s%s%04d', $prefix, $year, $month, $newNumber);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved' && $this->approved_at !== null;
    }

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

    public function getProgressPercentage(): float
    {
        if ($this->planned_quantity <= 0) {
            return 0;
        }

        return ($this->completed_quantity / $this->planned_quantity) * 100;
    }

    public function getRemainingQuantity(): float
    {
        return $this->planned_quantity - $this->completed_quantity;
    }

    public function getEfficiency(): float
    {
        if ($this->estimated_hours <= 0) {
            return 0;
        }

        return ($this->estimated_hours / $this->actual_hours) * 100;
    }
}
