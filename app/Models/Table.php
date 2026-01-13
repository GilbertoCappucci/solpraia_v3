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
        'admin_id',
        'name',
        'number',
        'status',
    ];

    public function checks()
    {
        return $this->hasMany(Check::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function checkLast()
    {
        return $this->hasOne(Check::class)->latestOfMany();
    }
}
