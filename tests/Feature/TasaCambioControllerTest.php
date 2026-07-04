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
        TasaCambio::factory()->count(3)->create();
        $this->actingAs($this->admin);

        $response = $this->get('/tasas-cambio');

        $response->assertStatus(200);
        $response->assertViewHas('tasas');
    }

    public function test_store_crea_tasa()
    {
        $this->actingAs($this->admin);

        $response = $this->post('/tasas-cambio', [
            'tipo' => 'promedio',
            'moneda' => 'Bs',
            'monto' => 45.50,
            'fecha' => '2026-07-04',
        ]);

        $response->assertRedirect('/tasas-cambio');
        $this->assertDatabaseHas('tasa_cambios', ['monto' => 45.50]);
    }

    public function test_store_valida_campos()
    {
        $this->actingAs($this->admin);

        $response = $this->post('/tasas-cambio', []);

        $response->assertSessionHasErrors(['tipo', 'moneda', 'monto', 'fecha']);
    }
}
