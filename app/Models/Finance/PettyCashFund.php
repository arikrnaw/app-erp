<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PettyCashFund extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'custodian',
        'initial_amount',
        'current_amount',
        'currency',
        'description',
        'location',
        'status',
        'last_replenishment_date',
        'replenishment_threshold',
        'notes'
    ];

    protected $casts = [
        'initial_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'replenishment_threshold' => 'decimal:2',
        'last_replenishment_date' => 'date'
    ];

    protected $attributes = [
        'status' => 'active',
        'current_amount' => 0,
        'replenishment_threshold' => 0
    ];

    /**
     * Get the cash transactions for this petty cash fund
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class);
    }

    /**
     * Get the balance attribute (alias for current_amount)
     */
    public function getBalanceAttribute()
    {
        return $this->current_amount;
    }

    /**
     * Get the status label
     */
    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }

    /**
     * Check if fund is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if fund needs replenishment
     */
    public function needsReplenishment(): bool
    {
        return $this->current_amount <= $this->replenishment_threshold;
    }

    /**
     * Get the amount needed for replenishment
     */
    public function getReplenishmentAmount(): float
    {
        return $this->initial_amount - $this->current_amount;
    }

    /**
     * Get formatted current amount
     */
    public function getFormattedCurrentAmountAttribute(): string
    {
        return number_format($this->current_amount, 2);
    }

    /**
     * Get formatted initial amount
     */
    public function getFormattedInitialAmountAttribute(): string
    {
        return number_format($this->initial_amount, 2);
    }

    /**
     * Scope for active funds
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for funds by currency
     */
    public function scopeByCurrency($query, $currency)
    {
        return $query->where('currency', $currency);
    }

    /**
     * Scope for funds that need replenishment
     */
    public function scopeNeedsReplenishment($query)
    {
        return $query->whereRaw('current_amount <= replenishment_threshold');
    }
}
