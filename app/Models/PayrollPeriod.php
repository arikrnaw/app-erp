<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollPeriod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'period_code',
        'name',
        'start_date',
        'end_date',
        'pay_date',
        'frequency',
        'status',
        'total_employees',
        'total_gross_pay',
        'total_net_pay',
        'total_tax',
        'total_deductions',
        'total_allowances',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'pay_date' => 'date',
        'total_gross_pay' => 'decimal:2',
        'total_net_pay' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'approved_at' => 'datetime',
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

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payrollRecords()
    {
        return $this->hasMany(PayrollRecord::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
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

    public function scopeByFrequency($query, $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate]);
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
        return $query->where('pay_date', '>=', now()->toDateString());
    }

    public function scopeOverdue($query)
    {
        return $query->where('pay_date', '<', now()->toDateString())
                    ->where('status', '!=', 'paid');
    }

    // Methods
    public function getCreatorNameAttribute()
    {
        return $this->createdBy->name ?? 'Unknown';
    }

    public function getApproverNameAttribute()
    {
        return $this->approvedBy->name ?? 'Not approved';
    }

    public function getDateRangeAttribute()
    {
        return $this->start_date->format('M d, Y') . ' - ' . $this->end_date->format('M d, Y');
    }

    public function getPeriodLengthAttribute()
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function getDaysUntilPayDateAttribute()
    {
        return $this->pay_date->diffInDays(now(), false);
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isProcessing()
    {
        return $this->status === 'processing';
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
        return $this->pay_date < now()->toDateString() && !$this->isPaid();
    }

    public function isUpcoming()
    {
        return $this->pay_date > now()->toDateString();
    }

    public function isDueSoon()
    {
        return $this->days_until_pay_date <= 3 && $this->days_until_pay_date >= 0;
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'draft' => 'secondary',
            'processing' => 'info',
            'approved' => 'success',
            'paid' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getFrequencyBadgeAttribute()
    {
        return match($this->frequency) {
            'daily' => 'primary',
            'weekly' => 'info',
            'bi_weekly' => 'warning',
            'monthly' => 'success',
            'quarterly' => 'secondary',
            'yearly' => 'dark',
            default => 'secondary',
        };
    }

    public function getFrequencyTextAttribute()
    {
        return match($this->frequency) {
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'bi_weekly' => 'Bi-Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'yearly' => 'Yearly',
            default => 'Unknown',
        };
    }

    public function getTotalTaxPercentageAttribute()
    {
        if ($this->total_gross_pay > 0) {
            return round(($this->total_tax / $this->total_gross_pay) * 100, 2);
        }
        return 0;
    }

    public function getTotalDeductionsPercentageAttribute()
    {
        if ($this->total_gross_pay > 0) {
            return round(($this->total_deductions / $this->total_gross_pay) * 100, 2);
        }
        return 0;
    }

    public function getTotalAllowancesPercentageAttribute()
    {
        if ($this->total_gross_pay > 0) {
            return round(($this->total_allowances / $this->total_gross_pay) * 100, 2);
        }
        return 0;
    }

    public function getAverageGrossPayAttribute()
    {
        if ($this->total_employees > 0) {
            return round($this->total_gross_pay / $this->total_employees, 2);
        }
        return 0;
    }

    public function getAverageNetPayAttribute()
    {
        if ($this->total_employees > 0) {
            return round($this->total_net_pay / $this->total_employees, 2);
        }
        return 0;
    }

    public function getProcessedRecordsCountAttribute()
    {
        return $this->payrollRecords()->count();
    }

    public function getApprovedRecordsCountAttribute()
    {
        return $this->payrollRecords()->where('status', 'approved')->count();
    }

    public function getPaidRecordsCountAttribute()
    {
        return $this->payrollRecords()->where('status', 'paid')->count();
    }

    public function getPendingRecordsCountAttribute()
    {
        return $this->payrollRecords()->where('status', 'draft')->count();
    }

    public function getCompletionPercentageAttribute()
    {
        if ($this->total_employees > 0) {
            return round(($this->processed_records_count / $this->total_employees) * 100, 1);
        }
        return 0;
    }

    public function getPaymentCompletionPercentageAttribute()
    {
        if ($this->total_employees > 0) {
            return round(($this->paid_records_count / $this->total_employees) * 100, 1);
        }
        return 0;
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

    public function calculateTotals()
    {
        $records = $this->payrollRecords()->where('status', '!=', 'cancelled');
        
        $this->total_employees = $records->count();
        $this->total_gross_pay = $records->sum('gross_pay');
        $this->total_net_pay = $records->sum('net_pay');
        $this->total_tax = $records->sum('tax_deduction');
        $this->total_deductions = $records->sum('total_deductions');
        $this->total_allowances = $records->sum('total_allowances');
        
        $this->save();
    }

    public function canBeProcessed()
    {
        return $this->isDraft() && $this->total_employees > 0;
    }

    public function canBeApproved()
    {
        return $this->isProcessing() && $this->processed_records_count > 0;
    }

    public function canBePaid()
    {
        return $this->isApproved() && $this->approved_records_count > 0;
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['draft', 'processing']) && !$this->isPaid();
    }
}
