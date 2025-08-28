<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionMatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reconciliation_id',
        'bank_transaction_id',
        'book_transaction_id',
        'match_score', // 0-100 percentage
        'match_type', // exact, partial, manual
        'confidence_score', // 0-100 percentage (alias for match_score)
        'matched_by', // user ID who made the match
        'matched_at', // timestamp when match was made
        'notes',
        'created_by'
    ];

    protected $casts = [
        'match_score' => 'integer',
        'confidence_score' => 'integer',
        'matched_at' => 'datetime'
    ];

    // Relationships
    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(BankReconciliation::class);
    }

    public function bankTransaction(): BelongsTo
    {
        return $this->belongsTo(BankTransaction::class);
    }

    public function bookTransaction(): BelongsTo
    {
        return $this->belongsTo(\App\Models\JournalEntryLine::class, 'book_transaction_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // Scopes
    public function scopeExact($query)
    {
        return $query->where('match_type', 'exact');
    }

    public function scopePartial($query)
    {
        return $query->where('match_type', 'partial');
    }

    public function scopeManual($query)
    {
        return $query->where('match_type', 'manual');
    }

    public function scopeHighScore($query, $minScore = 80)
    {
        return $query->where('match_score', '>=', $minScore);
    }

    // Accessors
    public function getMatchTypeLabelAttribute(): string
    {
        return match($this->match_type) {
            'exact' => 'Exact Match',
            'partial' => 'Partial Match',
            'manual' => 'Manual Match',
            default => 'Unknown'
        };
    }

    public function getMatchTypeColorAttribute(): string
    {
        return match($this->match_type) {
            'exact' => 'success',
            'partial' => 'warning',
            'manual' => 'info',
            default => 'secondary'
        };
    }

    public function getIsHighConfidenceAttribute(): bool
    {
        return $this->match_score >= 80;
    }
}
