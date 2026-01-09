<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Check extends Model
{
    /** @use HasFactory<\Database\Factories\CheckFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'admin_id',
        'table_id',
        'total',
        'status',
        'opened_at',
        'closed_at',
    ];

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /* =======================
     |  SCOPES
     =======================*/

    /* =======================
     |  HELPERS
    =======================*/

    public function billableOrders()
    {
        return $this->orders()
            ->whereIn('status', Order::BILLABLE_STATUSES);
    }

    public function totalAmount(): float
    {
        return (float) $this->billableOrders()->sum('total_price');
    }

    public function paidAmount(): float
    {
        return (float) $this->billableOrders()
            ->get()
            ->sum(fn ($order) => $order->paidAmount());
    }
    
    public function openAmount(): float
    {
        return max(
            0,
            $this->totalAmount() - $this->paidAmount()
        );
    }

    public function isPaid(): bool
    {
        return $this->openAmount() === 0.0;
    }
    
}
