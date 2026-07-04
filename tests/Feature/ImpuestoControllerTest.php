<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Impuesto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpuestoControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['rol' => 'admin']);
    }

    public function test_index_muestra_impuestos()
    {
        Impuesto::factory()->count(2)->create();
        $this->actingAs($this->admin);

        $response = $this->get('/impuestos');

        $response->assertStatus(200);
        $response->assertViewHas('impuestos');
    }

    public function test_store_crea_impuesto()
    {
        $this->actingAs($this->admin);

        $response = $this->from('/impuestos')->post('/impuestos', [
            'nombre' => 'IVA',
            'porcentaje' => 16.00,
            'fecha' => '2026-07-04',
        ]);

        $response->assertRedirect('/impuestos');
        $this->assertDatabaseHas('impuestos', ['porcentaje' => 16.00]);
    }

    public function test_store_valida_campos()
    {
        $this->actingAs($this->admin);

        $response = $this->post('/impuestos', []);

        $response->assertSessionHasErrors(['nombre', 'porcentaje', 'fecha']);
    }

    public function test_destroy_elimina_impuesto()
    {
        $impuesto = Impuesto::factory()->create();
        $this->actingAs($this->admin);

        $response = $this->delete("/impuestos/{$impuesto->id}");

        $response->assertRedirect('/impuestos');
        $this->assertDatabaseMissing('impuestos', ['id' => $impuesto->id]);
    }
}
