<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prospect extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'phone',
        'company_name',
        'position',
        'industry',
        'source',
        'status',
        'priority',
        'estimated_value',
        'notes',
        'assigned_to',
        'next_follow_up_date',
        'last_contact_date',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'next_follow_up_date' => 'datetime',
        'last_contact_date' => 'datetime',
        'status' => 'string',
        'priority' => 'string',
        'source' => 'string',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}
