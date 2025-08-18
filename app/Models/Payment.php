<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'payment_number',
        'customer_id',
        'invoice_id',
        'payment_date',
        'payment_method',
        'reference_number',
        'amount',
        'notes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the company that owns the payment.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the customer for this payment.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the invoice for this payment.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the user who created the payment.
     */
    public function created_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate payment number.
     */
    public static function generatePaymentNumber(int $companyId): string
    {
        $lastPayment = self::where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->first();

        $lastNumber = $lastPayment ? (int) substr($lastPayment->payment_number, -6) : 0;
        $newNumber = $lastNumber + 1;

        return 'PAY-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
