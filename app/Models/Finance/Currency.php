<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'currencies';

    protected $fillable = [
        'code',
        'symbol',
        'name',
        'description',
        'decimal_places',
        'is_base',
        'is_active',
        'auto_update',
        'exchange_rate_source',
        'last_update',
        'next_update'
    ];

    protected $casts = [
        'is_base' => 'boolean',
        'is_active' => 'boolean',
        'auto_update' => 'boolean',
        'decimal_places' => 'integer',
        'last_update' => 'datetime',
        'next_update' => 'datetime'
    ];

    protected $attributes = [
        'is_base' => false,
        'is_active' => true,
        'auto_update' => false,
        'decimal_places' => 2
    ];

    /**
     * Get the exchange rates where this currency is the base currency
     */
    public function baseExchangeRates(): HasMany
    {
        return $this->hasMany(ExchangeRate::class, 'base_currency_id');
    }

    /**
     * Get the exchange rates where this currency is the target currency
     */
    public function targetExchangeRates(): HasMany
    {
        return $this->hasMany(ExchangeRate::class, 'target_currency_id');
    }

    /**
     * Get the exchange rate history where this currency is the base currency
     */
    public function baseExchangeRateHistory(): HasMany
    {
        return $this->hasMany(ExchangeRateHistory::class, 'base_currency_id');
    }

    /**
     * Get the exchange rate history where this currency is the target currency
     */
    public function targetExchangeRateHistory(): HasMany
    {
        return $this->hasMany(ExchangeRateHistory::class, 'target_currency_id');
    }

    /**
     * Get the current exchange rate to another currency
     */
    public function getExchangeRateTo(string $targetCurrencyCode): ?float
    {
        if ($this->code === $targetCurrencyCode) {
            return 1.0;
        }

        $targetCurrency = self::where('code', $targetCurrencyCode)->first();
        if (!$targetCurrency) {
            return null;
        }

        // Try to get direct rate
        $rate = ExchangeRate::where('base_currency_id', $this->id)
            ->where('target_currency_id', $targetCurrency->id)
            ->first();

        if ($rate) {
            return $rate->rate;
        }

        // Try to get inverse rate
        $inverseRate = ExchangeRate::where('base_currency_id', $targetCurrency->id)
            ->where('target_currency_id', $this->id)
            ->first();

        if ($inverseRate) {
            return 1 / $inverseRate->rate;
        }

        return null;
    }

    /**
     * Convert amount from this currency to another currency
     */
    public function convertTo(float $amount, string $targetCurrencyCode): ?float
    {
        $rate = $this->getExchangeRateTo($targetCurrencyCode);
        return $rate ? $amount * $rate : null;
    }

    /**
     * Convert amount to this currency from another currency
     */
    public function convertFrom(float $amount, string $sourceCurrencyCode): ?float
    {
        $sourceCurrency = self::where('code', $sourceCurrencyCode)->first();
        if (!$sourceCurrency) {
            return null;
        }

        $rate = $sourceCurrency->getExchangeRateTo($this->code);
        return $rate ? $amount * $rate : null;
    }

    /**
     * Format amount according to currency settings
     */
    public function formatAmount(float $amount): string
    {
        return $this->symbol . number_format($amount, $this->decimal_places);
    }

    /**
     * Get currency label
     */
    public function getLabelAttribute(): string
    {
        return "{$this->code} - {$this->name}";
    }

    /**
     * Check if currency is base currency
     */
    public function isBaseCurrency(): bool
    {
        return $this->is_base;
    }

    /**
     * Check if currency is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if currency auto-updates exchange rates
     */
    public function autoUpdates(): bool
    {
        return $this->auto_update;
    }

    /**
     * Scope for active currencies
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for base currency
     */
    public function scopeBase($query)
    {
        return $query->where('is_base', true);
    }

    /**
     * Scope for non-base currencies
     */
    public function scopeNonBase($query)
    {
        return $query->where('is_base', false);
    }

    /**
     * Scope for auto-updating currencies
     */
    public function scopeAutoUpdating($query)
    {
        return $query->where('auto_update', true);
    }

    /**
     * Scope for currencies by code
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', strtoupper($code));
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // When creating a new base currency, ensure only one exists
        static::creating(function ($currency) {
            if ($currency->is_base) {
                static::where('is_base', true)->update(['is_base' => false]);
            }
        });

        // When updating to base currency, ensure only one exists
        static::updating(function ($currency) {
            if ($currency->is_base && $currency->getOriginal('is_base') === false) {
                static::where('id', '!=', $currency->id)
                    ->where('is_base', true)
                    ->update(['is_base' => false]);
            }
        });
    }
}
