<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'department_code',
        'name',
        'description',
        'manager_id',
        'location',
        'phone',
        'email',
        'budget',
        'is_active',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function positions()
    {
        return $this->hasMany(Position::class);
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

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeByManager($query, $managerId)
    {
        return $query->where('manager_id', $managerId);
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name', 'asc');
    }

    // Methods
    public function getManagerNameAttribute()
    {
        return $this->manager->full_name ?? 'Not assigned';
    }

    public function getActiveEmployeesCountAttribute()
    {
        return $this->employees()->where('is_active', true)->count();
    }

    public function getTotalEmployeesCountAttribute()
    {
        return $this->employees()->count();
    }

    public function getActivePositionsCountAttribute()
    {
        return $this->positions()->where('is_active', true)->count();
    }

    public function getTotalPositionsCountAttribute()
    {
        return $this->positions()->count();
    }

    public function getActiveJobPostingsCountAttribute()
    {
        return $this->jobPostings()->where('status', 'published')->count();
    }

    public function getTotalJobPostingsCountAttribute()
    {
        return $this->jobPostings()->count();
    }

    public function getBudgetDisplayAttribute()
    {
        if ($this->budget > 0) {
            return '$' . number_format($this->budget, 2);
        }
        return 'Not set';
    }

    public function getBudgetUtilizationAttribute()
    {
        // This would need to be calculated based on actual expenses
        return 0;
    }

    public function getBudgetRemainingAttribute()
    {
        if ($this->budget > 0) {
            return $this->budget - ($this->budget * $this->budget_utilization / 100);
        }
        return 0;
    }

    public function getBudgetRemainingDisplayAttribute()
    {
        return '$' . number_format($this->budget_remaining, 2);
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

    public function isActive()
    {
        return $this->is_active;
    }

    public function hasManager()
    {
        return !is_null($this->manager_id);
    }

    public function hasEmployees()
    {
        return $this->total_employees_count > 0;
    }

    public function hasPositions()
    {
        return $this->total_positions_count > 0;
    }

    public function hasJobPostings()
    {
        return $this->total_job_postings_count > 0;
    }

    public function hasBudget()
    {
        return $this->budget > 0;
    }

    public function getStatusBadgeAttribute()
    {
        return $this->is_active ? 'success' : 'secondary';
    }

    public function getManagerBadgeAttribute()
    {
        return $this->hasManager() ? 'success' : 'warning';
    }

    public function getEmployeesBadgeAttribute()
    {
        if ($this->active_employees_count > 0) return 'success';
        if ($this->total_employees_count > 0) return 'warning';
        return 'secondary';
    }

    public function getPositionsBadgeAttribute()
    {
        if ($this->active_positions_count > 0) return 'success';
        if ($this->total_positions_count > 0) return 'warning';
        return 'secondary';
    }

    public function getJobPostingsBadgeAttribute()
    {
        if ($this->active_job_postings_count > 0) return 'success';
        if ($this->total_job_postings_count > 0) return 'warning';
        return 'secondary';
    }

    public function getBudgetBadgeAttribute()
    {
        if (!$this->hasBudget()) return 'secondary';
        if ($this->budget_utilization >= 90) return 'danger';
        if ($this->budget_utilization >= 75) return 'warning';
        return 'success';
    }

    public function getFullTitleAttribute()
    {
        return $this->name . ' (' . $this->department_code . ')';
    }

    public function getContactInfoAttribute()
    {
        $info = [];
        if ($this->phone) $info[] = $this->phone;
        if ($this->email) $info[] = $this->email;
        return empty($info) ? 'No contact info' : implode(' | ', $info);
    }

    public function getLocationDisplayAttribute()
    {
        return $this->location ?: 'Not specified';
    }

    public function canBeDeactivated()
    {
        return $this->is_active && $this->active_employees_count === 0;
    }

    public function canBeDeleted()
    {
        return $this->total_employees_count === 0 && 
               $this->total_positions_count === 0 && 
               $this->total_job_postings_count === 0;
    }

    public function getAverageSalaryAttribute()
    {
        $activeEmployees = $this->employees()->where('is_active', true);
        $count = $activeEmployees->count();
        
        if ($count > 0) {
            return round($activeEmployees->avg('base_salary'), 2);
        }
        
        return 0;
    }

    public function getAverageSalaryDisplayAttribute()
    {
        if ($this->average_salary > 0) {
            return '$' . number_format($this->average_salary, 2);
        }
        return 'No data';
    }

    public function getTotalSalaryAttribute()
    {
        return $this->employees()
            ->where('is_active', true)
            ->sum('base_salary');
    }

    public function getTotalSalaryDisplayAttribute()
    {
        if ($this->total_salary > 0) {
            return '$' . number_format($this->total_salary, 2);
        }
        return '$0.00';
    }

    public function getGenderDistributionAttribute()
    {
        $employees = $this->employees()->where('is_active', true);
        $total = $employees->count();
        
        if ($total === 0) {
            return ['male' => 0, 'female' => 0, 'other' => 0];
        }
        
        $male = $employees->where('gender', 'male')->count();
        $female = $employees->where('gender', 'female')->count();
        $other = $total - $male - $female;
        
        return [
            'male' => round(($male / $total) * 100, 1),
            'female' => round(($female / $total) * 100, 1),
            'other' => round(($other / $total) * 100, 1)
        ];
    }

    public function getAgeDistributionAttribute()
    {
        $employees = $this->employees()->where('is_active', true);
        $total = $employees->count();
        
        if ($total === 0) {
            return ['young' => 0, 'middle' => 0, 'senior' => 0];
        }
        
        $young = $employees->where('birth_date', '>=', now()->subYears(30))->count();
        $senior = $employees->where('birth_date', '<=', now()->subYears(50))->count();
        $middle = $total - $young - $senior;
        
        return [
            'young' => round(($young / $total) * 100, 1),
            'middle' => round(($middle / $total) * 100, 1),
            'senior' => round(($senior / $total) * 100, 1)
        ];
    }

    public function getEmploymentTypeDistributionAttribute()
    {
        $employees = $this->employees()->where('is_active', true);
        $total = $employees->count();
        
        if ($total === 0) {
            return ['full_time' => 0, 'part_time' => 0, 'contract' => 0, 'intern' => 0];
        }
        
        $fullTime = $employees->where('employment_type', 'full_time')->count();
        $partTime = $employees->where('employment_type', 'part_time')->count();
        $contract = $employees->where('employment_type', 'contract')->count();
        $intern = $employees->where('employment_type', 'intern')->count();
        
        return [
            'full_time' => round(($fullTime / $total) * 100, 1),
            'part_time' => round(($partTime / $total) * 100, 1),
            'contract' => round(($contract / $total) * 100, 1),
            'intern' => round(($intern / $total) * 100, 1)
        ];
    }

    public function getTenureDistributionAttribute()
    {
        $employees = $this->employees()->where('is_active', true);
        $total = $employees->count();
        
        if ($total === 0) {
            return ['new' => 0, 'experienced' => 0, 'veteran' => 0];
        }
        
        $new = $employees->where('hire_date', '>=', now()->subYears(1))->count();
        $veteran = $employees->where('hire_date', '<=', now()->subYears(5))->count();
        $experienced = $total - $new - $veteran;
        
        return [
            'new' => round(($new / $total) * 100, 1),
            'experienced' => round(($experienced / $total) * 100, 1),
            'veteran' => round(($veteran / $total) * 100, 1)
        ];
    }
}
