<?php

namespace Tests\Feature;

use App\Models\Mascota;
use App\Models\Propietario;
use App\Models\User;
use App\Support\Campos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tipos de caracteres y máximos de cada campo (App\Support\Campos), en el
 * formulario y en el servidor.
 */
class ValidacionCamposTest extends TestCase
{
    use RefreshDatabase;

    private Mascota $mascota;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['rol' => 'veterinario']));

        Propietario::create($this->propietario());
        $this->mascota = Mascota::create($this->mascota() + ['propietario_dni' => '30111222']);
    }

    private function propietario(array $datos = []): array
    {
        return array_merge(['dni' => '30111222', 'nombre' => 'Ana María', 'apellido' => "D'Angelo-Pérez", 'localidad' => 'Corrientes',
            'telefono' => '3794123456', 'calle' => '25 de Mayo', 'altura' => '1234'], $datos);
    }

    private function mascota(array $datos = []): array
    {
        return array_merge(['nombre' => 'Firulais', 'especie' => 'Perro', 'raza' => 'Mestizo', 'sexo' => 'Macho',
            'peso' => '12.5', 'edad' => '3', 'tipo_pelaje' => 'Corto'], $datos);
    }

    private function ficha(array $datos = []): array
    {
        return array_merge(['fecha_consulta' => now()->format('Y-m-d'), 'motivo_consulta' => 'Control', 'estado' => 'Saludable'], $datos);
    }

    /** Envía el formulario y devuelve los errores de validación (campo => mensaje). */
    private function errores(string $metodo, string $url, array $datos): array
    {
        $this->$metodo($url, $datos);

        return collect(session('errors')?->getMessages() ?? [])->map(fn ($m) => $m[0])->all();
    }

    // ---------- Datos válidos ----------

    public function test_datos_validos_con_tildes_enie_apostrofo_y_guion_se_aceptan(): void
    {
        $this->assertSame([], $this->errores('put', '/propietarios/30111222', $this->propietario()));
        $this->assertSame([], $this->errores('put', "/mascotas/{$this->mascota->id}", $this->mascota(['nombre' => 'Ñandú Jr.', 'color' => 'Marrón claro'])));
        $this->assertSame([], $this->errores('post', "/mascotas/{$this->mascota->id}/fichas", $this->ficha(['fc' => '120', 'fr' => '30', 'temperatura' => '38.5'])));
    }

    // ---------- Solo números ----------

    public function test_campos_numericos_rechazan_letras(): void
    {
        $e = $this->errores('put', '/propietarios/30111222', $this->propietario(['telefono' => '3794-12345', 'altura' => '12B']));
        $this->assertSame('El teléfono solo puede tener números.', $e['telefono']);
        $this->assertSame('La altura solo puede tener números.', $e['altura']);

        $e = $this->errores('put', "/mascotas/{$this->mascota->id}", $this->mascota(['edad' => '3a']));
        $this->assertArrayHasKey('edad', $e);

        $e = $this->errores('post', "/mascotas/{$this->mascota->id}/fichas", $this->ficha(['fc' => '1e2', 'fr' => 'abc']));
        $this->assertArrayHasKey('fc', $e);
        $this->assertArrayHasKey('fr', $e);
    }

    public function test_telefono_tiene_entre_6_y_15_numeros(): void
    {
        $e = $this->errores('put', '/propietarios/30111222', $this->propietario(['telefono' => '12345']));
        $this->assertSame('El teléfono tiene que tener al menos 6 números.', $e['telefono']);

        $e = $this->errores('put', '/propietarios/30111222', $this->propietario(['telefono' => str_repeat('1', 16)]));
        $this->assertSame('El teléfono puede tener como máximo 15 caracteres.', $e['telefono']);
    }

    // ---------- Rangos y decimales ----------

    public function test_rangos_de_valores_numericos(): void
    {
        $e = $this->errores('put', "/mascotas/{$this->mascota->id}", $this->mascota(['edad' => '41', 'peso' => '0']));
        $this->assertSame('La edad (años) tiene que estar entre 0 y 40.', $e['edad']);
        $this->assertSame('El peso (kg) tiene que estar entre 0.01 y 999.99.', $e['peso']);

        $e = $this->errores('post', "/mascotas/{$this->mascota->id}/fichas", $this->ficha(['fc' => '401', 'fr' => '201', 'temperatura' => '50']));
        $this->assertSame('La FC tiene que estar entre 0 y 400.', $e['fc']);
        $this->assertSame('La FR tiene que estar entre 0 y 200.', $e['fr']);
        $this->assertSame('La temperatura (°C) tiene que estar entre 30 y 45.', $e['temperatura']);
    }

    public function test_cantidad_de_decimales(): void
    {
        $e = $this->errores('put', "/mascotas/{$this->mascota->id}", $this->mascota(['peso' => '12.555']));
        $this->assertSame('El peso (kg) puede tener como máximo 2 decimales.', $e['peso']);

        $e = $this->errores('post', "/mascotas/{$this->mascota->id}/fichas", $this->ficha(['temperatura' => '38.55']));
        $this->assertSame('La temperatura (°C) puede tener como máximo 1 decimales.', $e['temperatura']);
    }

    // ---------- Solo letras ----------

    public function test_campos_de_letras_rechazan_numeros_y_simbolos(): void
    {
        $e = $this->errores('put', '/propietarios/30111222', $this->propietario(['nombre' => 'Ana2', 'apellido' => 'Gómez!', 'localidad' => 'Corrientes 3400']));
        $this->assertSame('El nombre solo puede tener letras.', $e['nombre']);
        $this->assertSame('El apellido solo puede tener letras.', $e['apellido']);
        $this->assertSame('La localidad solo puede tener letras.', $e['localidad']);

        $e = $this->errores('put', "/mascotas/{$this->mascota->id}", $this->mascota(['nombre' => 'Rex<script>', 'raza' => 'Lab123', 'color' => '#fff', 'tipo_pelaje' => 'Corto2']));
        foreach (['nombre', 'raza', 'color', 'tipo_pelaje'] as $campo) {
            $this->assertArrayHasKey($campo, $e);
        }
    }

    // ---------- Máximo de caracteres ----------

    public function test_ningun_campo_acepta_mas_caracteres_que_su_maximo(): void
    {
        $largo = fn (string $campo) => str_repeat('a', Campos::max($campo) + 1);

        $e = $this->errores('put', '/propietarios/30111222', $this->propietario(['nombre' => $largo('nombre_propietario'), 'apellido' => $largo('apellido'), 'calle' => $largo('calle'), 'localidad' => $largo('localidad')]));
        $this->assertSame(['nombre', 'apellido', 'localidad', 'calle'], array_keys($e));
        $this->assertSame('La calle puede tener como máximo 100 caracteres.', $e['calle']);

        $e = $this->errores('put', "/mascotas/{$this->mascota->id}", $this->mascota(['nombre' => $largo('nombre_mascota'), 'raza' => $largo('raza'), 'tipo_pelaje' => $largo('tipo_pelaje'), 'color' => $largo('color'), 'procedencia' => $largo('procedencia'), 'senas_particulares' => $largo('senas_particulares')]));
        $this->assertCount(6, $e);

        $e = $this->errores('post', "/mascotas/{$this->mascota->id}/fichas", $this->ficha(['motivo_consulta' => $largo('motivo_consulta'), 'vacunas_aplicadas' => $largo('vacunas_aplicadas'), 'droga_desparasitacion' => $largo('droga_desparasitacion'), 'producto_desparasitacion' => $largo('producto_desparasitacion'), 'pulso' => $largo('pulso'), 'tllc' => $largo('tllc'), 'relacion_entorno' => $largo('relacion_entorno')]));
        $this->assertCount(7, $e);

        $e = $this->errores('post', '/equipo', ['rol' => 'estudiante', 'dni' => '40111222', 'name' => $largo('name'), 'catedra' => $largo('catedra')]);
        $this->assertArrayHasKey('name', $e);
        $this->assertArrayHasKey('catedra', $e);
    }

    // ---------- Fechas ----------

    public function test_las_fechas_no_pueden_ser_futuras(): void
    {
        $manana = now()->addDay()->format('Y-m-d');

        $e = $this->errores('post', "/mascotas/{$this->mascota->id}/fichas", $this->ficha(['fecha_consulta' => $manana, 'fecha_desparasitacion' => $manana]));
        $this->assertSame('La fecha de la consulta no puede ser futura.', $e['fecha_consulta']);
        $this->assertSame('La fecha de desparasitación no puede ser futura.', $e['fecha_desparasitacion']);

        $e = $this->errores('put', "/mascotas/{$this->mascota->id}", $this->mascota(['fecha_nacimiento' => $manana]));
        $this->assertSame('La fecha de nacimiento no puede ser futura.', $e['fecha_nacimiento']);
    }

    public function test_selectores_solo_aceptan_sus_opciones(): void
    {
        $e = $this->errores('put', "/mascotas/{$this->mascota->id}", $this->mascota(['especie' => 'Dinosaurio', 'sexo' => 'Otro']));
        $this->assertSame('La especie seleccionada no es válida.', $e['especie']);
        $this->assertSame('El sexo seleccionado no es válido.', $e['sexo']);
    }

    // ---------- Formulario sincronizado con el servidor ----------

    public function test_cada_campo_del_formulario_tiene_el_maximo_y_el_filtro_de_su_definicion(): void
    {
        $formularios = [
            '/propietarios/create' => ['nombre' => 'nombre_propietario', 'apellido' => 'apellido', 'telefono' => 'telefono', 'calle' => 'calle', 'altura' => 'altura', 'localidad' => 'localidad'],
            "/mascotas/{$this->mascota->id}/edit" => ['nombre' => 'nombre_mascota', 'raza' => 'raza', 'peso' => 'peso', 'edad' => 'edad', 'tipo_pelaje' => 'tipo_pelaje', 'color' => 'color', 'procedencia' => 'procedencia', 'senas_particulares' => 'senas_particulares'],
            "/mascotas/{$this->mascota->id}/fichas/create" => ['motivo_consulta' => 'motivo_consulta', 'vacunas_aplicadas' => 'vacunas_aplicadas', 'droga_desparasitacion' => 'droga_desparasitacion', 'producto_desparasitacion' => 'producto_desparasitacion', 'fc' => 'fc', 'fr' => 'fr', 'temperatura' => 'temperatura', 'pulso' => 'pulso', 'tllc' => 'tllc', 'relacion_entorno' => 'relacion_entorno'],
            '/equipo/create' => ['name' => 'name', 'catedra' => 'catedra'],
        ];

        foreach ($formularios as $url => $campos) {
            $html = $this->get($url)->assertOk()->getContent();

            foreach ($campos as $name => $clave) {
                $this->assertMatchesRegularExpression('/<(input|textarea)[^>]*name="' . $name . '"[^>]*>/s', $html, "Falta el campo $name en $url");
                preg_match('/<(?:input|textarea)[^>]*name="' . $name . '"[^>]*>/s', $html, $tag);

                $this->assertStringContainsString('maxlength="' . Campos::max($clave) . '"', $tag[0], "maxlength de $name en $url");
                $this->assertStringNotContainsString('type="number"', $tag[0], "$name no debería ser type=number");

                $filtro = match (Campos::DEFINICIONES[$clave][0]) {
                    Campos::NUMEROS => 'data-solo-numeros',
                    Campos::DECIMAL => 'data-solo-decimal',
                    Campos::LETRAS => 'data-solo-letras',
                    default => null,
                };
                if ($filtro) {
                    $this->assertStringContainsString($filtro, $tag[0], "filtro de $name en $url");
                }
            }
        }

        // Fechas con máximo = hoy
        $this->get("/mascotas/{$this->mascota->id}/fichas/create")->assertSee('max="' . now()->format('Y-m-d') . '"', false);
    }
}
