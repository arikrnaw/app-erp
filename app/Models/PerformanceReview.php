<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceReview extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'employee_id',
        'review_number',
        'title',
        'description',
        'review_start_date',
        'review_end_date',
        'review_type',
        'status',
        'reviewer_id',
        'second_reviewer_id',
        'hr_reviewer_id',
        'self_assessment',
        'self_rating',
        'manager_assessment',
        'manager_rating',
        'strengths',
        'areas_for_improvement',
        'goals_achieved',
        'goals_not_achieved',
        'final_rating',
        'performance_level',
        'overall_comments',
        'recommendations',
        'action_plan',
        'training_needs',
        'career_development',
        'salary_increase_percentage',
        'bonus_amount',
        'approved_by',
        'approved_at',
        'approval_notes',
        'employee_acknowledged',
        'acknowledged_at',
        'employee_comments',
    ];

    protected $casts = [
        'review_start_date' => 'date',
        'review_end_date' => 'date',
        'self_rating' => 'decimal:1',
        'manager_rating' => 'decimal:1',
        'final_rating' => 'decimal:1',
        'salary_increase_percentage' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'employee_acknowledged' => 'boolean',
        'approved_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function secondReviewer()
    {
        return $this->belongsTo(User::class, 'second_reviewer_id');
    }

    public function hrReviewer()
    {
        return $this->belongsTo(User::class, 'hr_reviewer_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByReviewer($query, $reviewerId)
    {
        return $query->where('reviewer_id', $reviewerId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByReviewType($query, $type)
    {
        return $query->where('review_type', $type);
    }

    public function scopeByPerformanceLevel($query, $level)
    {
        return $query->where('performance_level', $level);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('review_start_date', [$startDate, $endDate]);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('review_start_date', now()->year);
    }

    public function scopeOverdue($query)
    {
        return $query->where('review_end_date', '<', now()->toDateString())
                    ->whereNotIn('status', ['completed', 'approved', 'cancelled']);
    }

    public function scopePendingAcknowledgment($query)
    {
        return $query->where('status', 'approved')
                    ->where('employee_acknowledged', false);
    }

    // Methods
    public function getFullNameAttribute()
    {
        return $this->employee->full_name ?? 'Unknown Employee';
    }

    public function getReviewerNameAttribute()
    {
        return $this->reviewer->name ?? 'Not assigned';
    }

    public function getSecondReviewerNameAttribute()
    {
        return $this->secondReviewer->name ?? 'Not assigned';
    }

    public function getHrReviewerNameAttribute()
    {
        return $this->hrReviewer->name ?? 'Not assigned';
    }

    public function getApproverNameAttribute()
    {
        return $this->approvedBy->name ?? 'Not approved';
    }

    public function getDateRangeAttribute()
    {
        return $this->review_start_date->format('M d, Y') . ' - ' . $this->review_end_date->format('M d, Y');
    }

    public function getReviewPeriodAttribute()
    {
        return $this->review_start_date->diffInDays($this->review_end_date) + 1;
    }

    public function getDaysUntilEndAttribute()
    {
        return $this->review_end_date->diffInDays(now(), false);
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isOverdue()
    {
        return $this->review_end_date < now()->toDateString() && !in_array($this->status, ['completed', 'approved', 'cancelled']);
    }

    public function isAcknowledged()
    {
        return $this->employee_acknowledged;
    }

    public function isPendingAcknowledgment()
    {
        return $this->isApproved() && !$this->isAcknowledged();
    }

    public function hasSelfAssessment()
    {
        return !empty($this->self_assessment);
    }

    public function hasManagerAssessment()
    {
        return !empty($this->manager_assessment);
    }

    public function hasFinalRating()
    {
        return !is_null($this->final_rating);
    }

    public function hasSalaryIncrease()
    {
        return $this->salary_increase_percentage > 0;
    }

    public function hasBonus()
    {
        return $this->bonus_amount > 0;
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'draft' => 'secondary',
            'in_progress' => 'info',
            'completed' => 'success',
            'approved' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getReviewTypeBadgeAttribute()
    {
        return match($this->review_type) {
            'probation' => 'warning',
            'monthly' => 'info',
            'quarterly' => 'primary',
            'annual' => 'success',
            'project' => 'secondary',
            'special' => 'dark',
            default => 'secondary',
        };
    }

    public function getPerformanceLevelBadgeAttribute()
    {
        return match($this->performance_level) {
            'excellent' => 'success',
            'good' => 'info',
            'satisfactory' => 'warning',
            'needs_improvement' => 'danger',
            'unsatisfactory' => 'danger',
            default => 'secondary',
        };
    }

    public function getRatingBadgeAttribute()
    {
        if (!$this->final_rating) return 'secondary';
        
        if ($this->final_rating >= 4.5) return 'success';
        if ($this->final_rating >= 4.0) return 'info';
        if ($this->final_rating >= 3.0) return 'warning';
        return 'danger';
    }

    public function getAcknowledgmentBadgeAttribute()
    {
        return $this->employee_acknowledged ? 'success' : 'warning';
    }

    public function getProgressPercentageAttribute()
    {
        $steps = 0;
        $completed = 0;

        // Self assessment
        $steps++;
        if ($this->hasSelfAssessment()) $completed++;

        // Manager assessment
        $steps++;
        if ($this->hasManagerAssessment()) $completed++;

        // Final rating
        $steps++;
        if ($this->hasFinalRating()) $completed++;

        // Approval
        $steps++;
        if ($this->isApproved()) $completed++;

        // Acknowledgment
        $steps++;
        if ($this->isAcknowledged()) $completed++;

        return round(($completed / $steps) * 100, 1);
    }

    public function getRatingDescriptionAttribute()
    {
        if (!$this->final_rating) return 'Not rated';
        
        if ($this->final_rating >= 4.5) return 'Outstanding';
        if ($this->final_rating >= 4.0) return 'Exceeds Expectations';
        if ($this->final_rating >= 3.5) return 'Meets Expectations';
        if ($this->final_rating >= 3.0) return 'Needs Improvement';
        return 'Unsatisfactory';
    }

    public function getSalaryIncreaseAmountAttribute()
    {
        if ($this->hasSalaryIncrease() && $this->employee) {
            return ($this->employee->base_salary * $this->salary_increase_percentage) / 100;
        }
        return 0;
    }

    public function getTotalCompensationIncreaseAttribute()
    {
        return $this->salary_increase_amount + $this->bonus_amount;
    }

    public function getStrengthsPreviewAttribute()
    {
        if ($this->strengths) {
            return strlen($this->strengths) > 100 
                ? substr($this->strengths, 0, 100) . '...' 
                : $this->strengths;
        }
        return 'No strengths identified';
    }

    public function getAreasForImprovementPreviewAttribute()
    {
        if ($this->areas_for_improvement) {
            return strlen($this->areas_for_improvement) > 100 
                ? substr($this->areas_for_improvement, 0, 100) . '...' 
                : $this->areas_for_improvement;
        }
        return 'No areas for improvement identified';
    }

    public function getGoalsAchievedPreviewAttribute()
    {
        if ($this->goals_achieved) {
            return strlen($this->goals_achieved) > 100 
                ? substr($this->goals_achieved, 0, 100) . '...' 
                : $this->goals_achieved;
        }
        return 'No goals achieved';
    }

    public function getGoalsNotAchievedPreviewAttribute()
    {
        if ($this->goals_not_achieved) {
            return strlen($this->goals_not_achieved) > 100 
                ? substr($this->goals_not_achieved, 0, 100) . '...' 
                : $this->goals_not_achieved;
        }
        return 'No goals not achieved';
    }

    public function getActionPlanPreviewAttribute()
    {
        if ($this->action_plan) {
            return strlen($this->action_plan) > 100 
                ? substr($this->action_plan, 0, 100) . '...' 
                : $this->action_plan;
        }
        return 'No action plan';
    }

    public function getTrainingNeedsPreviewAttribute()
    {
        if ($this->training_needs) {
            return strlen($this->training_needs) > 100 
                ? substr($this->training_needs, 0, 100) . '...' 
                : $this->training_needs;
        }
        return 'No training needs identified';
    }

    public function getCareerDevelopmentPreviewAttribute()
    {
        if ($this->career_development) {
            return strlen($this->career_development) > 100 
                ? substr($this->career_development, 0, 100) . '...' 
                : $this->career_development;
        }
        return 'No career development plan';
    }

    public function canBeStarted()
    {
        return $this->isDraft();
    }

    public function canBeCompleted()
    {
        return $this->isInProgress() && $this->hasManagerAssessment() && $this->hasFinalRating();
    }

    public function canBeApproved()
    {
        return $this->isCompleted();
    }

    public function canBeAcknowledged()
    {
        return $this->isApproved() && !$this->isAcknowledged();
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['draft', 'in_progress']) && !$this->isApproved();
    }

    public function getFormattedApprovedDateAttribute()
    {
        return $this->approved_at ? $this->approved_at->format('M d, Y H:i') : 'Not approved';
    }

    public function getFormattedAcknowledgedDateAttribute()
    {
        return $this->acknowledged_at ? $this->acknowledged_at->format('M d, Y H:i') : 'Not acknowledged';
    }
}
