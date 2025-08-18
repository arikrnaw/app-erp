<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class ServiceTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_code',
        'title',
        'description',
        'ticket_type',
        'priority',
        'status',
        'source',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_company',
        'issue_details',
        'steps_to_reproduce',
        'error_messages',
        'affected_product',
        'affected_version',
        'attachments',
        'assigned_to',
        'escalated_to',
        'escalation_date',
        'escalation_reason',
        'escalation_level',
        'resolution_notes',
        'solution_provided',
        'internal_notes',
        'first_response_date',
        'resolution_date',
        'response_time_hours',
        'resolution_time_hours',
        'sla_due_date',
        'sla_breached',
        'sla_breach_hours',
        'satisfaction_rating',
        'customer_feedback',
        'customer_responded',
        'tags',
        'category_id',
        'created_by',
        'company_id',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'escalation_date' => 'date',
        'first_response_date' => 'date',
        'resolution_date' => 'date',
        'sla_due_date' => 'date',
        'sla_breached' => 'boolean',
        'customer_responded' => 'boolean',
        'tags' => 'array',
        'attachments' => 'array',
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

    public function escalatedTo()
    {
        return $this->belongsTo(User::class, 'escalated_to');
    }

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class);
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
    public function getTicketTypeLabelAttribute()
    {
        $labels = [
            'technical_support' => 'Technical Support',
            'billing_support' => 'Billing Support',
            'product_support' => 'Product Support',
            'account_support' => 'Account Support',
            'feature_request' => 'Feature Request',
            'bug_report' => 'Bug Report',
            'general_inquiry' => 'General Inquiry',
            'escalation' => 'Escalation',
            'other' => 'Other',
        ];

        return $labels[$this->ticket_type] ?? $this->ticket_type;
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
            'assigned' => 'Assigned',
            'in_progress' => 'In Progress',
            'waiting_customer' => 'Waiting Customer',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getSourceLabelAttribute()
    {
        $labels = [
            'phone' => 'Phone',
            'email' => 'Email',
            'chat' => 'Chat',
            'web_form' => 'Web Form',
            'social_media' => 'Social Media',
            'in_person' => 'In Person',
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
        return $query->whereIn('status', ['open', 'assigned', 'in_progress']);
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
        return $query->where('ticket_type', $type);
    }

    public function scopeSlaBreached($query)
    {
        return $query->where('sla_breached', true);
    }

    public function scopeEscalated($query)
    {
        return $query->whereNotNull('escalated_to');
    }

    // Boot method to generate ticket code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_code)) {
                $ticket->ticket_code = 'TKT-' . date('Y') . '-' . str_pad(static::whereYear('created_at', date('Y'))->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
