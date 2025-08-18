<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class CustomerComplaint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'complaint_code',
        'title',
        'description',
        'complaint_type',
        'priority',
        'status',
        'source',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'product_name',
        'order_number',
        'incident_date',
        'expected_resolution',
        'resolution_notes',
        'action_taken',
        'resolution_date',
        'resolution_time_hours',
        'satisfaction_rating',
        'customer_feedback',
        'assigned_to',
        'created_by',
        'company_id',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'resolution_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
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
    public function getComplaintTypeLabelAttribute()
    {
        $labels = [
            'product_issue' => 'Product Issue',
            'service_issue' => 'Service Issue',
            'billing_issue' => 'Billing Issue',
            'delivery_issue' => 'Delivery Issue',
            'technical_issue' => 'Technical Issue',
            'quality_issue' => 'Quality Issue',
            'communication_issue' => 'Communication Issue',
            'other' => 'Other',
        ];

        return $labels[$this->complaint_type] ?? $this->complaint_type;
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
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
            'escalated' => 'Escalated',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getSourceLabelAttribute()
    {
        $labels = [
            'phone' => 'Phone',
            'email' => 'Email',
            'chat' => 'Chat',
            'social_media' => 'Social Media',
            'in_person' => 'In Person',
            'website' => 'Website',
        ];

        return $labels[$this->source] ?? $this->source;
    }

    public function getSatisfactionRatingLabelAttribute()
    {
        $labels = [
            'very_dissatisfied' => 'Very Dissatisfied',
            'dissatisfied' => 'Dissatisfied',
            'neutral' => 'Neutral',
            'satisfied' => 'Satisfied',
            'very_satisfied' => 'Very Satisfied',
        ];

        return $labels[$this->satisfaction_rating] ?? $this->satisfaction_rating;
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    public function scopeResolved($query)
    {
        return $query->whereIn('status', ['resolved', 'closed']);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('complaint_type', $type);
    }

    // Boot method to generate complaint code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($complaint) {
            if (empty($complaint->complaint_code)) {
                $complaint->complaint_code = 'COMP-' . date('Y') . '-' . str_pad(static::whereYear('created_at', date('Y'))->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
