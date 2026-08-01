<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\TasaCambio;
use App\Models\Impuesto;
use App\Models\Factura;
use App\Models\ItemFactura;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $cajero;
    private Cliente $cliente;
    private Producto $producto;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermisoSeeder::class);
        $this->cajero = User::factory()->create(['rol' => 'admin']);
        $this->cliente = Cliente::factory()->create();
        $categoria = Categoria::factory()->create();
        $this->producto = Producto::factory()->create([
            'categoria_id' => $categoria->id,
            'nombre' => 'Producto Test',
            'unidades_por_paquete' => 12,
            'stock_paquetes' => 10,
            'stock_unidades' => 5,
            'precio_unitario_usd' => 10.00,
            'precio_mayor_usd' => 8.00,
            'costo_usd' => 5.00,
            'margen_detal' => 0,
            'margen_mayor' => 0,
            'tiene_iva' => true,
            'fuente_tasa' => 'promedio',
            'estado' => 'disponible',
        ]);
        TasaCambio::factory()->create(['tipo' => 'promedio', 'monto' => 50.00, 'fecha' => '2026-07-04']);
        TasaCambio::factory()->create(['tipo' => 'bcv', 'monto' => 45.00, 'fecha' => '2026-07-04']);
        Impuesto::factory()->create(['porcentaje' => 16.00, 'fecha' => '2026-07-04']);
    }

    private function crearFactura(array $datos = []): Factura
    {
        return Factura::factory()->create(array_merge([
            'user_id' => $this->cajero->id,
            'cliente_id' => $this->cliente->id,
            'metodo_pago' => 'efectivo',
            'subtotal_bs' => 500,
            'iva_bs' => 80,
            'total_bs' => 580,
            'total_usd' => 12.89,
            'fecha_venta' => '2026-07-10',
        ], $datos));
    }

    private function crearItem(Factura $factura, array $datos = []): ItemFactura
    {
        return ItemFactura::create(array_merge([
            'factura_id' => $factura->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 2,
            'tipo_venta' => 'unitario',
            'precio_unitario_usd' => 10.00,
            'precio_unitario_bs' => 500.00,
            'subtotal' => 1000.00,
        ], $datos));
    }

    public function test_facturas_muestra_kpis_y_desglose()
    {
        $this->actingAs($this->cajero);

        $this->crearFactura();
        $this->crearFactura([
            'metodo_pago' => 'mixto',
            'detalle_pago' => [
                ['metodo' => 'efectivo', 'monto' => 400.00],
                ['metodo' => 'punto', 'monto' => 180.00],
            ],
        ]);

        $response = $this->get('/reportes/facturas?desde=2026-07-01&hasta=2026-07-31');

        $response->assertStatus(200);
        $response->assertViewHas(['facturas', 'kpis', 'desglose']);

        $kpis = $response->viewData('kpis');
        $this->assertEquals(1160.0, $kpis['total_bs']['valor']);
        $this->assertEquals(2, $kpis['cantidad']['valor']);
        $this->assertEquals(580.0, $kpis['ticket_promedio']['valor']);

        $desglose = $response->viewData('desglose');
        $this->assertEquals(980.0, $desglose['efectivo']);
        $this->assertEquals(180.0, $desglose['punto']);
    }

    public function test_facturas_filtra_por_metodo_y_excluye_anuladas()
    {
        $this->actingAs($this->cajero);

        $this->crearFactura();
        $this->crearFactura(['metodo_pago' => 'pago_movil']);
        $this->crearFactura(['estado' => 'anulada']);

        $response = $this->get('/reportes/facturas?desde=2026-07-01&hasta=2026-07-31&metodo_pago=pago_movil');

        $response->assertStatus(200);
        $facturas = $response->viewData('facturas');
        $kpis = $response->viewData('kpis');

        $this->assertCount(1, $facturas);
        $this->assertEquals(580.0, $kpis['total_bs']['valor']);
        $this->assertEquals(1, $kpis['cantidad']['valor']);
    }

    public function test_facturas_compara_con_periodo_anterior()
    {
        $this->actingAs($this->cajero);

        $this->crearFactura(['fecha_venta' => '2026-06-05', 'total_bs' => 500, 'subtotal_bs' => 450, 'iva_bs' => 50]);
        $this->crearFactura();

        $response = $this->get('/reportes/facturas?desde=2026-07-01&hasta=2026-07-31');

        $kpis = $response->viewData('kpis');
        $this->assertEquals(580.0, $kpis['total_bs']['valor']);
        $this->assertEquals(16.0, $kpis['total_bs']['variacion']);
    }

    public function test_facturas_export_csv()
    {
        $this->actingAs($this->cajero);

        $factura = $this->crearFactura();

        $response = $this->get('/reportes/facturas?desde=2026-07-01&hasta=2026-07-31&export=csv');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Correlativo;Cliente;Metodo;Estado', $response->streamedContent());
        $this->assertStringContainsString($factura->correlativo, $response->streamedContent());
    }

    public function test_facturas_export_pdf()
    {
        $this->actingAs($this->cajero);

        $this->crearFactura();

        $response = $this->get('/reportes/facturas?desde=2026-07-01&hasta=2026-07-31&export=pdf');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_estadisticas_muestra_graficas_y_agrupaciones()
    {
        $this->actingAs($this->cajero);

        $factura = $this->crearFactura();
        $this->crearItem($factura);
        $this->crearItem($factura, ['tipo_venta' => 'mayor', 'precio_unitario_usd' => 8.00, 'precio_unitario_bs' => 400.00, 'subtotal' => 800.00]);

        $response = $this->get('/reportes/estadisticas?desde=2026-07-01&hasta=2026-07-31');

        $response->assertStatus(200);
        $response->assertViewHas(['porDia', 'porMetodo', 'semanaLabels', 'topProductos', 'porVendedor', 'topClientes', 'creditos']);

        $porDia = $response->viewData('porDia');
        $this->assertEquals(580.0, $porDia['2026-07-10']);

        $porMetodo = $response->viewData('porMetodo');
        $this->assertEquals(580.0, $porMetodo['efectivo']);

        $topProductos = $response->viewData('topProductos');
        $this->assertCount(1, $topProductos);
        $this->assertEquals('Producto Test', $topProductos[0]->nombre);
        $this->assertEquals(4, $topProductos[0]->unidades);
        $this->assertEquals(1800.0, $topProductos[0]->ingreso_bs);

        $porVendedor = $response->viewData('porVendedor');
        $this->assertCount(1, $porVendedor);
        $this->assertEquals(580.0, $porVendedor[0]['total_bs']);

        $creditos = $response->viewData('creditos');
        $this->assertEquals(0, $creditos->cantidad);
    }

    public function test_rentabilidad_calcula_ganancia_y_margen()
    {
        $this->actingAs($this->cajero);

        $factura = $this->crearFactura();
        $this->crearItem($factura);

        $response = $this->get('/reportes/rentabilidad?desde=2026-07-01&hasta=2026-07-31');

        $response->assertStatus(200);
        $filas = $response->viewData('filas');
        $this->assertCount(1, $filas);

        $fila = $filas->first();
        $this->assertEquals('Producto Test', $fila->nombre);
        $this->assertEquals(2, $fila->unidades);
        $this->assertEquals(1000.0, $fila->ingreso_bs);
        $this->assertEquals(500.0, $fila->costo_bs);
        $this->assertEquals(500.0, $fila->ganancia_bs);
        $this->assertEquals(50.0, $fila->margen);
    }

    public function test_rentabilidad_filtra_por_tipo_venta()
    {
        $this->actingAs($this->cajero);

        $factura = $this->crearFactura();
        $this->crearItem($factura);
        $this->crearItem($factura, ['tipo_venta' => 'mayor', 'precio_unitario_usd' => 8.00, 'precio_unitario_bs' => 400.00, 'subtotal' => 800.00]);

        $response = $this->get('/reportes/rentabilidad?desde=2026-07-01&hasta=2026-07-31&tipo_venta=mayor');

        $filas = $response->viewData('filas');
        $this->assertCount(1, $filas);
        $this->assertEquals(2, $filas->first()->unidades);
        $this->assertEquals(800.0, $filas->first()->ingreso_bs);
    }

    public function test_rentabilidad_export_csv()
    {
        $this->actingAs($this->cajero);

        $factura = $this->crearFactura();
        $this->crearItem($factura);

        $response = $this->get('/reportes/rentabilidad?desde=2026-07-01&hasta=2026-07-31&export=csv');

        $response->assertStatus(200);
        $this->assertStringContainsString('Producto;Unidades;Ingreso Bs', $response->streamedContent());
        $this->assertStringContainsString('Producto Test', $response->streamedContent());
    }

    public function test_balance_agrupa_mensualmente()
    {
        $this->actingAs($this->cajero);

        $this->crearFactura(['fecha_venta' => '2026-01-15', 'total_bs' => 1000, 'subtotal_bs' => 900, 'iva_bs' => 100]);
        $this->crearFactura();

        $response = $this->get('/reportes/balance?anio=2026');

        $response->assertStatus(200);
        $mensual = $response->viewData('mensual');
        $serieBs = $response->viewData('serieBs');

        $this->assertEquals(1000.0, $mensual[1]['total_bs']);
        $this->assertEquals(1, $mensual[1]['cantidad']);
        $this->assertEquals(1, $mensual[7]['cantidad']);
        $this->assertEquals(1000.0, $serieBs[0]);
        $this->assertEquals(580.0, $serieBs[6]);
    }

    public function test_balance_export_pdf()
    {
        $this->actingAs($this->cajero);

        $this->crearFactura();

        $response = $this->get('/reportes/balance?anio=2026&export=pdf');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
