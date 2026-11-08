<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Veterinario extends Model
{
    protected $fillable = [
        'user_id',
        'dni',
        'nombre',
        'catedra',
    ];

    /** Usuario con el que ingresa al sistema. */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Relación "atiende" del DER. */
    public function propietarios()
    {
        return $this->hasMany(Propietario::class);
    }

    /** Relación "a cargo" del DER. */
    public function estudiantes()
    {
        return $this->hasMany(Estudiante::class);
    }

    /** Consultas en las que fue el veterinario responsable. */
    public function fichasClinicas()
    {
        return $this->hasMany(FichaClinica::class);
    }

    /**
     * Solo los que pueden aparecer en los selectores: los que no tienen usuario
     * o cuyo usuario está activo (no dado de baja).
     */
    public function scopeActivos($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('user_id')->orWhereHas('user', fn ($u) => $u->where('activo', true));
        });
    }
}
