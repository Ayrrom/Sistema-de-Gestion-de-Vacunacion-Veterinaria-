<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Propietario;
use App\Models\FichaClinica;

class Mascota extends Model
{
    protected $fillable = [
        'nombre',
        'especie',
        'raza',
        'sexo',
        'peso',
        'edad',
        'tipo_pelaje',
        'fecha_nacimiento',
        'castrado',
        'color',
        'procedencia',
        'senas_particulares',
        'propietario_dni',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'castrado' => 'boolean',
    ];

    public function propietario()
    {
        return $this->belongsTo(Propietario::class, 'propietario_dni', 'dni');
    }

    public function fichasClinicas()
    {
        return $this->hasMany(FichaClinica::class);
    }
}
