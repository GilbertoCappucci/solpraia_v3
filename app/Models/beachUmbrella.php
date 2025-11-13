<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BeachUmbrella extends Model
{
    /** @use HasFactory<\Database\Factories\BeachUmbrellaFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'number',
        'active',
    ];
}
