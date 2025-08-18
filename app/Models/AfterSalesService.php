<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class AfterSalesService extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_code',
        'title',
        'description',
        'service_type',
        'priority',
        'status',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'product_name',
        'product_model',
        'serial_number',
        'order_number',
        'purchase_date',
        'warranty_expiry',
        'requested_date',
        'scheduled_date',
        'scheduled_time',
        'service_location',
        'special_instructions',
        'service_notes',
        'work_performed',
        'parts_used',
        'labor_cost',
        'parts_cost',
        'total_cost',
        'is_warranty_covered',
        'completion_date',
        'service_duration_hours',
        'service_quality',
        'customer_signature',
        'technician_signature',
        'follow_up_date',
        'follow_up_notes',
        'customer_satisfaction',
        'customer_feedback',
        'assigned_technician',
        'created_by',
        'company_id',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'requested_date' => 'date',
        'scheduled_date' => 'date',
        'scheduled_time' => 'datetime',
        'completion_date' => 'date',
        'follow_up_date' => 'date',
        'labor_cost' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'is_warranty_covered' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedTechnician()
    {
        return $this->belongsTo(User::class, 'assigned_technician');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Accessors
    public function getServiceTypeLabelAttribute()
    {
        $labels = [
            'warranty_service' => 'Warranty Service',
            'repair_service' => 'Repair Service',
            'maintenance_service' => 'Maintenance Service',
            'installation_service' => 'Installation Service',
            'training_service' => 'Training Service',
            'consultation_service' => 'Consultation Service',
            'replacement_service' => 'Replacement Service',
            'upgrade_service' => 'Upgrade Service',
            'other' => 'Other',
        ];

        return $labels[$this->service_type] ?? $this->service_type;
    }

    public function getPriorityLabelAttribute()
    {
        $labels = [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'critical' => 'Critical',
        ];

        return $labels[$this->priority] ?? $this->priority;
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'scheduled' => 'Scheduled',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'on_hold' => 'On Hold',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getServiceQualityLabelAttribute()
    {
        $labels = [
            'poor' => 'Poor',
            'fair' => 'Fair',
            'good' => 'Good',
            'excellent' => 'Excellent',
        ];

        return $labels[$this->service_quality] ?? $this->service_quality;
    }

    public function getCustomerSatisfactionLabelAttribute()
    {
        $labels = [
            'very_dissatisfied' => 'Very Dissatisfied',
            'dissatisfied' => 'Dissatisfied',
            'neutral' => 'Neutral',
            'satisfied' => 'Satisfied',
            'very_satisfied' => 'Very Satisfied',
        ];

        return $labels[$this->customer_satisfaction] ?? $this->customer_satisfaction;
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'scheduled']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('service_type', $type);
    }

    public function scopeWarrantyCovered($query)
    {
        return $query->where('is_warranty_covered', true);
    }

    // Boot method to generate service code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($service) {
            if (empty($service->service_code)) {
                $service->service_code = 'ASS-' . date('Y') . '-' . str_pad(static::whereYear('created_at', date('Y'))->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
