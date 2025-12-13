<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPreference extends Model
{
    /** @use HasFactory<\Database\Factories\UserPreferenceFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'table_filter_table',
        'table_filter_check',
        'table_filter_order',
        'table_filter_departament',
    ];

    protected $casts = [
        'table_filter_table' => 'array',
        'table_filter_check' => 'array',
        'table_filter_order' => 'array',
        'table_filter_departament' => 'array',
    ];

    /**
     * Relacionamento com o usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
