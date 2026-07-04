<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'usuario' => 'admin',
            'password' => bcrypt('admin123'),
            'rol' => 'admin',
        ]);

        User::create([
            'name' => 'Cajero',
            'usuario' => 'cajero',
            'password' => bcrypt('cajero123'),
            'rol' => 'cajero',
        ]);
    }
}
