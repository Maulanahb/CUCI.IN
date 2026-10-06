<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Seed layanan laundry default. estimated_duration dalam jam.
     */
    public function run(): void
    {
        $services = [
            ['name' => 'Cuci Kering', 'price' => 7000, 'estimated_duration' => 48, 'description' => 'Cuci dan keringkan, tanpa setrika.', 'is_active' => true],
            ['name' => 'Cuci + Setrika', 'price' => 10000, 'estimated_duration' => 48, 'description' => 'Cuci, keringkan, dan setrika rapi.', 'is_active' => true],
            ['name' => 'Setrika', 'price' => 5000, 'estimated_duration' => 24, 'description' => 'Setrika saja untuk pakaian bersih.', 'is_active' => true],
            ['name' => 'Cuci Express', 'price' => 15000, 'estimated_duration' => 6, 'description' => 'Cuci + setrika selesai di hari yang sama.', 'is_active' => true],
            ['name' => 'Cuci Bedcover', 'price' => 20000, 'estimated_duration' => 72, 'description' => 'Layanan lama yang sudah tidak tersedia.', 'is_active' => false],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(['name' => $service['name']], $service);
        }
    }
}
