<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'type',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $attributes = [
        'status' => 'active',
        'type' => 'expense',
    ];

    // Relationships
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class, 'category_id');
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Accessors
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'expense' => 'Expense',
            'revenue' => 'Revenue',
            'asset' => 'Asset',
            'liability' => 'Liability',
            default => 'Unknown'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }
}
