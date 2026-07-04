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
            'cantidad_minima_mayor' => 12,
            'tiene_iva' => true,
            'fuente_tasa' => 'promedio',
            'estado' => 'disponible',
        ]);
        $this->tasa = TasaCambio::factory()->create([
            'tipo' => 'promedio',
            'monto' => 50.00,
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
}
