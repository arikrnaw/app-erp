<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'department_id',
        'expense_date',
        'description',
        'amount',
        'tax_amount',
        'total_amount',
        'payment_method',
        'vendor_name',
        'invoice_number',
        'receipt_attachment',
        'notes',
        'is_recurring',
        'recurring_frequency',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_recurring' => 'boolean',
    ];

    protected $attributes = [
        'status' => 'draft',
        'is_recurring' => false,
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Finance\ExpenseCategory::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Department::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function approvalRequest(): MorphOne
    {
        return $this->morphOne(ApprovalRequest::class, 'approvable');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    public function scopeByAmountRange($query, $minAmount, $maxAmount)
    {
        return $query->whereBetween('total_amount', [$minAmount, $maxAmount]);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    // Helper methods
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isPendingApproval()
    {
        return $this->status === 'pending_approval';
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

    public function canEdit()
    {
        return in_array($this->status, ['draft', 'pending_approval']);
    }

    public function canCancel()
    {
        return in_array($this->status, ['draft', 'pending_approval']);
    }

    public function canApprove()
    {
        return $this->isPendingApproval() && $this->approvalRequest;
    }

    public function getFormattedAmount()
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedTaxAmount()
    {
        return number_format($this->tax_amount, 2);
    }

    public function getFormattedTotalAmount()
    {
        return number_format($this->total_amount, 2);
    }

    public function getFormattedExpenseDate()
    {
        return $this->expense_date->format('M d, Y');
    }

    public function getStatusBadgeVariant()
    {
        return match($this->status) {
            'draft' => 'secondary',
            'pending_approval' => 'warning',
            'approved' => 'default',
            'rejected' => 'destructive',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    public function getStatusLabel()
    {
        return match($this->status) {
            'draft' => 'Draft',
            'pending_approval' => 'Pending Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
            default => 'Unknown',
        };
    }

    public function getPaymentMethodLabel()
    {
        return match($this->payment_method) {
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'credit_card' => 'Credit Card',
            'check' => 'Check',
            default => 'Unknown',
        };
    }

    public function getRecurringFrequencyLabel()
    {
        return match($this->recurring_frequency) {
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'yearly' => 'Yearly',
            default => 'One-time',
        };
    }

    // Events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $expense->created_by = \Illuminate\Support\Facades\Auth::id();
            }
            
            // Auto-calculate total amount if not provided
            if (!$expense->total_amount) {
                $expense->total_amount = $expense->amount + ($expense->tax_amount ?? 0);
            }
        });

        static::updating(function ($expense) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $expense->updated_by = \Illuminate\Support\Facades\Auth::id();
            }
            
            // Auto-calculate total amount if amount or tax changed
            if ($expense->isDirty(['amount', 'tax_amount'])) {
                $expense->total_amount = $expense->amount + ($expense->tax_amount ?? 0);
            }
        });
    }
}
