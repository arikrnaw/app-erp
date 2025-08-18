<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessAnalytics extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'analysis_code',
        'title',
        'description',
        'analysis_type',
        'data_source',
        'analysis_date',
        'data_start_date',
        'data_end_date',
        'key_metrics',
        'insights',
        'recommendations',
        'visualization_data',
        'priority',
        'status',
        'created_by',
        'company_id',
    ];

    protected $casts = [
        'analysis_date' => 'date',
        'data_start_date' => 'date',
        'data_end_date' => 'date',
        'key_metrics' => 'array',
        'insights' => 'array',
        'recommendations' => 'array',
        'visualization_data' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getAnalysisTypeLabelAttribute(): string
    {
        return match($this->analysis_type) {
            'sales_analysis' => 'Sales Analysis',
            'customer_analysis' => 'Customer Analysis',
            'product_analysis' => 'Product Analysis',
            'market_analysis' => 'Market Analysis',
            'performance_analysis' => 'Performance Analysis',
            'trend_analysis' => 'Trend Analysis',
            'forecasting' => 'Forecasting',
            'custom' => 'Custom Analysis',
            default => ucfirst(str_replace('_', ' ', $this->analysis_type)),
        };
    }

    public function getDataSourceLabelAttribute(): string
    {
        return match($this->data_source) {
            'sales_orders' => 'Sales Orders',
            'customers' => 'Customers',
            'products' => 'Products',
            'inventory' => 'Inventory',
            'financial_reports' => 'Financial Reports',
            'external_api' => 'External API',
            'custom' => 'Custom Data',
            default => ucfirst(str_replace('_', ' ', $this->data_source)),
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return ucfirst($this->priority);
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
}
