<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'account_code',
        'name',
        'description',
        'type',
        'parent_id',
        'balance',
        'status',
        'created_by',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    /**
     * Get the company that owns the account.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the parent account.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    /**
     * Get the child accounts.
     */
    public function children(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    /**
     * Get the user who created the account.
     */
    public function created_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the journal entries for this account.
     */
    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'account_id');
    }

    /**
     * Get the journal entry lines for this account.
     */
    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    /**
     * Scope to get only active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get accounts by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the full account code with parent hierarchy.
     */
    public function getFullAccountCodeAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->full_account_code . '.' . $this->account_code;
        }
        return $this->account_code;
    }

    /**
     * Get the account name with hierarchy.
     */
    public function getFullAccountNameAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->full_account_name . ' > ' . $this->name;
        }
        return $this->name;
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
