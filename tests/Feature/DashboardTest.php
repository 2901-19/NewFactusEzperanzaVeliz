<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Factura;
use App\Models\TasaCambio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private TasaCambio $tasa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['rol' => 'admin']);
        $this->tasa = TasaCambio::factory()->create([
            'tipo' => 'promedio',
            'monto' => 50.00,
            'fecha' => now()->format('Y-m-d'),
        ]);
    }

    public function test_dashboard_muestra_datos()
    {
        Cliente::factory()->count(5)->create();
        $categoria = Categoria::factory()->create();
        Producto::factory()->count(10)->create(['categoria_id' => $categoria->id]);

        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas(['totalProductos', 'totalClientes', 'ventasHoy', 'totalHoyBs', 'totalHoyUsd', 'creditosPendientes']);
    }

    public function test_dashboard_muestra_ceros_sin_datos()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('totalProductos', function ($value) {
            return $value == 0;
        });
    }
}
