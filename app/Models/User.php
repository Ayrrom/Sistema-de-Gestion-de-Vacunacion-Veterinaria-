<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROL_ADMIN = 'admin';
    public const ROL_VETERINARIO = 'veterinario';
    public const ROL_ESTUDIANTE = 'estudiante';

    /** Roles válidos con su nombre para mostrar. */
    public const ROLES = [
        self::ROL_ADMIN => 'Administrador',
        self::ROL_VETERINARIO => 'Veterinario',
        self::ROL_ESTUDIANTE => 'Estudiante',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'dni',
        'rol',
        'password',
        'debe_cambiar_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
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
            'password' => 'hashed',
            'debe_cambiar_password' => 'boolean',
        ];
    }

    /** Perfil de veterinario (si el usuario es veterinario). */
    public function veterinario()
    {
        return $this->hasOne(Veterinario::class);
    }

    /** Perfil de estudiante (si el usuario es estudiante). */
    public function estudiante()
    {
        return $this->hasOne(Estudiante::class);
    }

    public function tieneRol(string ...$roles): bool
    {
        return in_array($this->rol, $roles, true);
    }

    public function esAdmin(): bool
    {
        return $this->rol === self::ROL_ADMIN;
    }

    /** Admin y veterinario pueden eliminar; el estudiante no. */
    public function puedeEliminar(): bool
    {
        return $this->tieneRol(self::ROL_ADMIN, self::ROL_VETERINARIO);
    }

    public function getNombreRolAttribute(): string
    {
        return self::ROLES[$this->rol] ?? $this->rol;
    }
}
