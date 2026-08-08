<?php

namespace Tests\Unit\Services;

use App\Services\PrecioService;
use PHPUnit\Framework\TestCase;

class PrecioServiceTest extends TestCase
{
    public function test_calcula_precios_desde_margenes()
    {
        $precios = PrecioService::preciosDesdeMargenes(10, 25, 15);

        $this->assertEquals(12.50, $precios['precio_unitario_usd']);
        $this->assertEquals(11.50, $precios['precio_mayor_usd']);
    }

    public function test_calcula_precios_con_margen_cero()
    {
        $precios = PrecioService::preciosDesdeMargenes(5.5, 0, 0);

        $this->assertEquals(5.50, $precios['precio_unitario_usd']);
        $this->assertEquals(5.50, $precios['precio_mayor_usd']);
    }

    public function test_redondea_a_dos_decimales()
    {
        $precios = PrecioService::preciosDesdeMargenes(1.68, 25, 15);

        $this->assertEquals(2.10, $precios['precio_unitario_usd']);
        $this->assertEquals(1.93, $precios['precio_mayor_usd']);
    }
}
