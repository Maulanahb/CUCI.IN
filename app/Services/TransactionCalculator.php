<?php

namespace App\Services;

use App\Models\Service;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;
use InvalidArgumentException;

class TransactionCalculator
{
    /**
     * Hitung subtotal tiap item, total transaksi, dan estimasi waktu selesai.
     *
     * @param  array<int, array<string, mixed>>  $items  Daftar item berisi 'service_id' dan 'weight'
     * @param  CarbonInterface|DateTimeInterface|string|null  $startTime  Waktu acuan awal (default: sekarang)
     * @return array{
     *     items: list<array{service_id: int, weight: string, unit_price: string, subtotal: string}>,
     *     details: list<array{service_id: int, weight: string, unit_price: string, subtotal: string}>,
     *     total_amount: string,
     *     estimated_completed_at: CarbonInterface,
     *     estimated_duration: int
     * }
     *
     * @throws InvalidArgumentException
     */
    public static function calculate(array $items, CarbonInterface|DateTimeInterface|string|null $startTime = null): array
    {
        if ($items === []) {
            throw new InvalidArgumentException('Daftar item transaksi tidak boleh kosong.');
        }

        $now = match (true) {
            $startTime instanceof CarbonInterface => $startTime->copy(),
            $startTime instanceof DateTimeInterface => Carbon::instance($startTime),
            is_string($startTime) && $startTime !== '' => Carbon::parse($startTime),
            default => Carbon::now(),
        };

        $calculatedItems = [];
        $totalAmount = '0.00';
        $maxDurationHours = 0;
        $hasExpress = false;
        $serviceCache = [];

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                throw new InvalidArgumentException("Item transaksi pada indeks {$index} harus berupa array.");
            }

            if (! array_key_exists('service_id', $item)) {
                throw new InvalidArgumentException("Item transaksi pada indeks {$index} harus memiliki 'service_id'.");
            }

            if (! array_key_exists('weight', $item)) {
                throw new InvalidArgumentException("Item transaksi pada indeks {$index} harus memiliki 'weight'.");
            }

            $serviceId = $item['service_id'];
            $weight = $item['weight'];

            if (! is_numeric($weight) || bccomp((string) $weight, '0', 4) <= 0) {
                throw new InvalidArgumentException("Berat cucian untuk service ID {$serviceId} harus berupa angka lebih besar dari 0.");
            }

            if (! array_key_exists($serviceId, $serviceCache)) {
                $serviceCache[$serviceId] = Service::find($serviceId);
            }

            /** @var Service|null $service */
            $service = $serviceCache[$serviceId];

            if ($service === null) {
                throw new InvalidArgumentException("Layanan dengan ID {$serviceId} tidak ditemukan.");
            }

            if (! $service->is_active) {
                throw new InvalidArgumentException("Layanan '{$service->name}' (ID: {$serviceId}) sedang tidak aktif dan tidak dapat dipilih.");
            }

            // Ambil harga dan durasi resmi dari database, abaikan input dari luar
            $unitPrice = self::formatDecimal((string) $service->price);
            $weightFormatted = self::formatDecimal((string) $weight);

            // Subtotal = weight x unit_price
            $rawSubtotal = bcmul($unitPrice, (string) $weight, 4);
            $subtotal = self::formatDecimal($rawSubtotal);

            // Akumulasi total transaksi
            $totalAmount = bcadd($totalAmount, $subtotal, 2);

            // Durasi terlama (estimated_duration dalam jam)
            $duration = (int) $service->estimated_duration;
            if ($duration > $maxDurationHours) {
                $maxDurationHours = $duration;
            }

            if ($service->is_express) {
                $hasExpress = true;
            }

            $calculatedItems[] = [
                'service_id' => (int) $service->id,
                'service_name' => (string) $service->name,
                'unit' => (string) ($service->unit ?? 'kg'),
                'unit_label' => (string) ($service->unit_label ?? 'Kg'),
                'service_type' => (string) ($service->service_type ?? 'reguler'),
                'is_express' => (bool) $service->is_express,
                'weight' => $weightFormatted,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ];
        }

        $estimatedCompletedAt = $now->addHours($maxDurationHours);

        return [
            'items' => $calculatedItems,
            'details' => $calculatedItems,
            'total_amount' => $totalAmount,
            'estimated_completed_at' => $estimatedCompletedAt,
            'estimated_duration' => $maxDurationHours,
            'has_express' => $hasExpress,
        ];
    }

    /**
     * Memungkinkan pemanggilan objek sebagai callable/invokable.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    public function __invoke(array $items, CarbonInterface|DateTimeInterface|string|null $startTime = null): array
    {
        return self::calculate($items, $startTime);
    }

    /**
     * Format dan bulatkan angka desimal secara konsisten (decimal 12,2).
     */
    private static function formatDecimal(string $number, int $decimals = 2): string
    {
        if (function_exists('bcround')) {
            return bcround($number, $decimals);
        }

        return number_format(round((float) $number, $decimals, PHP_ROUND_HALF_UP), $decimals, '.', '');
    }
}
