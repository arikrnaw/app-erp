<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExchangeRate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'exchange_rates';

    protected $fillable = [
        'base_currency_id',
        'target_currency_id',
        'rate',
        'effective_date',
        'source',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'rate' => 'decimal:8',
        'effective_date' => 'date',
        'is_active' => 'boolean'
    ];

    protected $attributes = [
        'is_active' => true
    ];

    /**
     * Get the base currency
     */
    public function baseCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'base_currency_id');
    }

    /**
     * Get the target currency
     */
    public function targetCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'target_currency_id');
    }

    /**
     * Get the inverse exchange rate
     */
    public function getInverseRate(): float
    {
        return 1 / $this->rate;
    }

    /**
     * Get the rate change from previous rate
     */
    public function getRateChange(): ?float
    {
        $previousRate = self::where('base_currency_id', $this->base_currency_id)
            ->where('target_currency_id', $this->target_currency_id)
            ->where('effective_date', '<', $this->effective_date)
            ->orderBy('effective_date', 'desc')
            ->first();

        return $previousRate ? $this->rate - $previousRate->rate : null;
    }

    /**
     * Get the percentage change from previous rate
     */
    public function getPercentageChange(): ?float
    {
        $previousRate = self::where('base_currency_id', $this->base_currency_id)
            ->where('target_currency_id', $this->target_currency_id)
            ->where('effective_date', '<', $this->effective_date)
            ->orderBy('effective_date', 'desc')
            ->first();

        if (!$previousRate) {
            return null;
        }

        return (($this->rate - $previousRate->rate) / $previousRate->rate) * 100;
    }

    /**
     * Check if this is the current active rate
     */
    public function isCurrent(): bool
    {
        $latestRate = self::where('base_currency_id', $this->base_currency_id)
            ->where('target_currency_id', $this->target_currency_id)
            ->orderBy('effective_date', 'desc')
            ->first();

        return $latestRate && $latestRate->id === $this->id;
    }

    /**
     * Get the formatted rate
     */
    public function getFormattedRateAttribute(): string
    {
        return number_format($this->rate, 4);
    }

    /**
     * Get the source label
     */
    public function getSourceLabelAttribute(): string
    {
        return ucfirst($this->source);
    }

    /**
     * Scope for active rates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for current rates
     */
    public function scopeCurrent($query)
    {
        return $query->whereIn('id', function ($subquery) {
            $subquery->selectRaw('MAX(id)')
                ->from('exchange_rates')
                ->groupBy('base_currency_id', 'target_currency_id');
        });
    }

    /**
     * Scope for rates by base currency
     */
    public function scopeByBaseCurrency($query, int $baseCurrencyId)
    {
        return $query->where('base_currency_id', $baseCurrencyId);
    }

    /**
     * Scope for rates by target currency
     */
    public function scopeByTargetCurrency($query, int $targetCurrencyId)
    {
        return $query->where('target_currency_id', $targetCurrencyId);
    }

    /**
     * Scope for rates by source
     */
    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope for rates effective on a specific date
     */
    public function scopeEffectiveOn($query, string $date)
    {
        return $query->where('effective_date', '<=', $date);
    }

    /**
     * Scope for rates effective between dates
     */
    public function scopeEffectiveBetween($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('effective_date', [$startDate, $endDate]);
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // When creating a new rate, archive the previous rate
        static::created(function ($rate) {
            // Create history record
            ExchangeRateHistory::create([
                'base_currency_id' => $rate->base_currency_id,
                'target_currency_id' => $rate->target_currency_id,
                'rate' => $rate->rate,
                'date' => $rate->effective_date,
                'source' => $rate->source,
                'notes' => $rate->notes
            ]);

            // Deactivate previous rates
            self::where('base_currency_id', $rate->base_currency_id)
                ->where('target_currency_id', $rate->target_currency_id)
                ->where('id', '!=', $rate->id)
                ->update(['is_active' => false]);
        });
    }
}
