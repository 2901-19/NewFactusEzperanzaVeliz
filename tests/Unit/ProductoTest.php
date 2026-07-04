<?php

namespace Tests\Unit;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoTest extends TestCase
{
    use RefreshDatabase;

    public function test_calcula_stock_total_correctamente()
    {
        $producto = Producto::factory()->create([
            'unidades_por_paquete' => 12,
            'stock_paquetes' => 3,
            'stock_unidades' => 5,
        ]);

        $this->assertEquals(41, $producto->stock_total);
    }

    public function test_stock_total_con_cero_paquetes()
    {
        $producto = Producto::factory()->create([
            'unidades_por_paquete' => 12,
            'stock_paquetes' => 0,
            'stock_unidades' => 7,
        ]);

        $this->assertEquals(7, $producto->stock_total);
    }

    public function test_stock_total_con_cero_unidades()
    {
        $producto = Producto::factory()->create([
            'unidades_por_paquete' => 6,
            'stock_paquetes' => 4,
            'stock_unidades' => 0,
        ]);

        $this->assertEquals(24, $producto->stock_total);
    }

    public function test_stock_total_cero()
    {
        $producto = Producto::factory()->create([
            'unidades_por_paquete' => 10,
            'stock_paquetes' => 0,
            'stock_unidades' => 0,
        ]);

        $this->assertEquals(0, $producto->stock_total);
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
