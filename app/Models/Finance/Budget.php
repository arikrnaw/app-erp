<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'category_id',
        'period_id',
        'amount',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'amount' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(BudgetCategory::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(BudgetPeriod::class, 'period_id');
    }

    public function variances(): HasMany
    {
        return $this->hasMany(BudgetVariance::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->where('period_start', '<=', $endDate)
                    ->where('period_end', '>=', $startDate);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // Accessors
    public function getPeriodLabelAttribute(): string
    {
        return $this->period_start->format('M Y') . ' - ' . $this->period_end->format('M Y');
    }

    public function getAmountFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    // Methods
    public function getActualSpentAttribute(): float
    {
        return $this->variances->sum('actual_amount');
    }

    public function getVarianceAttribute(): float
    {
        return $this->getActualSpentAttribute() - $this->amount;
    }

    public function getVariancePercentageAttribute(): float
    {
        if ($this->amount == 0) return 0;
        return ($this->getVarianceAttribute() / $this->amount) * 100;
    }

    public function isOverBudget(): bool
    {
        return $this->getActualSpentAttribute() > $this->amount;
    }

    public function isUnderBudget(): bool
    {
        return $this->getActualSpentAttribute() < $this->amount;
    }

    public function getRemainingAmountAttribute(): float
    {
        return $this->amount - $this->getActualSpentAttribute();
    }
}
