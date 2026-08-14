<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class LanzadorControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermisoSeeder::class);
        config()->set('session.driver', 'database');
    }

    public function test_token_desconocido_responde_204_sin_afectar_sesiones()
    {
        $user = User::factory()->create(['rol' => 'admin', 'password' => Hash::make('secreto')]);

        $cookie = Str::random(40);
        $this->call('POST', '/login', ['usuario' => $user->usuario, 'password' => 'secreto'], ['laravel_session' => $cookie]);
        $sesion = session()->getId();

        $this->post('/lanzador/cerrar-sesion', ['token' => 'token-inexistente'])->assertStatus(204);

        $this->assertDatabaseHas('sessions', ['id' => $sesion]);
        $this->call('GET', '/dashboard', [], ['laravel_session' => $sesion])->assertStatus(200);
    }
}
