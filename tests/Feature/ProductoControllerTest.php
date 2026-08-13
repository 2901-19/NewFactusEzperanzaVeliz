<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\TasaCambio;
use App\Models\User;
use Database\Seeders\PermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermisoSeeder::class);
        foreach (['promedio', 'dolar', 'bcv'] as $tipo) {
            TasaCambio::create([
                'tipo' => $tipo,
                'nombre' => ucfirst($tipo),
                'monto' => 50.00,
                'fecha' => now()->toDateString(),
            ]);
        }
        $this->user = User::factory()->create(['rol' => 'admin']);
    }

    public function test_index_muestra_productos()
    {
        Producto::factory()->count(3)->create();
        $this->actingAs($this->user);

        $response = $this->get('/productos');

        $response->assertStatus(200);
        $response->assertViewHas('productos');
    }

    public function test_create_muestra_formulario()
    {
        $this->actingAs($this->user);

        $response = $this->get('/productos/create');

        $response->assertStatus(200);
    }

    public function test_store_crea_producto()
    {
        $categoria = Categoria::factory()->create();
        $this->actingAs($this->user);

        $response = $this->post('/productos', [
            'nombre' => 'Nuevo Producto',
            'categoria_id' => $categoria->id,
            'costo_usd' => 10.00,
            'controla_inventario' => true,
            'unidad_medida' => 'unidad',
            'stock_actual' => 5,
            'impuesto_id' => null,
            'fuente_tasa' => 'promedio',
            'estado' => 'disponible',
            'presentaciones' => [
                ['nombre' => 'Unidad', 'factor_conversion' => 1, 'margen' => 25, 'activa' => true],
            ],
        ]);

        $response->assertRedirect('/productos');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('productos', ['nombre' => 'Nuevo Producto']);
        $this->assertDatabaseHas('producto_presentaciones', ['nombre' => 'Unidad', 'precio_usd' => 12.50]);
    }

    public function test_store_valida_campos_requeridos()
    {
        $this->actingAs($this->user);

        $response = $this->post('/productos', []);

        $response->assertSessionHasErrors(['nombre', 'costo_usd', 'unidad_medida', 'presentaciones', 'fuente_tasa', 'estado']);
    }

    public function test_edit_muestra_formulario()
    {
        $producto = Producto::factory()->create();
        $this->actingAs($this->user);

        $response = $this->get("/productos/{$producto->id}/edit");

        $response->assertStatus(200);
        $response->assertViewHas('producto');
    }

    public function test_update_actualiza_producto()
    {
        $producto = Producto::factory()->create(['nombre' => 'Original']);
        ProductoPresentacion::factory()->create([
            'producto_id' => $producto->id,
            'nombre' => 'Unidad',
            'factor_conversion' => 1,
            'margen' => 25,
            'precio_usd' => 12.50,
            'activa' => true,
        ]);
        $this->actingAs($this->user);

        $response = $this->put("/productos/{$producto->id}", [
            'nombre' => 'Actualizado',
            'categoria_id' => $producto->categoria_id,
            'costo_usd' => $producto->costo_usd,
            'controla_inventario' => $producto->controla_inventario,
            'unidad_medida' => 'unidad',
            'stock_actual' => $producto->stock_actual,
            'impuesto_id' => $producto->impuesto_id,
            'fuente_tasa' => $producto->fuente_tasa,
            'estado' => $producto->estado,
            'presentaciones' => [
                ['nombre' => 'Unidad', 'factor_conversion' => 1, 'margen' => 25, 'activa' => true],
            ],
        ]);

        $response->assertRedirect('/productos');
        $this->assertDatabaseHas('productos', ['nombre' => 'Actualizado']);
    }

    public function test_destroy_desactiva_producto()
    {
        $producto = Producto::factory()->create();
        $this->actingAs($this->user);

        $response = $this->delete("/productos/{$producto->id}");

        $response->assertRedirect('/productos');
        $this->assertSoftDeleted($producto);
    }

    public function test_restore_reactiva_producto()
    {
        $producto = Producto::factory()->create();
        $producto->delete();
        $this->actingAs($this->user);

        $response = $this->post("/productos/{$producto->id}/restore");

        $response->assertRedirect('/productos');
        $this->assertNotSoftDeleted($producto);
    }
}
