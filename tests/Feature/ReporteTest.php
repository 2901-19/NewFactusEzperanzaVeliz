<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Factura;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\TasaCambio;
use App\Models\Categoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private TasaCambio $tasa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['rol' => 'admin']);
        $this->tasa = TasaCambio::factory()->create([
            'tipo' => 'promedio',
            'monto' => 50.00,
            'fecha' => now()->format('Y-m-d'),
        ]);
    }

    public function test_reporte_facturas_muestra_datos()
    {
        Factura::factory()->count(5)->create();
        $this->actingAs($this->admin);

        $response = $this->get('/reportes/facturas');

        $response->assertStatus(200);
        $response->assertViewHas('facturas');
    }

    public function test_reporte_stock_muestra_datos()
    {
        $categoria = Categoria::factory()->create();
        Producto::factory()->count(5)->create(['categoria_id' => $categoria->id]);
        $this->actingAs($this->admin);

        $response = $this->get('/reportes/stock');

        $response->assertStatus(200);
        $response->assertViewHas('productos');
    }

    public function test_reporte_facturas_con_filtros()
    {
        Factura::factory()->create(['fecha_venta' => '2026-01-15']);
        Factura::factory()->create(['fecha_venta' => '2026-06-20']);
        $this->actingAs($this->admin);

        $response = $this->get('/reportes/facturas?desde=2026-01-01&hasta=2026-03-31');

        $response->assertStatus(200);
    }
}
