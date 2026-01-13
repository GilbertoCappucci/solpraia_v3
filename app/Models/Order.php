<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'admin_id',
        'check_id',
        'product_id',
        'customer_account_id',
        'is_paid',
        'paid_at',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
    ];

    // Atributos virtuais (buscam do histórico)
    protected $appends = ['status', 'price', 'quantity', 'status_changed_at'];

    /**
     * Retorna o status atual do pedido baseado no histórico mais recente
     */
    public function getStatusAttribute()
    {
        // Usa a relação já carregada ou retorna 'pending' se não houver histórico
        return $this->currentStatusHistory ? $this->currentStatusHistory->to_status : OrderStatusEnum::PENDING->value;
    }

    /**
     * Retorna o preço do pedido baseado no histórico mais recente
     */
    public function getPriceAttribute()
    {
        return $this->currentStatusHistory?->price ?? 0;
    }

    /**
     * Retorna a quantidade do pedido baseada no histórico mais recente
     */
    public function getQuantityAttribute()
    {
        return $this->currentStatusHistory?->quantity ?? 1;
    }

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

    public function customerAccount()
    {
        return $this->belongsTo(CustomerAccount::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function currentStatusHistory()
    {
        return $this->hasOne(OrderStatusHistory::class)->latestOfMany();
    }


    /**
     * Retorna o timestamp da última mudança de status
     */
    public function getStatusChangedAtAttribute()
    {
        return $this->currentStatusHistory?->changed_at;
    }
}
