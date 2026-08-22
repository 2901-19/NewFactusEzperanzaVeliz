<?php

namespace Tests\Feature;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\User;
use Database\Seeders\PermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermisoSeeder::class);
        $this->admin = User::factory()->create(['rol' => 'admin']);
    }

    public function test_no_elimina_al_unico_administrador()
    {
        // Un rol no admin con permiso de gestionar usuarios puede alcanzar
        // la ruta; no debe poder eliminar al único administrador del sistema.
        $rolGestor = Rol::create(['nombre' => 'Gestor', 'slug' => 'gestor']);
        $rolGestor->permisos()->sync([Permiso::where('slug', 'gestionar-usuarios')->value('id')]);
        $gestor = User::factory()->create(['rol' => 'gestor']);
        $this->actingAs($gestor);

        $response = $this->from('/usuarios')->delete("/usuarios/{$this->admin->id}");

        $response->assertRedirect('/usuarios');
        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_elimina_admin_cuando_hay_otros_administradores()
    {
        $otroAdmin = User::factory()->create(['rol' => 'admin']);
        $tercerAdmin = User::factory()->create(['rol' => 'admin']);
        $this->actingAs($this->admin);

        $response = $this->delete("/usuarios/{$otroAdmin->id}");

        $response->assertRedirect('/usuarios');
        $this->assertDatabaseMissing('users', ['id' => $otroAdmin->id]);
        $this->assertDatabaseHas('users', ['id' => $tercerAdmin->id]);
    }

    public function test_no_elimina_su_propio_usuario()
    {
        $this->actingAs($this->admin);

        $response = $this->from('/usuarios')->delete("/usuarios/{$this->admin->id}");

        $response->assertRedirect('/usuarios');
        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }
}
