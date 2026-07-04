<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_con_usuario_y_password()
    {
        User::factory()->create([
            'usuario' => 'testuser',
            'password' => Hash::make('password123'),
            'rol' => 'cajero',
        ]);

        $response = $this->post('/login', [
            'usuario' => 'testuser',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_login_fallido_con_password_incorrecto()
    {
        User::factory()->create([
            'usuario' => 'testuser',
            'password' => Hash::make('correcta'),
        ]);

        $response = $this->post('/login', [
            'usuario' => 'testuser',
            'password' => 'incorrecta',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_login_fallido_con_usuario_inexistente()
    {
        $response = $this->post('/login', [
            'usuario' => 'noexiste',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_logout_funciona()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_acceso_a_dashboard_requiere_autenticacion()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_usuario_autenticado_puede_ver_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
    }
}
