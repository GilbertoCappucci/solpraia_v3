<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'check_id',
        'product_id',
        'quantity',
    ];

    // Atributo virtual para status (busca do histórico)
    protected $appends = ['status'];

    /**
     * Retorna o status atual do pedido baseado no histórico mais recente
     */
    public function getStatusAttribute()
    {
        // Usa a relação já carregada ou retorna 'pending' se não houver histórico
        return $this->currentStatusHistory ? $this->currentStatusHistory->to_status : 'pending';
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

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function currentStatusHistory()
    {
        return $this->hasOne(OrderStatusHistory::class)
            ->latest('changed_at');
    }

    /**
     * Retorna o timestamp da última mudança de status
     */
    public function getStatusChangedAtAttribute()
    {
        return $this->currentStatusHistory?->changed_at;
    }
}
