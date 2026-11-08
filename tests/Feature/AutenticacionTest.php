<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Login, registro cerrado y cambio obligatorio de la contraseña inicial.
 */
class AutenticacionTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioNuevo(array $datos = []): User
    {
        // Como se crea desde /usuarios: contraseña = DNI y obligado a cambiarla.
        return User::factory()->create(array_merge([
            'dni' => '40111222',
            'password' => '40111222',
            'debe_cambiar_password' => true,
        ], $datos));
    }

    public function test_la_raiz_redirige_al_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_el_login_se_muestra_sin_link_de_registro(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertDontSee('Registrate');
    }

    public function test_login_correcto_con_dni_y_contrasena(): void
    {
        $user = User::factory()->create(['dni' => '123', 'password' => 'ClaveSegura1']);

        $this->post('/login', ['dni' => '123', 'password' => 'ClaveSegura1'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_incorrecto_muestra_error(): void
    {
        User::factory()->create(['dni' => '123', 'password' => 'ClaveSegura1']);

        $this->from('/login')->post('/login', ['dni' => '123', 'password' => 'mal'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['dni' => 'El DNI o la contraseña son incorrectos.']);

        $this->assertGuest();
    }

    public function test_bloquea_el_login_despues_de_5_intentos_fallidos(): void
    {
        User::factory()->create(['dni' => '123', 'password' => 'ClaveSegura1']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['dni' => '123', 'password' => 'mal']);
        }

        // Aunque la contraseña sea correcta, queda bloqueado por un minuto.
        $this->post('/login', ['dni' => '123', 'password' => 'ClaveSegura1'])
            ->assertSessionHasErrors('dni');
        $this->assertGuest();

        $this->travel(61)->seconds();

        $this->post('/login', ['dni' => '123', 'password' => 'ClaveSegura1']);
        $this->assertAuthenticated();
    }

    public function test_no_existe_registro_publico(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/usuarios', ['name' => 'X', 'dni' => '1', 'rol' => 'admin'])->assertRedirect(route('login'));

        $this->assertDatabaseCount('users', 0);
    }

    public function test_con_la_contrasena_inicial_solo_puede_ir_a_cambiarla(): void
    {
        $this->actingAs($this->usuarioNuevo());

        foreach (['/dashboard', '/mascotas', '/propietarios', '/usuarios'] as $url) {
            $this->get($url)->assertRedirect(route('password.edit'));
        }

        $this->get('/password')->assertOk();
    }

    public function test_validaciones_del_cambio_de_contrasena(): void
    {
        $this->actingAs($this->usuarioNuevo());

        $casos = [
            'password_actual' => ['password_actual' => 'mal', 'password' => 'NuevaClave1', 'password_confirmation' => 'NuevaClave1'],
            'password' => ['password_actual' => '40111222', 'password' => '40111222', 'password_confirmation' => '40111222'], // igual al DNI
        ];

        foreach ($casos as $campo => $datos) {
            $this->from('/password')->put('/password', $datos)->assertSessionHasErrors($campo);
        }

        $this->from('/password')->put('/password', ['password_actual' => '40111222', 'password' => 'corta', 'password_confirmation' => 'corta'])
            ->assertSessionHasErrors('password');
        $this->from('/password')->put('/password', ['password_actual' => '40111222', 'password' => 'NuevaClave1', 'password_confirmation' => 'Otra12345'])
            ->assertSessionHasErrors('password');
    }

    public function test_cambiar_la_contrasena_habilita_el_sistema(): void
    {
        $user = $this->usuarioNuevo();
        $this->actingAs($user);

        $this->put('/password', [
            'password_actual' => '40111222',
            'password' => 'NuevaClave1',
            'password_confirmation' => 'NuevaClave1',
        ])->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertFalse($user->debe_cambiar_password);
        $this->assertTrue(Hash::check('NuevaClave1', $user->password));
        $this->get('/dashboard')->assertOk();
    }

    public function test_el_login_limita_dni_a_8_y_contrasena_a_20_caracteres(): void
    {
        $this->get('/login')
            ->assertSee('maxlength="8"', false)
            ->assertSee('maxlength="20"', false);

        $this->post('/login', ['dni' => '123456789', 'password' => 'x'])
            ->assertSessionHasErrors(['dni' => 'El DNI puede tener como máximo 8 caracteres.']);
        $this->post('/login', ['dni' => '12345678', 'password' => str_repeat('a', 21)])
            ->assertSessionHasErrors(['password' => 'La contraseña puede tener como máximo 20 caracteres.']);

        // Justo en el límite funciona.
        User::factory()->create(['dni' => '12345678', 'password' => str_repeat('a', 20)]);
        $this->post('/login', ['dni' => '12345678', 'password' => str_repeat('a', 20)])
            ->assertRedirect(route('dashboard'));
    }

    public function test_la_nueva_contrasena_tiene_entre_8_y_20_caracteres(): void
    {
        $this->actingAs($this->usuarioNuevo());

        $this->get('/password')->assertSee('maxlength="20"', false);

        $larga = str_repeat('a', 21);
        $this->put('/password', ['password_actual' => '40111222', 'password' => $larga, 'password_confirmation' => $larga])
            ->assertSessionHasErrors(['password' => 'La nueva contraseña puede tener como máximo 20 caracteres.']);

        $justa = str_repeat('a', 20);
        $this->put('/password', ['password_actual' => '40111222', 'password' => $justa, 'password_confirmation' => $justa])
            ->assertRedirect(route('dashboard'));
    }

    public function test_el_seeder_crea_el_admin_inicial_una_sola_vez(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('users', 1);
        $admin = User::first();
        $this->assertSame('00000000', $admin->dni);
        $this->assertTrue($admin->esAdmin());
        $this->assertTrue($admin->debe_cambiar_password);
    }
}
