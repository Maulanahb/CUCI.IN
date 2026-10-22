@props([
    'customers',
    'prefix' => null,
])

@php
    $prefix = $prefix ?? (request()->routeIs('admin.*') ? 'admin' : 'staff');
@endphp

<x-ui.card {{ $attributes->class(['flex flex-col']) }}>
    <div class="mb-4 flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
        <div class="flex items-center gap-2">
            <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M10 10a4 4 0 100-8 4 4 0 000 8z" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900">Pelanggan Baru</h3>
                <p class="text-xs text-slate-500">
                    @if ($customers->isEmpty())
                        Pendaftar terbaru.
                    @else
                        {{ $customers->count() }} pendaftar terbaru.
                    @endif
                </p>
            </div>
        </div>
        <a href="{{ route("{$prefix}.customers.index") }}" class="shrink-0 text-xs font-semibold text-brand-600 hover:text-brand-700">
            Semua &rarr;
        </a>
    </div>

    @if ($customers->isEmpty())
        <div class="flex flex-1 flex-col items-center justify-center py-8 text-center">
            <div class="flex size-11 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
            </div>
            <p class="mt-3 text-xs font-semibold text-slate-700">Belum Ada Pelanggan</p>
            <p class="mt-1 max-w-xs text-xs text-slate-500">Data customer laundry baru akan ditampilkan di sini.</p>
        </div>
    @else
        <ul class="space-y-4">
            @foreach ($customers as $customer)
                @php
                    $initials = collect(preg_split('/\s+/u', trim((string) $customer->name)))
                        ->filter()
                        ->take(2)
                        ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
                        ->implode('');
                @endphp

                <li class="flex items-center gap-2.5">
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-xs font-bold text-emerald-700">
                        {{ $initials !== '' ? $initials : '?' }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <p class="min-w-0 truncate text-xs font-bold text-slate-900">{{ $customer->name }}</p>

                            @if ($customer->transactions_count > 0)
                                <x-ui.badge tone="neutral" size="sm" class="shrink-0">
                                    {{ $customer->transactions_count }} transaksi
                                </x-ui.badge>
                            @else
                                <x-ui.badge tone="warning" size="sm" class="shrink-0" title="Pelanggan belum memiliki transaksi">
                                    0 transaksi
                                </x-ui.badge>
                            @endif
                        </div>

                        <p class="mt-0.5 min-w-0 truncate text-xs text-slate-500">
                            {{ $customer->phone ?? 'Tanpa nomor HP' }}
                            <span class="text-slate-300">&bull;</span>
                            Terdaftar {{ $customer->created_at?->locale('id')->diffForHumans() ?? '-' }}
                        </p>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-ui.card>
