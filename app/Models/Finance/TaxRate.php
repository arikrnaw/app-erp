<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxRate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'tax_category_id',
        'rate',
        'is_compound',
        'is_recoverable',
        'effective_from',
        'effective_to',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_compound' => 'boolean',
        'is_recoverable' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_compound' => false,
        'is_recoverable' => true,
        'is_active' => true,
    ];

    /**
     * Get the company that owns the tax rate.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the tax category for this rate.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TaxCategory::class, 'tax_category_id');
    }

    /**
     * Get the user who created the tax rate.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the tax transactions for this rate.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(TaxTransaction::class);
    }

    /**
     * Scope to get only active tax rates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get tax rates by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('tax_category_id', $categoryId);
    }

    /**
     * Scope to get tax rates effective on a specific date.
     */
    public function scopeEffectiveOn($query, $date)
    {
        return $query->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $date);
            });
    }

    /**
     * Check if the tax rate is effective on a specific date.
     */
    public function isEffectiveOn($date): bool
    {
        $date = \Carbon\Carbon::parse($date);
        return $this->effective_from <= $date && 
               ($this->effective_to === null || $this->effective_to >= $date);
    }

    /**
     * Calculate tax amount for given taxable amount.
     */
    public function calculateTax($taxableAmount): float
    {
        return $taxableAmount * ($this->rate / 100);
    }

    /**
     * Calculate total amount including tax.
     */
    public function calculateTotal($taxableAmount): float
    {
        if ($this->is_compound) {
            return $taxableAmount + $this->calculateTax($taxableAmount);
        }
        return $taxableAmount;
    }
}
