<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = $request->query('buscar');

        // El orden y la paginación los resuelve la base de datos: antes se
        // traían todas las mascotas con todas sus fichas y se ordenaban en PHP.
        $mascotas = Mascota::with(['propietario', 'ultimaFicha'])
            ->withMax('fichasClinicas as fecha_ultima_visita', 'fecha_consulta')
            ->when($busqueda, function ($query) use ($busqueda) {
                $query->where('nombre', 'like', "%{$busqueda}%");
            })
            // Las que tienen consultas van primero, ordenadas por fecha más reciente.
            ->orderByRaw('fecha_ultima_visita IS NULL')
            ->orderByDesc('fecha_ultima_visita')
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString()
            ->through(function ($mascota) {
                $ultimaFicha = $mascota->ultimaFicha;

                $mascota->ultima_ficha = $ultimaFicha;
                $mascota->estado_actual = $ultimaFicha->estado ?? 'Sin consultas';
                $mascota->ultima_visita = $ultimaFicha->fecha_consulta ?? null;

                return $mascota;
            });

        return view('dashboard', compact('mascotas', 'busqueda'));
    }
}
