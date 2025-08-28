<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankStatement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bank_account_id',
        'statement_date',
        'opening_balance',
        'closing_balance',
        'file_path',
        'file_name',
        'file_size',
        'import_status', // pending, processing, completed, failed
        'total_transactions',
        'processed_transactions',
        'import_notes',
        'created_by'
    ];

    protected $casts = [
        'statement_date' => 'date',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'file_size' => 'integer',
        'total_transactions' => 'integer',
        'processed_transactions' => 'integer'
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

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class, 'statement_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('import_status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('import_status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('import_status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('import_status', 'failed');
    }

    public function scopeForBankAccount($query, $bankAccountId)
    {
        return $query->where('bank_account_id', $bankAccountId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('statement_date', [$startDate, $endDate]);
    }

    // Accessors
    public function getImportStatusLabelAttribute(): string
    {
        return match($this->import_status) {
            'pending' => 'Pending',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'failed' => 'Failed',
            default => 'Unknown'
        };
    }

    public function getImportStatusColorAttribute(): string
    {
        return match($this->import_status) {
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            default => 'secondary'
        };
    }

    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_transactions === 0) {
            return 0;
        }
        return round(($this->processed_transactions / $this->total_transactions) * 100);
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->import_status === 'completed';
    }

    public function getIsFailedAttribute(): bool
    {
        return $this->import_status === 'failed';
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
