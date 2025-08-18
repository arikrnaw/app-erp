<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'employee_id',
        'leave_type_id',
        'request_number',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'total_days',
        'total_hours',
        'leave_duration',
        'reason',
        'additional_notes',
        'attachment_file',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejection_reason',
        'days_taken',
        'days_remaining',
        'days_carried_forward',
        'is_urgent',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'total_days' => 'integer',
        'total_hours' => 'integer',
        'days_taken' => 'integer',
        'days_remaining' => 'integer',
        'days_carried_forward' => 'integer',
        'is_urgent' => 'boolean',
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

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
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

    public function scopeByLeaveType($query, $leaveTypeId)
    {
        return $query->where('leave_type_id', $leaveTypeId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeUrgent($query)
    {
        return $query->where('is_urgent', true);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function ($subQ) use ($startDate, $endDate) {
                  $subQ->where('start_date', '<=', $startDate)
                       ->where('end_date', '>=', $endDate);
              });
        });
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('start_date', now()->year);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereYear('start_date', now()->year)
                    ->whereMonth('start_date', now()->month);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now()->toDateString());
    }

    public function scopeOverlapping($query, $employeeId, $startDate, $endDate, $excludeId = null)
    {
        $query->where('employee_id', $employeeId)
              ->where('status', '!=', 'cancelled')
              ->where(function ($q) use ($startDate, $endDate) {
                  $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($subQ) use ($startDate, $endDate) {
                        $subQ->where('start_date', '<=', $startDate)
                             ->where('end_date', '>=', $endDate);
                    });
              });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query;
    }

    // Methods
    public function getFullNameAttribute()
    {
        return $this->employee->full_name ?? 'Unknown Employee';
    }

    public function getLeaveTypeNameAttribute()
    {
        return $this->leaveType->name ?? 'Unknown Type';
    }

    public function getApproverNameAttribute()
    {
        return $this->approvedBy->name ?? 'Not approved';
    }

    public function getDateRangeAttribute()
    {
        if ($this->start_date->equalTo($this->end_date)) {
            return $this->start_date->format('M d, Y');
        }
        return $this->start_date->format('M d, Y') . ' - ' . $this->end_date->format('M d, Y');
    }

    public function getTimeRangeAttribute()
    {
        if ($this->start_time && $this->end_time) {
            return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
        }
        return 'Full day';
    }

    public function getDurationTextAttribute()
    {
        if ($this->leave_duration === 'hours') {
            return "{$this->total_hours} hours";
        }
        return "{$this->total_days} day(s)";
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isUrgent()
    {
        return $this->is_urgent;
    }

    public function isFullDay()
    {
        return $this->leave_duration === 'full_day';
    }

    public function isHalfDay()
    {
        return $this->leave_duration === 'half_day';
    }

    public function isHourly()
    {
        return $this->leave_duration === 'hours';
    }

    public function isOverlapping()
    {
        return $this->overlapping($this->employee_id, $this->start_date, $this->end_date, $this->id)->exists();
    }

    public function isInProgress()
    {
        $today = now()->toDateString();
        return $this->isApproved() && 
               $this->start_date <= $today && 
               $this->end_date >= $today;
    }

    public function isCompleted()
    {
        return $this->isApproved() && $this->end_date < now()->toDateString();
    }

    public function isUpcoming()
    {
        return $this->isApproved() && $this->start_date > now()->toDateString();
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    public function getDurationBadgeAttribute()
    {
        return match($this->leave_duration) {
            'full_day' => 'primary',
            'half_day' => 'info',
            'hours' => 'warning',
            default => 'secondary',
        };
    }

    public function getUrgencyBadgeAttribute()
    {
        return $this->is_urgent ? 'danger' : 'secondary';
    }

    public function getProgressStatusAttribute()
    {
        if ($this->isInProgress()) {
            return 'In Progress';
        } elseif ($this->isCompleted()) {
            return 'Completed';
        } elseif ($this->isUpcoming()) {
            return 'Upcoming';
        }
        return 'N/A';
    }

    public function getProgressBadgeAttribute()
    {
        return match($this->progress_status) {
            'In Progress' => 'warning',
            'Completed' => 'success',
            'Upcoming' => 'info',
            default => 'secondary',
        };
    }

    public function getDaysUntilStartAttribute()
    {
        return $this->start_date->diffInDays(now(), false);
    }

    public function getDaysUntilEndAttribute()
    {
        return $this->end_date->diffInDays(now(), false);
    }

    public function getIsStartingSoonAttribute()
    {
        return $this->days_until_start <= 3 && $this->days_until_start >= 0;
    }

    public function getIsEndingSoonAttribute()
    {
        return $this->days_until_end <= 1 && $this->days_until_end >= 0;
    }

    public function getBalanceAfterLeaveAttribute()
    {
        return $this->days_remaining - $this->total_days;
    }

    public function willExceedLimit()
    {
        return $this->balance_after_leave < 0;
    }

    public function getExcessDaysAttribute()
    {
        return max(0, -$this->balance_after_leave);
    }

    public function getReasonPreviewAttribute()
    {
        if ($this->reason) {
            return strlen($this->reason) > 100 
                ? substr($this->reason, 0, 100) . '...' 
                : $this->reason;
        }
        return 'No reason provided';
    }

    public function getNotesPreviewAttribute()
    {
        if ($this->additional_notes) {
            return strlen($this->additional_notes) > 100 
                ? substr($this->additional_notes, 0, 100) . '...' 
                : $this->additional_notes;
        }
        return 'No additional notes';
    }

    public function calculateTotalDays()
    {
        if ($this->start_date && $this->end_date) {
            $start = $this->start_date;
            $end = $this->end_date;
            $totalDays = 0;
            
            while ($start <= $end) {
                // Skip weekends (assuming Saturday = 6, Sunday = 0)
                if ($start->dayOfWeek !== 0 && $start->dayOfWeek !== 6) {
                    if ($this->leave_duration === 'half_day') {
                        $totalDays += 0.5;
                    } else {
                        $totalDays += 1;
                    }
                }
                $start->addDay();
            }
            
            $this->total_days = $totalDays;
            $this->save();
        }
    }

    public function calculateTotalHours()
    {
        if ($this->leave_duration === 'hours' && $this->start_time && $this->end_time) {
            $this->total_hours = $this->start_time->diffInHours($this->end_time);
            $this->save();
        }
    }
}
