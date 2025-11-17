<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Device extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nickname',
        'device_token',
        'device_fingerprint',
        'ip_address',
        'active',
        'expires_at',
        'last_used_at',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'active' => 'boolean',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    /**
     * Relacionamento com usuário que criou o device
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope para devices ativos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para devices não expirados
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope para devices válidos (ativos e não expirados)
     */
    public function scopeValid($query)
    {
        return $query->active()->notExpired();
    }

    /**
     * Verificar se o device está válido
     */
    public function isValid(): bool
    {
        return $this->active && 
               ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /**
     * Verificar se o device expirou
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Atualizar último uso
     */
    public function updateUsage(?string $ipAddress = null): void
    {
        $this->forceFill([
            'last_used_at' => now(),
            'ip_address' => $ipAddress ?? $this->ip_address,
        ])->save();
    }

    /**
     * Gerar token único para o device
     */
    public static function generateToken(): string
    {
        do {
            $token = 'DEV_' . Str::upper(Str::random(32));
        } while (static::where('device_token', $token)->exists());

        return $token;
    }

    /**
     * Buscar device por token
     */
    public static function findByToken(string $token): ?self
    {
        return static::where('device_token', $token)->first();
    }

    /**
     * Validar fingerprint do device
     */
    public function validateFingerprint(string $fingerprint): bool
    {
        // Se não tem fingerprint registrado, permitir (será registrado no primeiro uso)
        if (!$this->device_fingerprint) {
            return true;
        }

        return $this->device_fingerprint === $fingerprint;
    }

    /**
     * Registrar fingerprint no primeiro uso
     */
    public function registerFingerprint(string $fingerprint): void
    {
        if (!$this->device_fingerprint) {
            $this->forceFill([
                'device_fingerprint' => $fingerprint,
            ])->save();
        }
    }
}
