<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'amount',
        'description',
        'date',
        'bank_account_id',
        'petty_cash_fund_id',
        'reference_number',
        'status',
        'notes',
        'transaction_category',
        'related_document',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'approved_at' => 'datetime'
    ];

    protected $attributes = [
        'status' => 'completed'
    ];

    /**
     * Get the bank account for this transaction
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Get the petty cash fund for this transaction
     */
    public function pettyCashFund(): BelongsTo
    {
        return $this->belongsTo(PettyCashFund::class);
    }

    /**
     * Get the account name (either bank account or petty cash fund)
     */
    public function getAccountNameAttribute(): string
    {
        if ($this->bank_account_id) {
            return $this->bankAccount?->name ?? 'Unknown Bank Account';
        }
        
        if ($this->petty_cash_fund_id) {
            return $this->pettyCashFund?->name ?? 'Unknown Petty Cash Fund';
        }
        
        return 'Unknown Account';
    }

    /**
     * Get the transaction type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'deposit' => 'Deposit',
            'withdrawal' => 'Withdrawal',
            'transfer' => 'Transfer',
            'expense' => 'Expense',
            'opening_balance' => 'Opening Balance',
            'replenishment' => 'Replenishment',
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
     * Check if transaction is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if transaction is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if transaction is a deposit
     */
    public function isDeposit(): bool
    {
        return $this->type === 'deposit';
    }

    /**
     * Check if transaction is a withdrawal
     */
    public function isWithdrawal(): bool
    {
        return $this->type === 'withdrawal';
    }

    /**
     * Check if transaction is a transfer
     */
    public function isTransfer(): bool
    {
        return $this->type === 'transfer';
    }

    /**
     * Check if transaction is an expense
     */
    public function isExpense(): bool
    {
        return $this->type === 'expense';
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    /**
     * Get absolute amount (for display purposes)
     */
    public function getAbsoluteAmountAttribute(): float
    {
        return abs($this->amount);
    }

    /**
     * Scope for transactions by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for transactions by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for transactions by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for bank account transactions
     */
    public function scopeBankAccount($query, $bankAccountId)
    {
        return $query->where('bank_account_id', $bankAccountId);
    }

    /**
     * Scope for petty cash fund transactions
     */
    public function scopePettyCashFund($query, $pettyCashFundId)
    {
        return $query->where('petty_cash_fund_id', $pettyCashFundId);
    }

    /**
     * Scope for completed transactions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
