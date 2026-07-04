<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
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
            'unidades_por_paquete' => 12,
            'stock_paquetes' => 5,
            'stock_unidades' => 3,
            'precio_unitario_usd' => 10.50,
            'precio_mayor_usd' => 8.00,
            'cantidad_minima_mayor' => 12,
            'tiene_iva' => true,
            'fuente_tasa' => 'promedio',
            'estado' => 'disponible',
        ]);

        $response->assertRedirect('/productos');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('productos', ['nombre' => 'Nuevo Producto']);
    }

    public function test_store_valida_campos_requeridos()
    {
        $this->actingAs($this->user);

        $response = $this->post('/productos', []);

        $response->assertSessionHasErrors(['nombre', 'unidades_por_paquete', 'stock_paquetes', 'stock_unidades', 'precio_unitario_usd', 'precio_mayor_usd', 'cantidad_minima_mayor', 'fuente_tasa', 'estado']);
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
        $this->actingAs($this->user);

        $response = $this->put("/productos/{$producto->id}", [
            'nombre' => 'Actualizado',
            'categoria_id' => $producto->categoria_id,
            'unidades_por_paquete' => $producto->unidades_por_paquete,
            'stock_paquetes' => $producto->stock_paquetes,
            'stock_unidades' => $producto->stock_unidades,
            'precio_unitario_usd' => $producto->precio_unitario_usd,
            'precio_mayor_usd' => $producto->precio_mayor_usd,
            'cantidad_minima_mayor' => $producto->cantidad_minima_mayor,
            'tiene_iva' => $producto->tiene_iva,
            'fuente_tasa' => $producto->fuente_tasa,
            'estado' => $producto->estado,
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
