<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'enabled',
        'note',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function customerAccounts()
    {
        return $this->hasMany(CustomerAccount::class);
    }
}
