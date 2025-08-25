<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'code',
        'is_active',
        'parent_id',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'is_active' => true,
        'sort_order' => 0,
    ];

    // Relationships
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    public function parent()
    {
        return $this->belongsTo(ExpenseCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ExpenseCategory::class, 'parent_id');
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class, 'category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    // Helper methods
    public function isRoot()
    {
        return is_null($this->parent_id);
    }

    public function hasChildren()
    {
        return $this->children()->exists();
    }

    public function getFullName()
    {
        if ($this->parent) {
            return $this->parent->name . ' > ' . $this->name;
        }
        return $this->name;
    }

    public function getTotalExpenses($startDate = null, $endDate = null)
    {
        $query = $this->expenses()->where('status', 'approved');
        
        if ($startDate && $endDate) {
            $query->whereBetween('expense_date', [$startDate, $endDate]);
        }
        
        return $query->sum('total_amount');
    }

    public function getBudgetAmount($year, $month)
    {
        $budget = $this->budgets()
            ->where('year', $year)
            ->where('month', $month)
            ->first();
            
        return $budget ? $budget->amount : 0;
    }

    public function getBudgetVariance($year, $month)
    {
        $budgetAmount = $this->getBudgetAmount($year, $month);
        $actualAmount = $this->getTotalExpenses(
            \Carbon\Carbon::create($year, $month, 1)->startOfMonth(),
            \Carbon\Carbon::create($year, $month, 1)->endOfMonth()
        );
        
        return [
            'budgeted' => $budgetAmount,
            'actual' => $actualAmount,
            'variance' => $budgetAmount - $actualAmount,
            'variance_percentage' => $budgetAmount > 0 ? (($budgetAmount - $actualAmount) / $budgetAmount) * 100 : 0,
        ];
    }
}
