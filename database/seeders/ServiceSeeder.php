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
            [
                'name' => 'Cuci Kering',
                'price' => 7000,
                'unit' => 'kg',
                'service_type' => 'reguler',
                'is_express' => false,
                'minimum_order' => 2.00,
                'estimated_duration' => 48,
                'description' => 'Cuci dan keringkan, tanpa setrika.',
                'is_active' => true,
            ],
            [
                'name' => 'Cuci + Setrika',
                'price' => 10000,
                'unit' => 'kg',
                'service_type' => 'reguler',
                'is_express' => false,
                'minimum_order' => 2.00,
                'estimated_duration' => 48,
                'description' => 'Cuci bersih, keringkan, dan setrika rapi siap pakai.',
                'is_active' => true,
            ],
            [
                'name' => 'Setrika',
                'price' => 5000,
                'unit' => 'kg',
                'service_type' => 'reguler',
                'is_express' => false,
                'minimum_order' => 1.00,
                'estimated_duration' => 24,
                'description' => 'Setrika saja untuk pakaian bersih.',
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Express',
                'price' => 15000,
                'unit' => 'kg',
                'service_type' => 'express',
                'is_express' => true,
                'minimum_order' => 2.00,
                'estimated_duration' => 6,
                'description' => 'Cuci + setrika prioritas selesai di hari yang sama (6 jam).',
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Bedcover',
                'price' => 20000,
                'unit' => 'pcs',
                'service_type' => 'khusus',
                'is_express' => false,
                'minimum_order' => 1.00,
                'estimated_duration' => 72,
                'description' => 'Layanan lama yang dinonaktifkan sementara.',
                'is_active' => false,
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(['name' => $service['name']], $service);
        }
    }
}
