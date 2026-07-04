<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Configuracion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class HerramientasTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['rol' => 'admin']);
    }

    public function test_configuracion_muestra_formulario()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/herramientas/configuracion');

        $response->assertStatus(200);
    }

    public function test_configuracion_guarda_datos()
    {
        $this->actingAs($this->admin);

        $response = $this->from('/herramientas/configuracion')->post('/herramientas/configuracion', [
            'nombre_negocio' => 'Mi Abasto',
            'rif' => 'J-12345678-9',
            'direccion' => 'Calle Principal',
            'telefono' => '0412-1111111',
        ]);

        $response->assertRedirect('/herramientas/configuracion');
        $response->assertSessionHas('success');

        $this->assertEquals('Mi Abasto', Configuracion::obtener('nombre_negocio'));
        $this->assertEquals('J-12345678-9', Configuracion::obtener('rif'));
        $this->assertEquals('Calle Principal', Configuracion::obtener('direccion'));
        $this->assertEquals('0412-1111111', Configuracion::obtener('telefono'));
    }

    public function test_exportar_genera_archivo()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/herramientas/exportar');

        $response->assertStatus(200);
    }

    public function test_importar_con_archivo_valido()
    {
        $this->actingAs($this->admin);

        $json = json_encode(['productos' => [], 'clientes' => [], 'impuestos' => [], 'tasas_cambio' => []]);
        $archivo = UploadedFile::fake()->createWithContent('datos.json', $json);

        $response = $this->from('/herramientas/datos')->post('/herramientas/importar', [
            'archivo' => $archivo,
        ]);

        $response->assertSessionHas('success');
    }

    public function test_impresora_muestra_configuracion()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/herramientas/impresora');

        $response->assertStatus(200);
    }

    public function test_impresora_guarda_configuracion()
    {
        $this->actingAs($this->admin);

        $response = $this->from('/herramientas/impresora')->post('/herramientas/impresora', [
            'tipo' => 'network',
            'host' => '192.168.1.100',
            'port' => 9100,
        ]);

        $response->assertRedirect('/herramientas/impresora');
        $response->assertSessionHas('success');
    }

    public function test_datos_muestra_estadisticas()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/herramientas/datos');

        $response->assertStatus(200);
    }

    public function test_precios_muestra_lista()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/herramientas/precios');

        $response->assertStatus(200);
    }
}
