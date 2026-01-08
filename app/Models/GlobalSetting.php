<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

class GlobalSetting extends Model
{
    /** @use HasFactory<\Database\Factories\GlobalSettingFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'admin_id',
        'menu_id',
        'pix_enabled',
        'pix_key',
        'pix_key_type',
        'pix_name',
        'pix_city',
        'time_limit_pending',
        'time_limit_in_production',
        'time_limit_in_transit',
        'time_limit_closed',
        'time_limit_releasing',
        'polling_interval',
    ];

    /**
     * Garante que o menu_id seja um inteiro ou nulo.
     */
    protected function menuId(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => filter_var($value, FILTER_VALIDATE_INT) !== false ? (int) $value : null,
        );
    }

    /**
     * Relacionamento com o usuário (admin)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com o menu ativo
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
