<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed akun default untuk development. Semua password: "password".
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Admin CUCI.IN', 'email' => 'admin@cuci.in', 'role' => 'admin', 'is_active' => true],
            ['name' => 'Staff CUCI.IN', 'email' => 'staff@cuci.in', 'role' => 'staff', 'is_active' => true],
            ['name' => 'Staff Nonaktif', 'email' => 'nonaktif@cuci.in', 'role' => 'staff', 'is_active' => false],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [...$user, 'password' => 'password'],
            );
        }
    }
}
