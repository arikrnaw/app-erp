<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'job_posting_id',
        'application_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'birth_date',
        'gender',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'national_id',
        'highest_education',
        'institution',
        'years_of_experience',
        'current_company',
        'current_position',
        'current_salary',
        'expected_salary',
        'currency',
        'cover_letter',
        'skills',
        'references',
        'resume_file',
        'portfolio_url',
        'linkedin_url',
        'source',
        'source_details',
        'status',
        'notes',
        'rejection_reason',
        'interview_date',
        'interview_time',
        'interview_location',
        'interview_notes',
        'assigned_to',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'current_salary' => 'decimal:2',
        'expected_salary' => 'decimal:2',
        'interview_date' => 'date',
        'interview_time' => 'datetime',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByJobPosting($query, $jobPostingId)
    {
        return $query->where('job_posting_id', $jobPostingId);
    }

    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeWithInterview($query)
    {
        return $query->whereNotNull('interview_date');
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Methods
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country
        ]);
        return implode(', ', $parts);
    }

    public function getSalaryRangeAttribute()
    {
        if ($this->current_salary && $this->expected_salary) {
            return "{$this->currency} {$this->current_salary} - {$this->expected_salary}";
        } elseif ($this->current_salary) {
            return "{$this->currency} {$this->current_salary}";
        } elseif ($this->expected_salary) {
            return "{$this->currency} {$this->expected_salary}";
        }
        return 'Not specified';
    }

    public function getAgeAttribute()
    {
        if ($this->birth_date) {
            return $this->birth_date->age;
        }
        return null;
    }

    public function isPending()
    {
        return $this->status === 'applied';
    }

    public function isInProgress()
    {
        return in_array($this->status, ['screening', 'interview_scheduled', 'interviewed', 'shortlisted']);
    }

    public function isCompleted()
    {
        return in_array($this->status, ['hired', 'rejected', 'withdrawn']);
    }

    public function isHired()
    {
        return $this->status === 'hired';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function hasInterview()
    {
        return !is_null($this->interview_date);
    }

    public function isInterviewScheduled()
    {
        return $this->status === 'interview_scheduled';
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'applied' => 'secondary',
            'screening' => 'info',
            'interview_scheduled' => 'warning',
            'interviewed' => 'primary',
            'shortlisted' => 'success',
            'offer_sent' => 'success',
            'hired' => 'success',
            'rejected' => 'danger',
            'withdrawn' => 'secondary',
            default => 'secondary',
        };
    }

    public function getSourceBadgeAttribute()
    {
        return match($this->source) {
            'website' => 'primary',
            'job_board' => 'info',
            'referral' => 'success',
            'social_media' => 'warning',
            'direct' => 'secondary',
            'other' => 'secondary',
            default => 'secondary',
        };
    }

    public function getGenderBadgeAttribute()
    {
        return match($this->gender) {
            'male' => 'blue',
            'female' => 'pink',
            'other' => 'gray',
            default => 'gray',
        };
    }

    public function getDaysSinceAppliedAttribute()
    {
        return $this->created_at->diffInDays(now());
    }

    public function isRecent()
    {
        return $this->days_since_applied <= 7;
    }

    public function isUrgent()
    {
        return $this->days_since_applied >= 14;
    }

    public function getExperienceLevelAttribute()
    {
        if ($this->years_of_experience >= 10) return 'Senior';
        if ($this->years_of_experience >= 5) return 'Mid-level';
        if ($this->years_of_experience >= 2) return 'Junior';
        return 'Entry-level';
    }

    public function getExperienceBadgeAttribute()
    {
        return match($this->experience_level) {
            'Senior' => 'success',
            'Mid-level' => 'info',
            'Junior' => 'warning',
            'Entry-level' => 'secondary',
            default => 'secondary',
        };
    }
}
