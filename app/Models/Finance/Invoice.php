<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'invoice_number',
        'customer_id',
        'invoice_date',
        'due_date',
        'reference_type',
        'reference_id',
        'description',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'status',
        'notes',
        'posted_at',
        'journal_entry_id',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'posted_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'draft',
        'paid_amount' => 0,
    ];

    /**
     * Get the company that owns the invoice.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the customer for this invoice.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    /**
     * Get the user who created the invoice.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the journal entry for this invoice.
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(\App\Models\JournalEntry::class, 'journal_entry_id');
    }

    /**
     * Get the invoice items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(\App\Models\InvoiceItem::class);
    }

    /**
     * Get the payments for this invoice.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scope to get only draft invoices.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to get only sent invoices.
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope to get only open invoices (sent but not fully paid).
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['sent']);
    }

    /**
     * Scope to get only paid invoices.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope to get overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->whereIn('status', ['sent'])
            ->where('due_date', '<', now());
    }

    /**
     * Check if the invoice is overdue.
     */
    public function isOverdue(): bool
    {
        return in_array($this->status, ['sent']) && $this->due_date < now();
    }

    /**
     * Get the remaining amount to be paid.
     */
    public function getRemainingAmountAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Check if the invoice is fully paid.
     */
    public function isFullyPaid(): bool
    {
        return $this->paid_amount >= $this->total_amount;
    }

    /**
     * Get the payment percentage.
     */
    public function getPaymentPercentageAttribute(): float
    {
        if ($this->total_amount == 0) return 0;
        return ($this->paid_amount / $this->total_amount) * 100;
    }
}
