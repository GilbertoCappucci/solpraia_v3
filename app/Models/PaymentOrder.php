<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\PaymentStatusEnum;

class PaymentOrder extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentOrderFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'admin_id',
        'check_id',
        'total_amount',
        'currency',
        'status',
        'payment_method',
        'paid_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'status' => PaymentStatusEnum::class,
    ];

    /* =======================
     |  RELATIONSHIPS
     =======================*/

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function orders(): BelongsToMany    {
        return $this->belongsToMany(Order::class)
            ->withPivot('amount_paid')
            ->withTimestamps();
    }

    /* =======================
     |  SCOPES
     =======================*/

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /* =======================
     |  DOMAIN METHODS
     =======================*/

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update([
            'status' => 'failed',
        ]);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

}
