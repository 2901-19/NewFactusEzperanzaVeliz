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

class FacturaControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $cajero;
    private Cliente $cliente;
    private Producto $producto;
    private TasaCambio $tasa;
    private Impuesto $iva;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermisoSeeder::class);
        $this->cajero = User::factory()->create(['rol' => 'cajero']);
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
            'costo_usd' => 10.00,
            'margen_detal' => 0,
            'margen_mayor' => 0,
            'tiene_iva' => true,
            'fuente_tasa' => 'promedio',
            'estado' => 'disponible',
        ]);
        $this->tasa = TasaCambio::factory()->create([
            'tipo' => 'promedio',
            'monto' => 50.00,
            'fecha' => '2026-07-04',
        ]);
        TasaCambio::factory()->create([
            'tipo' => 'bcv',
            'monto' => 45.00,
            'fecha' => '2026-07-04',
        ]);
        $this->iva = Impuesto::factory()->create([
            'porcentaje' => 16.00,
            'fecha' => '2026-07-04',
        ]);
    }

    public function test_index_muestra_facturas()
    {
        Factura::factory()->count(3)->create();
        $this->actingAs($this->cajero);

        $response = $this->get('/facturas');

        $response->assertStatus(200);
        $response->assertViewHas('facturas');
    }

    public function test_pos_muestra_formulario()
    {
        $this->actingAs($this->cajero);

        $response = $this->get('/pos');

        $response->assertStatus(200);
        $response->assertViewHas(['productos', 'clientes']);
    }

    public function test_store_crea_factura_contado()
    {
        $this->actingAs($this->cajero);

        $response = $this->postJson('/facturas', [
            'cliente_id' => $this->cliente->id,
            'metodo_pago' => 'efectivo',
            'estado' => 'contado',
            'items' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 3,
                    'tipo_venta' => 'unitario',
                ],
            ],
        ]);

        $response->assertJson(['success' => true]);
        $response->assertStatus(200);

        $factura = Factura::first();
        $this->assertNotNull($factura);
        $this->assertEquals($this->cliente->id, $factura->cliente_id);
        $this->assertEquals('contado', $factura->estado);
        $this->assertNull($factura->estado_credito);
        $this->assertEquals($this->cajero->id, $factura->user_id);

        $this->assertDatabaseHas('items_factura', [
            'factura_id' => $factura->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 3,
            'tipo_venta' => 'unitario',
        ]);
    }

    public function test_store_aplica_precio_mayor_segun_tipo_venta()
    {
        $this->actingAs($this->cajero);

        $response = $this->postJson('/facturas', [
            'cliente_id' => $this->cliente->id,
            'metodo_pago' => 'efectivo',
            'estado' => 'contado',
            'items' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 1,
                    'tipo_venta' => 'mayor',
                ],
            ],
        ]);

        $response->assertJson(['success' => true]);

        $factura = Factura::first();
        $item = $factura->items->first();
        $this->assertEquals('mayor', $item->tipo_venta);
        $this->assertEquals(8.00, (float) $item->precio_unitario_usd);
    }

    public function test_store_calcula_total_usd_y_tasa_a_tasa_bcv()
    {
        $this->actingAs($this->cajero);

        $response = $this->postJson('/facturas', [
            'cliente_id' => $this->cliente->id,
            'metodo_pago' => 'efectivo',
            'estado' => 'contado',
            'items' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 1,
                    'tipo_venta' => 'unitario',
                ],
            ],
        ]);

        $response->assertJson(['success' => true]);

        $factura = Factura::first();
        $this->assertEquals(500.0, (float) $factura->subtotal_bs);
        $this->assertEquals(80.0, (float) $factura->iva_bs);
        $this->assertEquals(580.0, (float) $factura->total_bs);
        $this->assertEquals(12.89, (float) $factura->total_usd);
        $this->assertEquals(45.0, (float) $factura->tasa_cambio);
    }

    public function test_store_pago_mixto_guarda_detalle_pago()
    {
        $this->actingAs($this->cajero);

        $response = $this->postJson('/facturas', [
            'cliente_id' => $this->cliente->id,
            'metodo_pago' => 'mixto',
            'pagos' => [
                ['metodo' => 'efectivo', 'monto' => 400.00],
                ['metodo' => 'punto', 'monto' => 180.00],
            ],
            'estado' => 'contado',
            'items' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 1,
                    'tipo_venta' => 'unitario',
                ],
            ],
        ]);

        $response->assertJson(['success' => true]);
        $response->assertStatus(200);

        $factura = Factura::first();
        $this->assertSame('mixto', $factura->metodo_pago);
        $this->assertEquals([
            ['metodo' => 'efectivo', 'monto' => 400.0],
            ['metodo' => 'punto', 'monto' => 180.0],
        ], $factura->detalle_pago);
        $this->assertEquals(580.0, (float) $factura->total_bs);
    }

    public function test_store_pago_mixto_suma_incorrecta_es_rechazado()
    {
        $this->actingAs($this->cajero);

        $response = $this->postJson('/facturas', [
            'cliente_id' => $this->cliente->id,
            'metodo_pago' => 'mixto',
            'pagos' => [
                ['metodo' => 'efectivo', 'monto' => 100.00],
                ['metodo' => 'punto', 'monto' => 100.00],
            ],
            'estado' => 'contado',
            'items' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 1,
                    'tipo_venta' => 'unitario',
                ],
            ],
        ]);

        $response->assertJson(['success' => false]);
        $this->assertDatabaseCount('facturas', 0);
    }

    public function test_store_pago_mixto_sin_pagos_falla_validacion()
    {
        $this->actingAs($this->cajero);

        $response = $this->postJson('/facturas', [
            'cliente_id' => $this->cliente->id,
            'metodo_pago' => 'mixto',
            'estado' => 'contado',
            'items' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 1,
                    'tipo_venta' => 'unitario',
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['pagos']);
    }

    public function test_store_descuenta_stock_paquetes()
    {
        $this->actingAs($this->cajero);

        $this->postJson('/facturas', [
            'cliente_id' => $this->cliente->id,
            'metodo_pago' => 'efectivo',
            'estado' => 'contado',
            'items' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 14,
                    'tipo_venta' => 'unitario',
                ],
            ],
        ]);

        $this->producto->refresh();
        $this->assertEquals(9, $this->producto->stock_paquetes);
        $this->assertEquals(3, $this->producto->stock_unidades);
    }

    public function test_store_crea_factura_credito()
    {
        $this->actingAs($this->cajero);

        $response = $this->postJson('/facturas', [
            'cliente_id' => $this->cliente->id,
            'metodo_pago' => 'credito',
            'estado' => 'credito',
            'items' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 2,
                    'tipo_venta' => 'unitario',
                ],
            ],
        ]);

        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('facturas', [
            'cliente_id' => $this->cliente->id,
            'estado' => 'credito',
            'estado_credito' => 'pendiente',
        ]);
    }

    public function test_anular_factura_restaura_stock()
    {
        $this->actingAs($this->cajero);

        $this->postJson('/facturas', [
            'cliente_id' => $this->cliente->id,
            'metodo_pago' => 'efectivo',
            'estado' => 'contado',
            'items' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 12,
                    'tipo_venta' => 'mayor',
                ],
            ],
        ]);

        $factura = Factura::first();

        $response = $this->post("/facturas/{$factura->id}/anular");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('facturas', [
            'id' => $factura->id,
            'estado' => 'anulada',
        ]);

        $this->producto->refresh();
        $this->assertEquals(125, $this->producto->stock_total);
    }

    public function test_pagar_credito_marca_como_cancelado()
    {
        $this->actingAs($this->cajero);

        $this->postJson('/facturas', [
            'cliente_id' => $this->cliente->id,
            'metodo_pago' => 'credito',
            'estado' => 'credito',
            'items' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 5,
                    'tipo_venta' => 'unitario',
                ],
            ],
        ]);

        $factura = Factura::first();

        $response = $this->post("/facturas/{$factura->id}/pagar-credito");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('facturas', [
            'id' => $factura->id,
            'estado_credito' => 'cancelado',
        ]);
    }

    public function test_validacion_requiere_items()
    {
        $this->actingAs($this->cajero);

        $response = $this->postJson('/facturas', [
            'cliente_id' => $this->cliente->id,
            'metodo_pago' => 'efectivo',
            'estado' => 'contado',
            'items' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items']);
    }

    public function test_validacion_stock_insuficiente()
    {
        $this->actingAs($this->cajero);

        $response = $this->postJson('/facturas', [
            'cliente_id' => $this->cliente->id,
            'metodo_pago' => 'efectivo',
            'estado' => 'contado',
            'items' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 9999,
                    'tipo_venta' => 'unitario',
                ],
            ],
        ]);

        $response->assertJson(['success' => false]);
    }

    public function test_credito_requiere_cliente()
    {
        $this->actingAs($this->cajero);

        $response = $this->postJson('/facturas', [
            'metodo_pago' => 'credito',
            'estado' => 'credito',
            'items' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 2,
                    'tipo_venta' => 'unitario',
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['cliente_id']);
    }

    public function test_show_muestra_factura()
    {
        $factura = Factura::factory()->create();
        $this->actingAs($this->cajero);

        $response = $this->get("/facturas/{$factura->id}");

        $response->assertStatus(200);
        $response->assertViewHas('factura');
    }

    public function test_creditos_muestra_pendientes()
    {
        Factura::factory()->count(2)->create(['estado' => 'credito', 'estado_credito' => 'pendiente']);
        $this->actingAs($this->cajero);

        $response = $this->get('/creditos');

        $response->assertStatus(200);
        $response->assertViewHas('facturas');
    }

    public function test_store_rechaza_venta_sin_tasa_configurada()
    {
        TasaCambio::query()->delete();
        $this->actingAs($this->cajero);

        $response = $this->postJson('/facturas', [
            'cliente_id' => $this->cliente->id,
            'metodo_pago' => 'efectivo',
            'estado' => 'contado',
            'items' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 1,
                    'tipo_venta' => 'unitario',
                ],
            ],
        ]);

        $response->assertStatus(500);
        $response->assertJson(['success' => false]);
        $this->assertDatabaseCount('facturas', 0);

        $this->producto->refresh();
        $this->assertEquals(125, $this->producto->stock_total);
    }

    public function test_store_rechaza_producto_sin_precio()
    {
        $this->producto->update(['precio_unitario_usd' => 0, 'precio_mayor_usd' => 0]);
        $this->actingAs($this->cajero);

        $response = $this->postJson('/facturas', [
            'cliente_id' => $this->cliente->id,
            'metodo_pago' => 'efectivo',
            'estado' => 'contado',
            'items' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 1,
                    'tipo_venta' => 'unitario',
                ],
            ],
        ]);

        $response->assertStatus(500);
        $response->assertJson(['success' => false]);
        $this->assertDatabaseCount('facturas', 0);
    }

    public function test_anular_credito_cobrado_es_bloqueado()
    {
        $this->actingAs($this->cajero);

        $this->postJson('/facturas', [
            'cliente_id' => $this->cliente->id,
            'metodo_pago' => 'credito',
            'estado' => 'credito',
            'items' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 2,
                    'tipo_venta' => 'unitario',
                ],
            ],
        ]);

        $factura = Factura::first();
        $this->post("/facturas/{$factura->id}/pagar-credito");
        $factura->refresh();
        $this->assertEquals('cancelado', $factura->estado_credito);

        $response = $this->post("/facturas/{$factura->id}/anular");

        $response->assertRedirect();
        $response->assertSessionHasErrors('error');

        $this->assertDatabaseHas('facturas', [
            'id' => $factura->id,
            'estado' => 'credito',
            'estado_credito' => 'cancelado',
        ]);
    }

    public function test_anular_restaura_stock_de_producto_desactivado()
    {
        $this->actingAs($this->cajero);

        $this->postJson('/facturas', [
            'cliente_id' => $this->cliente->id,
            'metodo_pago' => 'efectivo',
            'estado' => 'contado',
            'items' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 10,
                    'tipo_venta' => 'unitario',
                ],
            ],
        ]);

        $factura = Factura::first();
        $this->producto->delete();

        $response = $this->post("/facturas/{$factura->id}/anular");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->producto->refresh();
        $this->assertEquals(125, $this->producto->stock_total);
    }

    public function test_store_redondea_subtotal_iva_y_total()
    {
        $this->producto->update(['precio_unitario_usd' => 10.3333, 'precio_mayor_usd' => 8.0]);
        $this->actingAs($this->cajero);

        $response = $this->postJson('/facturas', [
            'cliente_id' => $this->cliente->id,
            'metodo_pago' => 'efectivo',
            'estado' => 'contado',
            'items' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 3,
                    'tipo_venta' => 'unitario',
                ],
            ],
        ]);

        $response->assertJson(['success' => true]);

        $factura = Factura::first();
        $this->assertEquals(1550.00, (float) $factura->subtotal_bs);
        $this->assertEquals(248.00, (float) $factura->iva_bs);
        $this->assertEquals(1798.00, (float) $factura->total_bs);
    }
}
