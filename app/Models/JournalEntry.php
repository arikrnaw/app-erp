<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'entry_number',
        'entry_date',
        'reference_type',
        'reference_id',
        'description',
        'total_debit',
        'total_credit',
        'status',
        'posted_at',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'posted_at' => 'datetime',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    /**
     * Get the company that owns the journal entry.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created the journal entry.
     */
    public function created_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the journal entry lines.
     */
    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    /**
     * Scope to get only draft entries.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to get only posted entries.
     */
    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    /**
     * Scope to get entries by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('entry_date', [$startDate, $endDate]);
    }

    /**
     * Check if the journal entry is balanced.
     */
    public function isBalanced(): bool
    {
        return $this->total_debit == $this->total_credit;
    }

    /**
     * Get the difference between debit and credit.
     */
    public function getDifferenceAttribute(): float
    {
        return abs($this->total_debit - $this->total_credit);
    }

    /**
     * Generate entry number.
     */
    public static function generateEntryNumber(int $companyId): string
    {
        $lastEntry = self::where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->first();

        $lastNumber = $lastEntry ? (int) substr($lastEntry->entry_number, 3) : 0;
        $newNumber = $lastNumber + 1;

        return 'JE-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
