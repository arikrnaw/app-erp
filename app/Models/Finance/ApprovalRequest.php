<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'workflow_id',
        'requestor_id',
        'approver_id',
        'approvable_type',
        'approvable_id',
        'amount',
        'description',
        'priority',
        'due_date',
        'status',
        'current_level',
        'approved_at',
        'rejected_at',
        'completed_at',
        'escalated_at',
        'requestor_comments',
        'approver_comments',
        'escalation_reason',
        'delegated_to',
        'delegated_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
        'escalated_at' => 'datetime',
        'delegated_at' => 'datetime',
        'current_level' => 'integer',
    ];

    protected $attributes = [
        'status' => 'pending',
        'priority' => 'medium',
        'current_level' => 1,
    ];

    // Relationships
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }

    public function requestor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'requestor_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approver_id');
    }

    public function delegatedTo(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'delegated_to');
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeEscalated($query)
    {
        return $query->where('status', 'escalated');
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByType($query, $type)
    {
        return $query->whereHas('workflow', function ($q) use ($type) {
            $q->where('type', $type);
        });
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->where('status', 'pending');
    }

    public function scopeByRequestor($query, $requestorId)
    {
        return $query->where('requestor_id', $requestorId);
    }

    public function scopeByApprover($query, $approverId)
    {
        return $query->where('approver_id', $approverId);
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isEscalated()
    {
        return $this->status === 'escalated';
    }

    public function isOverdue()
    {
        return $this->due_date && $this->due_date->isPast() && $this->isPending();
    }

    public function canApprove($userId)
    {
        return $this->approver_id === $userId && $this->isPending();
    }

    public function canDelegate($userId)
    {
        return $this->approver_id === $userId && $this->isPending();
    }

    public function isHighPriority()
    {
        return in_array($this->priority, ['high', 'urgent']);
    }

    public function isUrgent()
    {
        return $this->priority === 'urgent';
    }

    public function getDaysOverdue()
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return $this->due_date->diffInDays(now());
    }

    public function getStatusBadgeVariant()
    {
        return match($this->status) {
            'pending' => 'secondary',
            'approved' => 'default',
            'rejected' => 'destructive',
            'completed' => 'default',
            'escalated' => 'warning',
            default => 'secondary',
        };
    }

    public function getPriorityBadgeVariant()
    {
        return match($this->priority) {
            'low' => 'secondary',
            'medium' => 'default',
            'high' => 'warning',
            'urgent' => 'destructive',
            default => 'default',
        };
    }

    public function getFormattedAmount()
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedDueDate()
    {
        return $this->due_date ? $this->due_date->format('M d, Y') : 'No due date';
    }

    public function getFormattedCreatedAt()
    {
        return $this->created_at->format('M d, Y H:i');
    }

    public function getFormattedUpdatedAt()
    {
        return $this->updated_at->format('M d, Y H:i');
    }

    // Events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $request->requestor_id = \Illuminate\Support\Facades\Auth::id();
            }
        });

        static::updating(function ($request) {
            // Auto-escalate if overdue
            if ($request->isPending() && $request->isOverdue()) {
                $request->status = 'escalated';
                $request->escalated_at = now();
                $request->escalation_reason = 'Auto-escalated due to overdue';
            }
        });
    }
}
