@php
    $currentRoutePrefix = $routePrefix ?? (request()->routeIs('admin.*') ? 'admin' : 'staff');
    $isAdmin = auth()->user()?->role === 'admin';
@endphp

<x-layouts::app title="Katalog Layanan Laundry">
    <div
        class="space-y-6"
        @keydown.slash.window="
            if (!createModalOpen && !editModalOpen && !deleteModalOpen && !detailModalOpen && !['INPUT', 'TEXTAREA', 'SELECT'].includes($event.target.tagName)) {
                $event.preventDefault();
                const search = document.getElementById('service-search-input');
                if (search) { search.focus(); search.select(); }
            }
        "
        @keydown.escape.window="
            createModalOpen = false;
            editModalOpen = false;
            deleteModalOpen = false;
            detailModalOpen = false;
        "
        x-data="{
            createModalOpen: false,
            editModalOpen: false,
            deleteModalOpen: false,
            detailModalOpen: false,
            activeService: null,
            editForm: {
                id: '',
                name: '',
                price: '',
                estimated_duration: '',
                description: '',
                is_active: true
            },
            deleteForm: {
                id: '',
                name: '',
                transactionCount: 0
            },
            detailData: {
                id: '',
                name: '',
                price: 0,
                estimated_duration: 0,
                description: '',
                is_active: true,
                transaction_details_count: 0,
                calcWeight: 3
            },
            openCreateModal() {
                this.createModalOpen = true;
                this.$nextTick(() => {
                    document.getElementById('create_name')?.focus();
                });
            },
            openEditModal(service) {
                this.editForm = {
                    id: service.id,
                    name: service.name,
                    price: Math.round(parseFloat(service.price)),
                    estimated_duration: service.estimated_duration,
                    description: service.description || '',
                    is_active: Boolean(service.is_active)
                };
                this.editModalOpen = true;
                this.$nextTick(() => {
                    document.getElementById('edit_name')?.focus();
                });
            },
            openDeleteModal(service) {
                this.deleteForm = {
                    id: service.id,
                    name: service.name,
                    transactionCount: service.transaction_details_count || 0
                };
                this.deleteModalOpen = true;
            },
            openDetailModal(service) {
                this.detailData = {
                    id: service.id,
                    name: service.name,
                    price: parseFloat(service.price),
                    estimated_duration: service.estimated_duration,
                    description: service.description || 'Tidak ada keterangan tambahan.',
                    is_active: Boolean(service.is_active),
                    transaction_details_count: service.transaction_details_count || 0,
                    calcWeight: 3
                };
                this.detailModalOpen = true;
            },
            formatRupiah(val) {
                const num = Math.max(0, parseFloat(val) || 0);
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
            }
        }"
    >
        {{-- Header & Aksi --}}
        @include('services.partials.service-header')

        {{-- Alert Notifikasi Status --}}
        @if (session('status'))
            <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50/90 p-4 text-xs font-medium text-emerald-800 shadow-2xs sm:text-sm">
                <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50/90 p-4 text-xs text-rose-800 shadow-2xs sm:text-sm">
                <div class="flex items-center gap-2 font-semibold">
                    <svg class="size-4 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Perhatian:</span>
                </div>
                <ul class="mt-2 list-inside list-disc space-y-0.5 pl-2 font-normal">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Statistik KPI --}}
        @include('services.partials.service-stats')

        {{-- Toolbar Filter & Pencarian --}}
        @include('services.partials.service-toolbar')

        {{-- Tabel Master Layanan & Pagination --}}
        @include('services.partials.service-table')

        {{-- Dialog Modal-Modal CRUD & Simulasi --}}
        @include('services.partials.service-modals')
    </div>
</x-layouts::app>
