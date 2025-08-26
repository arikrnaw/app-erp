<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetPeriod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'fiscal_year',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'fiscal_year' => 'string',
    ];

    protected $attributes = [
        'status' => 'planning',
    ];

    /**
     * Get the company that owns the budget period
     */
    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the budgets for this period
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class, 'period_id');
    }

    /**
     * Get the user who created the budget period
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the budget period
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Scope a query to only include active periods
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include planning periods
     */
    public function scopePlanning($query)
    {
        return $query->where('status', 'planning');
    }

    /**
     * Check if the period is currently active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the period is currently in planning
     */
    public function isPlanning(): bool
    {
        return $this->status === 'planning';
    }

    /**
     * Get the duration of the period in days
     */
    public function getDurationInDays(): int
    {
        return $this->start_date->diffInDays($this->end_date);
    }

    /**
     * Get the progress percentage of the period
     */
    public function getProgressPercentage(): float
    {
        $totalDays = $this->getDurationInDays();
        $elapsedDays = $this->start_date->diffInDays(now());
        
        if ($totalDays === 0) return 0;
        
        $progress = ($elapsedDays / $totalDays) * 100;
        return min(max($progress, 0), 100);
    }
}
