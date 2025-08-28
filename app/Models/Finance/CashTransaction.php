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
        'transaction_number',
        'type',
        'bank_account_id',
        'petty_cash_fund_id',
        'description',
        'amount',
        'currency',
        'transaction_date',
        'reference_number',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'approved_at' => 'datetime'
    ];

    protected $attributes = [
        'status' => 'completed',
        'currency' => 'IDR'
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
     * Get the type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'deposit' => 'Deposit',
            'withdrawal' => 'Withdrawal',
            'transfer' => 'Transfer',
            'expense' => 'Expense',
            'income' => 'Income',
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
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
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

    /**
     * Scope for transactions by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for transactions by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope for recent transactions
     */
    public function scopeRecent($query, $limit = 20)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}
