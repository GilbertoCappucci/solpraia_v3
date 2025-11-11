<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    /** @use HasFactory<\Database\Factories\ProductCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'parent_id',
        'name',
        'description',
        'active',
    ];

    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
