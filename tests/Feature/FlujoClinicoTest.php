<?php

namespace Tests\Feature;

use App\Models\Estudiante;
use App\Models\FichaClinica;
use App\Models\Mascota;
use App\Models\Propietario;
use App\Models\User;
use App\Models\Veterinario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Recorrido completo: propietario -> paciente -> ficha clínica -> historial.
 */
class FlujoClinicoTest extends TestCase
{
    use RefreshDatabase;

    private Veterinario $vet;
    private Estudiante $estudiante;

    protected function setUp(): void
    {
        parent::setUp();

        $userVet = User::factory()->create(['rol' => 'veterinario', 'name' => 'Dra. Perez']);
        $this->vet = Veterinario::create(['user_id' => $userVet->id, 'dni' => $userVet->dni, 'nombre' => 'Dra. Perez', 'catedra' => 'Clínica']);
        $this->estudiante = Estudiante::create(['dni' => '40111222', 'nombre' => 'Juan Estudiante', 'veterinario_id' => $this->vet->id]);

        $this->actingAs($userVet);
    }

    private function propietario(array $datos = []): array
    {
        return array_merge(['dni' => '30111222', 'nombre' => 'Ana', 'apellido' => 'Gomez', 'localidad' => 'Corrientes', 'telefono' => '3794000000', 'calle' => 'Junin', 'altura' => '123'], $datos);
    }

    private function mascota(array $datos = []): array
    {
        return array_merge(['propietario_dni' => '30111222', 'nombre' => 'Firulais', 'especie' => 'Perro', 'raza' => 'Mestizo', 'sexo' => 'Macho', 'peso' => '12.5', 'edad' => '3', 'tipo_pelaje' => 'Corto'], $datos);
    }

    private function crearPaciente(array $datos = []): Mascota
    {
        if (! Propietario::find('30111222')) {
            $this->post('/propietarios', $this->propietario());
        }
        $this->post('/mascotas', $this->mascota($datos));

        return Mascota::latest('id')->firstOrFail();
    }

    public function test_todas_las_pantallas_responden(): void
    {
        $m = $this->crearPaciente();
        $this->post("/mascotas/{$m->id}/fichas", ['fecha_consulta' => '2026-11-01', 'motivo_consulta' => 'Control', 'estado' => 'Saludable']);
        $f = FichaClinica::firstOrFail();

        $urls = ['/dashboard', '/dashboard?buscar=Firu', '/propietarios', '/propietarios/create', '/propietarios/30111222/edit',
            '/mascotas', '/mascotas/create', '/mascotas/create?dni=30111222', "/mascotas/{$m->id}", "/mascotas/{$m->id}/edit",
            "/mascotas/{$m->id}/fichas/create", "/mascotas/{$m->id}/historial", "/fichas/{$f->id}", "/fichas/{$f->id}/edit", '/password'];

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }

        $this->get("/mascotas/{$m->id}/historial/pdf")
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_alta_de_propietario_con_veterinario_que_lo_atiende(): void
    {
        $this->post('/propietarios', $this->propietario(['veterinario_id' => $this->vet->id]))
            ->assertRedirect(route('propietarios.index'));

        $this->assertSame($this->vet->id, Propietario::find('30111222')->veterinario_id);
        $this->get('/propietarios')->assertSee('Dra. Perez');
    }

    public function test_buscar_por_dni_completo_abre_el_perfil_del_propietario(): void
    {
        $this->post('/propietarios', $this->propietario());

        $this->get('/propietarios?buscar=30111222')->assertRedirect(route('propietarios.show', '30111222'));
        $this->get('/propietarios?buscar=%2030111222%20')->assertRedirect(route('propietarios.show', '30111222')); // con espacios
    }

    public function test_buscar_propietarios_por_dni_parcial_nombre_o_apellido(): void
    {
        $this->post('/propietarios', $this->propietario());
        $this->post('/propietarios', $this->propietario(['dni' => '25999888', 'nombre' => 'Carlos', 'apellido' => 'Lopez']));

        $this->get('/propietarios?buscar=3011')->assertOk()->assertSee('Gomez')->assertDontSee('Lopez');
        $this->get('/propietarios?buscar=carlos')->assertOk()->assertSee('Lopez')->assertDontSee('Gomez');
        $this->get('/propietarios?buscar=Gom')->assertOk()->assertSee('Gomez')->assertDontSee('Lopez');
        $this->get('/propietarios?buscar=nadie')->assertOk()
            ->assertSee('No se encontró ningún propietario para')
            ->assertSee('Registrar nuevo propietario');
        $this->get('/propietarios')->assertSee('Gomez')->assertSee('Lopez');
    }

    public function test_sugerencias_de_dni_devuelven_hasta_10_que_empiezan_con_lo_escrito(): void
    {
        foreach (range(10, 22) as $i) { // 13 propietarios con DNI 300000NN
            Propietario::create(['dni' => "300000{$i}", 'nombre' => "Nombre{$i}", 'apellido' => "Apellido{$i}", 'telefono' => '1', 'calle' => 'J', 'altura' => '1', 'localidad' => 'C']);
        }
        Propietario::create(['dni' => '41000000', 'nombre' => 'Otro', 'apellido' => 'Inicio', 'telefono' => '1', 'calle' => 'J', 'altura' => '1', 'localidad' => 'C']);

        // Con "3" hay 13 que empiezan así: se devuelven solo 10, ordenados por DNI.
        $respuesta = $this->getJson('/propietarios/sugerencias?dni=3')->assertOk()->assertJsonCount(10);
        $this->assertSame('30000010', $respuesta->json('0.dni'));
        $this->assertSame('Nombre10 Apellido10', $respuesta->json('0.nombre'));

        // Al seguir escribiendo se achica la lista.
        $this->getJson('/propietarios/sugerencias?dni=3000002')->assertJsonCount(3)->assertJsonPath('0.dni', '30000020');
        $this->getJson('/propietarios/sugerencias?dni=30000015')->assertJsonCount(1)->assertJsonPath('0.dni', '30000015');
        $this->getJson('/propietarios/sugerencias?dni=4')->assertJsonCount(1)->assertJsonPath('0.dni', '41000000');

        // Solo coincide el comienzo del DNI, no el medio.
        $this->getJson('/propietarios/sugerencias?dni=0000')->assertJsonCount(0);
        $this->getJson('/propietarios/sugerencias?dni=9')->assertJsonCount(0);
        $this->getJson('/propietarios/sugerencias?dni=')->assertJsonCount(0);
        $this->getJson('/propietarios/sugerencias?dni=abc')->assertJsonCount(0);
    }

    public function test_sugerencias_requieren_sesion_y_estan_en_ambos_buscadores(): void
    {
        $this->get('/propietarios')->assertSee('data-sugerencias="' . route('propietarios.sugerencias') . '"', false);
        $this->get('/mascotas/create')->assertSee('data-sugerencias="' . route('propietarios.sugerencias') . '"', false);
        $this->get('/dashboard')->assertSee('js/sugerencias-dni.js');

        auth()->logout();
        $this->getJson('/propietarios/sugerencias?dni=3')->assertUnauthorized();
    }

    public function test_perfil_del_propietario_muestra_sus_mascotas(): void
    {
        $firulais = $this->crearPaciente();
        $this->crearPaciente(['nombre' => 'Michi', 'especie' => 'Gato']);
        FichaClinica::create(['mascota_id' => $firulais->id, 'fecha_consulta' => '2026-11-01', 'motivo_consulta' => 'X', 'estado' => 'En tratamiento']);
        $this->post('/propietarios', $this->propietario(['dni' => '25999888', 'nombre' => 'Carlos', 'apellido' => 'Lopez']));
        $this->post('/mascotas', $this->mascota(['propietario_dni' => '25999888', 'nombre' => 'Rocky']));

        $this->get('/propietarios/30111222')
            ->assertOk()
            ->assertSee('Ana Gomez')
            ->assertSee('Mascotas (2)')
            ->assertSee('Firulais')->assertSee('Michi')
            ->assertDontSee('Rocky') // es de otro propietario
            ->assertSee('En tratamiento')
            ->assertSee('01/11/2026')
            ->assertSee(route('fichas.create', $firulais))
            ->assertSee(route('mascotas.create', ['dni' => '30111222']), false);

        $this->get("/mascotas/{$firulais->id}")->assertSee(route('propietarios.show', '30111222'));
        $this->get('/propietarios/99999999')->assertNotFound();
    }

    /** Arma 4 pacientes de 2 propietarios con consultas en distintas fechas. */
    private function pacientesParaFiltrar(): void
    {
        $this->post('/propietarios', $this->propietario()); // 30111222
        $this->post('/propietarios', $this->propietario(['dni' => '41222333', 'nombre' => 'Carlos', 'apellido' => 'Lopez']));

        $consultas = [
            ['Firulais', 'Perro', '30111222', ['2026-03-10']],
            ['Michi', 'Gato', '30111222', ['2026-06-15', '2026-11-01']],
            ['Rocky', 'Perro', '41222333', ['2026-11-01']],
            ['Pelusa', 'Gato', '41222333', []], // sin consultas
        ];
        foreach ($consultas as [$nombre, $especie, $dni, $fechas]) {
            $this->post('/mascotas', $this->mascota(['nombre' => $nombre, 'especie' => $especie, 'propietario_dni' => $dni]));
            $m = Mascota::where('nombre', $nombre)->first();
            foreach ($fechas as $fecha) {
                FichaClinica::create(['mascota_id' => $m->id, 'fecha_consulta' => $fecha, 'motivo_consulta' => 'X', 'estado' => 'Saludable']);
            }
        }
    }

    /** Nombres de pacientes que aparecen en el listado con esos filtros. */
    private function pacientesListados(string $query): array
    {
        $html = $this->get('/mascotas' . $query)->assertOk()->getContent();

        return array_values(array_filter(['Firulais', 'Michi', 'Rocky', 'Pelusa'], fn ($n) => str_contains($html, "<td>{$n}</td>")));
    }

    public function test_filtrar_pacientes_por_especie(): void
    {
        $this->pacientesParaFiltrar();

        $this->assertSame(['Firulais', 'Michi', 'Rocky', 'Pelusa'], $this->pacientesListados(''));
        $this->assertSame(['Firulais', 'Rocky'], $this->pacientesListados('?especie=Perro'));
        $this->assertSame(['Michi', 'Pelusa'], $this->pacientesListados('?especie=Gato'));
        $this->assertSame(['Firulais', 'Michi', 'Rocky', 'Pelusa'], $this->pacientesListados('?especie=Dinosaurio')); // inválida: se ignora
    }

    public function test_filtrar_pacientes_por_dni_del_propietario(): void
    {
        $this->pacientesParaFiltrar();

        $this->assertSame(['Firulais', 'Michi'], $this->pacientesListados('?dni=30111222'));
        $this->assertSame(['Rocky', 'Pelusa'], $this->pacientesListados('?dni=41'));      // empieza con
        $this->assertSame([], $this->pacientesListados('?dni=1222'));                     // no coincide el medio
        $this->assertSame(['Firulais', 'Michi'], $this->pacientesListados('?dni=30.111')); // se quitan los no números
    }

    public function test_filtrar_pacientes_por_fecha_de_consulta(): void
    {
        $this->pacientesParaFiltrar();

        // Fecha puntual
        $this->assertSame(['Michi', 'Rocky'], $this->pacientesListados('?desde=2026-11-01&hasta=2026-11-01'));
        // Rango: Michi entra por su consulta de junio
        $this->assertSame(['Firulais', 'Michi'], $this->pacientesListados('?desde=2026-03-01&hasta=2026-06-30'));
        // Solo "desde" o solo "hasta"
        $this->assertSame(['Michi', 'Rocky'], $this->pacientesListados('?desde=2026-07-01'));
        $this->assertSame(['Firulais'], $this->pacientesListados('?hasta=2026-04-01'));
        // Cargadas al revés se invierten
        $this->assertSame(['Firulais', 'Michi'], $this->pacientesListados('?desde=2026-06-30&hasta=2026-03-01'));
        // Fecha inválida se ignora
        $this->assertSame(['Firulais', 'Michi', 'Rocky', 'Pelusa'], $this->pacientesListados('?desde=hola'));
    }

    public function test_filtros_de_pacientes_combinados(): void
    {
        $this->pacientesParaFiltrar();

        $this->assertSame(['Michi'], $this->pacientesListados('?especie=Gato&desde=2026-11-01&hasta=2026-11-01'));
        $this->assertSame(['Rocky'], $this->pacientesListados('?dni=41&especie=Perro'));
        $this->assertSame([], $this->pacientesListados('?dni=41&especie=Gato&desde=2026-01-01'));

        $this->get('/mascotas?dni=41&especie=Gato&desde=2026-01-01')
            ->assertSee('No se encontraron pacientes con esos filtros.')
            ->assertSee('Limpiar');
        $this->get('/mascotas?especie=Perro')
            ->assertSee('2 pacientes encontrados')
            ->assertSee('<option value="Perro" selected>', false)
            ->assertSee('01/11/2026'); // columna Última visita de Rocky
    }

    public function test_los_filtros_de_pacientes_se_mantienen_al_cambiar_de_pagina(): void
    {
        $this->post('/propietarios', $this->propietario());
        foreach (range(1, 17) as $i) {
            $this->post('/mascotas', $this->mascota(['nombre' => sprintf('Perro %02d', $i)]));
        }

        $this->get('/mascotas?especie=Perro')
            ->assertSee('17 pacientes encontrados')
            ->assertSee('especie=Perro&amp;page=2', false);
    }

    public function test_validaciones_de_propietario_y_paciente(): void
    {
        $this->post('/propietarios', [])->assertSessionHasErrors(['dni', 'nombre', 'apellido', 'localidad', 'telefono', 'calle', 'altura']);
        $this->post('/propietarios', $this->propietario());
        $this->post('/propietarios', $this->propietario())->assertSessionHasErrors(['dni' => 'Ya existe un propietario registrado con ese DNI.']);

        $this->post('/propietarios', $this->propietario(['dni' => '123456789']))
            ->assertSessionHasErrors(['dni' => 'El DNI puede tener como máximo 8 caracteres.']);
        $this->get('/propietarios/create')->assertSee('maxlength="8"', false)->assertSee('data-solo-numeros', false);
        $this->get('/mascotas/create')->assertSee('maxlength="8"', false)->assertSee('data-solo-numeros', false);
        $this->post('/propietarios', $this->propietario(['dni' => '3011A222']))
            ->assertSessionHasErrors(['dni' => 'El DNI solo puede tener números.']);
        $this->post('/propietarios', $this->propietario(['dni' => '30.111.2']))
            ->assertSessionHasErrors(['dni' => 'El DNI solo puede tener números.']);

        $this->post('/mascotas', $this->mascota(['propietario_dni' => '999']))->assertSessionHasErrors('propietario_dni');
        $this->post('/mascotas', $this->mascota(['peso' => 'mucho']))->assertSessionHasErrors(['peso' => 'El peso tiene que ser un número.']);
    }

    public function test_el_dni_del_propietario_no_se_modifica_al_editar(): void
    {
        $this->post('/propietarios', $this->propietario());

        $this->put('/propietarios/30111222', $this->propietario(['dni' => '999', 'nombre' => 'Ana María']))
            ->assertRedirect(route('propietarios.index'));

        $this->assertNotNull(Propietario::find('30111222'));
        $this->assertSame('Ana María', Propietario::find('30111222')->nombre);
    }

    public function test_ficha_con_veterinario_y_estudiantes(): void
    {
        $m = $this->crearPaciente();

        $this->post("/mascotas/{$m->id}/fichas", [
            'fecha_consulta' => '2026-11-05', 'motivo_consulta' => 'Vacunación', 'estado' => 'Saludable',
            'veterinario_id' => $this->vet->id, 'estudiantes' => [$this->estudiante->id],
            'esquema_completo' => '1', 'temperatura' => '38.5', 'condicion_corporal' => '3', 'piel_anexos' => 'N',
        ])->assertRedirect(route('mascotas.show', $m));

        $ficha = FichaClinica::firstOrFail();
        $this->assertSame($this->vet->id, $ficha->veterinario_id);
        $this->assertSame('Dra. Perez', $ficha->veterinario); // nombre guardado para historial y PDF
        $this->assertTrue($ficha->esquema_completo);
        $this->assertSame('2026-11-05', $ficha->estudiantes->first()->pivot->fecha_atencion);

        $this->get("/fichas/{$ficha->id}")->assertSee('Juan Estudiante');
        $this->get("/mascotas/{$m->id}/historial")->assertSee('Dra. Perez');
    }

    public function test_ficha_anterior_con_veterinario_en_texto_se_conserva(): void
    {
        $m = $this->crearPaciente();
        $vieja = FichaClinica::create(['mascota_id' => $m->id, 'fecha_consulta' => '2026-07-01', 'motivo_consulta' => 'Control', 'estado' => 'Saludable', 'veterinario' => 'Dr. Texto Libre']);

        $this->get("/fichas/{$vieja->id}")->assertSee('Dr. Texto Libre');
        $this->get("/fichas/{$vieja->id}/edit")->assertSee('Cargado anteriormente como texto: Dr. Texto Libre');

        $this->put("/fichas/{$vieja->id}", ['fecha_consulta' => '2026-07-01', 'motivo_consulta' => 'Control', 'estado' => 'Saludable']);
        $this->assertSame('Dr. Texto Libre', $vieja->fresh()->veterinario);
    }

    public function test_imagenes_de_condicion_corporal_segun_especie(): void
    {
        $perro = $this->crearPaciente();
        $gato = $this->crearPaciente(['nombre' => 'Michi', 'especie' => 'Gato']);

        $this->get("/mascotas/{$perro->id}/fichas/create")->assertSee('perro-1.jpg')->assertDontSee('gato-1.jpg');
        $this->get("/mascotas/{$gato->id}/fichas/create")->assertSee('gato-1.jpg')->assertDontSee('perro-1.jpg');

        // Cada opción muestra su nombre debajo de la imagen, en orden.
        foreach ([$perro, $gato] as $m) {
            $this->get("/mascotas/{$m->id}/fichas/create")
                ->assertSeeInOrder(['condicion-texto', 'Desnutrido', 'condicion-texto', 'Delgado', 'condicion-texto', 'Ideal', 'condicion-texto', 'Sobrepeso', 'condicion-texto', 'Obeso']);
        }
    }

    public function test_la_condicion_corporal_se_muestra_con_su_nombre_en_detalle_y_pdf(): void
    {
        $m = $this->crearPaciente();
        $ficha = FichaClinica::create(['mascota_id' => $m->id, 'fecha_consulta' => '2026-11-01', 'motivo_consulta' => 'X', 'estado' => 'Saludable', 'condicion_corporal' => 1]);

        $this->get("/fichas/{$ficha->id}")
            ->assertSee('1 - Desnutrido')
            ->assertSee('Más de 20% bajo el peso ideal')
            ->assertDontSee('Caquéctico');

        // Formulario: peso del paciente y referencia de peso en cada opción.
        $this->get("/mascotas/{$m->id}/fichas/create")
            ->assertSee('Peso registrado de Firulais:')
            ->assertSee('12,5 kg')
            ->assertSeeInOrder(['Desnutrido', '-20% o más', 'Delgado', '-10% a -20%', 'Ideal', 'Peso ideal', 'Sobrepeso', '+10% a +20%', 'Obeso', '+20% o más']);

        $htmlDelPdf = view('fichas.historial-pdf', ['mascota' => $m->load('propietario'), 'fichas' => $m->fichasClinicas()->with('estudiantes')->get()])->render();
        $this->assertStringContainsString('1 / 5 - Desnutrido', $htmlDelPdf);

        $this->get("/mascotas/{$m->id}/historial/pdf")->assertOk();
    }

    public function test_no_se_elimina_un_propietario_con_mascotas(): void
    {
        $this->crearPaciente();

        $this->from('/propietarios')->delete('/propietarios/30111222')
            ->assertSessionHas('error');

        $this->assertNotNull(Propietario::find('30111222'));
    }

    public function test_no_se_elimina_un_paciente_con_fichas_y_si_uno_sin_fichas(): void
    {
        $conFichas = $this->crearPaciente();
        FichaClinica::create(['mascota_id' => $conFichas->id, 'fecha_consulta' => '2026-11-01', 'motivo_consulta' => 'X', 'estado' => 'Saludable']);
        $sinFichas = $this->crearPaciente(['nombre' => 'Michi']);

        $this->from('/mascotas')->delete("/mascotas/{$conFichas->id}")
            ->assertRedirect('/mascotas')
            ->assertSessionHas('error', 'No se puede eliminar: el paciente tiene fichas clínicas registradas.');
        $this->assertNotNull($conFichas->fresh());

        $this->delete("/mascotas/{$sinFichas->id}")->assertRedirect(route('mascotas.index'));
        $this->assertNull($sinFichas->fresh());
    }

    public function test_ver_detalles_del_dashboard_abre_el_detalle_del_paciente(): void
    {
        $m = $this->crearPaciente();

        $this->get('/dashboard')
            ->assertSee(route('mascotas.show', $m) . '"', false)
            ->assertDontSee(route('mascotas.edit', $m));
    }

    public function test_eliminar_ficha_limpia_los_estudiantes_asociados(): void
    {
        $m = $this->crearPaciente();
        $this->post("/mascotas/{$m->id}/fichas", ['fecha_consulta' => '2026-11-05', 'motivo_consulta' => 'X', 'estado' => 'Saludable', 'estudiantes' => [$this->estudiante->id]]);
        $ficha = FichaClinica::firstOrFail();

        $this->delete("/fichas/{$ficha->id}")->assertRedirect();

        $this->assertDatabaseCount('estudiante_ficha_clinica', 0);
    }

    public function test_dashboard_ordena_por_ultima_visita_y_pagina(): void
    {
        $this->post('/propietarios', $this->propietario());
        foreach (range(1, 14) as $i) {
            $this->post('/mascotas', $this->mascota(['nombre' => sprintf('Paciente %02d', $i)]));
        }
        $reciente = Mascota::where('nombre', 'Paciente 14')->first();
        $vieja = Mascota::where('nombre', 'Paciente 13')->first();
        FichaClinica::create(['mascota_id' => $vieja->id, 'fecha_consulta' => '2026-01-01', 'motivo_consulta' => 'X', 'estado' => 'Revisión']);
        FichaClinica::create(['mascota_id' => $reciente->id, 'fecha_consulta' => '2026-10-01', 'motivo_consulta' => 'X', 'estado' => 'En tratamiento']);
        FichaClinica::create(['mascota_id' => $reciente->id, 'fecha_consulta' => '2026-11-01', 'motivo_consulta' => 'X', 'estado' => 'Saludable']);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSeeInOrder(['Paciente 14', 'Saludable', '01/11/2026', 'Paciente 13', 'Revisión', 'Paciente 01', 'Sin consultas'])
            ->assertDontSee('Paciente 11') // 12 por página: la página 1 llega hasta "Paciente 10"
            ->assertSee('page=2');

        $this->get('/dashboard?page=2')->assertSee('Paciente 11')->assertSee('Paciente 12');
        $this->get('/dashboard?buscar=Paciente 13')->assertSee('Paciente 13')->assertDontSee('Paciente 14');
    }

    public function test_nombres_con_codigo_no_se_ejecutan_en_las_confirmaciones(): void
    {
        $malicioso = "x'); alert(document.cookie); //";
        $this->post('/propietarios', $this->propietario(['nombre' => $malicioso]));
        $this->post('/mascotas', $this->mascota(['nombre' => $malicioso]));

        foreach (['/propietarios', '/mascotas'] as $url) {
            preg_match('/onsubmit="([^"]*)"/', $this->get($url)->getContent(), $m);
            $jsQueEjecutaElNavegador = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5);

            $this->assertStringNotContainsString("'); alert(", $jsQueEjecutaElNavegador, "XSS en $url");
        }
    }
}
