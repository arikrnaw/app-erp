<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetPurchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'department_id',
        'supplier_id',
        'asset_name',
        'asset_code',
        'purchase_date',
        'description',
        'purchase_cost',
        'tax_amount',
        'shipping_cost',
        'installation_cost',
        'total_cost',
        'expected_life_years',
        'depreciation_method',
        'salvage_value',
        'warranty_period_months',
        'maintenance_required',
        'location',
        'notes',
        'is_capital_expenditure',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'installation_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'expected_life_years' => 'integer',
        'salvage_value' => 'decimal:2',
        'warranty_period_months' => 'integer',
        'maintenance_required' => 'boolean',
        'is_capital_expenditure' => 'boolean',
    ];

    protected $attributes = [
        'status' => 'draft',
        'is_capital_expenditure' => true,
        'maintenance_required' => false,
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Finance\AssetCategory::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Department::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Supplier::class);
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

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('purchase_date', [$startDate, $endDate]);
    }

    public function scopeByAmountRange($query, $minAmount, $maxAmount)
    {
        return $query->whereBetween('total_cost', [$minAmount, $maxAmount]);
    }

    public function scopeCapitalExpenditure($query)
    {
        return $query->where('is_capital_expenditure', true);
    }

    public function scopeByDepreciationMethod($query, $method)
    {
        return $query->where('depreciation_method', $method);
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

    public function getFormattedPurchaseCost()
    {
        return number_format($this->purchase_cost, 2);
    }

    public function getFormattedTaxAmount()
    {
        return number_format($this->tax_amount, 2);
    }

    public function getFormattedShippingCost()
    {
        return number_format($this->shipping_cost, 2);
    }

    public function getFormattedInstallationCost()
    {
        return number_format($this->installation_cost, 2);
    }

    public function getFormattedTotalCost()
    {
        return number_format($this->total_cost, 2);
    }

    public function getFormattedSalvageValue()
    {
        return number_format($this->salvage_value, 2);
    }

    public function getFormattedPurchaseDate()
    {
        return $this->purchase_date->format('M d, Y');
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

    public function getDepreciationMethodLabel()
    {
        return match($this->depreciation_method) {
            'straight_line' => 'Straight Line',
            'declining_balance' => 'Declining Balance',
            'sum_of_years' => 'Sum of Years',
            default => 'Unknown',
        };
    }

    public function getCapitalExpenditureLabel()
    {
        return $this->is_capital_expenditure ? 'Capital' : 'Operating';
    }

    public function getMaintenanceRequiredLabel()
    {
        return $this->maintenance_required ? 'Required' : 'Not Required';
    }

    // Depreciation calculations
    public function calculateAnnualDepreciation(): float
    {
        $depreciableAmount = $this->total_cost - $this->salvage_value;

        return match($this->depreciation_method) {
            'straight_line' => $depreciableAmount / $this->expected_life_years,
            'declining_balance' => $depreciableAmount * (2 / $this->expected_life_years), // Double declining balance
            'sum_of_years' => $depreciableAmount * ($this->expected_life_years / (($this->expected_life_years * ($this->expected_life_years + 1)) / 2)),
            default => $depreciableAmount / $this->expected_life_years,
        };
    }

    public function calculateMonthlyDepreciation(): float
    {
        return $this->calculateAnnualDepreciation() / 12;
    }

    public function calculateDepreciationForPeriod(int $months): float
    {
        return $this->calculateMonthlyDepreciation() * $months;
    }

    public function getBookValueAtDate(\Carbon\Carbon $date): float
    {
        if ($date < $this->purchase_date) {
            return $this->total_cost;
        }

        $monthsSincePurchase = $date->diffInMonths($this->purchase_date);
        $totalDepreciation = $this->calculateDepreciationForPeriod($monthsSincePurchase);
        
        return max($this->salvage_value, $this->total_cost - $totalDepreciation);
    }

    public function getWarrantyExpiryDate(): ?\Carbon\Carbon
    {
        if (!$this->warranty_period_months) {
            return null;
        }

        return $this->purchase_date->addMonths($this->warranty_period_months);
    }

    public function isUnderWarranty(): bool
    {
        $warrantyExpiry = $this->getWarrantyExpiryDate();
        return $warrantyExpiry && now()->lt($warrantyExpiry);
    }

    public function getWarrantyStatus(): string
    {
        if (!$this->warranty_period_months) {
            return 'No Warranty';
        }

        if ($this->isUnderWarranty()) {
            $expiryDate = $this->getWarrantyExpiryDate();
            $daysLeft = now()->diffInDays($expiryDate, false);
            return "Under Warranty ({$daysLeft} days left)";
        }

        return 'Warranty Expired';
    }

    // Events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($assetPurchase) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $assetPurchase->created_by = \Illuminate\Support\Facades\Auth::id();
            }
            
            // Auto-calculate total cost if not provided
            if (!$assetPurchase->total_cost) {
                $assetPurchase->total_cost = $assetPurchase->purchase_cost + 
                                           ($assetPurchase->tax_amount ?? 0) + 
                                           ($assetPurchase->shipping_cost ?? 0) + 
                                           ($assetPurchase->installation_cost ?? 0);
            }
        });

        static::updating(function ($assetPurchase) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $assetPurchase->updated_by = \Illuminate\Support\Facades\Auth::id();
            }
            
            // Auto-calculate total cost if any cost component changed
            if ($assetPurchase->isDirty(['purchase_cost', 'tax_amount', 'shipping_cost', 'installation_cost'])) {
                $assetPurchase->total_cost = $assetPurchase->purchase_cost + 
                                           ($assetPurchase->tax_amount ?? 0) + 
                                           ($assetPurchase->shipping_cost ?? 0) + 
                                           ($assetPurchase->installation_cost ?? 0);
            }
        });
    }
}
