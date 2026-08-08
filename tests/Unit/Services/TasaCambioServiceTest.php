<?php

namespace Tests\Unit\Services;

use App\Models\TasaCambio;
use App\Services\TasaCambioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TasaCambioServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_monto_retorna_null_si_no_existe()
    {
        $this->assertNull(TasaCambioService::monto('inexistente'));
    }

    public function test_monto_usa_la_ultima_fila_por_id()
    {
        TasaCambio::create(['tipo' => 'bcv', 'nombre' => 'BCV', 'monto' => 50.00, 'fecha' => '2026-08-01']);
        TasaCambio::create(['tipo' => 'bcv', 'nombre' => 'BCV', 'monto' => 52.00, 'fecha' => '2026-08-01']);

        $this->assertEquals(52.00, TasaCambioService::monto('bcv'));
    }

    public function test_monto_ignora_orden_de_creacion_cuando_ids_mayores()
    {
        TasaCambio::create(['tipo' => 'dolar', 'nombre' => 'Dólar', 'monto' => 60.00, 'fecha' => '2026-08-02']);
        TasaCambio::create(['tipo' => 'dolar', 'nombre' => 'Dólar', 'monto' => 61.00, 'fecha' => '2026-08-01']);

        $this->assertEquals(61.00, TasaCambioService::monto('dolar'));
    }

    public function test_monto_o_exception_retorna_monto_valido()
    {
        TasaCambio::create(['tipo' => 'bcv', 'nombre' => 'BCV', 'monto' => 52.00, 'fecha' => '2026-08-01']);

        $this->assertEquals(52.00, TasaCambioService::montoOException('bcv'));
    }

    public function test_monto_o_exception_lanza_si_no_existe()
    {
        $this->expectException(\RuntimeException::class);

        TasaCambioService::montoOException('inexistente');
    }

    public function test_monto_o_exception_lanza_si_monto_cero()
    {
        TasaCambio::create(['tipo' => 'bcv', 'nombre' => 'BCV', 'monto' => 0, 'fecha' => '2026-08-01']);

        $this->expectException(\RuntimeException::class);

        TasaCambioService::montoOException('bcv');
    }

    public function test_convertir_usd_a_bs()
    {
        TasaCambio::create(['tipo' => 'bcv', 'nombre' => 'BCV', 'monto' => 52.00, 'fecha' => '2026-08-01']);

        $this->assertEquals(104.00, TasaCambioService::convertirUsdABs(2, 'bcv'));
    }
}
