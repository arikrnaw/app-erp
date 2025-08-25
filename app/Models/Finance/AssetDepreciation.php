<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDepreciation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'asset_depreciations';

    protected $fillable = [
        'asset_id',
        'date',
        'amount',
        'method',
        'period_start',
        'period_end',
        'notes',
        'company_id',
        'created_by'
    ];

    protected $casts = [
        'date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'amount' => 'decimal:2'
    ];

    /**
     * Get the asset that owns the depreciation record
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'asset_id');
    }

    /**
     * Get the company that owns the depreciation record
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the user who created the depreciation record
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
