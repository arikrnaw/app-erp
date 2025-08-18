<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'employee_number',
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
        'tax_number',
        'bank_name',
        'bank_account_number',
        'bank_routing_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'department_id',
        'position_id',
        'supervisor_id',
        'hire_date',
        'contract_start_date',
        'contract_end_date',
        'employment_status',
        'employment_type',
        'base_salary',
        'currency',
        'pay_frequency',
        'start_time',
        'end_time',
        'working_hours_per_day',
        'working_days_per_week',
        'work_location',
        'is_remote',
        'user_id',
        'is_active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'base_salary' => 'decimal:2',
        'is_remote' => 'boolean',
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

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'supervisor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function payrollRecords()
    {
        return $this->hasMany(PayrollRecord::class);
    }

    public function performanceReviews()
    {
        return $this->hasMany(PerformanceReview::class);
    }

    public function employeeBenefits()
    {
        return $this->hasMany(EmployeeBenefit::class);
    }

    public function employeeDocuments()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class);
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

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByPosition($query, $positionId)
    {
        return $query->where('position_id', $positionId);
    }

    public function scopeBySupervisor($query, $supervisorId)
    {
        return $query->where('supervisor_id', $supervisorId);
    }

    public function scopeByEmploymentStatus($query, $status)
    {
        return $query->where('employment_status', $status);
    }

    public function scopeByEmploymentType($query, $type)
    {
        return $query->where('employment_type', $type);
    }

    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    public function scopeRemote($query)
    {
        return $query->where('is_remote', true);
    }

    public function scopeOnsite($query)
    {
        return $query->where('is_remote', false);
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('work_location', 'like', "%{$location}%");
    }

    public function scopeBySalaryRange($query, $minSalary, $maxSalary = null)
    {
        if ($maxSalary) {
            return $query->whereBetween('base_salary', [$minSalary, $maxSalary]);
        }
        return $query->where('base_salary', '>=', $minSalary);
    }

    public function scopeByHireDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('hire_date', [$startDate, $endDate]);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('hire_date', now()->year);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereYear('hire_date', now()->year)
                    ->whereMonth('hire_date', now()->month);
    }

    public function scopeNewHires($query, $months = 6)
    {
        $date = now()->subMonths($months);
        return $query->where('hire_date', '>=', $date);
    }

    public function scopeLongTerm($query, $years = 5)
    {
        $date = now()->subYears($years);
        return $query->where('hire_date', '<=', $date);
    }

    // Methods
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getDepartmentNameAttribute()
    {
        return $this->department->name ?? 'Not assigned';
    }

    public function getPositionTitleAttribute()
    {
        return $this->position->title ?? 'Not assigned';
    }

    public function getSupervisorNameAttribute()
    {
        return $this->supervisor->full_name ?? 'Not assigned';
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

    public function getAgeAttribute()
    {
        if ($this->birth_date) {
            return $this->birth_date->age;
        }
        return null;
    }

    public function getTenureAttribute()
    {
        if ($this->hire_date) {
            return $this->hire_date->diffInYears(now());
        }
        return 0;
    }

    public function getTenureMonthsAttribute()
    {
        if ($this->hire_date) {
            return $this->hire_date->diffInMonths(now());
        }
        return 0;
    }

    public function getTenureDaysAttribute()
    {
        if ($this->hire_date) {
            return $this->hire_date->diffInDays(now());
        }
        return 0;
    }

    public function getSalaryDisplayAttribute()
    {
        if ($this->base_salary > 0) {
            return $this->currency . ' ' . number_format($this->base_salary, 2);
        }
        return 'Not set';
    }

    public function getWorkingHoursDisplayAttribute()
    {
        if ($this->start_time && $this->end_time) {
            return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
        }
        return 'Not set';
    }

    public function getContractPeriodAttribute()
    {
        if ($this->contract_start_date && $this->contract_end_date) {
            return $this->contract_start_date->format('M d, Y') . ' - ' . $this->contract_end_date->format('M d, Y');
        } elseif ($this->contract_start_date) {
            return 'From ' . $this->contract_start_date->format('M d, Y');
        }
        return 'Not specified';
    }

    public function getDaysUntilContractEndAttribute()
    {
        if ($this->contract_end_date) {
            return $this->contract_end_date->diffInDays(now(), false);
        }
        return null;
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function isRemote()
    {
        return $this->is_remote;
    }

    public function isOnsite()
    {
        return !$this->is_remote;
    }

    public function hasSupervisor()
    {
        return !is_null($this->supervisor_id);
    }

    public function hasSubordinates()
    {
        return $this->subordinates()->count() > 0;
    }

    public function hasUser()
    {
        return !is_null($this->user_id);
    }

    public function isContractExpiringSoon($days = 30)
    {
        if (!$this->contract_end_date) return false;
        
        $expiryDate = now()->addDays($days)->toDateString();
        return $this->contract_end_date <= $expiryDate && $this->contract_end_date >= now()->toDateString();
    }

    public function isContractExpired()
    {
        return $this->contract_end_date && $this->contract_end_date < now()->toDateString();
    }

    public function isNewHire($months = 6)
    {
        return $this->hire_date && $this->hire_date >= now()->subMonths($months);
    }

    public function isLongTerm($years = 5)
    {
        return $this->hire_date && $this->hire_date <= now()->subYears($years);
    }

    public function getStatusBadgeAttribute()
    {
        return $this->is_active ? 'success' : 'secondary';
    }

    public function getEmploymentStatusBadgeAttribute()
    {
        return match($this->employment_status) {
            'active' => 'success',
            'inactive' => 'secondary',
            'terminated' => 'danger',
            'resigned' => 'warning',
            'retired' => 'info',
            default => 'secondary',
        };
    }

    public function getEmploymentTypeBadgeAttribute()
    {
        return match($this->employment_type) {
            'full_time' => 'primary',
            'part_time' => 'info',
            'contract' => 'warning',
            'intern' => 'secondary',
            'temporary' => 'dark',
            default => 'secondary',
        };
    }

    public function getGenderBadgeAttribute()
    {
        return match($this->gender) {
            'male' => 'primary',
            'female' => 'warning',
            'other' => 'info',
            default => 'secondary',
        };
    }

    public function getWorkLocationBadgeAttribute()
    {
        return $this->is_remote ? 'success' : 'info';
    }

    public function getContractStatusBadgeAttribute()
    {
        if (!$this->contract_end_date) return 'success';
        if ($this->isContractExpired()) return 'danger';
        if ($this->isContractExpiringSoon(7)) return 'danger';
        if ($this->isContractExpiringSoon(30)) return 'warning';
        return 'success';
    }

    public function getTenureBadgeAttribute()
    {
        if ($this->isNewHire(3)) return 'success';
        if ($this->isNewHire(12)) return 'info';
        if ($this->isLongTerm(10)) return 'dark';
        if ($this->isLongTerm(5)) return 'warning';
        return 'primary';
    }

    public function getSalaryBadgeAttribute()
    {
        if ($this->base_salary >= 100000) return 'success';
        if ($this->base_salary >= 50000) return 'info';
        if ($this->base_salary >= 30000) return 'warning';
        return 'secondary';
    }

    public function getAttendanceRateAttribute()
    {
        $records = $this->attendanceRecords()->thisMonth();
        $totalDays = $records->count();
        
        if ($totalDays === 0) return 0;
        
        $presentDays = $records->where('status', 'present')->count();
        return round(($presentDays / $totalDays) * 100, 1);
    }

    public function getLeaveDaysUsedAttribute()
    {
        return $this->leaveRequests()
            ->where('status', 'approved')
            ->whereYear('start_date', now()->year)
            ->sum('total_days');
    }

    public function getLeaveDaysRemainingAttribute()
    {
        // This would need to be calculated based on leave policy
        return 0;
    }

    public function getOvertimeHoursAttribute()
    {
        return $this->attendanceRecords()
            ->thisMonth()
            ->sum('overtime_hours');
    }

    public function getLastAttendanceAttribute()
    {
        return $this->attendanceRecords()
            ->latest('date')
            ->first();
    }

    public function getLastLeaveRequestAttribute()
    {
        return $this->leaveRequests()
            ->latest('created_at')
            ->first();
    }

    public function getLastPayrollRecordAttribute()
    {
        return $this->payrollRecords()
            ->latest('created_at')
            ->first();
    }

    public function getLastPerformanceReviewAttribute()
    {
        return $this->performanceReviews()
            ->latest('created_at')
            ->first();
    }

    public function getActiveBenefitsCountAttribute()
    {
        return $this->employeeBenefits()
            ->where('is_active', true)
            ->count();
    }

    public function getActiveDocumentsCountAttribute()
    {
        return $this->employeeDocuments()
            ->where('status', 'active')
            ->count();
    }

    public function getExpiringDocumentsCountAttribute()
    {
        return $this->employeeDocuments()
            ->where('status', 'active')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->count();
    }

    public function getSubordinatesCountAttribute()
    {
        return $this->subordinates()->count();
    }

    public function getSubordinatesActiveCountAttribute()
    {
        return $this->subordinates()->where('is_active', true)->count();
    }

    public function canBeDeactivated()
    {
        return $this->is_active && $this->subordinates_active_count === 0;
    }

    public function canBeTerminated()
    {
        return $this->is_active;
    }

    public function canBePromoted()
    {
        return $this->is_active && $this->position;
    }

    public function canBeTransferred()
    {
        return $this->is_active;
    }

    public function getFormattedBirthDateAttribute()
    {
        return $this->birth_date ? $this->birth_date->format('M d, Y') : 'Not set';
    }

    public function getFormattedHireDateAttribute()
    {
        return $this->hire_date ? $this->hire_date->format('M d, Y') : 'Not set';
    }

    public function getFormattedContractStartDateAttribute()
    {
        return $this->contract_start_date ? $this->contract_start_date->format('M d, Y') : 'Not set';
    }

    public function getFormattedContractEndDateAttribute()
    {
        return $this->contract_end_date ? $this->contract_end_date->format('M d, Y') : 'No end date';
    }

    public function getEmploymentStatusTextAttribute()
    {
        return match($this->employment_status) {
            'active' => 'Active',
            'inactive' => 'Inactive',
            'terminated' => 'Terminated',
            'resigned' => 'Resigned',
            'retired' => 'Retired',
            default => 'Unknown',
        };
    }

    public function getEmploymentTypeTextAttribute()
    {
        return match($this->employment_type) {
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
            'contract' => 'Contract',
            'intern' => 'Intern',
            'temporary' => 'Temporary',
            default => 'Unknown',
        };
    }

    public function getGenderTextAttribute()
    {
        return match($this->gender) {
            'male' => 'Male',
            'female' => 'Female',
            'other' => 'Other',
            default => 'Not specified',
        };
    }

    public function getPayFrequencyTextAttribute()
    {
        return match($this->pay_frequency) {
            'weekly' => 'Weekly',
            'bi_weekly' => 'Bi-Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'yearly' => 'Yearly',
            default => 'Not set',
        };
    }
}
