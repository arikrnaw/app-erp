<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
        'priority',
        'type',
        'project_id',
        'parent_task_id',
        'assigned_to',
        'created_by',
        'start_date',
        'due_date',
        'actual_start_date',
        'actual_end_date',
        'estimated_hours',
        'actual_hours',
        'progress_percentage',
        'order',
        'dependencies',
        'attachments',
        'tags',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'dependencies' => 'array',
        'attachments' => 'array',
        'tags' => 'array',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'parent_task_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'parent_task_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())->where('status', '!=', 'completed');
    }

    // Accessors
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date < now() && $this->status !== 'completed';
    }

    public function getIsDelayedAttribute(): bool
    {
        return $this->actual_start_date && $this->actual_start_date > $this->start_date;
    }

    public function getTimeVarianceAttribute(): int
    {
        return $this->actual_hours - $this->estimated_hours;
    }

    public function getTimeVariancePercentageAttribute(): float
    {
        if ($this->estimated_hours == 0) return 0;
        return (($this->actual_hours - $this->estimated_hours) / $this->estimated_hours) * 100;
    }

    public function getDurationAttribute(): int
    {
        if (!$this->actual_start_date || !$this->actual_end_date) return 0;
        return $this->actual_start_date->diffInDays($this->actual_end_date);
    }

    // Methods
    public function updateProgress(): void
    {
        $totalSubtasks = $this->subtasks()->count();
        if ($totalSubtasks > 0) {
            $completedSubtasks = $this->subtasks()->where('status', 'completed')->count();
            $this->progress_percentage = ($completedSubtasks / $totalSubtasks) * 100;
            $this->save();
        }
    }

    public function canStart(): bool
    {
        if (empty($this->dependencies)) return true;
        
        foreach ($this->dependencies as $dependencyId) {
            $dependency = ProjectTask::find($dependencyId);
            if ($dependency && $dependency->status !== 'completed') {
                return false;
            }
        }
        return true;
    }
}
