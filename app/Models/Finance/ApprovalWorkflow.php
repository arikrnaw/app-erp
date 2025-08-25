<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalWorkflow extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'description',
        'threshold_amount',
        'is_active',
        'auto_escalate',
        'require_all_levels',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'threshold_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'auto_escalate' => 'boolean',
        'require_all_levels' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
        'auto_escalate' => false,
        'require_all_levels' => false,
    ];

    // Relationships
    public function levels(): HasMany
    {
        return $this->hasMany(ApprovalLevel::class, 'workflow_id');
    }

    public function approvalRequests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class, 'workflow_id');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(ApprovalRule::class, 'workflow_id');
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
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByThreshold($query, $amount)
    {
        return $query->where('threshold_amount', '<=', $amount);
    }

    // Helper methods
    public function getNextLevel($currentLevel)
    {
        return $this->levels()
            ->where('level', '>', $currentLevel)
            ->orderBy('level')
            ->first();
    }

    public function getFirstLevel()
    {
        return $this->levels()
            ->orderBy('level')
            ->first();
    }

    public function isMultiLevel()
    {
        return $this->levels()->count() > 1;
    }

    public function getMaxLevel()
    {
        return $this->levels()->max('level');
    }

    public function canAutoApprove($amount)
    {
        return $amount < $this->threshold_amount;
    }

    public function getApprovalTime($level)
    {
        $levelData = $this->levels()->where('level', $level)->first();
        return $levelData ? $levelData->escalation_hours : 24;
    }

    // Events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($workflow) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $workflow->created_by = \Illuminate\Support\Facades\Auth::id();
            }
        });

        static::updating(function ($workflow) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $workflow->updated_by = \Illuminate\Support\Facades\Auth::id();
            }
        });
    }
}
