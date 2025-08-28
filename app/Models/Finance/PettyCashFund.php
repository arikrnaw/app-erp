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
        'current_balance',
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
        'current_balance' => 'decimal:2',
        'last_replenishment_date' => 'date',
        'replenishment_threshold' => 'decimal:2'
    ];

    protected $attributes = [
        'status' => 'active',
        'currency' => 'IDR'
    ];

    /**
     * Get the balance attribute (alias for current_balance)
     */
    public function getBalanceAttribute()
    {
        return $this->current_balance;
    }

    /**
     * Get the petty cash transactions for this fund
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class, 'petty_cash_fund_id');
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
        if (!$this->replenishment_threshold) {
            return false;
        }
        return $this->current_balance <= $this->replenishment_threshold;
    }

    /**
     * Get formatted balance
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->current_balance, 2);
    }

    /**
     * Scope for active funds
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for funds by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for funds by currency
     */
    public function scopeByCurrency($query, $currency)
    {
        return $query->where('currency', $currency);
    }
}
