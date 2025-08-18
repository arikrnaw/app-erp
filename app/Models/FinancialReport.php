<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'report_code',
        'title',
        'description',
        'report_type',
        'period_type',
        'start_date',
        'end_date',
        'total_revenue',
        'total_expenses',
        'net_profit',
        'gross_margin',
        'operating_margin',
        'financial_metrics',
        'chart_data',
        'status',
        'created_by',
        'company_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_revenue' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'net_profit' => 'decimal:2',
        'gross_margin' => 'decimal:2',
        'operating_margin' => 'decimal:2',
        'financial_metrics' => 'array',
        'chart_data' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getReportTypeLabelAttribute(): string
    {
        return match($this->report_type) {
            'income_statement' => 'Income Statement',
            'balance_sheet' => 'Balance Sheet',
            'cash_flow' => 'Cash Flow',
            'profit_loss' => 'Profit & Loss',
            'revenue_analysis' => 'Revenue Analysis',
            'expense_analysis' => 'Expense Analysis',
            'budget_variance' => 'Budget Variance',
            'custom' => 'Custom Report',
            default => ucfirst(str_replace('_', ' ', $this->report_type)),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'published' => 'Published',
            'archived' => 'Archived',
            default => ucfirst($this->status),
        };
    }

    public function getPeriodTypeLabelAttribute(): string
    {
        return ucfirst($this->period_type);
    }
}
