<?php

namespace App\Models;

use App\Enums\RoleEnum;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, SoftDeletes, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'user_id',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Determine if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === RoleEnum::ADMIN->value;
    }

    /**
     * Determine if the user is a device.
     */
    public function isDevice(): bool
    {
        return $this->role === RoleEnum::DEVICE->value;
    }

    /**
     * Determine if the user can access the dashboard.
     */
    public function canAccessDashboard(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Determine if the user can access orders.
     */
    public function canAccessOrders(): bool
    {
        return $this->isAdmin() || $this->isDevice();
    }

    /**
     * Determine if the user can access the Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Apenas usuários com role ADMIN podem acessar o painel admin
        if ($panel->getId() === 'admin') {
            return $this->role === RoleEnum::ADMIN->value;
        }

        return false;
    }

    public function menus()
    {
        return $this->hasMany(Menu::class, 'user_id');
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'user_id');
    }
    
    public function products()
    {
        return $this->hasManyThrough(Product::class, Category::class);
    }

    public function devices()
    {
        return $this->hasMany(User::class, 'user_id');
    }

    public function tables()
    {
        return $this->hasMany(Table::class, 'user_id');
    }

    /**
     * Gerar token de device para employee (chamado pelo Admin)
     * 
     * @param string $deviceName Nome descritivo do device
     * @param int $expirationDays Dias até expiração (padrão: 365 = 1 ano)
     * @return string Token gerado (plaintext)
     */
    public function generateDeviceToken(string $deviceName = 'Device', int $expirationDays = 365): string
    {
        $token = $this->createToken(
            name: $deviceName,
            abilities: ['employee-access'],
            expiresAt: now()->addDays($expirationDays)
        );

        return $token->plainTextToken;
    }

    /**
     * Obter todos os tokens de device do usuário
     */
    public function deviceTokens()
    {
        return $this->tokens()
            ->whereNotNull('device_fingerprint')
            ->orWhere('name', 'like', '%Device%')
            ->orWhereJsonContains('abilities', 'employee-access');
    }

    /**
     * Revogar token específico
     */
    public function revokeDeviceToken(int $tokenId): bool
    {
        return $this->tokens()->where('id', $tokenId)->delete() > 0;
    }

    /**
     * Revogar todos os tokens de device
     */
    public function revokeAllDeviceTokens(): int
    {
        return $this->tokens()
            ->where(function($query) {
                $query->whereNotNull('device_fingerprint')
                    ->orWhere('name', 'like', '%Device%')
                    ->orWhereJsonContains('abilities', 'employee-access');
            })
            ->delete();
    }
}
