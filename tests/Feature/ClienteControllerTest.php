<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['rol' => 'admin']);
    }

    public function test_index_muestra_clientes()
    {
        Cliente::factory()->count(3)->create();
        $this->actingAs($this->user);

        $response = $this->get('/clientes');

        $response->assertStatus(200);
        $response->assertViewHas('clientes');
    }

    public function test_store_crea_cliente()
    {
        $this->actingAs($this->user);

        $response = $this->post('/clientes', [
            'ci' => 'V12345678',
            'nombre' => 'Cliente de Prueba',
            'telefono' => '0412-1234567',
        ]);

        $response->assertRedirect('/clientes');
        $this->assertDatabaseHas('clientes', ['ci' => 'V12345678']);
    }

    public function test_store_valida_ci_requerido()
    {
        $this->actingAs($this->user);

        $response = $this->post('/clientes', [
            'nombre' => 'Sin CI',
        ]);

        $response->assertSessionHasErrors(['ci']);
    }

    public function test_update_actualiza_cliente()
    {
        $cliente = Cliente::factory()->create(['nombre' => 'Original']);
        $this->actingAs($this->user);

        $response = $this->put("/clientes/{$cliente->id}", [
            'ci' => $cliente->ci,
            'nombre' => 'Actualizado',
            'telefono' => $cliente->telefono,
        ]);

        $response->assertRedirect('/clientes');
        $this->assertDatabaseHas('clientes', ['nombre' => 'Actualizado']);
    }

    public function test_destroy_elimina_cliente()
    {
        $cliente = Cliente::factory()->create();
        $this->actingAs($this->user);

        $response = $this->delete("/clientes/{$cliente->id}");

        $response->assertRedirect('/clientes');
        $this->assertDatabaseMissing('clientes', ['id' => $cliente->id]);
    }
}
