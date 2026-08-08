<?php

namespace Tests\Unit\Services;

use App\Services\PrecioService;
use PHPUnit\Framework\TestCase;

class PrecioServiceTest extends TestCase
{
    public function test_calcula_precio_de_presentacion_con_margen_y_factor()
    {
        $precio = PrecioService::precioPresentacion(10, 25, 1);

        $this->assertEquals(12.50, $precio);
    }

    public function test_multiplica_por_factor_de_conversion()
    {
        $precio = PrecioService::precioPresentacion(10, 25, 12);

        $this->assertEquals(150.00, $precio);
    }

    public function test_precio_con_margen_cero()
    {
        $precio = PrecioService::precioPresentacion(5.5, 0, 1);

        $this->assertEquals(5.50, $precio);
    }

    public function test_redondea_a_dos_decimales()
    {
        $precio = PrecioService::precioPresentacion(1.68, 25, 1);

        $this->assertEquals(2.10, $precio);
    }

    public function test_precio_base_es_costo_mas_margen()
    {
        $precio = PrecioService::precioBase(1.68, 25);

        $this->assertEquals(2.10, $precio);
    }
}
