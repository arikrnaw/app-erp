<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'customer_id',
        'balance',
        'last_updated',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'last_updated' => 'datetime',
    ];

    /**
     * Get the company that owns the customer balance.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the customer for this balance.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    /**
     * Check if customer has credit balance.
     */
    public function hasCredit(): bool
    {
        return $this->balance < 0;
    }

    /**
     * Check if customer has debit balance.
     */
    public function hasDebit(): bool
    {
        return $this->balance > 0;
    }

    /**
     * Get the absolute balance amount.
     */
    public function getAbsoluteBalanceAttribute(): float
    {
        return abs($this->balance);
    }
}
