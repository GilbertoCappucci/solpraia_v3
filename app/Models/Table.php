<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Table extends Model
{
    /** @use HasFactory<\Database\Factories\TableFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'number',
        'active',
    ];

    public function checks()
    {
        return $this->hasMany(Check::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
