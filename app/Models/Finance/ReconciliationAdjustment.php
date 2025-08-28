<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReconciliationAdjustment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reconciliation_id',
        'bank_account_id',
        'type', // bank_charge, interest_earned, service_fee, other
        'description',
        'amount',
        'date',
        'reference',
        'related_transaction_id',
        'notes',
        'approved',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'approved' => 'boolean'
    ];

    // Relationships
    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(BankReconciliation::class, 'reconciliation_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
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
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'bank_charge' => 'Bank Charge',
            'interest_earned' => 'Interest Earned',
            'service_fee' => 'Service Fee',
            'other' => 'Other',
            default => 'Unknown'
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'bank_charge' => 'danger',
            'interest_earned' => 'success',
            'service_fee' => 'warning',
            'other' => 'info',
            default => 'secondary'
        };
    }

    public function getIsPositiveAttribute(): bool
    {
        return $this->amount > 0;
    }
}
