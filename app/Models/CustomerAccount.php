<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerAccount extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerAccountFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'credit_limit',
        'total_balance',
        'note',
        'enabled',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'credit_limit' => 'decimal:2',
        'total_balance' => 'decimal:2',
        'enabled' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
