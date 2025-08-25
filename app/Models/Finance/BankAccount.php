<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'account_number',
        'description',
        'bank_name',
        'bank_branch',
        'swift_code',
        'iban',
        'currency',
        'opening_balance',
        'opening_date',
        'account_type',
        'status',
        'reconcile_automatically',
        'allow_overdraft',
        'include_in_cash_flow',
        'notes',
        'last_reconciled_date',
        'balance'
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'balance' => 'decimal:2',
        'opening_date' => 'date',
        'last_reconciled_date' => 'date',
        'reconcile_automatically' => 'boolean',
        'allow_overdraft' => 'boolean',
        'include_in_cash_flow' => 'boolean'
    ];

    protected $attributes = [
        'status' => 'active',
        'balance' => 0,
        'reconcile_automatically' => false,
        'allow_overdraft' => false,
        'include_in_cash_flow' => true
    ];

    /**
     * Get the cash transactions for this bank account
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class);
    }

    /**
     * Get the account type label
     */
    public function getAccountTypeLabelAttribute(): string
    {
        return match($this->account_type) {
            'checking' => 'Checking Account',
            'savings' => 'Savings Account',
            'time_deposit' => 'Time Deposit',
            'investment' => 'Investment Account',
            default => 'Unknown'
        };
    }

    /**
     * Get the status label
     */
    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }

    /**
     * Check if account is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if account can be reconciled automatically
     */
    public function canReconcileAutomatically(): bool
    {
        return $this->reconcile_automatically;
    }

    /**
     * Check if account allows overdraft
     */
    public function allowsOverdraft(): bool
    {
        return $this->allow_overdraft;
    }

    /**
     * Check if account is included in cash flow reports
     */
    public function isIncludedInCashFlow(): bool
    {
        return $this->include_in_cash_flow;
    }

    /**
     * Get formatted balance
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, 2);
    }

    /**
     * Scope for active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for accounts by currency
     */
    public function scopeByCurrency($query, $currency)
    {
        return $query->where('currency', $currency);
    }

    /**
     * Scope for accounts by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('account_type', $type);
    }

    /**
     * Scope for accounts included in cash flow
     */
    public function scopeIncludedInCashFlow($query)
    {
        return $query->where('include_in_cash_flow', true);
    }
}
