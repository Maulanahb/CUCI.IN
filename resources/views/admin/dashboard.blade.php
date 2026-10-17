<x-layouts::app title="Dashboard Admin">
    <div class="space-y-6">
        {{-- Banner & Quick Actions --}}
        @include('admin.partials.admin-header')

        {{-- 4 Kartu Metrik Ringkasan Utama --}}
        @include('admin.partials.admin-metrics')

        {{-- Pipeline Antrean & Status Operasional Cucian --}}
        @include('admin.partials.admin-pipeline')

        {{-- Grid Konten 2 Kolom: Transaksi Terkini & Katalog Layanan --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Kolom Kiri: Tabel Transaksi Terkini (2/3 width) --}}
            @include('admin.partials.admin-recent-orders')

            {{-- Kolom Kanan: Master Layanan Aktif & Shortcut Cepat (1/3 width) --}}
            @include('admin.partials.admin-quick-catalog')
        </div>
    </div>
</x-layouts::app>
