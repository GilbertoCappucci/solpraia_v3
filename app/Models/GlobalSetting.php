<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GlobalSetting extends Model
{
    /** @use HasFactory<\Database\Factories\GlobalSettingFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'pix_key',
        'pix_key_type',
        'pix_name',
        'pix_city',
        'time_limit_pending',
        'time_limit_in_production',
        'time_limit_in_transit',
        'time_limit_closed',
        'time_limit_releasing',
    ];

    /**
     * Relacionamento com o usuário (admin)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
