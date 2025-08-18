<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'employee_id',
        'date',
        'check_in_time',
        'check_out_time',
        'break_start_time',
        'break_end_time',
        'total_work_hours',
        'total_break_hours',
        'overtime_hours',
        'late_minutes',
        'early_leave_minutes',
        'status',
        'notes',
        'check_in_location',
        'check_out_location',
        'check_in_method',
        'check_out_method',
        'is_approved',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'break_start_time' => 'datetime',
        'break_end_time' => 'datetime',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
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

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereYear('date', now()->year)
                    ->whereMonth('date', now()->month);
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    // Methods
    public function getFullNameAttribute()
    {
        return $this->employee->full_name ?? 'Unknown Employee';
    }

    public function getWorkHoursAttribute()
    {
        return round($this->total_work_hours / 60, 2); // Convert minutes to hours
    }

    public function getBreakHoursAttribute()
    {
        return round($this->total_break_hours / 60, 2); // Convert minutes to hours
    }

    public function getOvertimeHoursAttribute()
    {
        return round($this->overtime_hours / 60, 2); // Convert minutes to hours
    }

    public function getNetWorkHoursAttribute()
    {
        return $this->work_hours - $this->break_hours;
    }

    public function isPresent()
    {
        return $this->status === 'present';
    }

    public function isAbsent()
    {
        return $this->status === 'absent';
    }

    public function isLate()
    {
        return $this->status === 'late' || $this->late_minutes > 0;
    }

    public function isEarlyLeave()
    {
        return $this->status === 'early_leave' || $this->early_leave_minutes > 0;
    }

    public function isHalfDay()
    {
        return $this->status === 'half_day';
    }

    public function isOnLeave()
    {
        return $this->status === 'leave';
    }

    public function isHoliday()
    {
        return $this->status === 'holiday';
    }

    public function isWeekend()
    {
        return $this->status === 'weekend';
    }

    public function hasOvertime()
    {
        return $this->overtime_hours > 0;
    }

    public function isApproved()
    {
        return $this->is_approved;
    }

    public function isPendingApproval()
    {
        return !$this->is_approved;
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'present' => 'success',
            'absent' => 'danger',
            'late' => 'warning',
            'early_leave' => 'warning',
            'half_day' => 'info',
            'leave' => 'secondary',
            'holiday' => 'primary',
            'weekend' => 'secondary',
            default => 'secondary',
        };
    }

    public function getCheckInMethodBadgeAttribute()
    {
        return match($this->check_in_method) {
            'biometric' => 'success',
            'mobile' => 'info',
            'web' => 'primary',
            'manual' => 'secondary',
            default => 'secondary',
        };
    }

    public function getCheckOutMethodBadgeAttribute()
    {
        return match($this->check_out_method) {
            'biometric' => 'success',
            'mobile' => 'info',
            'web' => 'primary',
            'manual' => 'secondary',
            default => 'secondary',
        };
    }

    public function getLateStatusAttribute()
    {
        if ($this->late_minutes > 0) {
            return "Late by {$this->late_minutes} minutes";
        }
        return 'On time';
    }

    public function getEarlyLeaveStatusAttribute()
    {
        if ($this->early_leave_minutes > 0) {
            return "Left early by {$this->early_leave_minutes} minutes";
        }
        return 'Normal time';
    }

    public function getOvertimeStatusAttribute()
    {
        if ($this->overtime_hours > 0) {
            return "Overtime: {$this->overtime_hours} hours";
        }
        return 'No overtime';
    }

    public function calculateWorkHours()
    {
        if ($this->check_in_time && $this->check_out_time) {
            $workMinutes = $this->check_out_time->diffInMinutes($this->check_in_time);
            $breakMinutes = 0;
            
            if ($this->break_start_time && $this->break_end_time) {
                $breakMinutes = $this->break_end_time->diffInMinutes($this->break_start_time);
            }
            
            $this->total_work_hours = $workMinutes;
            $this->total_break_hours = $breakMinutes;
            
            // Calculate overtime (assuming 8 hours = 480 minutes standard work day)
            $standardWorkMinutes = 480;
            $netWorkMinutes = $workMinutes - $breakMinutes;
            $this->overtime_hours = max(0, $netWorkMinutes - $standardWorkMinutes);
            
            $this->save();
        }
    }

    public function calculateLateMinutes()
    {
        if ($this->check_in_time && $this->employee) {
            $expectedStartTime = $this->employee->work_start_time;
            $actualStartTime = $this->check_in_time->format('H:i:s');
            
            if ($actualStartTime > $expectedStartTime) {
                $expected = \Carbon\Carbon::parse($expectedStartTime);
                $actual = \Carbon\Carbon::parse($actualStartTime);
                $this->late_minutes = $expected->diffInMinutes($actual);
                $this->save();
            }
        }
    }

    public function calculateEarlyLeaveMinutes()
    {
        if ($this->check_out_time && $this->employee) {
            $expectedEndTime = $this->employee->work_end_time;
            $actualEndTime = $this->check_out_time->format('H:i:s');
            
            if ($actualEndTime < $expectedEndTime) {
                $expected = \Carbon\Carbon::parse($expectedEndTime);
                $actual = \Carbon\Carbon::parse($actualEndTime);
                $this->early_leave_minutes = $expected->diffInMinutes($actual);
                $this->save();
            }
        }
    }
}
