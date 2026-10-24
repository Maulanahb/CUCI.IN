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
                <x-lucide-user-plus class="size-4" aria-hidden="true" />
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
        <a href="{{ route("{$prefix}.customers.index") }}" class="inline-flex items-center gap-1 shrink-0 text-xs font-semibold text-brand-700 hover:text-brand-800 hover:underline">
            <span>Semua</span>
            <x-lucide-arrow-right class="size-3" />
        </a>
    </div>

    @if ($customers->isEmpty())
        <div class="flex flex-1 flex-col items-center justify-center py-8 text-center">
            <div class="flex size-11 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <x-lucide-users class="size-5" aria-hidden="true" />
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

                        <p class="mt-0.5 min-w-0 truncate text-2xs text-slate-500">
                            <span class="font-sans tabular-nums">{{ $customer->phone ?? 'Tanpa nomor HP' }}</span>
                            <span class="text-slate-300">&bull;</span>
                            Terdaftar {{ $customer->created_at?->locale('id')->diffForHumans() ?? '-' }}
                        </p>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-ui.card>
