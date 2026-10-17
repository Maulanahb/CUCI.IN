<x-layouts::app title="Dashboard Staff">
    <div class="space-y-6">
        {{-- Banner Sapaan Staff & Quick Action Kasir --}}
        @include('staff.partials.staff-header')

        {{-- Statistik Operasional Hari Ini (4 Metric Cards) --}}
        @include('staff.partials.staff-metrics')

        {{-- Pipeline Antrean & Status Operasional Cucian --}}
        @include('staff.partials.staff-pipeline')

        {{-- Grid Konten 2 Kolom: Antrean Cucian Terkini & Katalog Layanan --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Kolom Kiri (2/3 width): Transaksi & Antrean Terkini --}}
            @include('staff.partials.staff-recent-orders')

            {{-- Kolom Kanan (1/3 width): Katalog Layanan & Aksi Operasional --}}
            @include('staff.partials.staff-quick-catalog')
        </div>
    </div>
</x-layouts::app>