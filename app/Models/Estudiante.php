<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    protected $fillable = [
        'user_id',
        'dni',
        'nombre',
        'veterinario_id',
    ];

    /** Usuario con el que ingresa al sistema. */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Veterinario a cargo (relación "a cargo" del DER). */
    public function veterinario()
    {
        return $this->belongsTo(Veterinario::class);
    }

    /** Fichas clínicas en las que participó (M:N con fecha_atencion). */
    public function fichasClinicas()
    {
        return $this->belongsToMany(FichaClinica::class, 'estudiante_ficha_clinica')
            ->withPivot('fecha_atencion')
            ->withTimestamps();
    }
}
