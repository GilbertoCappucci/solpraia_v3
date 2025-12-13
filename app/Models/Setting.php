<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    /** @use HasFactory<\Database\Factories\SettingFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'time_limit_pending',
        'time_limit_in_production',
        'time_limit_in_transit',
        'time_limit_closed',
        'time_limit_releasing',
        'table_filter_mode',
        'table_filter_table',
        'table_filter_check',
        'table_filter_order',
        'table_filter_departament',
        'pix_key',
        'pix_key_type',
        'pix_name',
        'pix_city',
    ];

    protected $casts = [
        'table_filter_table' => 'array',
        'table_filter_check' => 'array',
        'table_filter_order' => 'array',
        'table_filter_departament' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
