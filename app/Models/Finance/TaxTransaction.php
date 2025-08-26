<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'tax_rate_id',
        'transaction_type',
        'transactionable_type',
        'transactionable_id',
        'transaction_date',
        'taxable_amount',
        'tax_amount',
        'description',
        'reference_number',
        'journal_entry_id',
        'is_posted',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'taxable_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'is_posted' => 'boolean',
    ];

    protected $attributes = [
        'is_posted' => false,
    ];

    /**
     * Get the company that owns the tax transaction.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the tax rate for this transaction.
     */
    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    /**
     * Get the user who created the tax transaction.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the journal entry for this transaction.
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(\App\Models\JournalEntry::class, 'journal_entry_id');
    }

    /**
     * Get the parent transaction (polymorphic).
     */
    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to get only posted transactions.
     */
    public function scopePosted($query)
    {
        return $query->where('is_posted', true);
    }

    /**
     * Scope to get only unposted transactions.
     */
    public function scopeUnposted($query)
    {
        return $query->where('is_posted', false);
    }

    /**
     * Scope to get transactions by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope to get transactions by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Check if the transaction is a sales tax.
     */
    public function isSalesTax(): bool
    {
        return $this->transaction_type === 'sales_tax';
    }

    /**
     * Check if the transaction is a purchase tax.
     */
    public function isPurchaseTax(): bool
    {
        return $this->transaction_type === 'purchase_tax';
    }

    /**
     * Check if the transaction is an adjustment.
     */
    public function isAdjustment(): bool
    {
        return $this->transaction_type === 'adjustment';
    }

    /**
     * Get the tax rate percentage.
     */
    public function getTaxRatePercentageAttribute(): float
    {
        return $this->taxRate ? $this->taxRate->rate : 0;
    }

    /**
     * Calculate the effective tax rate.
     */
    public function getEffectiveTaxRateAttribute(): float
    {
        if ($this->taxable_amount == 0) return 0;
        return ($this->tax_amount / $this->taxable_amount) * 100;
    }
}
