<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankReconciliation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bank_account_id',
        'period_start',
        'period_end',
        'bank_statement_balance',
        'book_balance',
        'difference',
        'status', // pending, in_progress, completed, cancelled
        'reconciliation_date',
        'notes',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'reconciliation_date' => 'date',
        'approved_at' => 'datetime',
        'bank_statement_balance' => 'decimal:2',
        'book_balance' => 'decimal:2',
        'difference' => 'decimal:2'
    ];

    // Relationships
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(ReconciliationAdjustment::class, 'reconciliation_id');
    }

    public function transactionMatches(): HasMany
    {
        return $this->hasMany(TransactionMatch::class, 'reconciliation_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForBankAccount($query, $bankAccountId)
    {
        return $query->where('bank_account_id', $bankAccountId);
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('period_start', [$startDate, $endDate])
                    ->orWhereBetween('period_end', [$startDate, $endDate]);
    }

    // Accessors
    public function getIsBalancedAttribute(): bool
    {
        return abs($this->difference) < 0.01;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => 'Unknown'
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'in_progress' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }
}
