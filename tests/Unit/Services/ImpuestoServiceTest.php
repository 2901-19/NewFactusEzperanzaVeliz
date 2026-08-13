<?php

namespace Tests\Unit\Services;

use App\Models\Impuesto;
use App\Services\ImpuestoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpuestoServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_porcentaje_de_retorna_cero_sin_impuesto()
    {
        $this->assertEquals(0.0, ImpuestoService::porcentajeDe(null));
    }

    public function test_porcentaje_de_retorna_el_porcentaje_del_impuesto()
    {
        $impuesto = Impuesto::factory()->create(['porcentaje' => 16.00]);

        $this->assertEquals(16.0, ImpuestoService::porcentajeDe($impuesto));
    }

    public function test_porcentaje_de_respeta_el_porcentaje_de_cada_impuesto()
    {
        $iva = Impuesto::factory()->create(['porcentaje' => 16.00]);
        $exento = Impuesto::factory()->create(['porcentaje' => 0]);

        $this->assertEquals(16.0, ImpuestoService::porcentajeDe($iva));
        $this->assertEquals(0.0, ImpuestoService::porcentajeDe($exento));
    }
}
