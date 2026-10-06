<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Seed customer contoh: guest (tanpa akun) dan satu customer terdaftar.
     */
    public function run(): void
    {
        $guests = [
            ['name' => 'Budi Santoso', 'phone' => '081234567801', 'address' => 'Jl. Melati No. 1'],
            ['name' => 'Siti Aminah', 'phone' => '081234567802', 'address' => 'Jl. Mawar No. 2'],
            ['name' => 'Andi Pratama', 'phone' => '081234567803', 'address' => 'Jl. Kenanga No. 3'],
        ];

        foreach ($guests as $guest) {
            Customer::updateOrCreate(['phone' => $guest['phone']], $guest);
        }

        $customerUser = User::updateOrCreate(
            ['email' => 'customer@cuci.in'],
            ['name' => 'Rina Wulandari', 'password' => 'password', 'role' => 'customer', 'is_active' => true],
        );

        Customer::updateOrCreate(
            ['phone' => '081234567804'],
            ['user_id' => $customerUser->id, 'name' => 'Rina Wulandari', 'address' => 'Jl. Anggrek No. 4'],
        );
    }
}
