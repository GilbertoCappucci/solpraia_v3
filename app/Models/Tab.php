<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tab extends Model
{
    /** @use HasFactory<\Database\Factories\TabFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'credit_limit',
        'notes',
        'status',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'credit_limit' => 'decimal:2',
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
