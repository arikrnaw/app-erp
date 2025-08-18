<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'payroll_period_id',
        'employee_id',
        'payroll_number',
        'base_salary',
        'basic_pay',
        'working_days',
        'present_days',
        'absent_days',
        'leave_days',
        'overtime_hours',
        'overtime_pay',
        'transport_allowance',
        'meal_allowance',
        'housing_allowance',
        'medical_allowance',
        'other_allowances',
        'total_allowances',
        'tax_deduction',
        'social_security',
        'health_insurance',
        'loan_deduction',
        'advance_deduction',
        'other_deductions',
        'total_deductions',
        'gross_pay',
        'net_pay',
        'notes',
        'status',
        'approved_by',
        'approved_at',
        'payment_date',
        'payment_method',
        'bank_account',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'basic_pay' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'meal_allowance' => 'decimal:2',
        'housing_allowance' => 'decimal:2',
        'medical_allowance' => 'decimal:2',
        'other_allowances' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'tax_deduction' => 'decimal:2',
        'social_security' => 'decimal:2',
        'health_insurance' => 'decimal:2',
        'loan_deduction' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'approved_at' => 'datetime',
        'payment_date' => 'date',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class);
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

    public function scopeByPayrollPeriod($query, $payrollPeriodId)
    {
        return $query->where('payroll_period_id', $payrollPeriodId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('payment_date', now()->year);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereYear('payment_date', now()->year)
                    ->whereMonth('payment_date', now()->month);
    }

    public function scopeOverdue($query)
    {
        return $query->where('payment_date', '<', now()->toDateString())
                    ->where('status', '!=', 'paid');
    }

    // Methods
    public function getFullNameAttribute()
    {
        return $this->employee->full_name ?? 'Unknown Employee';
    }

    public function getApproverNameAttribute()
    {
        return $this->approvedBy->name ?? 'Not approved';
    }

    public function getPeriodNameAttribute()
    {
        return $this->payrollPeriod->name ?? 'Unknown Period';
    }

    public function getAttendanceRateAttribute()
    {
        if ($this->working_days > 0) {
            return round(($this->present_days / $this->working_days) * 100, 1);
        }
        return 0;
    }

    public function getAbsentRateAttribute()
    {
        if ($this->working_days > 0) {
            return round(($this->absent_days / $this->working_days) * 100, 1);
        }
        return 0;
    }

    public function getLeaveRateAttribute()
    {
        if ($this->working_days > 0) {
            return round(($this->leave_days / $this->working_days) * 100, 1);
        }
        return 0;
    }

    public function getOvertimeRateAttribute()
    {
        if ($this->overtime_hours > 0) {
            return round(($this->overtime_pay / $this->basic_pay) * 100, 1);
        }
        return 0;
    }

    public function getAllowancesPercentageAttribute()
    {
        if ($this->gross_pay > 0) {
            return round(($this->total_allowances / $this->gross_pay) * 100, 1);
        }
        return 0;
    }

    public function getDeductionsPercentageAttribute()
    {
        if ($this->gross_pay > 0) {
            return round(($this->total_deductions / $this->gross_pay) * 100, 1);
        }
        return 0;
    }

    public function getTaxPercentageAttribute()
    {
        if ($this->gross_pay > 0) {
            return round(($this->tax_deduction / $this->gross_pay) * 100, 1);
        }
        return 0;
    }

    public function getNetPayPercentageAttribute()
    {
        if ($this->gross_pay > 0) {
            return round(($this->net_pay / $this->gross_pay) * 100, 1);
        }
        return 0;
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isOverdue()
    {
        return $this->payment_date && $this->payment_date < now()->toDateString() && !$this->isPaid();
    }

    public function hasOvertime()
    {
        return $this->overtime_hours > 0;
    }

    public function hasAllowances()
    {
        return $this->total_allowances > 0;
    }

    public function hasDeductions()
    {
        return $this->total_deductions > 0;
    }

    public function hasTax()
    {
        return $this->tax_deduction > 0;
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'draft' => 'secondary',
            'approved' => 'success',
            'paid' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getPaymentMethodBadgeAttribute()
    {
        return match($this->payment_method) {
            'bank_transfer' => 'success',
            'cash' => 'warning',
            'check' => 'info',
            'direct_deposit' => 'primary',
            default => 'secondary',
        };
    }

    public function getAttendanceBadgeAttribute()
    {
        if ($this->attendance_rate >= 95) return 'success';
        if ($this->attendance_rate >= 90) return 'warning';
        return 'danger';
    }

    public function getOvertimeBadgeAttribute()
    {
        if ($this->overtime_hours > 0) return 'warning';
        return 'secondary';
    }

    public function getAllowancesBadgeAttribute()
    {
        if ($this->total_allowances > 0) return 'success';
        return 'secondary';
    }

    public function getDeductionsBadgeAttribute()
    {
        if ($this->total_deductions > 0) return 'danger';
        return 'secondary';
    }

    public function getNotesPreviewAttribute()
    {
        if ($this->notes) {
            return strlen($this->notes) > 100 
                ? substr($this->notes, 0, 100) . '...' 
                : $this->notes;
        }
        return 'No notes';
    }

    public function calculateBasicPay()
    {
        if ($this->working_days > 0 && $this->base_salary > 0) {
            $dailyRate = $this->base_salary / $this->working_days;
            $this->basic_pay = $dailyRate * $this->present_days;
            $this->save();
        }
    }

    public function calculateOvertimePay()
    {
        if ($this->overtime_hours > 0 && $this->basic_pay > 0) {
            // Assuming 1.5x rate for overtime
            $hourlyRate = $this->basic_pay / ($this->present_days * 8); // 8 hours per day
            $this->overtime_pay = $hourlyRate * $this->overtime_hours * 1.5;
            $this->save();
        }
    }

    public function calculateAllowances()
    {
        $this->total_allowances = $this->transport_allowance + 
                                 $this->meal_allowance + 
                                 $this->housing_allowance + 
                                 $this->medical_allowance + 
                                 $this->other_allowances;
        $this->save();
    }

    public function calculateDeductions()
    {
        $this->total_deductions = $this->tax_deduction + 
                                 $this->social_security + 
                                 $this->health_insurance + 
                                 $this->loan_deduction + 
                                 $this->advance_deduction + 
                                 $this->other_deductions;
        $this->save();
    }

    public function calculateGrossPay()
    {
        $this->gross_pay = $this->basic_pay + $this->overtime_pay + $this->total_allowances;
        $this->save();
    }

    public function calculateNetPay()
    {
        $this->net_pay = $this->gross_pay - $this->total_deductions;
        $this->save();
    }

    public function calculateAll()
    {
        $this->calculateBasicPay();
        $this->calculateOvertimePay();
        $this->calculateAllowances();
        $this->calculateDeductions();
        $this->calculateGrossPay();
        $this->calculateNetPay();
    }

    public function canBeApproved()
    {
        return $this->isDraft() && $this->net_pay > 0;
    }

    public function canBePaid()
    {
        return $this->isApproved() && $this->net_pay > 0;
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['draft', 'approved']) && !$this->isPaid();
    }

    public function getFormattedPaymentDateAttribute()
    {
        return $this->payment_date ? $this->payment_date->format('M d, Y') : 'Not set';
    }

    public function getFormattedApprovedDateAttribute()
    {
        return $this->approved_at ? $this->approved_at->format('M d, Y H:i') : 'Not approved';
    }
}
