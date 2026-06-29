<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Mascota;

class FichaClinica extends Model
{
    protected $fillable = [
        'fecha_consulta',
        'motivo_consulta',
        'anamnesis',
        'fc',
        'fr',
        'temperatura',
        'pulso',
        'tllc',
        'relacion_entorno',
        'examen_fisico',
        'examen_fisiologico',
        'hidratacion',
        'esquema_completo',
        'vacunas_aplicadas',
        'desparasitacion',
        'mascota_id',
    ];

    protected $casts = [
        'fecha_consulta' => 'date',
        'esquema_completo' => 'boolean',
    ];

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }
}
