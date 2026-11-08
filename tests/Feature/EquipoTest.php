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
 * Equipo: el veterinario da de alta y de baja a veterinarios y estudiantes.
 */
class EquipoTest extends TestCase
{
    use RefreshDatabase;

    private User $vet;
    private Veterinario $perfilVet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->vet = User::factory()->create(['rol' => 'veterinario', 'name' => 'Dra. Perez', 'dni' => '20111222']);
        $this->perfilVet = Veterinario::create(['user_id' => $this->vet->id, 'dni' => '20111222', 'nombre' => 'Dra. Perez']);
    }

    private function nuevoEstudiante(string $nombre = 'Juan', string $dni = '40111222'): User
    {
        $this->actingAs($this->vet)->post('/equipo', ['rol' => 'estudiante', 'name' => $nombre, 'dni' => $dni, 'veterinario_id' => $this->perfilVet->id]);

        return User::where('dni', $dni)->firstOrFail();
    }

    public function test_la_pestana_equipo_aparece_solo_para_el_veterinario_activo(): void
    {
        $this->actingAs($this->vet)->get('/dashboard')->assertSee(route('equipo.index'));
        $this->get('/equipo')->assertOk()->assertSee('Estudiantes')->assertSee('Veterinarios');

        // En la vista de estudiante no la tiene.
        $this->post('/perfil/vista');
        $this->get('/dashboard')->assertDontSee(route('equipo.index'));
        $this->get('/equipo')->assertForbidden();

        foreach (['admin', 'estudiante'] as $rol) {
            $this->actingAs(User::factory()->create(['rol' => $rol]))->get('/equipo')->assertForbidden();
        }
    }

    public function test_el_veterinario_da_de_alta_un_estudiante_a_su_cargo(): void
    {
        $this->actingAs($this->vet)->get('/equipo/create?rol=estudiante')
            ->assertOk()
            ->assertSee('Dra. Perez (vos)')
            ->assertSee('data-solo-numeros', false);

        $this->post('/equipo', ['rol' => 'estudiante', 'name' => 'Juan Estudiante', 'dni' => '40111222', 'veterinario_id' => $this->perfilVet->id])
            ->assertRedirect(route('equipo.index'))
            ->assertSessionHas('success');

        $user = User::where('dni', '40111222')->firstOrFail();
        $this->assertSame('estudiante', $user->rol);
        $this->assertTrue($user->activo);
        $this->assertTrue($user->debe_cambiar_password);
        $this->assertTrue(Hash::check('40111222', $user->password));
        $this->assertSame($this->perfilVet->id, $user->estudiante->veterinario_id);
        $this->get('/perfil')->assertSee('Juan Estudiante'); // aparece en "Estudiantes a cargo"
    }

    public function test_el_veterinario_da_de_alta_otro_veterinario(): void
    {
        $this->actingAs($this->vet)->post('/equipo', ['rol' => 'veterinario', 'name' => 'Dr. Gomez', 'dni' => '25111222', 'catedra' => 'Cirugía'])
            ->assertRedirect(route('equipo.index'));

        $nuevo = User::where('dni', '25111222')->firstOrFail();
        $this->assertSame('veterinario', $nuevo->rol);
        $this->assertSame('Cirugía', $nuevo->veterinario->catedra);
    }

    public function test_el_veterinario_no_puede_crear_administradores_ni_dni_invalidos(): void
    {
        $this->actingAs($this->vet);

        $this->post('/equipo', ['rol' => 'admin', 'name' => 'Hack', 'dni' => '5'])->assertSessionHasErrors('rol');
        $this->post('/equipo', ['rol' => 'estudiante', 'name' => 'X', 'dni' => '4011a'])->assertSessionHasErrors(['dni' => 'El DNI solo puede tener números.']);
        $this->post('/equipo', ['rol' => 'estudiante', 'name' => 'X', 'dni' => '20111222'])->assertSessionHasErrors(['dni' => 'Ya existe una persona registrada con ese DNI.']);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_dar_de_baja_impide_ingresar_y_conserva_el_historial(): void
    {
        $juan = $this->nuevoEstudiante();
        $juan->update(['password' => 'ClaveJuan1', 'debe_cambiar_password' => false]);

        // Juan atendió una consulta
        Propietario::create(['dni' => '30111222', 'nombre' => 'Ana', 'apellido' => 'G', 'telefono' => '1', 'calle' => 'J', 'altura' => '1', 'localidad' => 'C']);
        $m = Mascota::create(['propietario_dni' => '30111222', 'nombre' => 'Firu', 'especie' => 'Perro', 'raza' => 'M', 'sexo' => 'Macho', 'peso' => 10, 'edad' => 2, 'tipo_pelaje' => 'C']);
        $ficha = FichaClinica::create(['mascota_id' => $m->id, 'fecha_consulta' => '2026-11-01', 'motivo_consulta' => 'X', 'estado' => 'Saludable']);
        $ficha->estudiantes()->attach($juan->estudiante->id, ['fecha_atencion' => '2026-11-01']);

        $this->actingAs($this->vet)->put("/equipo/{$juan->id}/baja")->assertRedirect()->assertSessionHas('success');

        $this->assertFalse($juan->fresh()->activo);
        $this->assertNotNull(User::find($juan->id));                                     // no se borró
        $this->assertSame(1, $ficha->estudiantes()->count());                              // historial intacto
        $this->get("/fichas/{$ficha->id}")->assertSee('Juan');
        $this->get('/equipo')->assertSee('Dado de baja')->assertSee('Dar de alta');

        // Ya no aparece para elegir en una ficha nueva, pero sí al editar la que atendió.
        $this->get("/mascotas/{$m->id}/fichas/create")->assertDontSee('estudiante_' . $juan->estudiante->id);
        $this->get("/fichas/{$ficha->id}/edit")->assertSee('estudiante_' . $juan->estudiante->id);

        // No puede iniciar sesión.
        auth()->logout();
        $this->post('/login', ['dni' => '40111222', 'password' => 'ClaveJuan1'])
            ->assertSessionHasErrors(['dni' => 'Tu usuario fue dado de baja. Consultá con un veterinario o un administrador.']);
        $this->assertGuest();
    }

    public function test_si_tenia_la_sesion_abierta_se_le_cierra(): void
    {
        $juan = $this->nuevoEstudiante();
        $juan->update(['debe_cambiar_password' => false, 'activo' => false]);

        $this->actingAs($juan)->get('/dashboard')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('dni');
        $this->assertGuest();
    }

    public function test_volver_a_dar_de_alta(): void
    {
        $juan = $this->nuevoEstudiante();
        $this->put("/equipo/{$juan->id}/baja");

        $this->put("/equipo/{$juan->id}/alta")->assertRedirect()->assertSessionHas('success');

        $this->assertTrue($juan->fresh()->activo);
        $this->get('/mascotas/create'); // sigue funcionando todo
        $this->assertSame(1, Estudiante::activos()->count());
    }

    public function test_dar_de_baja_un_veterinario_avisa_sus_estudiantes_a_cargo_y_lo_saca_de_los_selectores(): void
    {
        $this->actingAs($this->vet)->post('/equipo', ['rol' => 'veterinario', 'name' => 'Dr. Gomez', 'dni' => '25111222']);
        $gomez = User::where('dni', '25111222')->first();
        Estudiante::create(['dni' => '41000000', 'nombre' => 'Lucía', 'veterinario_id' => $gomez->veterinario->id]);

        $this->put("/equipo/{$gomez->id}/baja")
            ->assertSessionHas('success', fn ($m) => str_contains($m, 'Tenía 1 estudiante a cargo'));

        // (se mira el <option> porque el mensaje de la baja también nombra a Gomez)
        $this->get('/propietarios/create')->assertDontSee('>Dr. Gomez</option>', false)->assertSee('>Dra. Perez</option>', false);
        $this->get('/equipo/create')->assertDontSee('Dr. Gomez</option>', false);
    }

    public function test_nadie_se_puede_dar_de_baja_a_si_mismo(): void
    {
        $this->actingAs($this->vet)->put("/equipo/{$this->vet->id}/baja")->assertForbidden();
        $this->assertTrue($this->vet->fresh()->activo);

        $admin = User::factory()->create(['rol' => 'admin']);
        $this->actingAs($admin)->put("/equipo/{$admin->id}/baja")->assertForbidden();
    }

    public function test_el_veterinario_no_puede_dar_de_baja_a_un_administrador(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($this->vet)->put("/equipo/{$admin->id}/baja")->assertForbidden();
        $this->assertTrue($admin->fresh()->activo);
    }

    public function test_el_estudiante_no_puede_dar_de_baja_a_nadie(): void
    {
        $juan = $this->nuevoEstudiante();
        $juan->update(['debe_cambiar_password' => false]);

        $this->actingAs($juan)->put("/equipo/{$this->vet->id}/baja")->assertForbidden();
        $this->post('/equipo', ['rol' => 'estudiante', 'name' => 'X', 'dni' => '9'])->assertForbidden();
    }

    public function test_el_admin_da_de_baja_y_de_alta_desde_usuarios(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $otroAdmin = User::factory()->create(['rol' => 'admin', 'name' => 'Otro Admin']);

        $this->actingAs($admin)->get('/usuarios')->assertSee('Dar de baja')->assertSee('Activo');
        $this->put("/equipo/{$otroAdmin->id}/baja")->assertRedirect();
        $this->assertFalse($otroAdmin->fresh()->activo);
        $this->get('/usuarios')->assertSee('Dado de baja');
        $this->put("/equipo/{$otroAdmin->id}/alta");
        $this->assertTrue($otroAdmin->fresh()->activo);
    }
}
