<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetVariance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'budget_id',
        'actual_amount',
        'variance',
        'variance_percentage',
        'variance_date',
        'variance_reason',
        'corrective_action',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'actual_amount' => 'decimal:2',
        'variance' => 'decimal:2',
        'variance_percentage' => 'decimal:2',
        'variance_date' => 'date',
    ];

    /**
     * Get the budget that owns the variance
     */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Get the user who created the variance
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the variance
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Scope a query to only include positive variances (over budget)
     */
    public function scopeOverBudget($query)
    {
        return $query->where('variance', '>', 0);
    }

    /**
     * Scope a query to only include negative variances (under budget)
     */
    public function scopeUnderBudget($query)
    {
        return $query->where('variance', '<', 0);
    }

    /**
     * Scope a query to only include variances within acceptable range
     */
    public function scopeWithinRange($query, $percentage = 10)
    {
        return $query->whereBetween('variance_percentage', [-$percentage, $percentage]);
    }

    /**
     * Check if the variance is over budget
     */
    public function isOverBudget(): bool
    {
        return $this->variance > 0;
    }

    /**
     * Check if the variance is under budget
     */
    public function isUnderBudget(): bool
    {
        return $this->variance < 0;
    }

    /**
     * Check if the variance is within acceptable range
     */
    public function isWithinRange(float $percentage = 10): bool
    {
        return abs($this->variance_percentage) <= $percentage;
    }

    /**
     * Get the variance status
     */
    public function getVarianceStatus(): string
    {
        if ($this->isWithinRange(5)) {
            return 'on_track';
        } elseif ($this->isWithinRange(10)) {
            return 'watch';
        } else {
            return 'over_budget';
        }
    }

    /**
     * Get the variance color for UI
     */
    public function getVarianceColor(): string
    {
        if ($this->isWithinRange(5)) {
            return 'text-green-600';
        } elseif ($this->isWithinRange(10)) {
            return 'text-yellow-600';
        } else {
            return 'text-red-600';
        }
    }
}
