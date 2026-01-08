<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    /** @use HasFactory<\Database\Factories\MenuFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'admin_id',
        'menu_id',
        'name',
        'active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }
}
