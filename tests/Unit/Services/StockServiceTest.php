<?php

namespace Tests\Unit\Services;

use App\Models\Producto;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_actual_devuelve_decimal()
    {
        $producto = new Producto(['stock_actual' => 2.5, 'controla_inventario' => true]);

        $this->assertSame(2.5, StockService::stockActual($producto));
    }

    public function test_agregar_incrementa_stock_actual()
    {
        $producto = Producto::factory()->create(['stock_actual' => 10, 'controla_inventario' => true]);

        StockService::agregar($producto, 3.5);

        $producto->refresh();
        $this->assertEquals(13.5, (float) $producto->stock_actual);
    }

    public function test_agregar_ignora_si_no_controla_inventario()
    {
        $producto = Producto::factory()->create(['stock_actual' => 10, 'controla_inventario' => false]);

        StockService::agregar($producto, 5);

        $producto->refresh();
        $this->assertEquals(10, (float) $producto->stock_actual);
    }

    public function test_agregar_cantidad_no_positiva_retorna()
    {
        $producto = Producto::factory()->create(['stock_actual' => 10, 'controla_inventario' => true]);

        StockService::agregar($producto, 0);

        $producto->refresh();
        $this->assertEquals(10, (float) $producto->stock_actual);
    }

    public function test_descontar_descuenta_stock_actual()
    {
        $producto = Producto::factory()->create(['stock_actual' => 10, 'controla_inventario' => true]);

        StockService::descontar($producto, 4);

        $producto->refresh();
        $this->assertEquals(6, (float) $producto->stock_actual);
    }

    public function test_descontar_acepta_decimales()
    {
        $producto = Producto::factory()->create(['stock_actual' => 5, 'controla_inventario' => true]);

        StockService::descontar($producto, 2.75);

        $producto->refresh();
        $this->assertEquals(2.25, (float) $producto->stock_actual);
    }

    public function test_descontar_ignora_si_no_controla_inventario()
    {
        $producto = Producto::factory()->create(['stock_actual' => 10, 'controla_inventario' => false]);

        StockService::descontar($producto, 5);

        $producto->refresh();
        $this->assertEquals(10, (float) $producto->stock_actual);
    }

    public function test_descontar_lanza_excepcion_sin_stock_suficiente()
    {
        $producto = Producto::factory()->create(['stock_actual' => 2, 'controla_inventario' => true]);

        $this->expectException(\RuntimeException::class);

        StockService::descontar($producto, 5);
    }

    public function test_descontar_cantidad_no_positiva_retorna()
    {
        $producto = Producto::factory()->create(['stock_actual' => 10, 'controla_inventario' => true]);

        StockService::descontar($producto, 0);

        $producto->refresh();
        $this->assertEquals(10, (float) $producto->stock_actual);
    }
}
