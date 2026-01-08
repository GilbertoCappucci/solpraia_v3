<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentOrder extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentOrderFactory> */
    use HasFactory, SoftDeletes;
}
