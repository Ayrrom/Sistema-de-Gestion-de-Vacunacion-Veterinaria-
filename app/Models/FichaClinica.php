<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Mascota;

class FichaClinica extends Model
{
    protected $table = 'fichas_clinicas';

    protected $fillable = [
        'fecha_consulta',
        'veterinario',
        'motivo_consulta',
        'estado',
        'esquema_completo',
        'vacunas_aplicadas',
        'fecha_desparasitacion',
        'droga_desparasitacion',
        'producto_desparasitacion',
        'fc',
        'fr',
        'temperatura',
        'pulso',
        'tllc',
        'relacion_entorno',
        'hidratacion_normal',
        'grado_deshidratacion',
        'piel_anexos',
        'ojos',
        'oidos',
        'ganglios_linfaticos',
        'conjuntivas_mucosas',
        'sistema_cardiaco',
        'sistema_respiratorio',
        'sistema_digestivo',
        'sistema_urinario',
        'sistema_nervioso',
        'sistema_locomotor',
        'condicion_corporal',
        'mascota_id',
    ];

    protected $casts = [
        'fecha_consulta' => 'date',
        'fecha_desparasitacion' => 'date',
        'esquema_completo' => 'boolean',
        'hidratacion_normal' => 'boolean',
        'condicion_corporal' => 'integer',
    ];

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }
}
