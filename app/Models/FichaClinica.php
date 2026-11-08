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
        'veterinario_id',
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

    /**
     * Veterinario responsable de la consulta.
     * Se llama "veterinarioResponsable" porque la columna de texto
     * "veterinario" (nombre guardado al momento de la consulta) ya existe
     * y Eloquent no permite que una relación y una columna tengan el mismo nombre.
     */
    public function veterinarioResponsable()
    {
        return $this->belongsTo(Veterinario::class, 'veterinario_id');
    }

    /** Estudiantes que participaron de la consulta (M:N con fecha_atencion). */
    public function estudiantes()
    {
        return $this->belongsToMany(Estudiante::class, 'estudiante_ficha_clinica')
            ->withPivot('fecha_atencion')
            ->withTimestamps();
    }
}
