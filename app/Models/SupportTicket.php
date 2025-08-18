<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'ticket_number',
        'customer_id',
        'subject',
        'description',
        'priority',
        'status',
        'category',
        'assigned_to',
        'created_by',
        'resolved_by',
        'resolved_at',
        'due_date',
        'estimated_resolution_time',
        'actual_resolution_time',
        'customer_satisfaction_rating',
        'internal_notes',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'due_date' => 'datetime',
        'estimated_resolution_time' => 'integer',
        'actual_resolution_time' => 'integer',
        'customer_satisfaction_rating' => 'integer',
        'priority' => 'string',
        'status' => 'string',
        'category' => 'string',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function resolvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(TicketResponse::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }
}
