<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'workflow_id',
        'name',
        'description',
        'rule_type',
        'rule_conditions',
        'is_active',
        'priority',
        'auto_trigger',
        'notification_template',
        'escalation_rules',
    ];

    protected $casts = [
        'rule_conditions' => 'array',
        'escalation_rules' => 'array',
        'is_active' => 'boolean',
        'auto_trigger' => 'boolean',
        'priority' => 'integer',
    ];

    protected $attributes = [
        'is_active' => true,
        'auto_trigger' => false,
        'priority' => 1,
    ];

    // Relationships
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('rule_type', $type);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeAutoTrigger($query)
    {
        return $query->where('auto_trigger', true);
    }

    // Helper methods
    public function evaluateConditions($data)
    {
        if (!$this->rule_conditions || !is_array($this->rule_conditions)) {
            return true;
        }

        foreach ($this->rule_conditions as $condition) {
            if (!$this->evaluateCondition($condition, $data)) {
                return false;
            }
        }

        return true;
    }

    private function evaluateCondition($condition, $data)
    {
        $field = $condition['field'] ?? '';
        $operator = $condition['operator'] ?? '=';
        $value = $condition['value'] ?? null;

        if (!isset($data[$field])) {
            return false;
        }

        $fieldValue = $data[$field];

        return match($operator) {
            '=' => $fieldValue == $value,
            '!=' => $fieldValue != $value,
            '>' => $fieldValue > $value,
            '>=' => $fieldValue >= $value,
            '<' => $fieldValue < $value,
            '<=' => $fieldValue <= $value,
            'in' => in_array($fieldValue, (array) $value),
            'not_in' => !in_array($fieldValue, (array) $value),
            'contains' => str_contains($fieldValue, $value),
            'starts_with' => str_starts_with($fieldValue, $value),
            'ends_with' => str_ends_with($fieldValue, $value),
            'regex' => preg_match($value, $fieldValue),
            default => false,
        };
    }

    public function shouldAutoTrigger($data)
    {
        return $this->auto_trigger && $this->evaluateConditions($data);
    }

    public function getEscalationTime($level)
    {
        if (!$this->escalation_rules || !isset($this->escalation_rules[$level])) {
            return 24; // Default 24 hours
        }

        return $this->escalation_rules[$level]['hours'] ?? 24;
    }

    public function getEscalationAction($level)
    {
        if (!$this->escalation_rules || !isset($this->escalation_rules[$level])) {
            return 'notify_supervisor';
        }

        return $this->escalation_rules[$level]['action'] ?? 'notify_supervisor';
    }

    public function getNotificationTemplate()
    {
        return $this->notification_template ?? 'default';
    }

    public function isHighPriority()
    {
        return $this->priority <= 3;
    }

    public function isCritical()
    {
        return $this->priority === 1;
    }

    // Validation rules
    public static function getValidationRules()
    {
        return [
            'workflow_id' => 'required|exists:approval_workflows,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rule_type' => 'required|string|in:amount,department,user_role,time_based,custom',
            'rule_conditions' => 'nullable|array',
            'rule_conditions.*.field' => 'required_with:rule_conditions|string',
            'rule_conditions.*.operator' => 'required_with:rule_conditions|string',
            'rule_conditions.*.value' => 'required_with:rule_conditions',
            'is_active' => 'boolean',
            'priority' => 'integer|min:1|max:10',
            'auto_trigger' => 'boolean',
            'notification_template' => 'nullable|string|max:255',
            'escalation_rules' => 'nullable|array',
        ];
    }

    // Rule types
    public static function getRuleTypes()
    {
        return [
            'amount' => 'Amount-based',
            'department' => 'Department-based',
            'user_role' => 'User Role-based',
            'time_based' => 'Time-based',
            'custom' => 'Custom Logic',
        ];
    }

    // Operators
    public static function getOperators()
    {
        return [
            '=' => 'Equals',
            '!=' => 'Not Equals',
            '>' => 'Greater Than',
            '>=' => 'Greater Than or Equal',
            '<' => 'Less Than',
            '<=' => 'Less Than or Equal',
            'in' => 'In List',
            'not_in' => 'Not In List',
            'contains' => 'Contains',
            'starts_with' => 'Starts With',
            'ends_with' => 'Ends With',
            'regex' => 'Regular Expression',
        ];
    }

    // Escalation actions
    public static function getEscalationActions()
    {
        return [
            'notify_supervisor' => 'Notify Supervisor',
            'auto_escalate' => 'Auto Escalate',
            'send_reminder' => 'Send Reminder',
            'change_priority' => 'Change Priority',
            'reassign' => 'Reassign Request',
        ];
    }
}
