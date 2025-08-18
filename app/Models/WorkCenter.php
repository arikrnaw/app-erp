<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkCenter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'work_center_code',
        'name',
        'description',
        'location',
        'type',
        'capacity_per_hour',
        'efficiency_rate',
        'hourly_rate',
        'setup_time',
        'teardown_time',
        'is_active',
        'supervisor_id',
        'notes',
    ];

    protected $casts = [
        'capacity_per_hour' => 'decimal:2',
        'efficiency_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'setup_time' => 'decimal:2',
        'teardown_time' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function productionTracking(): HasMany
    {
        return $this->hasMany(ProductionTracking::class);
    }

    // Methods
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function getEffectiveCapacity(): float
    {
        return $this->capacity_per_hour * ($this->efficiency_rate / 100);
    }

    public function getTotalSetupTime(): float
    {
        return $this->setup_time + $this->teardown_time;
    }

    public function getDailyCapacity(): float
    {
        return $this->getEffectiveCapacity() * 8; // Assuming 8 hours per day
    }
}
