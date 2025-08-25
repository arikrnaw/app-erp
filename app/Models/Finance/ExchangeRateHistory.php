<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExchangeRateHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'exchange_rate_history';

    protected $fillable = [
        'base_currency_id',
        'target_currency_id',
        'rate',
        'date',
        'source',
        'notes',
        'volume',
        'change',
        'change_percentage'
    ];

    protected $casts = [
        'rate' => 'decimal:8',
        'date' => 'date',
        'volume' => 'decimal:2',
        'change' => 'decimal:8',
        'change_percentage' => 'decimal:4'
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
     * Get the formatted rate
     */
    public function getFormattedRateAttribute(): string
    {
        return number_format($this->rate, 4);
    }

    /**
     * Get the formatted change
     */
    public function getFormattedChangeAttribute(): string
    {
        return number_format($this->change, 4);
    }

    /**
     * Get the formatted change percentage
     */
    public function getFormattedChangePercentageAttribute(): string
    {
        return number_format($this->change_percentage, 4) . '%';
    }

    /**
     * Get the source label
     */
    public function getSourceLabelAttribute(): string
    {
        return ucfirst($this->source);
    }

    /**
     * Check if rate increased
     */
    public function isIncrease(): bool
    {
        return $this->change > 0;
    }

    /**
     * Check if rate decreased
     */
    public function isDecrease(): bool
    {
        return $this->change < 0;
    }

    /**
     * Check if rate remained stable
     */
    public function isStable(): bool
    {
        return $this->change == 0;
    }

    /**
     * Get the change direction
     */
    public function getChangeDirectionAttribute(): string
    {
        if ($this->isIncrease()) {
            return 'up';
        } elseif ($this->isDecrease()) {
            return 'down';
        }
        return 'stable';
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
     * Scope for rates on a specific date
     */
    public function scopeOnDate($query, string $date)
    {
        return $query->where('date', $date);
    }

    /**
     * Scope for rates between dates
     */
    public function scopeBetweenDates($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for rates with significant changes
     */
    public function scopeSignificantChanges($query, float $threshold = 1.0)
    {
        return $query->where('change_percentage', '>=', $threshold)
            ->orWhere('change_percentage', '<=', -$threshold);
    }

    /**
     * Scope for rates with increases
     */
    public function scopeIncreases($query)
    {
        return $query->where('change', '>', 0);
    }

    /**
     * Scope for rates with decreases
     */
    public function scopeDecreases($query)
    {
        return $query->where('change', '<', 0);
    }

    /**
     * Scope for rates with stable values
     */
    public function scopeStable($query)
    {
        return $query->where('change', 0);
    }

    /**
     * Scope for rates by volume
     */
    public function scopeByVolume($query, float $minVolume = 0)
    {
        return $query->where('volume', '>=', $minVolume);
    }

    /**
     * Get the previous rate in history
     */
    public function getPreviousRate(): ?self
    {
        return self::where('base_currency_id', $this->base_currency_id)
            ->where('target_currency_id', $this->target_currency_id)
            ->where('date', '<', $this->date)
            ->orderBy('date', 'desc')
            ->first();
    }

    /**
     * Get the next rate in history
     */
    public function getNextRate(): ?self
    {
        return self::where('base_currency_id', $this->base_currency_id)
            ->where('target_currency_id', $this->target_currency_id)
            ->where('date', '>', $this->date)
            ->orderBy('date', 'asc')
            ->first();
    }

    /**
     * Calculate the average rate over a period
     */
    public static function getAverageRate(int $baseCurrencyId, int $targetCurrencyId, string $startDate, string $endDate): float
    {
        $rates = self::where('base_currency_id', $baseCurrencyId)
            ->where('target_currency_id', $targetCurrencyId)
            ->whereBetween('date', [$startDate, $endDate])
            ->pluck('rate')
            ->toArray();

        return empty($rates) ? 0 : array_sum($rates) / count($rates);
    }

    /**
     * Calculate the volatility over a period
     */
    public static function getVolatility(int $baseCurrencyId, int $targetCurrencyId, string $startDate, string $endDate): float
    {
        $rates = self::where('base_currency_id', $baseCurrencyId)
            ->where('target_currency_id', $targetCurrencyId)
            ->whereBetween('date', [$startDate, $endDate])
            ->pluck('rate')
            ->toArray();

        if (empty($rates) || count($rates) < 2) {
            return 0;
        }

        $average = array_sum($rates) / count($rates);
        $variance = array_sum(array_map(function ($rate) use ($average) {
            return pow($rate - $average, 2);
        }, $rates)) / count($rates);

        return sqrt($variance);
    }

    /**
     * Get the highest rate over a period
     */
    public static function getHighestRate(int $baseCurrencyId, int $targetCurrencyId, string $startDate, string $endDate): ?float
    {
        return self::where('base_currency_id', $baseCurrencyId)
            ->where('target_currency_id', $targetCurrencyId)
            ->whereBetween('date', [$startDate, $endDate])
            ->max('rate');
    }

    /**
     * Get the lowest rate over a period
     */
    public static function getLowestRate(int $baseCurrencyId, int $targetCurrencyId, string $startDate, string $endDate): ?float
    {
        return self::where('base_currency_id', $baseCurrencyId)
            ->where('target_currency_id', $targetCurrencyId)
            ->whereBetween('date', [$startDate, $endDate])
            ->min('rate');
    }
}
