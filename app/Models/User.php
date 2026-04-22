<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'activo',
        'failed_login_attempts',
        'locked_until',
        'two_factor_code',
        'two_factor_expires_at',
        'two_factor_attempts',
        'two_factor_verified',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_code',
    ];

    protected $appends = [
        'rol_label',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
            'locked_until' => 'datetime',
            'two_factor_expires_at' => 'datetime',
            'two_factor_verified' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function getRolLabelAttribute(): string
    {
        return config("roles.definitions.{$this->rol}.label", ucfirst((string) $this->rol));
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
