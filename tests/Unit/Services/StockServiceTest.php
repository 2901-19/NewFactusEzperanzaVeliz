<?php

namespace Tests\Unit\Services;

use App\Models\Producto;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_unidades_suma_paquetes_y_unidades()
    {
        $producto = new Producto(['stock_paquetes' => 2, 'unidades_por_paquete' => 12, 'stock_unidades' => 3]);

        $this->assertEquals(27, StockService::totalUnidades($producto));
    }

    public function test_descomponer_divide_en_paquetes_y_unidades()
    {
        $componentes = StockService::descomponer(27, 12);

        $this->assertEquals(['stock_paquetes' => 2, 'stock_unidades' => 3], $componentes);
    }

    public function test_descomponer_con_multiplo_exacto()
    {
        $componentes = StockService::descomponer(24, 12);

        $this->assertEquals(['stock_paquetes' => 2, 'stock_unidades' => 0], $componentes);
    }

    public function test_descomponer_rechaza_unidades_por_paquete_invalido()
    {
        $this->expectException(\InvalidArgumentException::class);

        StockService::descomponer(5, 0);
    }

    public function test_descontar_usa_unidades_disponibles()
    {
        $producto = Producto::factory()->create(['stock_paquetes' => 1, 'unidades_por_paquete' => 12, 'stock_unidades' => 10]);

        StockService::descontar($producto, 4);

        $producto->refresh();
        $this->assertEquals(6, $producto->stock_unidades);
        $this->assertEquals(1, $producto->stock_paquetes);
    }

    public function test_descontar_abre_paquetes_cuando_no_habran_unidades()
    {
        $producto = Producto::factory()->create(['stock_paquetes' => 2, 'unidades_por_paquete' => 12, 'stock_unidades' => 3]);

        StockService::descontar($producto, 10);

        $producto->refresh();
        $this->assertEquals(1, $producto->stock_paquetes);
        $this->assertEquals(5, $producto->stock_unidades);
    }

    public function test_descontar_lanza_excepcion_sin_stock_suficiente()
    {
        $producto = Producto::factory()->create(['stock_paquetes' => 0, 'unidades_por_paquete' => 12, 'stock_unidades' => 2]);

        $this->expectException(\RuntimeException::class);

        StockService::descontar($producto, 5);
    }
}
