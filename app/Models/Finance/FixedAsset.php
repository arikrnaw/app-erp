<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FixedAsset extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fixed_assets';

    protected $fillable = [
        'name',
        'description',
        'tag_number',
        'category_id',
        'location',
        'purchase_date',
        'purchase_value',
        'current_value',
        'depreciation_method',
        'useful_life_years',
        'salvage_value',
        'accumulated_depreciation',
        'status',
        'manufacturer',
        'model',
        'serial_number',
        'warranty_expiry',
        'insurance_info',
        'maintenance_schedule',
        'last_maintenance_date',
        'next_maintenance_date',
        'disposal_date',
        'disposal_value',
        'notes',
        'company_id',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'disposal_date' => 'date',
        'purchase_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'disposal_value' => 'decimal:2',
        'useful_life_years' => 'integer'
    ];

    protected $attributes = [
        'status' => 'active',
        'depreciation_method' => 'straight_line',
        'accumulated_depreciation' => 0,
        'current_value' => 0
    ];

    /**
     * Get the category that owns the asset
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class);
    }

    /**
     * Get the depreciation records for the asset
     */
    public function depreciations(): HasMany
    {
        return $this->hasMany(AssetDepreciation::class, 'asset_id');
    }

    /**
     * Get the company that owns the asset
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the user who created the asset
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the asset
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Calculate current value based on depreciation
     */
    public function calculateCurrentValue(): float
    {
        return $this->purchase_value - $this->accumulated_depreciation;
    }

    /**
     * Calculate monthly depreciation
     */
    public function calculateMonthlyDepreciation(): float
    {
        if ($this->useful_life_years <= 0) {
            return 0;
        }

        $depreciableAmount = $this->purchase_value - $this->salvage_value;
        $monthlyRate = 1 / ($this->useful_life_years * 12);

        return $depreciableAmount * $monthlyRate;
    }

    /**
     * Check if asset is fully depreciated
     */
    public function isFullyDepreciated(): bool
    {
        return $this->current_value <= $this->salvage_value;
    }

    /**
     * Get remaining useful life in years
     */
    public function getRemainingUsefulLife(): float
    {
        if ($this->useful_life_years <= 0) {
            return 0;
        }

        $depreciatedAmount = $this->accumulated_depreciation;
        $depreciableAmount = $this->purchase_value - $this->salvage_value;
        
        if ($depreciableAmount <= 0) {
            return 0;
        }

        $depreciationRate = $depreciatedAmount / $depreciableAmount;
        return $this->useful_life_years * (1 - $depreciationRate);
    }
}
