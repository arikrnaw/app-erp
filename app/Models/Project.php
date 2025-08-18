<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
        'priority',
        'start_date',
        'end_date',
        'actual_start_date',
        'actual_end_date',
        'budget',
        'actual_cost',
        'progress_percentage',
        'project_manager_id',
        'client_id',
        'company_id',
        'location',
        'contact_person',
        'contact_email',
        'contact_phone',
        'tags',
        'custom_fields',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'budget' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'progress_percentage' => 'decimal:2',
        'tags' => 'array',
        'custom_fields' => 'array',
    ];

    // Relationships
    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'client_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(ProjectResource::class);
    }

    public function costs(): HasMany
    {
        return $this->hasMany(ProjectCost::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    public function team(): HasMany
    {
        return $this->hasMany(ProjectTeam::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    // Accessors
    public function getIsOverdueAttribute(): bool
    {
        return $this->end_date < now() && $this->status !== 'completed';
    }

    public function getIsDelayedAttribute(): bool
    {
        return $this->actual_start_date && $this->actual_start_date > $this->start_date;
    }

    public function getBudgetVarianceAttribute(): float
    {
        return $this->actual_cost - $this->budget;
    }

    public function getBudgetVariancePercentageAttribute(): float
    {
        if ($this->budget == 0) return 0;
        return (($this->actual_cost - $this->budget) / $this->budget) * 100;
    }

    public function getDurationAttribute(): int
    {
        $start = $this->actual_start_date ?? $this->start_date;
        $end = $this->actual_end_date ?? $this->end_date;
        return $start->diffInDays($end);
    }

    // Methods
    public function updateProgress(): void
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks > 0) {
            $completedTasks = $this->tasks()->where('status', 'completed')->count();
            $this->progress_percentage = ($completedTasks / $totalTasks) * 100;
            $this->save();
        }
    }

    public function calculateActualCost(): void
    {
        $totalCost = $this->costs()->sum('actual_cost');
        $this->actual_cost = $totalCost;
        $this->save();
    }
}
