<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalLevel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'workflow_id',
        'level',
        'approver_role',
        'approver_id',
        'escalation_hours',
        'is_active',
        'can_delegate',
        'auto_approve_if_same_user',
    ];

    protected $casts = [
        'level' => 'integer',
        'escalation_hours' => 'integer',
        'is_active' => 'boolean',
        'can_delegate' => 'boolean',
        'auto_approve_if_same_user' => 'boolean',
    ];

    protected $attributes = [
        'escalation_hours' => 24,
        'is_active' => true,
        'can_delegate' => false,
        'auto_approve_if_same_user' => false,
    ];

    // Relationships
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approver_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('approver_role', $role);
    }

    // Helper methods
    public function isLastLevel()
    {
        return $this->level === $this->workflow->getMaxLevel();
    }

    public function isFirstLevel()
    {
        return $this->level === 1;
    }

    public function getNextLevel()
    {
        return $this->workflow->levels()
            ->where('level', '>', $this->level)
            ->orderBy('level')
            ->first();
    }

    public function getPreviousLevel()
    {
        return $this->workflow->levels()
            ->where('level', '<', $this->level)
            ->orderBy('level', 'desc')
            ->first();
    }

    public function canEscalate()
    {
        return $this->escalation_hours > 0;
    }

    public function shouldEscalate($requestCreatedAt)
    {
        if (!$this->canEscalate()) {
            return false;
        }

        $escalationTime = $requestCreatedAt->addHours($this->escalation_hours);
        return now()->isAfter($escalationTime);
    }

    public function getEscalationTime($requestCreatedAt)
    {
        return $requestCreatedAt->addHours($this->escalation_hours);
    }

    // Validation rules
    public static function getValidationRules()
    {
        return [
            'workflow_id' => 'required|exists:approval_workflows,id',
            'level' => 'required|integer|min:1',
            'approver_role' => 'required|string|max:100',
            'approver_id' => 'nullable|exists:users,id',
            'escalation_hours' => 'nullable|integer|min:0|max:168', // Max 1 week
            'is_active' => 'boolean',
            'can_delegate' => 'boolean',
            'auto_approve_if_same_user' => 'boolean',
        ];
    }
}
