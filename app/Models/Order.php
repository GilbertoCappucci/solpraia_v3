<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'admin_id',
        'check_id',
        'product_id',
        'price',
        'quantity',
        'total_price',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'total_price' => 'decimal:2',
        'status' => OrderStatusEnum::class,        
    ];

    public const BILLABLE_STATUSES = [
        OrderStatusEnum::IN_PRODUCTION,
        OrderStatusEnum::IN_TRANSIT,
        OrderStatusEnum::COMPLETED,
    ];

    /* =======================
     |  RELATIONSHIPS
     =======================*/

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function check()
    {
        return $this->belongsTo(Check::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /* =======================
     |  SCOPES
     =======================*/

    /* =======================
     |  HELPERS
    =======================*/

    public function isBillable(): bool
    {
        return in_array($this->status, self::BILLABLE_STATUSES, true);
    }

    public function paymentOrders()
    {
        return $this->belongsToMany(
            PaymentOrder::class,
            'payment_order_items'
        )->withPivot('amount');
    }

    /**
     * Valor total já pago para esta order
     */
    public function paidAmount(): float
    {
        return (float) $this->paymentOrders()
            ->sum('payment_order_items.amount');
    }

    /**
     * Verifica se a order está totalmente paga
     */
    public function isPaid(): bool
    {
        return $this->paidAmount() >= (float) $this->total_price;
    }    

}
