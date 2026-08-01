<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\TasaCambio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TasaCambioControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['rol' => 'admin']);
    }

    public function test_index_muestra_tasas()
    {
        $this->seed(\Database\Seeders\PermisoSeeder::class);
        TasaCambio::factory()->create(['tipo' => 'promedio']);
        TasaCambio::factory()->create(['tipo' => 'dolar']);
        TasaCambio::factory()->create(['tipo' => 'bcv']);
        $this->actingAs($this->admin);

        $response = $this->get('/tasas-cambio');

        $response->assertStatus(200);
        $response->assertViewHas('tasas');
    }

    public function test_actualizar_guarda_tasa()
    {
        $this->seed(\Database\Seeders\PermisoSeeder::class);
        $this->actingAs($this->admin);

        $response = $this->post('/tasas-cambio/actualizar', [
            'tipo' => 'promedio',
            'monto' => 45.50,
        ]);

        $response->assertRedirect('/tasas-cambio');
        $this->assertDatabaseHas('tasa_cambios', ['tipo' => 'promedio', 'monto' => 45.50]);
    }

    public function test_actualizar_valida_campos()
    {
        $this->seed(\Database\Seeders\PermisoSeeder::class);
        $this->actingAs($this->admin);

        $response = $this->post('/tasas-cambio/actualizar', []);

        $response->assertSessionHasErrors(['tipo', 'monto']);
    }

    public function test_actualizar_rechaza_monto_cero_o_negativo()
    {
        $this->seed(\Database\Seeders\PermisoSeeder::class);
        $this->actingAs($this->admin);

        $response = $this->post('/tasas-cambio/actualizar', [
            'tipo' => 'promedio',
            'monto' => 0,
        ]);

        $response->assertSessionHasErrors(['monto']);
        $this->assertDatabaseCount('tasa_cambios', 0);
    }
}
