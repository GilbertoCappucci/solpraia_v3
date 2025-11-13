<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Check extends Model
{
    /** @use HasFactory<\Database\Factories\CheckFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'table_id',
        'total',
        'status',
        'opened_at',
        'closed_at',
    ];
}
