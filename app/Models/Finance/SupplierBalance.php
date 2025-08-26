<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'supplier_id',
        'balance',
        'last_updated',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'last_updated' => 'datetime',
    ];

    /**
     * Get the company that owns the supplier balance.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Supplier::class);
    }

    /**
     * Get the supplier for this balance.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Supplier::class);
    }

    /**
     * Check if supplier has credit balance.
     */
    public function hasCredit(): bool
    {
        return $this->balance < 0;
    }

    /**
     * Check if supplier has debit balance.
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
