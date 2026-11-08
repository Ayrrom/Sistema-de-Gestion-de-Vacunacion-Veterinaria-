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
        'activo',
        'password',
        'debe_cambiar_password',
    ];

    /**
     * Valores por defecto al crear un usuario desde el código (los mismos que
     * pone la base), así un usuario recién creado ya figura como activo.
     */
    protected $attributes = [
        'activo' => true,
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
            'activo' => 'boolean',
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

    /** Clave de sesión que indica que el veterinario está usando la vista de estudiante. */
    public const SESION_VISTA_ESTUDIANTE = 'vista_estudiante';

    /**
     * Rol con el que el usuario está usando el sistema en este momento.
     * Un veterinario puede pasar a la vista de estudiante desde "Mi perfil"
     * (se guarda en su sesión; su rol real no cambia). Para cualquier otro
     * usuario, o cuando se consulta a otra persona, es su rol real.
     */
    public function rolActivo(): string
    {
        if ($this->rol === self::ROL_VETERINARIO
            && auth()->id() === $this->id
            && session(self::SESION_VISTA_ESTUDIANTE, false)) {
            return self::ROL_ESTUDIANTE;
        }

        return $this->rol;
    }

    /** Solo el veterinario puede cambiar a la vista de estudiante. */
    public function puedeCambiarVista(): bool
    {
        return $this->rol === self::ROL_VETERINARIO;
    }

    public function enVistaEstudiante(): bool
    {
        return $this->puedeCambiarVista() && $this->rolActivo() === self::ROL_ESTUDIANTE;
    }

    /** Todos los permisos se evalúan con el rol activo. */
    public function tieneRol(string ...$roles): bool
    {
        return in_array($this->rolActivo(), $roles, true);
    }

    public function esAdmin(): bool
    {
        return $this->rolActivo() === self::ROL_ADMIN;
    }

    public function esVeterinarioActivo(): bool
    {
        return $this->rolActivo() === self::ROL_VETERINARIO;
    }

    /** Admin y veterinario pueden eliminar; el estudiante no. */
    public function puedeEliminar(): bool
    {
        return $this->tieneRol(self::ROL_ADMIN, self::ROL_VETERINARIO);
    }

    /** Nombre del rol activo (el que se muestra en la barra superior). */
    public function getNombreRolAttribute(): string
    {
        return self::ROLES[$this->rolActivo()] ?? $this->rolActivo();
    }

    /** Nombre del rol real, sin tener en cuenta la vista de estudiante. */
    public function getNombreRolRealAttribute(): string
    {
        return self::ROLES[$this->rol] ?? $this->rol;
    }
}
