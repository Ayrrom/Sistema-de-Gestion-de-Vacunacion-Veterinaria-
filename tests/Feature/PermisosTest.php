<?php

namespace Tests\Feature;

use App\Models\Estudiante;
use App\Models\FichaClinica;
use App\Models\Mascota;
use App\Models\Propietario;
use App\Models\User;
use App\Models\Veterinario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Roles: veterinario (administra todo) y estudiante (ve, carga y edita, no elimina).
 * No existe el rol administrador.
 */
class PermisosTest extends TestCase
{
    use RefreshDatabase;

    private Mascota $mascota;
    private FichaClinica $ficha;

    protected function setUp(): void
    {
        parent::setUp();

        Propietario::create(['dni' => '30111222', 'nombre' => 'Ana', 'apellido' => 'Gomez', 'telefono' => '1', 'calle' => 'Junin', 'altura' => '1', 'localidad' => 'Corrientes']);
        $this->mascota = Mascota::create(['propietario_dni' => '30111222', 'nombre' => 'Firulais', 'especie' => 'Perro', 'raza' => 'Mestizo', 'sexo' => 'Macho', 'peso' => 10, 'edad' => 2, 'tipo_pelaje' => 'Corto']);
        $this->ficha = FichaClinica::create(['mascota_id' => $this->mascota->id, 'fecha_consulta' => '2026-11-01', 'motivo_consulta' => 'Control', 'estado' => 'Saludable']);
    }

    private function usuario(string $rol): User
    {
        return User::factory()->create(['rol' => $rol]);
    }

    public function test_solo_existen_los_roles_veterinario_y_estudiante(): void
    {
        $this->assertSame(['veterinario', 'estudiante'], array_keys(User::ROLES));
    }

    public function test_la_pantalla_usuarios_ya_no_existe(): void
    {
        $this->actingAs($this->usuario('veterinario'));

        $this->get('/usuarios')->assertNotFound();
        $this->get('/dashboard')->assertDontSee('bi-people-fill');
    }

    public function test_el_estudiante_no_puede_administrar(): void
    {
        $this->actingAs($this->usuario('estudiante'));

        $this->get('/equipo')->assertForbidden();
        $this->get('/equipo/create')->assertForbidden();
        $this->post('/equipo', ['name' => 'X', 'dni' => '5', 'rol' => 'veterinario'])->assertForbidden();
        $this->get('/estadisticas')->assertForbidden();
    }

    public function test_el_estudiante_no_puede_eliminar(): void
    {
        $this->actingAs($this->usuario('estudiante'));

        $this->delete("/fichas/{$this->ficha->id}")->assertForbidden();
        $this->delete("/mascotas/{$this->mascota->id}")->assertForbidden();
        $this->delete('/propietarios/30111222')->assertForbidden();

        $this->assertDatabaseCount('fichas_clinicas', 1);
        $this->assertDatabaseCount('mascotas', 1);
        $this->assertDatabaseCount('propietarios', 1);
    }

    public function test_el_estudiante_no_ve_botones_de_eliminar(): void
    {
        $this->actingAs($this->usuario('estudiante'));

        $this->get('/mascotas')->assertDontSee('bi-trash');
        $this->get('/propietarios')->assertDontSee('bi-trash');
        $this->get("/mascotas/{$this->mascota->id}/historial")->assertDontSee('bi-trash');
    }

    public function test_el_estudiante_puede_cargar_y_editar_fichas(): void
    {
        $this->actingAs($this->usuario('estudiante'));

        $this->post("/mascotas/{$this->mascota->id}/fichas", ['fecha_consulta' => '2026-11-02', 'motivo_consulta' => 'Vacuna', 'estado' => 'Saludable'])
            ->assertRedirect();
        $this->put("/fichas/{$this->ficha->id}", ['fecha_consulta' => '2026-11-01', 'motivo_consulta' => 'Editado', 'estado' => 'Revisión'])
            ->assertRedirect();

        $this->assertDatabaseCount('fichas_clinicas', 2);
        $this->assertSame('Editado', $this->ficha->fresh()->motivo_consulta);
    }

    public function test_el_veterinario_puede_eliminar(): void
    {
        $this->actingAs($this->usuario('veterinario'));

        $this->get('/mascotas')->assertSee('bi-trash');
        $this->delete("/fichas/{$this->ficha->id}")->assertRedirect();

        $this->assertDatabaseCount('fichas_clinicas', 0);
    }

    public function test_el_veterinario_crea_veterinario_y_estudiante_a_cargo(): void
    {
        $this->actingAs($creador = $this->usuario('veterinario'));

        $this->post('/equipo', ['name' => 'Dra. Perez', 'dni' => '20111222', 'rol' => 'veterinario', 'catedra' => 'Clínica'])
            ->assertRedirect(route('equipo.index'));
        $vet = Veterinario::firstOrFail();
        $this->assertSame('veterinario', $vet->user->rol);
        $this->assertSame('Clínica', $vet->catedra);

        $this->post('/equipo', ['name' => 'Juan', 'dni' => '40111222', 'rol' => 'estudiante', 'veterinario_id' => $vet->id])
            ->assertRedirect(route('equipo.index'));
        $est = Estudiante::firstOrFail();
        $this->assertSame($vet->id, $est->veterinario_id);
        $this->assertTrue($est->user->debe_cambiar_password);
        $this->assertTrue(Hash::check('40111222', $est->user->password));

        // Sigue logueado como él mismo.
        $this->assertSame($creador->id, auth()->id());
    }

    public function test_alta_rechaza_rol_invalido_y_dni_invalido_o_repetido(): void
    {
        $this->actingAs($vet = $this->usuario('veterinario'));

        foreach (['admin', 'superadmin'] as $rol) {
            $this->post('/equipo', ['name' => 'X', 'dni' => '9', 'rol' => $rol])->assertSessionHasErrors('rol');
        }
        $this->post('/equipo', ['name' => 'X', 'dni' => $vet->dni, 'rol' => 'estudiante'])->assertSessionHasErrors('dni');
        $this->post('/equipo', ['name' => 'X', 'dni' => '123456789', 'rol' => 'estudiante'])
            ->assertSessionHasErrors(['dni' => 'El DNI puede tener como máximo 8 caracteres.']);
        $this->post('/equipo', ['name' => 'X', 'dni' => '4011abc', 'rol' => 'estudiante'])
            ->assertSessionHasErrors(['dni' => 'El DNI solo puede tener números.']);
        $this->get('/equipo/create')->assertSee('maxlength="8"', false)->assertSee('data-solo-numeros', false);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_el_veterinario_restablece_la_contrasena_al_dni(): void
    {
        $this->actingAs($vet = $this->usuario('veterinario'));
        $otro = User::factory()->create(['rol' => 'estudiante', 'dni' => '555', 'password' => 'Personal123', 'debe_cambiar_password' => false]);

        $this->get('/equipo')->assertSee('Restablecer contraseña');
        $this->put("/equipo/{$otro->id}/restablecer-password")->assertRedirect();

        $otro->refresh();
        $this->assertTrue(Hash::check('555', $otro->password));
        $this->assertTrue($otro->debe_cambiar_password);

        // A sí mismo no (para eso está "Cambiar contraseña").
        $this->put("/equipo/{$vet->id}/restablecer-password")->assertForbidden();
    }

    public function test_la_migracion_convierte_los_administradores_en_veterinarios(): void
    {
        // Simula la base de producción antes del cambio: dos usuarios admin.
        DB::table('users')->insert([
            ['name' => 'Ayrton', 'dni' => '11111111', 'rol' => 'admin', 'activo' => true, 'password' => 'x', 'debe_cambiar_password' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Jorge', 'dni' => '22222222', 'rol' => 'admin', 'activo' => true, 'password' => 'x', 'debe_cambiar_password' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
        // Jorge ya estaba cargado como veterinario (sin usuario) con su DNI.
        Veterinario::create(['dni' => '22222222', 'nombre' => 'Jorge M.', 'catedra' => 'Clínica']);

        $migracion = require database_path('migrations/2026_11_09_000002_convertir_administradores_en_veterinarios.php');
        $migracion->up();

        $this->assertSame(0, User::where('rol', 'admin')->count());
        $ayrton = User::where('dni', '11111111')->first();
        $jorge = User::where('dni', '22222222')->first();
        $this->assertSame('veterinario', $ayrton->rol);
        $this->assertSame('Ayrton', $ayrton->veterinario->nombre);       // perfil nuevo
        $this->assertSame('Clínica', $jorge->veterinario->catedra);      // perfil existente vinculado
        $this->assertSame(2, Veterinario::count());                      // no duplicó a Jorge

        $migracion->up(); // correrla dos veces no duplica nada
        $this->assertSame(2, Veterinario::count());
    }
}
