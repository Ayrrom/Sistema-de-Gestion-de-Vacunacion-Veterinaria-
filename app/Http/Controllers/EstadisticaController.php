<?php

namespace App\Http\Controllers;

use App\Models\FichaClinica;
use App\Models\Mascota;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Estadísticas clínicas para el veterinario (solo rol veterinario activo).
 * Se calculan sobre los pacientes y sus fichas; se pueden filtrar por especie.
 */
class EstadisticaController extends Controller
{
    public const ESPECIES = ['Perro', 'Gato'];

    /** Meses sin consulta para considerar vencido el control anual. */
    private const MESES_CONTROL = 12;

    /** Meses desde la última desparasitación para considerarla vencida. */
    private const MESES_DESPARASITACION = 3;

    /** Campos del examen físico (N = normal, A = anormal) con su nombre. */
    private const SISTEMAS = [
        'piel_anexos' => 'Piel y anexos',
        'ojos' => 'Ojos',
        'oidos' => 'Oídos',
        'ganglios_linfaticos' => 'Ganglios linfáticos',
        'conjuntivas_mucosas' => 'Conjuntivas y mucosas',
        'sistema_cardiaco' => 'Cardíaco',
        'sistema_respiratorio' => 'Respiratorio',
        'sistema_digestivo' => 'Digestivo',
        'sistema_urinario' => 'Urinario',
        'sistema_nervioso' => 'Nervioso',
        'sistema_locomotor' => 'Locomotor',
    ];

    public function index(Request $request)
    {
        $especie = in_array($request->query('especie'), self::ESPECIES, true) ? $request->query('especie') : '';
        $hoy = Carbon::today();
        $haceUnAnio = $hoy->copy()->subMonths(11)->startOfMonth();

        $mascotas = Mascota::with('ultimaFicha')
            ->withMax('fichasClinicas as ultima_desparasitacion', 'fecha_desparasitacion')
            ->when($especie, fn ($q) => $q->where('especie', $especie))
            ->orderBy('nombre')
            ->get();

        $fichasDelAnio = FichaClinica::with('mascota:id,especie')
            ->whereDate('fecha_consulta', '>=', $haceUnAnio)
            ->when($especie, fn ($q) => $q->whereHas('mascota', fn ($m) => $m->where('especie', $especie)))
            ->get();

        return view('estadisticas.index', [
            'especie' => $especie,
            'especies' => self::ESPECIES,
            'indicadores' => $this->indicadores($mascotas, $fichasDelAnio, $hoy),
            'requierenAtencion' => $this->requierenAtencion($mascotas, $hoy),
            'consultasPorMes' => $this->consultasPorMes($fichasDelAnio, $haceUnAnio),
            'condicionCorporal' => $this->condicionCorporal($mascotas, $especie),
            'estados' => $this->estadosActuales($mascotas),
            'motivos' => $this->motivosFrecuentes($fichasDelAnio),
            'hallazgos' => $this->hallazgosAnormales($fichasDelAnio),
            'edades' => $this->gruposDeEdad($mascotas, $especie),
            'totalFichasDelAnio' => $fichasDelAnio->count(),
        ]);
    }

    private function controlVencido(Mascota $m, Carbon $hoy): bool
    {
        return ! $m->ultimaFicha || $m->ultimaFicha->fecha_consulta->lt($hoy->copy()->subMonths(self::MESES_CONTROL));
    }

    private function desparasitacionVencida(Mascota $m, Carbon $hoy): bool
    {
        return ! $m->ultima_desparasitacion
            || Carbon::parse($m->ultima_desparasitacion)->lt($hoy->copy()->subMonths(self::MESES_DESPARASITACION));
    }

    private function vacunacionIncompleta(Mascota $m): bool
    {
        return ! $m->ultimaFicha || ! $m->ultimaFicha->esquema_completo;
    }

    private function indicadores(Collection $mascotas, Collection $fichasDelAnio, Carbon $hoy): array
    {
        $total = $mascotas->count();
        $esteMes = $fichasDelAnio->filter(fn ($f) => $f->fecha_consulta->isSameMonth($hoy))->count();
        $mesAnterior = $fichasDelAnio->filter(fn ($f) => $f->fecha_consulta->isSameMonth($hoy->copy()->subMonthNoOverflow()))->count();
        $porcentaje = fn ($n) => $total ? (int) round($n * 100 / $total) : 0;

        $enTratamiento = $mascotas->filter(fn ($m) => $m->ultimaFicha?->estado === 'En tratamiento')->count();
        $controlVencido = $mascotas->filter(fn ($m) => $this->controlVencido($m, $hoy))->count();
        $vacunacion = $mascotas->filter(fn ($m) => $this->vacunacionIncompleta($m))->count();
        $desparasitacion = $mascotas->filter(fn ($m) => $this->desparasitacionVencida($m, $hoy))->count();
        $castrados = $mascotas->where('castrado', true)->count();

        return [
            'total' => $total,
            'perros' => $mascotas->where('especie', 'Perro')->count(),
            'gatos' => $mascotas->where('especie', 'Gato')->count(),
            'consultas_mes' => $esteMes,
            'consultas_mes_anterior' => $mesAnterior,
            'en_tratamiento' => $enTratamiento,
            'control_vencido' => $controlVencido,
            'control_vencido_pct' => $porcentaje($controlVencido),
            'vacunacion_incompleta' => $vacunacion,
            'vacunacion_incompleta_pct' => $porcentaje($vacunacion),
            'desparasitacion_vencida' => $desparasitacion,
            'desparasitacion_vencida_pct' => $porcentaje($desparasitacion),
            'castrados' => $castrados,
            'castrados_pct' => $porcentaje($castrados),
        ];
    }

    /** Pacientes con algo pendiente, los que más pendientes tienen primero. */
    private function requierenAtencion(Collection $mascotas, Carbon $hoy): Collection
    {
        return $mascotas->map(function ($m) use ($hoy) {
            $motivos = [];
            if ($m->ultimaFicha?->estado === 'En tratamiento') {
                $motivos[] = 'En tratamiento';
            }
            if ($m->ultimaFicha?->estado === 'Revisión') {
                $motivos[] = 'Revisión pendiente';
            }
            if ($this->controlVencido($m, $hoy)) {
                $motivos[] = $m->ultimaFicha ? 'Más de 1 año sin control' : 'Nunca tuvo consulta';
            }
            if ($this->vacunacionIncompleta($m)) {
                $motivos[] = 'Vacunación incompleta';
            }
            if ($this->desparasitacionVencida($m, $hoy)) {
                $motivos[] = $m->ultima_desparasitacion ? 'Desparasitación vencida' : 'Sin desparasitación registrada';
            }

            return ['mascota' => $m, 'motivos' => $motivos];
        })
            ->filter(fn ($fila) => count($fila['motivos']) > 0)
            // Primero los que más pendientes tienen; a igual cantidad, los que
            // están en tratamiento y después los que tienen revisión pendiente.
            ->sortByDesc(fn ($fila) => count($fila['motivos']) * 10
                + (in_array('En tratamiento', $fila['motivos'], true) ? 5 : 0)
                + (in_array('Revisión pendiente', $fila['motivos'], true) ? 3 : 0))
            ->values();
    }

    /** Consultas de los últimos 12 meses, mes por mes (incluye meses en 0). */
    private function consultasPorMes(Collection $fichas, Carbon $desde): array
    {
        $porMes = $fichas->countBy(fn ($f) => $f->fecha_consulta->format('Y-m'));
        $meses = [];
        for ($i = 0; $i < 12; $i++) {
            $mes = $desde->copy()->addMonths($i);
            $meses[] = [
                'etiqueta' => Str::ucfirst($mes->locale('es')->translatedFormat('M')),
                'etiqueta_larga' => Str::ucfirst($mes->locale('es')->translatedFormat('F Y')),
                'valor' => $porMes->get($mes->format('Y-m'), 0),
            ];
        }

        return $meses;
    }

    /** Distribución de la condición corporal (según la última consulta) por especie. */
    private function condicionCorporal(Collection $mascotas, string $especie): array
    {
        $especies = $especie ? [$especie] : self::ESPECIES;
        $filas = [];
        foreach (FichaClinica::CONDICIONES_CORPORALES as $valor => $nombre) {
            $fila = ['valor' => $valor, 'nombre' => $nombre, 'referencia' => FichaClinica::REFERENCIAS_PESO_CORTAS[$valor], 'por_especie' => []];
            foreach ($especies as $e) {
                $fila['por_especie'][$e] = $mascotas->where('especie', $e)
                    ->filter(fn ($m) => $m->ultimaFicha?->condicion_corporal === $valor)->count();
            }
            $filas[] = $fila;
        }

        $evaluados = $mascotas->filter(fn ($m) => $m->ultimaFicha?->condicion_corporal)->count();
        $sobrepeso = $mascotas->filter(fn ($m) => ($m->ultimaFicha?->condicion_corporal ?? 0) >= 4)->count();

        return [
            'especies' => $especies,
            'filas' => $filas,
            'evaluados' => $evaluados,
            'sobrepeso_pct' => $evaluados ? (int) round($sobrepeso * 100 / $evaluados) : 0,
        ];
    }

    private function estadosActuales(Collection $mascotas): array
    {
        $conteo = $mascotas->countBy(fn ($m) => $m->ultimaFicha->estado ?? 'Sin consultas');

        return collect(['Saludable', 'En tratamiento', 'Revisión', 'Sin consultas'])
            ->map(fn ($estado) => ['nombre' => $estado, 'valor' => $conteo->get($estado, 0)])
            ->all();
    }

    /** Los 6 motivos de consulta más frecuentes del último año. */
    private function motivosFrecuentes(Collection $fichas): array
    {
        return $fichas
            ->groupBy(fn ($f) => Str::of($f->motivo_consulta)->trim()->lower()->ascii()->value())
            ->map(fn ($grupo) => ['nombre' => Str::ucfirst(trim($grupo->first()->motivo_consulta)), 'valor' => $grupo->count()])
            ->sortByDesc('valor')
            ->take(6)
            ->values()
            ->all();
    }

    /** Cuántas veces se marcó "A" (anormal) en cada sistema del examen físico en el último año. */
    private function hallazgosAnormales(Collection $fichas): array
    {
        return collect(self::SISTEMAS)
            ->map(fn ($nombre, $campo) => ['nombre' => $nombre, 'valor' => $fichas->where($campo, 'A')->count()])
            ->filter(fn ($s) => $s['valor'] > 0)
            ->sortByDesc('valor')
            ->values()
            ->all();
    }

    /** Pacientes por grupo de edad y especie. */
    private function gruposDeEdad(Collection $mascotas, string $especie): array
    {
        $grupos = [
            'Joven (menos de 1 año)' => fn ($edad) => $edad < 1,
            'Adulto (1 a 7 años)' => fn ($edad) => $edad >= 1 && $edad <= 7,
            'Senior (8 años o más)' => fn ($edad) => $edad >= 8,
        ];
        $especies = $especie ? [$especie] : self::ESPECIES;

        $filas = [];
        foreach ($grupos as $nombre => $condicion) {
            $fila = ['nombre' => $nombre, 'por_especie' => []];
            foreach ($especies as $e) {
                $fila['por_especie'][$e] = $mascotas->where('especie', $e)->filter(fn ($m) => $condicion((int) $m->edad))->count();
            }
            $filas[] = $fila;
        }

        return ['especies' => $especies, 'filas' => $filas];
    }
}
