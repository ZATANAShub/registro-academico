<?php

namespace Tests\Feature;

use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas de inicio de sesión, cierre de sesión y protección de rutas.
 * Se ejecutan en una base SQLite en memoria (ver phpunit.xml), nunca en MySQL.
 */
class SesionTest extends TestCase
{
    use RefreshDatabase;

    private function operador(): User
    {
        return User::factory()->create([
            'email' => 'operador@prueba.test',
            'password' => 'clave-de-prueba',
        ]);
    }

    public function test_la_contrasena_del_operador_se_guarda_con_hash(): void
    {
        $operador = $this->operador();

        $this->assertNotSame('clave-de-prueba', $operador->getRawOriginal('password'));
        $this->assertStringStartsWith('$2y$', $operador->getRawOriginal('password'));
    }

    public function test_iniciar_sesion_correctamente(): void
    {
        $this->operador();

        $this->post('/login', ['email' => 'operador@prueba.test', 'password' => 'clave-de-prueba'])
            ->assertRedirect(route('estudiantes.index'));

        $this->assertAuthenticated();
    }

    public function test_contrasena_incorrecta_no_inicia_sesion(): void
    {
        $this->operador();

        $this->from('/login')
            ->post('/login', ['email' => 'operador@prueba.test', 'password' => 'otra'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_acceder_sin_sesion_redirige_al_login(): void
    {
        $estudiante = Estudiante::factory()->create();

        $this->get('/')->assertRedirect('/estudiantes');
        $this->get('/estudiantes')->assertRedirect('/login');
        $this->get('/estudiantes/create')->assertRedirect('/login');
        $this->get("/estudiantes/{$estudiante->id}/edit")->assertRedirect('/login');
        $this->post('/estudiantes', [])->assertRedirect('/login');
        $this->delete("/estudiantes/{$estudiante->id}")->assertRedirect('/login');

        $this->assertDatabaseHas('estudiantes', ['id' => $estudiante->id]);
    }

    public function test_cerrar_sesion_y_volver_a_entrar_recupera_los_registros(): void
    {
        $this->operador();
        $estudiante = Estudiante::factory()->create(['nombre' => 'Laura Persistente']);

        $this->post('/login', ['email' => 'operador@prueba.test', 'password' => 'clave-de-prueba']);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $this->get('/estudiantes')->assertRedirect('/login');

        $this->post('/login', ['email' => 'operador@prueba.test', 'password' => 'clave-de-prueba']);
        $this->assertAuthenticated();
        $this->get('/estudiantes')->assertOk()->assertSee('Laura Persistente');
        $this->assertDatabaseHas('estudiantes', ['id' => $estudiante->id]);
    }
}
