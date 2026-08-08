<?php

namespace Tests\Unit\Services;

use App\Services\CatalogoService;
use PHPUnit\Framework\TestCase;

class CatalogoServiceTest extends TestCase
{
    public function test_metodos_pago_incluye_todos_los_cobrables()
    {
        $metodos = CatalogoService::metodosPago();

        $this->assertArrayHasKey('efectivo', $metodos);
        $this->assertArrayHasKey('punto', $metodos);
        $this->assertArrayHasKey('biopago', $metodos);
        $this->assertArrayHasKey('divisas', $metodos);
        $this->assertArrayHasKey('pago_movil', $metodos);
        $this->assertArrayHasKey('transferencia', $metodos);
        $this->assertArrayHasKey('mixto', $metodos);
        $this->assertArrayHasKey('credito', $metodos);
    }

    public function test_nombre_metodo_retorna_etiqueta()
    {
        $this->assertEquals('Pago Móvil', CatalogoService::nombreMetodo('pago_movil'));
        $this->assertEquals('Efectivo', CatalogoService::nombreMetodo('efectivo'));
    }

    public function test_nombre_metodo_desconocido_retorna_null()
    {
        $this->assertNull(CatalogoService::nombreMetodo('tarjeta'));
    }

    public function test_metodos_validos_excluye_mixto_y_credito()
    {
        $validos = CatalogoService::metodosValidos();

        $this->assertNotContains('mixto', $validos);
        $this->assertNotContains('credito', $validos);
        $this->assertContains('efectivo', $validos);
        $this->assertContains('transferencia', $validos);
    }

    public function test_umbral_stock_bajo()
    {
        $this->assertEquals(10, CatalogoService::UMBRAL_STOCK_BAJO);
    }
}
