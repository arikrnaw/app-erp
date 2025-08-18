<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'color',
        'default_days_per_year',
        'is_paid',
        'requires_approval',
        'requires_document',
        'can_carry_forward',
        'max_carry_forward_days',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'default_days_per_year' => 'integer',
        'is_paid' => 'boolean',
        'requires_approval' => 'boolean',
        'requires_document' => 'boolean',
        'can_carry_forward' => 'boolean',
        'max_carry_forward_days' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false);
    }

    public function scopeRequiresApproval($query)
    {
        return $query->where('requires_approval', true);
    }

    public function scopeRequiresDocument($query)
    {
        return $query->where('requires_document', true);
    }

    public function scopeCanCarryForward($query)
    {
        return $query->where('can_carry_forward', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }

    // Methods
    public function isPaid()
    {
        return $this->is_paid;
    }

    public function isUnpaid()
    {
        return !$this->is_paid;
    }

    public function requiresApproval()
    {
        return $this->requires_approval;
    }

    public function requiresDocument()
    {
        return $this->requires_document;
    }

    public function canCarryForward()
    {
        return $this->can_carry_forward;
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function getStatusBadgeAttribute()
    {
        return $this->is_active ? 'success' : 'secondary';
    }

    public function getPaidStatusBadgeAttribute()
    {
        return $this->is_paid ? 'success' : 'warning';
    }

    public function getApprovalRequiredBadgeAttribute()
    {
        return $this->requires_approval ? 'warning' : 'success';
    }

    public function getDocumentRequiredBadgeAttribute()
    {
        return $this->requires_document ? 'info' : 'secondary';
    }

    public function getCarryForwardBadgeAttribute()
    {
        return $this->can_carry_forward ? 'success' : 'secondary';
    }

    public function getColorStyleAttribute()
    {
        return "background-color: {$this->color}; color: white;";
    }

    public function getTextColorStyleAttribute()
    {
        return "color: {$this->color};";
    }

    public function getBorderColorStyleAttribute()
    {
        return "border-color: {$this->color};";
    }

    public function getUsageCountAttribute()
    {
        return $this->leaveRequests()->count();
    }

    public function getActiveUsageCountAttribute()
    {
        return $this->leaveRequests()->where('status', 'approved')->count();
    }

    public function getPendingUsageCountAttribute()
    {
        return $this->leaveRequests()->where('status', 'pending')->count();
    }

    public function getRejectedUsageCountAttribute()
    {
        return $this->leaveRequests()->where('status', 'rejected')->count();
    }

    public function getTotalDaysUsedAttribute()
    {
        return $this->leaveRequests()
            ->where('status', 'approved')
            ->sum('total_days');
    }

    public function getAverageDaysPerRequestAttribute()
    {
        $approvedRequests = $this->leaveRequests()->where('status', 'approved');
        $count = $approvedRequests->count();
        
        if ($count > 0) {
            return round($this->total_days_used / $count, 1);
        }
        
        return 0;
    }

    public function getRemainingDaysAttribute()
    {
        return max(0, $this->default_days_per_year - $this->total_days_used);
    }

    public function getUsagePercentageAttribute()
    {
        if ($this->default_days_per_year > 0) {
            return round(($this->total_days_used / $this->default_days_per_year) * 100, 1);
        }
        return 0;
    }

    public function isOverLimit()
    {
        return $this->total_days_used > $this->default_days_per_year;
    }

    public function getOverLimitDaysAttribute()
    {
        return max(0, $this->total_days_used - $this->default_days_per_year);
    }

    public function getCarryForwardDaysAvailableAttribute()
    {
        if (!$this->can_carry_forward) {
            return 0;
        }
        
        return min($this->remaining_days, $this->max_carry_forward_days);
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

    public function getFormattedDefaultDaysAttribute()
    {
        if ($this->default_days_per_year == 0) {
            return 'Unlimited';
        }
        return $this->default_days_per_year . ' days/year';
    }

    public function getFormattedMaxCarryForwardAttribute()
    {
        if (!$this->can_carry_forward) {
            return 'Not allowed';
        }
        return $this->max_carry_forward_days . ' days';
    }

    public function getRequirementsTextAttribute()
    {
        $requirements = [];
        
        if ($this->requires_approval) {
            $requirements[] = 'Approval required';
        }
        
        if ($this->requires_document) {
            $requirements[] = 'Document required';
        }
        
        if ($this->can_carry_forward) {
            $requirements[] = 'Can carry forward';
        }
        
        return empty($requirements) ? 'No special requirements' : implode(', ', $requirements);
    }
}
