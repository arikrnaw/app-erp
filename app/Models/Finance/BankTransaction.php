<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BankTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bank_account_id',
        'statement_id',
        'transaction_date',
        'value_date',
        'description',
        'reference_number',
        'amount',
        'currency',
        'transaction_type', // deposit, withdrawal, transfer, charge
        'counterparty',
        'is_reconciled',
        'reconciliation_notes',
        'created_by'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'value_date' => 'date',
        'amount' => 'decimal:2',
        'is_reconciled' => 'boolean'
    ];

    // Relationships
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function statement(): BelongsTo
    {
        return $this->belongsTo(BankStatement::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function transactionMatch(): HasOne
    {
        return $this->hasOne(TransactionMatch::class);
    }

    public function transactionMatches(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TransactionMatch::class);
    }

    // Scopes
    public function scopeReconciled($query)
    {
        return $query->where('is_reconciled', true);
    }

    public function scopeUnreconciled($query)
    {
        return $query->where('is_reconciled', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function scopePositive($query)
    {
        return $query->where('amount', '>', 0);
    }

    public function scopeNegative($query)
    {
        return $query->where('amount', '<', 0);
    }

    // Accessors
    public function getTransactionTypeLabelAttribute(): string
    {
        return match($this->transaction_type) {
            'deposit' => 'Deposit',
            'withdrawal' => 'Withdrawal',
            'transfer' => 'Transfer',
            'charge' => 'Charge',
            default => 'Unknown'
        };
    }

    public function getTransactionTypeColorAttribute(): string
    {
        return match($this->transaction_type) {
            'deposit' => 'success',
            'withdrawal' => 'danger',
            'transfer' => 'info',
            'charge' => 'warning',
            default => 'secondary'
        };
    }

    public function getIsPositiveAttribute(): bool
    {
        return $this->amount > 0;
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format(abs($this->amount), 2) . ' ' . ($this->currency ?? 'IDR');
    }
}
