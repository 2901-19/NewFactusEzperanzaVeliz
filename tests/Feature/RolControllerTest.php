<?php

namespace Tests\Feature;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\User;
use Database\Seeders\PermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermisoSeeder::class);
        $this->admin = User::factory()->create(['rol' => 'admin']);
    }

    public function test_listar_roles_muestra_administrador()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/roles');

        $response->assertStatus(200);
        $response->assertSee('Administrador');
    }

    public function test_crear_rol_con_permisos()
    {
        $this->actingAs($this->admin);
        $permisoUsarPos = Permiso::where('slug', 'usar-pos')->first();

        $response = $this->post('/roles', [
            'nombre' => 'Gerente',
            'descripcion' => 'Encargado de la tienda',
            'permisos' => [$permisoUsarPos->id],
        ]);

        $response->assertRedirect('/roles');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('roles', ['nombre' => 'Gerente', 'slug' => 'gerente', 'protegido' => false]);
        $this->assertDatabaseHas('permiso_rol', ['rol' => 'gerente', 'permiso_id' => $permisoUsarPos->id]);
    }

    public function test_crear_rol_genera_slug_unico()
    {
        $this->actingAs($this->admin);
        $this->post('/roles', ['nombre' => 'Supervisor']);
        $this->post('/roles', ['nombre' => 'Supervisor']);

        $this->assertDatabaseHas('roles', ['slug' => 'supervisor']);
        $this->assertDatabaseHas('roles', ['slug' => 'supervisor_2']);
    }

    public function test_actualizar_rol_quita_permisos()
    {
        $this->actingAs($this->admin);
        $usarPos = Permiso::where('slug', 'usar-pos')->first();
        $verFacturas = Permiso::where('slug', 'ver-facturas')->first();
        $rol = Rol::create(['nombre' => 'Cajero', 'slug' => 'cajero']);
        $rol->permisos()->sync([$usarPos->id, $verFacturas->id]);

        $response = $this->put('/roles/'.$rol->id, [
            'nombre' => 'Cajero',
            'descripcion' => null,
            'permisos' => [$usarPos->id],
        ]);

        $response->assertRedirect('/roles');
        $this->assertDatabaseHas('permiso_rol', ['rol' => 'cajero', 'permiso_id' => $usarPos->id]);
        $this->assertDatabaseMissing('permiso_rol', ['rol' => 'cajero', 'permiso_id' => $verFacturas->id]);
    }

    public function test_no_se_puede_editar_rol_protegido()
    {
        $this->actingAs($this->admin);
        $adminRol = Rol::where('slug', 'admin')->first();

        $response = $this->get('/roles/'.$adminRol->id.'/edit');

        $response->assertRedirect('/roles');
        $response->assertSessionHasErrors('error');
    }

    public function test_no_se_puede_actualizar_rol_protegido()
    {
        $this->actingAs($this->admin);
        $adminRol = Rol::where('slug', 'admin')->first();

        $response = $this->put('/roles/'.$adminRol->id, [
            'nombre' => 'Administrador',
            'permisos' => [],
        ]);

        $response->assertRedirect('/roles');
        $this->assertDatabaseHas('roles', ['id' => $adminRol->id, 'nombre' => 'Administrador']);
    }

    public function test_no_se_puede_eliminar_rol_protegido()
    {
        $this->actingAs($this->admin);
        $adminRol = Rol::where('slug', 'admin')->first();

        $response = $this->delete('/roles/'.$adminRol->id);

        $response->assertRedirect('/roles');
        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('roles', ['id' => $adminRol->id]);
    }

    public function test_no_se_puede_eliminar_rol_con_usuarios()
    {
        $this->actingAs($this->admin);
        $rol = Rol::create(['nombre' => 'Cajero', 'slug' => 'cajero']);
        User::factory()->create(['rol' => 'cajero']);

        $response = $this->delete('/roles/'.$rol->id);

        $response->assertRedirect('/roles');
        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('roles', ['id' => $rol->id]);
    }

    public function test_eliminar_rol_sin_usuarios_limpia_permisos()
    {
        $this->actingAs($this->admin);
        $usarPos = Permiso::where('slug', 'usar-pos')->first();
        $rol = Rol::create(['nombre' => 'Supervisor', 'slug' => 'supervisor']);
        $rol->permisos()->sync([$usarPos->id]);

        $response = $this->delete('/roles/'.$rol->id);

        $response->assertRedirect('/roles');
        $this->assertDatabaseMissing('roles', ['id' => $rol->id]);
        $this->assertDatabaseMissing('permiso_rol', ['rol' => 'supervisor']);
    }

    public function test_usuario_obtiene_permisos_de_su_rol()
    {
        $this->actingAs($this->admin);
        $usarPos = Permiso::where('slug', 'usar-pos')->first();
        $vendedor = Rol::create(['nombre' => 'Vendedor', 'slug' => 'vendedor']);
        $vendedor->permisos()->sync([$usarPos->id]);

        $conAcceso = User::factory()->create(['rol' => 'vendedor']);
        $this->actingAs($conAcceso);
        $this->get('/pos')->assertStatus(200);

        $sinAcceso = User::factory()->create(['rol' => 'sinacceso']);
        $this->actingAs($sinAcceso);
        $this->get('/pos')->assertStatus(403);
    }

    public function test_usuario_sin_rol_no_tiene_permisos()
    {
        $usuario = User::factory()->create(['rol' => 'inexistente']);
        $this->actingAs($usuario);

        $this->get('/pos')->assertStatus(403);
    }
}
