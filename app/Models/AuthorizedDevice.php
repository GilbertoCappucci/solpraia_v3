<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthorizedDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_name',
        'device_token',
        'device_fingerprint',
        'registered_ip',
        'user_agent',
        'device_info',
        'expires_at',
        'max_sessions',
        'ip_restriction',
        'allowed_ips',
        'last_used_at',
        'last_ip',
        'usage_count',
        'is_active',
        'created_by',
        'updated_by',
        'notes'
    ];

    protected $casts = [
        'device_info' => 'array',
        'allowed_ips' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'ip_restriction' => 'boolean',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
        'max_sessions' => 'integer'
    ];

    // Relacionamentos
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeValid($query)
    {
        return $query->active()->notExpired();
    }

    // Métodos auxiliares
    public static function generateToken(): string
    {
        do {
            $token = 'DEV_' . Str::upper(Str::random(32));
        } while (self::where('device_token', $token)->exists());

        return $token;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isValidForIp(string $ip): bool
    {
        if (!$this->ip_restriction) {
            return true;
        }

        return in_array($ip, $this->allowed_ips ?? []);
    }

    public function updateUsage(string $ip): void
    {
        $this->update([
            'last_used_at' => now(),
            'last_ip' => $ip,
            'usage_count' => $this->usage_count + 1
        ]);
    }

    public function getRemainingDaysAttribute(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }

        return max(0, now()->diffInDays($this->expires_at, false));
    }

    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inativo';
        }

        if ($this->isExpired()) {
            return 'Expirado';
        }

        return 'Ativo';
    }
}