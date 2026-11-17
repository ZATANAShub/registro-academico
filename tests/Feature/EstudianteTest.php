<?php

namespace Tests\Feature;

use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas del CRUD de estudiantes con las reglas de validación del Encuentro 16.
 * Se ejecutan en una base SQLite en memoria (ver phpunit.xml), nunca en MySQL.
 */
class EstudianteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Todas estas pruebas se hacen con el operador autenticado.
        $this->actingAs(User::factory()->create());
    }

    private function datosValidos(array $cambios = []): array
    {
        return array_merge([
            'nombre' => 'Ana Prueba',
            'correo' => 'ana.prueba@correo.test',
            'edad' => 20,
            'programa' => 'Ingeniería Informática',
        ], $cambios);
    }

    public function test_registrar_estudiante_valido(): void
    {
        $this->post('/estudiantes', $this->datosValidos())
            ->assertRedirect('/estudiantes')
            ->assertSessionHas('exito');

        $this->assertDatabaseHas('estudiantes', $this->datosValidos());
    }

    public function test_registrar_correo_duplicado_es_rechazado(): void
    {
        Estudiante::factory()->create(['correo' => 'ana.prueba@correo.test']);

        $this->post('/estudiantes', $this->datosValidos())->assertSessionHasErrors('correo');

        $this->assertDatabaseCount('estudiantes', 1);
    }

    public function test_edad_15_es_rechazada(): void
    {
        $this->post('/estudiantes', $this->datosValidos(['edad' => 15]))->assertSessionHasErrors('edad');

        $this->assertDatabaseCount('estudiantes', 0);
    }

    public function test_edad_no_numerica_es_rechazada(): void
    {
        $this->post('/estudiantes', $this->datosValidos(['edad' => 'veinte']))->assertSessionHasErrors('edad');

        $this->assertDatabaseCount('estudiantes', 0);
    }

    public function test_programa_inexistente_es_rechazado(): void
    {
        $this->post('/estudiantes', $this->datosValidos(['programa' => 'Medicina Espacial']))
            ->assertSessionHasErrors('programa');

        $this->assertDatabaseCount('estudiantes', 0);
    }

    public function test_limites_de_nombre_correo_y_edad(): void
    {
        $this->post('/estudiantes', $this->datosValidos(['nombre' => 'A']))->assertSessionHasErrors('nombre');
        $this->post('/estudiantes', $this->datosValidos(['nombre' => str_repeat('a', 81)]))->assertSessionHasErrors('nombre');
        $this->post('/estudiantes', $this->datosValidos(['correo' => 'no-es-correo']))->assertSessionHasErrors('correo');
        $this->post('/estudiantes', $this->datosValidos(['correo' => str_repeat('a', 110).'@correo.test']))->assertSessionHasErrors('correo');
        $this->post('/estudiantes', $this->datosValidos(['edad' => 101]))->assertSessionHasErrors('edad');
        $this->post('/estudiantes', $this->datosValidos(['edad' => 20.5]))->assertSessionHasErrors('edad');
        $this->post('/estudiantes', [])->assertSessionHasErrors(['nombre', 'correo', 'edad', 'programa']);

        $this->assertDatabaseCount('estudiantes', 0);

        // Los valores en el borde sí se aceptan.
        $this->post('/estudiantes', $this->datosValidos(['nombre' => 'Al', 'edad' => 16]))->assertSessionHasNoErrors();
        $this->post('/estudiantes', $this->datosValidos(['nombre' => str_repeat('b', 80), 'correo' => 'otro@correo.test', 'edad' => 100]))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('estudiantes', 2);
    }

    public function test_buscar_estudiante_existente(): void
    {
        Estudiante::factory()->create(['nombre' => 'Carlos Rojas']);
        Estudiante::factory()->create(['nombre' => 'María Gómez']);

        $this->get('/estudiantes?buscar=Carlos')
            ->assertOk()
            ->assertSee('Carlos Rojas')
            ->assertDontSee('María Gómez');
    }

    public function test_buscar_estudiante_inexistente_muestra_resultado_vacio(): void
    {
        Estudiante::factory()->create(['nombre' => 'Carlos Rojas']);

        $this->get('/estudiantes?buscar=Zacarias')
            ->assertOk()
            ->assertSee('No se encontraron estudiantes')
            ->assertDontSee('Carlos Rojas');
    }

    public function test_editar_actualiza_la_misma_fila(): void
    {
        $estudiante = Estudiante::factory()->create();
        $otro = Estudiante::factory()->create();

        $this->put("/estudiantes/{$estudiante->id}", $this->datosValidos(['nombre' => 'Nombre Editado']))
            ->assertRedirect('/estudiantes');

        $this->assertDatabaseCount('estudiantes', 2);
        $this->assertDatabaseHas('estudiantes', ['id' => $estudiante->id, 'nombre' => 'Nombre Editado']);
        $this->assertDatabaseHas('estudiantes', ['id' => $otro->id, 'nombre' => $otro->nombre]);
    }

    public function test_editar_conservando_su_propio_correo(): void
    {
        $estudiante = Estudiante::factory()->create(['correo' => 'mismo@correo.test']);

        $this->put("/estudiantes/{$estudiante->id}", $this->datosValidos(['correo' => 'mismo@correo.test', 'edad' => 30]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('estudiantes', ['id' => $estudiante->id, 'correo' => 'mismo@correo.test', 'edad' => 30]);
    }

    public function test_editar_usando_el_correo_de_otro_es_rechazado(): void
    {
        Estudiante::factory()->create(['correo' => 'ocupado@correo.test']);
        $estudiante = Estudiante::factory()->create();

        $this->put("/estudiantes/{$estudiante->id}", $this->datosValidos(['correo' => 'ocupado@correo.test']))
            ->assertSessionHasErrors('correo');
    }

    public function test_editar_identificador_inexistente_no_modifica_otra_fila(): void
    {
        $estudiante = Estudiante::factory()->create();

        $this->get('/estudiantes/9999/edit')->assertNotFound();
        $this->put('/estudiantes/9999', $this->datosValidos())->assertNotFound();

        $this->assertDatabaseCount('estudiantes', 1);
        $this->assertDatabaseHas('estudiantes', ['id' => $estudiante->id, 'nombre' => $estudiante->nombre]);
    }

    public function test_listado_tiene_confirmacion_y_cancelar_no_envia_formulario(): void
    {
        $estudiante = Estudiante::factory()->create();

        // El botón "Eliminar" solo abre el modal; "Cancelar" solo lo cierra (data-bs-dismiss).
        $this->get('/estudiantes')
            ->assertOk()
            ->assertSee('data-bs-target="#eliminar-'.$estudiante->id.'"', false)
            ->assertSee('Confirmar eliminación')
            ->assertSee('<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>', false);

        $this->assertDatabaseHas('estudiantes', ['id' => $estudiante->id]);
    }

    public function test_confirmar_eliminacion_borra_solo_la_fila_seleccionada(): void
    {
        $borrar = Estudiante::factory()->create();
        $conservar = Estudiante::factory()->create();

        $this->delete("/estudiantes/{$borrar->id}")->assertRedirect('/estudiantes');

        $this->assertDatabaseMissing('estudiantes', ['id' => $borrar->id]);
        $this->assertDatabaseHas('estudiantes', ['id' => $conservar->id]);
    }

    public function test_los_datos_se_escapan_en_html(): void
    {
        Estudiante::factory()->create(['nombre' => '<script>alert(1)</script>']);

        $this->get('/estudiantes')
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }
}
