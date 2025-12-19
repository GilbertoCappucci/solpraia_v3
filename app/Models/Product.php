<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'production_local',
        'name',
        'description',
        'price',
        'active',
        'favorite',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function stock()
    {
        return $this->hasOne(Stock::class);
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }
}
