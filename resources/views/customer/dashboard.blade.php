<x-layouts::app title="Dashboard Pelanggan">
    <div class="space-y-6">
        {{-- Banner Selamat Datang Pelanggan & Aksi Cepat --}}
        @include('customer.partials.customer-header')

        {{-- Kartu Ringkasan Statistik Pelanggan --}}
        @include('customer.partials.customer-stats')

        {{-- Section Utama: Cucian Aktif & Stepper Lifecycle Pelacakan --}}
        @include('customer.partials.customer-active-orders')

        {{-- Section Riwayat Transaksi Terbaru --}}
        @include('customer.partials.customer-recent-orders')
    </div>
</x-layouts::app>
