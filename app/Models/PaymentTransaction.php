<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentTransaction extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentTransactionFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_id',
        'status',
        'amount',
        'payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payload' => 'array',
    ];

    /* =======================
     |  RELATIONSHIPS
     =======================*/

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /* =======================
     |  HELPERS
     =======================*/

    public function isSuccessful(): bool
    {
        return in_array($this->status, ['authorized', 'captured']);
    }
}
