<?php

namespace Tests\Unit\Services;

use App\Models\Impuesto;
use App\Services\ImpuestoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpuestoServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_porcentaje_vigente_usa_defecto_sin_impuestos()
    {
        $this->assertEquals(16, ImpuestoService::porcentajeVigente());
    }

    public function test_porcentaje_vigente_retorna_el_mas_reciente_por_fecha()
    {
        Impuesto::create(['nombre' => 'IVA', 'porcentaje' => 12.00, 'fecha' => '2026-01-01']);
        Impuesto::create(['nombre' => 'IVA', 'porcentaje' => 16.00, 'fecha' => '2026-06-01']);

        $this->assertEquals(16.00, ImpuestoService::porcentajeVigente());
    }

    public function test_porcentaje_vigente_ignora_registros_con_fecha_anterior()
    {
        Impuesto::create(['nombre' => 'IVA', 'porcentaje' => 18.00, 'fecha' => '2026-12-01']);
        Impuesto::create(['nombre' => 'IVA', 'porcentaje' => 16.00, 'fecha' => '2026-06-01']);

        $this->assertEquals(18.00, ImpuestoService::porcentajeVigente());
    }
}
