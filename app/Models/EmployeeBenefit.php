<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeBenefit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'employee_id',
        'benefit_name',
        'description',
        'benefit_type',
        'calculation_type',
        'amount',
        'percentage',
        'currency',
        'effective_date',
        'expiry_date',
        'frequency',
        'is_taxable',
        'is_active',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'is_taxable' => 'boolean',
        'is_active' => 'boolean',
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

    public function scopeByBenefitType($query, $type)
    {
        return $query->where('benefit_type', $type);
    }

    public function scopeByCalculationType($query, $type)
    {
        return $query->where('calculation_type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeTaxable($query)
    {
        return $query->where('is_taxable', true);
    }

    public function scopeNonTaxable($query)
    {
        return $query->where('is_taxable', false);
    }

    public function scopeByFrequency($query, $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    public function scopeEffective($query, $date = null)
    {
        $date = $date ?? now()->toDateString();
        return $query->where('effective_date', '<=', $date)
                    ->where(function ($q) use ($date) {
                        $q->whereNull('expiry_date')
                          ->orWhere('expiry_date', '>=', $date);
                    });
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '<', now()->toDateString());
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        $expiryDate = now()->addDays($days)->toDateString();
        return $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '<=', $expiryDate)
                    ->where('expiry_date', '>=', now()->toDateString());
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('effective_date', [$startDate, $endDate])
              ->orWhereBetween('expiry_date', [$startDate, $endDate])
              ->orWhere(function ($subQ) use ($startDate, $endDate) {
                  $subQ->where('effective_date', '<=', $startDate)
                       ->where(function ($subSubQ) use ($endDate) {
                           $subSubQ->whereNull('expiry_date')
                                   ->orWhere('expiry_date', '>=', $endDate);
                       });
              });
        });
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

    public function getBenefitTypeTextAttribute()
    {
        return match($this->benefit_type) {
            'health_insurance' => 'Health Insurance',
            'life_insurance' => 'Life Insurance',
            'dental_insurance' => 'Dental Insurance',
            'vision_insurance' => 'Vision Insurance',
            'retirement' => 'Retirement',
            'transportation' => 'Transportation',
            'meal' => 'Meal',
            'housing' => 'Housing',
            'education' => 'Education',
            'gym' => 'Gym',
            'other' => 'Other',
            default => 'Unknown',
        };
    }

    public function getCalculationTypeTextAttribute()
    {
        return match($this->calculation_type) {
            'fixed_amount' => 'Fixed Amount',
            'percentage' => 'Percentage',
            'per_day' => 'Per Day',
            'per_month' => 'Per Month',
            'per_year' => 'Per Year',
            default => 'Unknown',
        };
    }

    public function getFrequencyTextAttribute()
    {
        return match($this->frequency) {
            'one_time' => 'One Time',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'yearly' => 'Yearly',
            default => 'Unknown',
        };
    }

    public function getDateRangeAttribute()
    {
        if ($this->effective_date && $this->expiry_date) {
            return $this->effective_date->format('M d, Y') . ' - ' . $this->expiry_date->format('M d, Y');
        } elseif ($this->effective_date) {
            return 'From ' . $this->effective_date->format('M d, Y');
        }
        return 'No date range';
    }

    public function getDurationAttribute()
    {
        if ($this->effective_date && $this->expiry_date) {
            return $this->effective_date->diffInDays($this->expiry_date) + 1;
        }
        return null;
    }

    public function getDaysUntilExpiryAttribute()
    {
        if ($this->expiry_date) {
            return $this->expiry_date->diffInDays(now(), false);
        }
        return null;
    }

    public function getDaysSinceEffectiveAttribute()
    {
        if ($this->effective_date) {
            return $this->effective_date->diffInDays(now(), false);
        }
        return null;
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function isTaxable()
    {
        return $this->is_taxable;
    }

    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date < now()->toDateString();
    }

    public function isEffective()
    {
        return $this->effective_date <= now()->toDateString() && !$this->isExpired();
    }

    public function isExpiringSoon($days = 30)
    {
        if (!$this->expiry_date) return false;
        
        $expiryDate = now()->addDays($days)->toDateString();
        return $this->expiry_date <= $expiryDate && $this->expiry_date >= now()->toDateString();
    }

    public function isOneTime()
    {
        return $this->frequency === 'one_time';
    }

    public function isRecurring()
    {
        return $this->frequency !== 'one_time';
    }

    public function isFixedAmount()
    {
        return $this->calculation_type === 'fixed_amount';
    }

    public function isPercentage()
    {
        return $this->calculation_type === 'percentage';
    }

    public function getStatusBadgeAttribute()
    {
        if (!$this->is_active) return 'secondary';
        if ($this->isExpired()) return 'danger';
        if ($this->isExpiringSoon()) return 'warning';
        return 'success';
    }

    public function getBenefitTypeBadgeAttribute()
    {
        return match($this->benefit_type) {
            'health_insurance' => 'success',
            'life_insurance' => 'primary',
            'dental_insurance' => 'info',
            'vision_insurance' => 'warning',
            'retirement' => 'dark',
            'transportation' => 'secondary',
            'meal' => 'warning',
            'housing' => 'danger',
            'education' => 'info',
            'gym' => 'success',
            'other' => 'secondary',
            default => 'secondary',
        };
    }

    public function getCalculationTypeBadgeAttribute()
    {
        return match($this->calculation_type) {
            'fixed_amount' => 'primary',
            'percentage' => 'success',
            'per_day' => 'warning',
            'per_month' => 'info',
            'per_year' => 'dark',
            default => 'secondary',
        };
    }

    public function getFrequencyBadgeAttribute()
    {
        return match($this->frequency) {
            'one_time' => 'secondary',
            'monthly' => 'primary',
            'quarterly' => 'info',
            'yearly' => 'success',
            default => 'secondary',
        };
    }

    public function getTaxableBadgeAttribute()
    {
        return $this->is_taxable ? 'danger' : 'success';
    }

    public function getAmountDisplayAttribute()
    {
        if ($this->isPercentage()) {
            return $this->percentage . '%';
        }
        return $this->currency . ' ' . number_format($this->amount, 2);
    }

    public function getAnnualValueAttribute()
    {
        if ($this->isOneTime()) {
            return $this->amount;
        }

        $multiplier = match($this->frequency) {
            'monthly' => 12,
            'quarterly' => 4,
            'yearly' => 1,
            default => 1,
        };

        return $this->amount * $multiplier;
    }

    public function getMonthlyValueAttribute()
    {
        if ($this->isOneTime()) {
            return $this->amount / 12; // Distribute over a year
        }

        $multiplier = match($this->frequency) {
            'monthly' => 1,
            'quarterly' => 1/3,
            'yearly' => 1/12,
            default => 1,
        };

        return $this->amount * $multiplier;
    }

    public function getQuarterlyValueAttribute()
    {
        if ($this->isOneTime()) {
            return $this->amount / 4; // Distribute over a year
        }

        $multiplier = match($this->frequency) {
            'monthly' => 3,
            'quarterly' => 1,
            'yearly' => 1/4,
            default => 1,
        };

        return $this->amount * $multiplier;
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

    public function getNotesPreviewAttribute()
    {
        if ($this->notes) {
            return strlen($this->notes) > 100 
                ? substr($this->notes, 0, 100) . '...' 
                : $this->notes;
        }
        return 'No notes';
    }

    public function getFormattedEffectiveDateAttribute()
    {
        return $this->effective_date ? $this->effective_date->format('M d, Y') : 'Not set';
    }

    public function getFormattedExpiryDateAttribute()
    {
        return $this->expiry_date ? $this->expiry_date->format('M d, Y') : 'No expiry';
    }

    public function getFormattedApprovedDateAttribute()
    {
        return $this->approved_at ? $this->approved_at->format('M d, Y H:i') : 'Not approved';
    }

    public function canBeActivated()
    {
        return !$this->is_active && $this->effective_date <= now()->toDateString();
    }

    public function canBeDeactivated()
    {
        return $this->is_active;
    }

    public function canBeExpired()
    {
        return $this->is_active && $this->expiry_date && $this->expiry_date < now()->toDateString();
    }

    public function canBeRenewed()
    {
        return $this->isExpired() || $this->isExpiringSoon();
    }

    public function getExpiryStatusAttribute()
    {
        if (!$this->expiry_date) return 'No Expiry';
        if ($this->isExpired()) return 'Expired';
        if ($this->isExpiringSoon(7)) return 'Expiring Soon (7 days)';
        if ($this->isExpiringSoon(30)) return 'Expiring Soon (30 days)';
        return 'Active';
    }

    public function getExpiryStatusBadgeAttribute()
    {
        return match($this->expiry_status) {
            'No Expiry' => 'success',
            'Expired' => 'danger',
            'Expiring Soon (7 days)' => 'danger',
            'Expiring Soon (30 days)' => 'warning',
            'Active' => 'success',
            default => 'secondary',
        };
    }
}
