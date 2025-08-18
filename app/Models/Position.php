<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'position_code',
        'title',
        'description',
        'department_id',
        'job_level',
        'job_requirements',
        'responsibilities',
        'min_salary',
        'max_salary',
        'is_active',
    ];

    protected $casts = [
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'is_active' => 'boolean',
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

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function jobPostings()
    {
        return $this->hasMany(JobPosting::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByJobLevel($query, $jobLevel)
    {
        return $query->where('job_level', $jobLevel);
    }

    // Methods
    public function getFullTitleAttribute()
    {
        return "{$this->title} ({$this->position_code})";
    }

    public function getSalaryRangeAttribute()
    {
        if ($this->min_salary && $this->max_salary) {
            return "{$this->min_salary} - {$this->max_salary}";
        } elseif ($this->min_salary) {
            return "Min: {$this->min_salary}";
        } elseif ($this->max_salary) {
            return "Max: {$this->max_salary}";
        }
        return 'Not specified';
    }

    public function getActiveEmployeesCountAttribute()
    {
        return $this->employees()->where('is_active', true)->count();
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function hasSalaryRange()
    {
        return $this->min_salary > 0 || $this->max_salary > 0;
    }

    public function getAverageSalaryAttribute()
    {
        if ($this->min_salary && $this->max_salary) {
            return ($this->min_salary + $this->max_salary) / 2;
        }
        return $this->min_salary ?: $this->max_salary ?: 0;
    }
}
