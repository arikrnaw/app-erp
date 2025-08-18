<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingProgram extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'program_code',
        'title',
        'description',
        'objectives',
        'curriculum',
        'training_type',
        'category',
        'instructor',
        'institution',
        'location',
        'duration_hours',
        'max_participants',
        'cost_per_participant',
        'currency',
        'start_date',
        'end_date',
        'status',
        'is_mandatory',
        'requires_certification',
        'prerequisites',
        'materials',
        'evaluation_criteria',
        'created_by',
    ];

    protected $casts = [
        'duration_hours' => 'integer',
        'max_participants' => 'integer',
        'cost_per_participant' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_mandatory' => 'boolean',
        'requires_certification' => 'boolean',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeByTrainingType($query, $type)
    {
        return $query->where('training_type', $type);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    public function scopeRequiresCertification($query)
    {
        return $query->where('requires_certification', true);
    }

    public function scopeByInstructor($query, $instructor)
    {
        return $query->where('instructor', 'like', "%{$instructor}%");
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate]);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now()->toDateString());
    }

    public function scopeOngoing($query)
    {
        $today = now()->toDateString();
        return $query->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today);
    }

    public function scopePast($query)
    {
        return $query->where('end_date', '<', now()->toDateString());
    }

    public function scopeByDuration($query, $minHours, $maxHours = null)
    {
        if ($maxHours) {
            return $query->whereBetween('duration_hours', [$minHours, $maxHours]);
        }
        return $query->where('duration_hours', '>=', $minHours);
    }

    public function scopeByCost($query, $minCost, $maxCost = null)
    {
        if ($maxCost) {
            return $query->whereBetween('cost_per_participant', [$minCost, $maxCost]);
        }
        return $query->where('cost_per_participant', '>=', $minCost);
    }

    // Methods
    public function getCreatorNameAttribute()
    {
        return $this->createdBy->name ?? 'Unknown';
    }

    public function getTrainingTypeTextAttribute()
    {
        return match($this->training_type) {
            'internal' => 'Internal',
            'external' => 'External',
            'online' => 'Online',
            'workshop' => 'Workshop',
            'seminar' => 'Seminar',
            'certification' => 'Certification',
            'degree' => 'Degree',
            default => 'Unknown',
        };
    }

    public function getCategoryTextAttribute()
    {
        return match($this->category) {
            'technical' => 'Technical',
            'soft_skills' => 'Soft Skills',
            'leadership' => 'Leadership',
            'compliance' => 'Compliance',
            'safety' => 'Safety',
            'product' => 'Product',
            'other' => 'Other',
            default => 'Unknown',
        };
    }

    public function getDateRangeAttribute()
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->format('M d, Y') . ' - ' . $this->end_date->format('M d, Y');
        } elseif ($this->start_date) {
            return 'From ' . $this->start_date->format('M d, Y');
        }
        return 'No date range';
    }

    public function getDurationAttribute()
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->diffInDays($this->end_date) + 1;
        }
        return null;
    }

    public function getDaysUntilStartAttribute()
    {
        if ($this->start_date) {
            return $this->start_date->diffInDays(now(), false);
        }
        return null;
    }

    public function getDaysUntilEndAttribute()
    {
        if ($this->end_date) {
            return $this->end_date->diffInDays(now(), false);
        }
        return null;
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isScheduled()
    {
        return $this->status === 'scheduled';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isMandatory()
    {
        return $this->is_mandatory;
    }

    public function requiresCertification()
    {
        return $this->requires_certification;
    }

    public function isUpcoming()
    {
        return $this->start_date && $this->start_date > now()->toDateString();
    }

    public function isOngoing()
    {
        $today = now()->toDateString();
        return $this->start_date && $this->end_date && 
               $this->start_date <= $today && $this->end_date >= $today;
    }

    public function isPast()
    {
        return $this->end_date && $this->end_date < now()->toDateString();
    }

    public function isStartingSoon($days = 7)
    {
        return $this->start_date && 
               $this->days_until_start <= $days && 
               $this->days_until_start >= 0;
    }

    public function isEndingSoon($days = 3)
    {
        return $this->end_date && 
               $this->days_until_end <= $days && 
               $this->days_until_end >= 0;
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'draft' => 'secondary',
            'scheduled' => 'info',
            'in_progress' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getTrainingTypeBadgeAttribute()
    {
        return match($this->training_type) {
            'internal' => 'primary',
            'external' => 'info',
            'online' => 'success',
            'workshop' => 'warning',
            'seminar' => 'secondary',
            'certification' => 'dark',
            'degree' => 'success',
            default => 'secondary',
        };
    }

    public function getCategoryBadgeAttribute()
    {
        return match($this->category) {
            'technical' => 'primary',
            'soft_skills' => 'info',
            'leadership' => 'success',
            'compliance' => 'warning',
            'safety' => 'danger',
            'product' => 'secondary',
            'other' => 'dark',
            default => 'secondary',
        };
    }

    public function getMandatoryBadgeAttribute()
    {
        return $this->is_mandatory ? 'danger' : 'secondary';
    }

    public function getCertificationBadgeAttribute()
    {
        return $this->requires_certification ? 'success' : 'secondary';
    }

    public function getCostDisplayAttribute()
    {
        if ($this->cost_per_participant > 0) {
            return $this->currency . ' ' . number_format($this->cost_per_participant, 2);
        }
        return 'Free';
    }

    public function getDurationDisplayAttribute()
    {
        if ($this->duration_hours >= 24) {
            $days = floor($this->duration_hours / 24);
            $hours = $this->duration_hours % 24;
            if ($hours > 0) {
                return "{$days} day(s) {$hours} hour(s)";
            }
            return "{$days} day(s)";
        }
        return "{$this->duration_hours} hour(s)";
    }

    public function getTotalCostAttribute()
    {
        return $this->cost_per_participant * ($this->max_participants ?: 1);
    }

    public function getTotalCostDisplayAttribute()
    {
        return $this->currency . ' ' . number_format($this->total_cost, 2);
    }

    public function getDescriptionPreviewAttribute()
    {
        if ($this->description) {
            return strlen($this->description) > 100 
                ? substr($this->description, 0, 100) . '...' 
                : $this->description;
        }
        return 'No description';
    }

    public function getObjectivesPreviewAttribute()
    {
        if ($this->objectives) {
            return strlen($this->objectives) > 100 
                ? substr($this->objectives, 0, 100) . '...' 
                : $this->objectives;
        }
        return 'No objectives';
    }

    public function getCurriculumPreviewAttribute()
    {
        if ($this->curriculum) {
            return strlen($this->curriculum) > 100 
                ? substr($this->curriculum, 0, 100) . '...' 
                : $this->curriculum;
        }
        return 'No curriculum';
    }

    public function getPrerequisitesPreviewAttribute()
    {
        if ($this->prerequisites) {
            return strlen($this->prerequisites) > 100 
                ? substr($this->prerequisites, 0, 100) . '...' 
                : $this->prerequisites;
        }
        return 'No prerequisites';
    }

    public function getMaterialsPreviewAttribute()
    {
        if ($this->materials) {
            return strlen($this->materials) > 100 
                ? substr($this->materials, 0, 100) . '...' 
                : $this->materials;
        }
        return 'No materials';
    }

    public function getEvaluationCriteriaPreviewAttribute()
    {
        if ($this->evaluation_criteria) {
            return strlen($this->evaluation_criteria) > 100 
                ? substr($this->evaluation_criteria, 0, 100) . '...' 
                : $this->evaluation_criteria;
        }
        return 'No evaluation criteria';
    }

    public function getFormattedStartDateAttribute()
    {
        return $this->start_date ? $this->start_date->format('M d, Y') : 'Not set';
    }

    public function getFormattedEndDateAttribute()
    {
        return $this->end_date ? $this->end_date->format('M d, Y') : 'Not set';
    }

    public function canBeScheduled()
    {
        return $this->isDraft();
    }

    public function canBeStarted()
    {
        return $this->isScheduled() && $this->start_date <= now()->toDateString();
    }

    public function canBeCompleted()
    {
        return $this->isInProgress() && $this->end_date <= now()->toDateString();
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['draft', 'scheduled', 'in_progress']);
    }

    public function getProgressStatusAttribute()
    {
        if ($this->isUpcoming()) return 'Upcoming';
        if ($this->isOngoing()) return 'Ongoing';
        if ($this->isPast()) return 'Past';
        return 'Unknown';
    }

    public function getProgressBadgeAttribute()
    {
        return match($this->progress_status) {
            'Upcoming' => 'info',
            'Ongoing' => 'warning',
            'Past' => 'success',
            default => 'secondary',
        };
    }

    public function getAvailabilityStatusAttribute()
    {
        if (!$this->max_participants) return 'Unlimited';
        // This would need to be calculated based on actual enrollments
        return 'Limited';
    }

    public function getAvailabilityBadgeAttribute()
    {
        return match($this->availability_status) {
            'Unlimited' => 'success',
            'Limited' => 'warning',
            default => 'secondary',
        };
    }
}
