<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'plan_number',
        'name',
        'description',
        'product_id',
        'bill_of_material_id',
        'planned_quantity',
        'unit',
        'start_date',
        'end_date',
        'due_date',
        'priority',
        'status',
        'estimated_cost',
        'actual_cost',
        'warehouse_id',
        'created_by',
        'approved_by',
        'approved_at',
        'approval_notes',
        'notes',
    ];

    protected $casts = [
        'planned_quantity' => 'decimal:4',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'due_date' => 'date',
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

    public function billOfMaterial(): BelongsTo
    {
        return $this->belongsTo(BillOfMaterial::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    // Methods
    public function generatePlanNumber(): string
    {
        $prefix = 'PP';
        $year = date('Y');
        $month = date('m');
        
        $lastPlan = self::where('plan_number', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('plan_number', 'desc')
            ->first();

        if ($lastPlan) {
            $lastNumber = (int) substr($lastPlan->plan_number, -4);
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

    public function getProgressPercentage(): float
    {
        if ($this->workOrders()->count() === 0) {
            return 0;
        }

        $totalWorkOrders = $this->workOrders()->count();
        $completedWorkOrders = $this->workOrders()->where('status', 'completed')->count();

        return ($completedWorkOrders / $totalWorkOrders) * 100;
    }
}
