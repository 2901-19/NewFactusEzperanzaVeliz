<?php

namespace Tests\Unit;

use App\Models\Configuracion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfiguracionTest extends TestCase
{
    use RefreshDatabase;

    public function test_obtener_retorna_valor_correcto()
    {
        Configuracion::updateOrCreate(
            ['clave' => 'nombre_negocio'],
            ['valor' => 'Mi Abasto']
        );

        $this->assertEquals('Mi Abasto', Configuracion::obtener('nombre_negocio'));
    }

    public function test_obtener_retorna_defecto_si_no_existe()
    {
        $this->assertEquals('Defecto', Configuracion::obtener('clave_inexistente', 'Defecto'));
    }

    public function test_obtener_retorna_vacio_si_no_existe_sin_defecto()
    {
        $this->assertEquals('', Configuracion::obtener('clave_inexistente'));
    }

    public function test_obtener_retorna_valor_actualizado()
    {
        Configuracion::updateOrCreate(['clave' => 'telefono'], ['valor' => '0412-1111111']);
        Configuracion::where('clave', 'telefono')->update(['valor' => '0412-2222222']);

        $this->assertEquals('0412-2222222', Configuracion::obtener('telefono'));
    }
}
