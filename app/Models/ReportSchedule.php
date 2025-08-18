<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'schedule_code',
        'name',
        'description',
        'report_type',
        'report_template',
        'frequency',
        'day_of_week',
        'day_of_month',
        'delivery_time',
        'delivery_method',
        'recipients',
        'parameters',
        'is_active',
        'last_generated_at',
        'next_generation_at',
        'created_by',
        'company_id',
    ];

    protected $casts = [
        'delivery_time' => 'datetime:H:i:s',
        'recipients' => 'array',
        'parameters' => 'array',
        'is_active' => 'boolean',
        'last_generated_at' => 'datetime',
        'next_generation_at' => 'datetime',
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
            'financial_report' => 'Financial Report',
            'business_analytics' => 'Business Analytics',
            default => ucfirst(str_replace('_', ' ', $this->report_type)),
        };
    }

    public function getFrequencyLabelAttribute(): string
    {
        return ucfirst($this->frequency);
    }

    public function getDeliveryMethodLabelAttribute(): string
    {
        return match($this->delivery_method) {
            'email' => 'Email',
            'dashboard' => 'Dashboard',
            'pdf' => 'PDF',
            'excel' => 'Excel',
            'api' => 'API',
            default => strtoupper($this->delivery_method),
        };
    }

    public function getDayOfWeekLabelAttribute(): string
    {
        return ucfirst($this->day_of_week);
    }
}
