<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\TasaCambio;
use App\Models\Configuracion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TasaCambioControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermisoSeeder::class);
        $this->admin = User::factory()->create(['rol' => 'admin']);
        $this->actingAs($this->admin);
    }

    public function test_index_muestra_tasas()
    {
        TasaCambio::factory()->create(['tipo' => 'promedio']);
        TasaCambio::factory()->create(['tipo' => 'dolar']);
        TasaCambio::factory()->create(['tipo' => 'bcv']);

        $response = $this->get('/tasas-cambio');

        $response->assertStatus(200);
        $response->assertViewHas('tasas');
        $response->assertViewHas('tasaReferencia', 'bcv');
    }

    public function test_actualizar_guarda_tasa()
    {
        TasaCambio::factory()->create(['tipo' => 'promedio', 'nombre' => 'Promedio']);

        $response = $this->post('/tasas-cambio/actualizar', [
            'tipo' => 'promedio',
            'monto' => 45.50,
        ]);

        $response->assertRedirect('/tasas-cambio');
        $this->assertDatabaseHas('tasa_cambios', ['tipo' => 'promedio', 'monto' => 45.50]);
    }

    public function test_actualizar_valida_campos()
    {
        $response = $this->post('/tasas-cambio/actualizar', []);

        $response->assertSessionHasErrors(['tipo', 'monto']);
    }

    public function test_actualizar_rechaza_monto_cero_o_negativo()
    {
        $response = $this->post('/tasas-cambio/actualizar', [
            'tipo' => 'promedio',
            'monto' => 0,
        ]);

        $response->assertSessionHasErrors(['monto']);
        $this->assertDatabaseCount('tasa_cambios', 0);
    }

    public function test_crear_nueva_tasa()
    {
        $response = $this->post('/tasas-cambio', [
            'tipo' => 'zelle',
            'nombre' => 'Dólar Zelle',
            'monto' => 62.75,
        ]);

        $response->assertRedirect('/tasas-cambio');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('tasa_cambios', [
            'tipo' => 'zelle',
            'nombre' => 'Dólar Zelle',
            'monto' => 62.75,
            'activo' => true,
        ]);
    }

    public function test_crear_rechaza_tipo_duplicado()
    {
        TasaCambio::factory()->create(['tipo' => 'promedio']);

        $response = $this->post('/tasas-cambio', [
            'tipo' => 'promedio',
            'nombre' => 'Promedio',
            'monto' => 60.00,
        ]);

        $response->assertSessionHasErrors(['tipo']);
        $this->assertDatabaseCount('tasa_cambios', 1);
    }

    public function test_crear_rechaza_tipo_invalido()
    {
        $response = $this->post('/tasas-cambio', [
            'tipo' => 'Dolar Zelle',
            'nombre' => 'Dólar Zelle',
            'monto' => 62.75,
        ]);

        $response->assertSessionHasErrors(['tipo']);
    }

    public function test_crear_rechaza_monto_no_positivo()
    {
        $response = $this->post('/tasas-cambio', [
            'tipo' => 'zelle',
            'nombre' => 'Dólar Zelle',
            'monto' => 0,
        ]);

        $response->assertSessionHasErrors(['monto']);
        $this->assertDatabaseCount('tasa_cambios', 0);
    }

    public function test_toggle_desactiva_tasa()
    {
        $tasa = TasaCambio::factory()->create(['tipo' => 'dolar', 'activo' => true]);

        $response = $this->post("/tasas-cambio/{$tasa->id}/toggle");

        $response->assertRedirect('/tasas-cambio');
        $this->assertDatabaseHas('tasa_cambios', ['id' => $tasa->id, 'activo' => false]);
    }

    public function test_toggle_reactiva_tasa()
    {
        $tasa = TasaCambio::factory()->create(['tipo' => 'dolar', 'activo' => false]);

        $response = $this->post("/tasas-cambio/{$tasa->id}/toggle");

        $response->assertRedirect('/tasas-cambio');
        $this->assertDatabaseHas('tasa_cambios', ['id' => $tasa->id, 'activo' => true]);
    }

    public function test_toggle_bloquea_desactivar_referencia()
    {
        $tasa = TasaCambio::factory()->create(['tipo' => 'bcv', 'activo' => true]);
        Configuracion::updateOrCreate(['clave' => 'tasa_referencia'], ['valor' => 'bcv']);

        $response = $this->post("/tasas-cambio/{$tasa->id}/toggle");

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('tasa_cambios', ['id' => $tasa->id, 'activo' => true]);
    }

    public function test_fijar_referencia_guarda_configuracion()
    {
        TasaCambio::factory()->create(['tipo' => 'dolar', 'activo' => true]);

        $response = $this->post('/tasas-cambio/referencia', ['referencia' => 'dolar']);

        $response->assertRedirect('/tasas-cambio');
        $this->assertDatabaseHas('configuraciones', ['clave' => 'tasa_referencia', 'valor' => 'dolar']);
    }

    public function test_fijar_referencia_rechaza_tasa_inactiva()
    {
        TasaCambio::factory()->create(['tipo' => 'dolar', 'activo' => false]);

        $response = $this->post('/tasas-cambio/referencia', ['referencia' => 'dolar']);

        $response->assertSessionHasErrors('referencia');
        $this->assertDatabaseMissing('configuraciones', ['clave' => 'tasa_referencia', 'valor' => 'dolar']);
    }

    public function test_fijar_referencia_rechaza_tipo_inexistente()
    {
        $response = $this->post('/tasas-cambio/referencia', ['referencia' => 'inexistente']);

        $response->assertSessionHasErrors('referencia');
    }
}