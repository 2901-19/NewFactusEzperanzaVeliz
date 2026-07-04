<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Categoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriaControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['rol' => 'admin']);
    }

    public function test_index_muestra_categorias()
    {
        Categoria::factory()->count(3)->create();
        $this->actingAs($this->user);

        $response = $this->get('/categorias');

        $response->assertStatus(200);
        $response->assertViewHas('categorias');
    }

    public function test_store_crea_categoria()
    {
        $this->actingAs($this->user);

        $response = $this->post('/categorias', [
            'nombre' => 'Nueva Categoria',
            'descripcion' => 'Descripcion de prueba',
        ]);

        $response->assertRedirect('/categorias');
        $this->assertDatabaseHas('categorias', ['nombre' => 'Nueva Categoria']);
    }

    public function test_store_valida_nombre()
    {
        $this->actingAs($this->user);

        $response = $this->post('/categorias', []);

        $response->assertSessionHasErrors(['nombre']);
    }

    public function test_destroy_elimina_categoria()
    {
        $categoria = Categoria::factory()->create();
        $this->actingAs($this->user);

        $response = $this->delete("/categorias/{$categoria->id}");

        $response->assertRedirect('/categorias');
        $this->assertDatabaseMissing('categorias', ['id' => $categoria->id]);
    }
}
