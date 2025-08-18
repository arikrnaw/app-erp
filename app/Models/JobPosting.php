<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPosting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'job_code',
        'title',
        'description',
        'requirements',
        'responsibilities',
        'benefits',
        'department_id',
        'position_id',
        'location',
        'employment_type',
        'experience_level',
        'min_experience_years',
        'max_experience_years',
        'education_level',
        'min_salary',
        'max_salary',
        'currency',
        'show_salary',
        'number_of_positions',
        'application_deadline',
        'status',
        'is_featured',
        'views_count',
        'applications_count',
        'created_by',
    ];

    protected $casts = [
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'show_salary' => 'boolean',
        'is_featured' => 'boolean',
        'application_deadline' => 'date',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByPosition($query, $positionId)
    {
        return $query->where('position_id', $positionId);
    }

    public function scopeByEmploymentType($query, $type)
    {
        return $query->where('employment_type', $type);
    }

    public function scopeByExperienceLevel($query, $level)
    {
        return $query->where('experience_level', $level);
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('application_deadline')
              ->orWhere('application_deadline', '>=', now()->toDateString());
        });
    }

    // Methods
    public function getFullTitleAttribute()
    {
        return "{$this->title} ({$this->job_code})";
    }

    public function getSalaryRangeAttribute()
    {
        if ($this->min_salary && $this->max_salary) {
            return "{$this->currency} {$this->min_salary} - {$this->max_salary}";
        } elseif ($this->min_salary) {
            return "{$this->currency} {$this->min_salary}+";
        } elseif ($this->max_salary) {
            return "{$this->currency} Up to {$this->max_salary}";
        }
        return 'Salary not disclosed';
    }

    public function getDisplaySalaryAttribute()
    {
        if (!$this->show_salary) {
            return 'Salary not disclosed';
        }
        return $this->salary_range;
    }

    public function isPublished()
    {
        return $this->status === 'published';
    }

    public function isExpired()
    {
        return $this->application_deadline && $this->application_deadline < now()->toDateString();
    }

    public function isActive()
    {
        return $this->isPublished() && !$this->isExpired();
    }

    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function incrementApplications()
    {
        $this->increment('applications_count');
    }

    public function getApplicationRateAttribute()
    {
        if ($this->views_count > 0) {
            return round(($this->applications_count / $this->views_count) * 100, 2);
        }
        return 0;
    }

    public function getRemainingPositionsAttribute()
    {
        return max(0, $this->number_of_positions - $this->applications()->where('status', 'hired')->count());
    }

    public function hasOpenPositions()
    {
        return $this->remaining_positions > 0;
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'draft' => 'secondary',
            'published' => 'success',
            'closed' => 'warning',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getEmploymentTypeBadgeAttribute()
    {
        return match($this->employment_type) {
            'full_time' => 'success',
            'part_time' => 'info',
            'contract' => 'warning',
            'intern' => 'primary',
            'freelance' => 'secondary',
            default => 'secondary',
        };
    }
}
