<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_cajero_no_puede_acceder_a_impuestos()
    {
        $cajero = User::factory()->create(['rol' => 'cajero']);
        $this->actingAs($cajero);

        $response = $this->get('/impuestos');

        $response->assertStatus(403);
    }

    public function test_admin_puede_acceder_a_impuestos()
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/impuestos');

        $response->assertStatus(200);
    }

    public function test_cajero_no_puede_acceder_a_tasas()
    {
        $cajero = User::factory()->create(['rol' => 'cajero']);
        $this->actingAs($cajero);

        $response = $this->get('/tasas-cambio');

        $response->assertStatus(403);
    }

    public function test_cajero_no_puede_acceder_a_reportes()
    {
        $cajero = User::factory()->create(['rol' => 'cajero']);
        $this->actingAs($cajero);

        $response = $this->get('/reportes/facturas');
        $response->assertStatus(403);
    }

    public function test_cajero_no_puede_acceder_a_herramientas()
    {
        $cajero = User::factory()->create(['rol' => 'cajero']);
        $this->actingAs($cajero);

        $response = $this->get('/herramientas/configuracion');
        $response->assertStatus(403);
    }

    public function test_admin_puede_acceder_a_herramientas()
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/herramientas/configuracion');
        $response->assertStatus(200);
    }

    public function test_admin_puede_acceder_a_categorias()
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/categorias');
        $response->assertStatus(200);
    }

    public function test_cajero_no_puede_acceder_a_categorias()
    {
        $cajero = User::factory()->create(['rol' => 'cajero']);
        $this->actingAs($cajero);

        $response = $this->get('/categorias');
        $response->assertStatus(403);
    }
}
