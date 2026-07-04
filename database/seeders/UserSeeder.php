<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['usuario' => 'admin'],
            [
                'name' => 'Administrador',
                'password' => bcrypt('admin123'),
                'rol' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['usuario' => 'cajero'],
            [
                'name' => 'Cajero',
                'password' => bcrypt('cajero123'),
                'rol' => 'cajero',
            ]
        );
    }
}
