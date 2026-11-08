<?php

namespace Tests\Feature;

use App\Models\Estudiante;
use App\Models\FichaClinica;
use App\Models\Mascota;
use App\Models\Propietario;
use App\Models\User;
use App\Models\Veterinario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Roles: admin (todo), veterinario (todo menos usuarios), estudiante (no elimina).
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

    public function test_solo_el_admin_gestiona_usuarios(): void
    {
        $this->actingAs($this->usuario('admin'))->get('/usuarios')->assertOk();

        foreach (['veterinario', 'estudiante'] as $rol) {
            $this->actingAs($this->usuario($rol));
            $this->get('/usuarios')->assertForbidden();
            $this->get('/usuarios/create')->assertForbidden();
            $this->post('/usuarios', ['name' => 'X', 'dni' => '5', 'rol' => 'admin'])->assertForbidden();
        }
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

    public function test_el_admin_crea_veterinario_y_estudiante_a_cargo(): void
    {
        $this->actingAs($this->usuario('admin'));

        $this->post('/usuarios', ['name' => 'Dra. Perez', 'dni' => '20111222', 'rol' => 'veterinario', 'catedra' => 'Clínica'])
            ->assertRedirect(route('usuarios.index'));
        $vet = Veterinario::firstOrFail();
        $this->assertSame('veterinario', $vet->user->rol);
        $this->assertSame('Clínica', $vet->catedra);

        $this->post('/usuarios', ['name' => 'Juan', 'dni' => '40111222', 'rol' => 'estudiante', 'veterinario_id' => $vet->id])
            ->assertRedirect(route('usuarios.index'));
        $est = Estudiante::firstOrFail();
        $this->assertSame($vet->id, $est->veterinario_id);
        $this->assertTrue($est->user->debe_cambiar_password);
        $this->assertTrue(Hash::check('40111222', $est->user->password));

        // El admin sigue logueado como él mismo (antes se cambiaba al usuario creado).
        $this->assertTrue(auth()->user()->esAdmin());
    }

    public function test_alta_de_usuario_rechaza_rol_invalido_y_dni_repetido(): void
    {
        $this->actingAs($admin = $this->usuario('admin'));

        $this->post('/usuarios', ['name' => 'X', 'dni' => '9', 'rol' => 'superadmin'])->assertSessionHasErrors('rol');
        $this->post('/usuarios', ['name' => 'X', 'dni' => $admin->dni, 'rol' => 'estudiante'])->assertSessionHasErrors('dni');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_el_admin_restablece_la_contrasena_al_dni(): void
    {
        $this->actingAs($this->usuario('admin'));
        $otro = User::factory()->create(['dni' => '555', 'password' => 'Personal123', 'debe_cambiar_password' => false]);

        $this->put("/usuarios/{$otro->id}/restablecer-password")->assertRedirect();

        $otro->refresh();
        $this->assertTrue(Hash::check('555', $otro->password));
        $this->assertTrue($otro->debe_cambiar_password);
    }
}
