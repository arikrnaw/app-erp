<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'transaction_type',
        'reference_type',
        'reference_id',
        'tax_rate_id',
        'taxable_amount',
        'tax_amount',
        'transaction_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'taxable_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFiled($query)
    {
        return $query->where('status', 'filed');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
