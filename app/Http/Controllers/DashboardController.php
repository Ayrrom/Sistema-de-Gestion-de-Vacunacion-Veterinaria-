<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = $request->query('buscar');

        $mascotas = Mascota::with(['propietario', 'fichasClinicas'])
            ->when($busqueda, function ($query) use ($busqueda) {
                $query->where('nombre', 'like', "%{$busqueda}%");
            })
            ->get()
            ->map(function ($mascota) {
                $ultimaFicha = $mascota->fichasClinicas->sortByDesc('fecha_consulta')->first();

                $mascota->ultima_ficha = $ultimaFicha;
                $mascota->estado_actual = $ultimaFicha->estado ?? 'Sin consultas';
                $mascota->ultima_visita = $ultimaFicha->fecha_consulta ?? null;

                return $mascota;
            })
            ->sortByDesc(function ($mascota) {
                // Las que tienen consultas van primero, ordenadas por fecha más reciente.
                return $mascota->ultima_visita ? $mascota->ultima_visita->timestamp : 0;
            })
            ->values();

        return view('dashboard', compact('mascotas', 'busqueda'));
    }
}
