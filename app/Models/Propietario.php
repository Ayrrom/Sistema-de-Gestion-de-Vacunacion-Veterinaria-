<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Propietario extends Model
{
    protected $primaryKey = 'dni';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'dni',
        'nombre',
        'apellido',
        'telefono',
        'calle',
        'altura',
        'localidad',
        'veterinario_id',
    ];

    public function mascotas()
    {
        return $this->hasMany(Mascota::class, 'propietario_dni', 'dni');
    }

    /** Veterinario que lo atiende (relación "atiende" del DER). */
    public function veterinario()
    {
        return $this->belongsTo(Veterinario::class);
    }
}
