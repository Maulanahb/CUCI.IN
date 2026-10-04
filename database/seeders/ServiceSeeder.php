<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Service::updateOrCreate(
            ['name' => 'Cuci Kering'],
            [
                'price' => 7000,
                'estimated_duration' => 2,
                'description' => 'Layanan cuci kering per kilogram',
                'is_active' => true,
            ]
        );

        Service::updateOrCreate(
            ['name' => 'Cuci + Setrika'],
            [
                'price' => 10000,
                'estimated_duration' => 2,
                'description' => 'Layanan cuci dan setrika per kilogram',
                'is_active' => true,
            ]
        );

        Service::updateOrCreate(
            ['name' => 'Setrika'],
            [
                'price' => 5000,
                'estimated_duration' => 1,
                'description' => 'Layanan setrika per kilogram',
                'is_active' => true,
            ]
        );
    }
}
