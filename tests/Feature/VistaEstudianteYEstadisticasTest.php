<?php

namespace Tests\Feature;

use App\Models\FichaClinica;
use App\Models\Mascota;
use App\Models\Propietario;
use App\Models\User;
use App\Models\Veterinario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Cambio de vista Veterinario <-> Estudiante y sección de Estadísticas.
 */
class VistaEstudianteYEstadisticasTest extends TestCase
{
    use RefreshDatabase;

    private User $vet;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-15');

        $this->vet = User::factory()->create(['rol' => 'veterinario', 'name' => 'Dra. Perez', 'dni' => '20111222', 'password' => 'ClaveVet123']);
        Veterinario::create(['user_id' => $this->vet->id, 'dni' => '20111222', 'nombre' => 'Dra. Perez', 'catedra' => 'Clínica']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // ---------- Cambio de vista ----------

    public function test_el_veterinario_entra_como_veterinario_aunque_haya_cambiado_de_vista(): void
    {
        $this->post('/login', ['dni' => '20111222', 'password' => 'ClaveVet123']);
        $this->post('/perfil/vista');
        $this->get('/dashboard')->assertSee('Estudiante Dra. Perez');

        $this->post('/logout');
        $this->post('/login', ['dni' => '20111222', 'password' => 'ClaveVet123']);

        $this->get('/dashboard')
            ->assertSee('Veterinario Dra. Perez')
            ->assertDontSee('Estás viendo el sistema');
    }

    public function test_el_perfil_muestra_datos_y_boton_de_cambio_de_vista(): void
    {
        $this->actingAs($this->vet)->get('/perfil')
            ->assertOk()
            ->assertSee('Dra. Perez')
            ->assertSee('Clínica')
            ->assertSee('Ver como Estudiante');
    }

    public function test_en_vista_estudiante_tiene_los_permisos_de_un_estudiante(): void
    {
        $this->actingAs($this->vet);
        $this->get('/mascotas')->assertOk();
        $this->get('/estadisticas')->assertOk();

        $this->post('/perfil/vista')->assertRedirect(route('dashboard'));

        $this->get('/dashboard')
            ->assertSee('Estás viendo el sistema')
            ->assertSee('Volver a Veterinario')
            ->assertDontSee(route('estadisticas.index'));
        $this->get('/estadisticas')->assertForbidden();

        Propietario::create(['dni' => '30111222', 'nombre' => 'Ana', 'apellido' => 'G', 'telefono' => '1', 'calle' => 'J', 'altura' => '1', 'localidad' => 'C']);
        $m = Mascota::create(['propietario_dni' => '30111222', 'nombre' => 'Firu', 'especie' => 'Perro', 'raza' => 'M', 'sexo' => 'Macho', 'peso' => 10, 'edad' => 2, 'tipo_pelaje' => 'C']);
        $this->get('/mascotas')->assertDontSee('bi-trash');
        $this->delete("/mascotas/{$m->id}")->assertForbidden();

        // Puede cargar fichas como cualquier estudiante.
        $this->post("/mascotas/{$m->id}/fichas", ['fecha_consulta' => '2026-11-10', 'motivo_consulta' => 'Control', 'estado' => 'Saludable'])->assertRedirect();

        // Vuelve a veterinario.
        $this->post('/perfil/vista');
        $this->get('/estadisticas')->assertOk();
        $this->get('/mascotas')->assertSee('bi-trash');
    }

    public function test_el_rol_guardado_no_cambia_al_usar_la_vista_de_estudiante(): void
    {
        $this->actingAs($this->vet)->post('/perfil/vista');

        $this->assertSame('veterinario', $this->vet->fresh()->rol);
    }

    public function test_solo_el_veterinario_puede_cambiar_de_vista(): void
    {
        foreach (['admin', 'estudiante'] as $rol) {
            $user = User::factory()->create(['rol' => $rol]);
            $this->actingAs($user)->post('/perfil/vista')->assertForbidden();
            $this->get('/perfil')->assertOk()->assertDontSee('Ver como Estudiante');
        }
    }

    // ---------- Estadísticas ----------

    public function test_solo_el_veterinario_activo_ve_estadisticas(): void
    {
        $this->actingAs($this->vet)->get('/dashboard')->assertSee(route('estadisticas.index'));

        foreach (['admin', 'estudiante'] as $rol) {
            $user = User::factory()->create(['rol' => $rol]);
            $this->actingAs($user)->get('/estadisticas')->assertForbidden();
            $this->get('/dashboard')->assertDontSee(route('estadisticas.index'));
        }
    }

    private function cargarDatosClinicos(): void
    {
        Propietario::create(['dni' => '30111222', 'nombre' => 'Ana', 'apellido' => 'G', 'telefono' => '1', 'calle' => 'J', 'altura' => '1', 'localidad' => 'C']);
        $nueva = fn ($nombre, $especie, $edad, $castrado = false) => Mascota::create(['propietario_dni' => '30111222', 'nombre' => $nombre, 'especie' => $especie, 'raza' => 'M', 'sexo' => 'Macho', 'peso' => 10, 'edad' => $edad, 'tipo_pelaje' => 'C', 'castrado' => $castrado]);

        $firu = $nueva('Firulais', 'Perro', 3, true);
        $rocky = $nueva('Rocky', 'Perro', 9);
        $michi = $nueva('Michi', 'Gato', 0, true);
        $nueva('Pelusa', 'Gato', 5); // nunca consultó

        $ficha = fn ($m, $fecha, $datos = []) => FichaClinica::create(array_merge(['mascota_id' => $m->id, 'fecha_consulta' => $fecha, 'motivo_consulta' => 'Control', 'estado' => 'Saludable'], $datos));

        // Firulais: al día (vacunas completas, desparasitado hace 1 mes), obeso, piel anormal
        $ficha($firu, '2026-10-05', ['motivo_consulta' => 'Vacunación']);
        $ficha($firu, '2026-11-10', ['motivo_consulta' => 'vacunación ', 'esquema_completo' => true, 'fecha_desparasitacion' => '2026-10-15', 'condicion_corporal' => 5, 'piel_anexos' => 'A']);
        // Rocky: en tratamiento, delgado, sin vacunas, sin desparasitar, oídos y piel anormales
        $ficha($rocky, '2026-11-02', ['motivo_consulta' => 'Otitis', 'estado' => 'En tratamiento', 'condicion_corporal' => 2, 'oidos' => 'A', 'piel_anexos' => 'A']);
        // Michi: última consulta hace más de un año, sobrepeso
        $ficha($michi, '2025-09-01', ['condicion_corporal' => 4, 'esquema_completo' => true, 'fecha_desparasitacion' => '2025-09-01']);
    }

    public function test_indicadores_de_estadisticas(): void
    {
        $this->cargarDatosClinicos();

        $datos = $this->actingAs($this->vet)->get('/estadisticas')->assertOk()->viewData('indicadores');

        $this->assertSame(4, $datos['total']);
        $this->assertSame(2, $datos['perros']);
        $this->assertSame(2, $datos['gatos']);
        $this->assertSame(2, $datos['consultas_mes']);          // noviembre: Firulais y Rocky
        $this->assertSame(1, $datos['consultas_mes_anterior']); // octubre: Firulais
        $this->assertSame(1, $datos['en_tratamiento']);         // Rocky
        $this->assertSame(2, $datos['control_vencido']);        // Michi (>1 año) y Pelusa (nunca)
        $this->assertSame(50, $datos['control_vencido_pct']);
        $this->assertSame(2, $datos['vacunacion_incompleta']);  // Rocky y Pelusa (Michi sí completa)
        $this->assertSame(3, $datos['desparasitacion_vencida']); // Rocky, Pelusa y Michi (hace 14 meses)
        $this->assertSame(2, $datos['castrados']);
    }

    public function test_graficos_de_estadisticas(): void
    {
        $this->cargarDatosClinicos();
        $respuesta = $this->actingAs($this->vet)->get('/estadisticas');

        // Consultas por mes: 12 meses, el último es noviembre con 2
        $meses = $respuesta->viewData('consultasPorMes');
        $this->assertCount(12, $meses);
        $this->assertSame(2, end($meses)['valor']);
        $this->assertSame(1, $meses[10]['valor']); // octubre
        $this->assertSame(0, $meses[0]['valor']);  // la consulta de sep-2025 queda fuera del año

        // Condición corporal según la última consulta
        $condicion = $respuesta->viewData('condicionCorporal');
        $porValor = collect($condicion['filas'])->keyBy('valor');
        $this->assertSame(1, $porValor[5]['por_especie']['Perro']); // Firulais obeso
        $this->assertSame(1, $porValor[2]['por_especie']['Perro']); // Rocky delgado
        $this->assertSame(1, $porValor[4]['por_especie']['Gato']);  // Michi sobrepeso
        $this->assertSame(3, $condicion['evaluados']);
        $this->assertSame(67, $condicion['sobrepeso_pct']);        // 2 de 3

        // Motivos: "Vacunación" y "vacunación " cuentan juntos
        $motivos = collect($respuesta->viewData('motivos'));
        $this->assertSame('Vacunación', $motivos->first()['nombre']);
        $this->assertSame(2, $motivos->first()['valor']);

        // Hallazgos anormales: piel 2, oídos 1
        $hallazgos = collect($respuesta->viewData('hallazgos'))->pluck('valor', 'nombre');
        $this->assertSame(2, $hallazgos['Piel y anexos']);
        $this->assertSame(1, $hallazgos['Oídos']);

        // Estados actuales
        $estados = collect($respuesta->viewData('estados'))->pluck('valor', 'nombre');
        $this->assertSame(['Saludable' => 2, 'En tratamiento' => 1, 'Revisión' => 0, 'Sin consultas' => 1], $estados->all());

        // Edades: Michi joven, Firulais y Pelusa adultos, Rocky senior
        $edades = collect($respuesta->viewData('edades')['filas'])->keyBy('nombre');
        $this->assertSame(['Perro' => 0, 'Gato' => 1], $edades['Joven (menos de 1 año)']['por_especie']);
        $this->assertSame(['Perro' => 1, 'Gato' => 0], $edades['Senior (8 años o más)']['por_especie']);

        // Requieren atención: Rocky primero (más pendientes); Firulais está al día
        $atencion = $respuesta->viewData('requierenAtencion');
        $this->assertSame('Rocky', $atencion->first()['mascota']->nombre);
        $this->assertNotContains('Firulais', $atencion->pluck('mascota.nombre'));
        $this->assertContains('Nunca tuvo consulta', $atencion->firstWhere('mascota.nombre', 'Pelusa')['motivos']);

        $respuesta->assertSee('Pacientes que requieren atención')->assertSee('Perros')->assertSee('Gatos');
    }

    public function test_estadisticas_filtradas_por_especie(): void
    {
        $this->cargarDatosClinicos();

        $respuesta = $this->actingAs($this->vet)->get('/estadisticas?especie=Gato')->assertOk();
        $this->assertSame(2, $respuesta->viewData('indicadores')['total']);
        $this->assertSame(0, $respuesta->viewData('indicadores')['consultas_mes']);
        $this->assertSame(['Gato'], $respuesta->viewData('condicionCorporal')['especies']);

        // Especie inválida: se ignora
        $this->get('/estadisticas?especie=Loro')->assertOk()->assertViewHas('especie', '');
    }

    public function test_estadisticas_sin_datos_no_fallan(): void
    {
        $this->actingAs($this->vet)->get('/estadisticas')
            ->assertOk()
            ->assertSee('Todavía no hay consultas en este período.')
            ->assertSee('No hay pacientes con pendientes.');
    }
}
