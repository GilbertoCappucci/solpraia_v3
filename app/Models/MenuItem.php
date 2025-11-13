<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    /** @use HasFactory<\Database\Factories\MenuItemFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'menu_id',
        'product_id',
        'active',
    ];
}
