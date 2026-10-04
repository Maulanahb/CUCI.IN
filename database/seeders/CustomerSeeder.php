<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::updateOrCreate(
            ['phone' => '081234567890'],
            [
                'name' => 'Budi Santoso',
                'address' => 'Malang',
                'user_id' => null,
            ]
        );

        Customer::updateOrCreate(
            ['phone' => '081234567891'],
            [
                'name' => 'Siti Aminah',
                'address' => 'Malang',
                'user_id' => null,
            ]
        );

        Customer::updateOrCreate(
            ['phone' => '081234567892'],
            [
                'name' => 'Andi Pratama',
                'address' => 'Malang',
                'user_id' => null,
            ]
        );
    }
}
