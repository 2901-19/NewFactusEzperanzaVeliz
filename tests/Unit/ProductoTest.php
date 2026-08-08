<?php

namespace Tests\Unit;

use App\Models\Producto;
use App\Models\ProductoPresentacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_actual_es_decimal()
    {
        $producto = Producto::factory()->create(['stock_actual' => 12.5]);

        $this->assertEquals(12.5, (float) $producto->stock_actual);
    }

    public function test_stock_actual_por_defecto_cero()
    {
        $producto = Producto::factory()->create(['stock_actual' => 0]);

        $this->assertEquals(0, (float) $producto->stock_actual);
    }

    public function test_precio_base_usa_presentacion_con_factor_1()
    {
        $producto = Producto::factory()->create();
        ProductoPresentacion::factory()->create([
            'producto_id' => $producto->id,
            'nombre' => 'Mayor',
            'factor_conversion' => 12,
            'precio_usd' => 96.00,
            'activa' => true,
        ]);
        ProductoPresentacion::factory()->create([
            'producto_id' => $producto->id,
            'nombre' => 'Unidad',
            'factor_conversion' => 1,
            'precio_usd' => 10.00,
            'activa' => true,
        ]);

        $this->assertEquals(10.00, (float) $producto->precio_base);
    }

    public function test_precio_base_ignora_presentaciones_inactivas()
    {
        $producto = Producto::factory()->create();
        ProductoPresentacion::factory()->create([
            'producto_id' => $producto->id,
            'nombre' => 'Unidad',
            'factor_conversion' => 1,
            'precio_usd' => 10.00,
            'activa' => false,
        ]);

        $this->assertEquals(0, (float) $producto->precio_base);
    }

    public function test_precio_base_sin_presentaciones_es_cero()
    {
        $producto = Producto::factory()->create();

        $this->assertEquals(0, (float) $producto->precio_base);
    }

    public function test_soft_delete_funciona()
    {
        $producto = Producto::factory()->create();
        $id = $producto->id;

        $producto->delete();

        $this->assertNull(Producto::find($id));
        $this->assertNotNull(Producto::withTrashed()->find($id));
    }

    public function test_restauracion_funciona()
    {
        $producto = Producto::factory()->create();
        $id = $producto->id;
        $producto->delete();

        Producto::withTrashed()->find($id)->restore();

        $this->assertNotNull(Producto::find($id));
    }
}
