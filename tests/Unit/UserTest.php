<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_username_retorna_usuario()
    {
        $user = User::factory()->create(['usuario' => 'admin']);

        $this->assertEquals('usuario', $user->username());
    }

    public function test_rol_admin()
    {
        $user = User::factory()->create(['rol' => 'admin']);

        $this->assertEquals('admin', $user->rol);
    }

    public function test_rol_cajero()
    {
        $user = User::factory()->create(['rol' => 'cajero']);

        $this->assertEquals('cajero', $user->rol);
    }

    public function test_password_hasheado()
    {
        $user = User::factory()->create(['password' => bcrypt('secreto')]);

        $this->assertTrue(password_verify('secreto', $user->password));
    }
}
