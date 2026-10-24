<?php

use App\Models\Transaction;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('laundry:check-unclaimed {--days=14 : Batas hari cucian belum diambil}', function () {
    $days = (int) $this->option('days');
    $unclaimed = Transaction::unclaimedStorage($days)->with('customer')->get();

    if ($unclaimed->isEmpty()) {
        $this->info("Tidak ada cucian yang melewati batas simpan {$days} hari.");

        return 0;
    }

    $this->warn("Ditemukan {$unclaimed->count()} cucian yang melewati batas simpan {$days} hari:");

    $this->table(
        ['Nota', 'Pelanggan', 'No. HP', 'Selesai Sejak', 'Hari di Rak', 'Sisa Tagihan'],
        $unclaimed->map(fn ($t) => [
            $t->transaction_code,
            $t->customer?->name ?? 'Umum',
            $t->customer?->phone ?? '-',
            $t->updated_at?->format('d M Y, H:i'),
            (int) $t->updated_at?->diffInDays(now()),
            'Rp '.number_format($t->remaining_amount, 0, ',', '.'),
        ])
    );

    return 0;
})->purpose('Periksa dan tampilkan daftar cucian yang selesai namun belum diambil melebihi ambang batas hari');
