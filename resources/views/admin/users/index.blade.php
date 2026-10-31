<x-layouts::app title="Kelola Pengguna & Hak Akses">
    <div class="space-y-6">

        {{-- Flash Messages --}}
        @if (session('success'))
            <x-ui.alert variant="success" dismissible class="shadow-2xs">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if (session('error'))
            <x-ui.alert variant="danger" dismissible class="shadow-2xs">
                {{ session('error') }}
            </x-ui.alert>
        @endif

        {{-- Header Banner & Metrik Pengguna --}}
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                        Manajemen Pengguna & Otorisasi Peran
                    </h1>
                    <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                        Kelola data seluruh akun staf, kasir, dan pelanggan. Atur hak akses serta status keaktifan akun pengguna.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a
                        href="{{ route('admin.users.create') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Tambah Pengguna</span>
                    </a>
                </div>
            </div>

            {{-- Kartu Statistik Ringkas --}}
            <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:gap-4">
                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3 sm:p-4">
                    <span class="text-2xs font-semibold uppercase tracking-wider text-slate-400">Total Akun</span>
                    <p class="mt-1 text-lg font-bold text-slate-900 sm:text-2xl">{{ number_format($counts['all']) }}</p>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3 sm:p-4">
                    <span class="text-2xs font-semibold uppercase tracking-wider text-slate-400">Administrator</span>
                    <p class="mt-1 text-lg font-bold text-purple-700 sm:text-2xl">{{ number_format($counts['admin']) }}</p>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3 sm:p-4">
                    <span class="text-2xs font-semibold uppercase tracking-wider text-slate-400">Staf & Kasir</span>
                    <p class="mt-1 text-lg font-bold text-brand-700 sm:text-2xl">{{ number_format($counts['staff']) }}</p>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3 sm:p-4">
                    <span class="text-2xs font-semibold uppercase tracking-wider text-slate-400">Akun Aktif</span>
                    <p class="mt-1 text-lg font-bold text-emerald-600 sm:text-2xl">{{ number_format($counts['active']) }}</p>
                </div>
            </div>
        </div>

        {{-- Bar Filter & Pencarian --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs space-y-4">
            {{-- Tab Filter Peran --}}
            <div class="flex flex-wrap items-center gap-1.5 border-b border-slate-100 pb-3">
                @php
                    $roleTabs = [
                        'all' => ['label' => 'Semua Peran', 'count' => $counts['all']],
                        'admin' => ['label' => 'Administrator', 'count' => $counts['admin']],
                        'staff' => ['label' => 'Staf / Kasir', 'count' => $counts['staff']],
                        'customer' => ['label' => 'Pelanggan', 'count' => $counts['customer']],
                    ];
                @endphp

                @foreach ($roleTabs as $key => $tab)
                    @php $isSelected = ($roleFilter === $key); @endphp
                    <a
                        href="{{ route('admin.users.index', array_merge(request()->query(), ['role' => $key, 'page' => 1])) }}"
                        @class([
                            'inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs font-medium transition sm:text-sm',
                            'bg-brand-50 text-brand-700 font-semibold shadow-2xs' => $isSelected,
                            'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $isSelected,
                        ])
                    >
                        <span>{{ $tab['label'] }}</span>
                        <span @class([
                            'rounded-full px-1.5 py-0.5 text-2xs',
                            'bg-brand-200/70 text-brand-800' => $isSelected,
                            'bg-slate-100 text-slate-500' => ! $isSelected,
                        ])>
                            {{ $tab['count'] }}
                        </span>
                    </a>
                @endforeach
            </div>

            {{-- Form Pencarian & Filter Status --}}
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <input type="hidden" name="role" value="{{ $roleFilter }}">

                <div class="relative flex-1">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="text"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Cari berdasarkan nama atau email pengguna..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2 pl-10 pr-4 text-xs text-slate-800 placeholder-slate-400 transition focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 sm:text-sm"
                    >
                </div>

                <div class="flex items-center gap-2.5">
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 sm:text-sm"
                    >
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Hanya Aktif</option>
                        <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Hanya Nonaktif</option>
                    </select>

                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-2 text-xs font-medium text-white transition hover:bg-slate-800 sm:text-sm"
                    >
                        <span>Terapkan</span>
                    </button>

                    @if ($search !== '' || $roleFilter !== 'all' || $statusFilter !== 'all')
                        <a
                            href="{{ route('admin.users.index') }}"
                            class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 sm:text-sm"
                            title="Reset Filter"
                        >
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>Reset</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Daftar Pengguna --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/75 text-2xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3.5">Pengguna</th>
                            <th class="px-4 py-3.5">Peran (Role)</th>
                            <th class="px-4 py-3.5">Status Keaktifan</th>
                            <th class="px-4 py-3.5">Tanggal Dibuat</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($users as $user)
                            @php
                                $isSelf = $user->id === auth()->id();
                                $roleBadge = match ($user->role) {
                                    'admin' => ['label' => 'Administrator', 'class' => 'bg-purple-50 text-purple-700 border-purple-200'],
                                    'staff' => ['label' => 'Staf / Kasir', 'class' => 'bg-brand-50 text-brand-700 border-brand-200'],
                                    'owner' => ['label' => 'Owner', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
                                    'customer' => ['label' => 'Pelanggan', 'class' => 'bg-slate-100 text-slate-700 border-slate-200'],
                                    default => ['label' => ucfirst($user->role), 'class' => 'bg-slate-100 text-slate-700 border-slate-200'],
                                };
                            @endphp
                            <tr class="transition hover:bg-slate-50/50">
                                {{-- Kolom Nama & Email --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-700">
                                            {{ str($user->name)->substr(0, 1)->upper() }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <p class="truncate font-semibold text-slate-900">{{ $user->name }}</p>
                                                @if ($isSelf)
                                                    <span class="rounded bg-brand-100 px-1.5 py-0.2 text-2xs font-medium text-brand-800">
                                                        Anda
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kolom Role --}}
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-2xs font-semibold {{ $roleBadge['class'] }}">
                                        {{ $roleBadge['label'] }}
                                    </span>
                                </td>

                                {{-- Kolom Status Keaktifan (Toggle Switch) --}}
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <label class="relative inline-flex items-center gap-2 cursor-pointer select-none">
                                            <input
                                                type="checkbox"
                                                name="is_active"
                                                value="1"
                                                {{ $user->is_active ? 'checked' : '' }}
                                                {{ $isSelf ? 'disabled' : '' }}
                                                onchange="this.form.submit()"
                                                class="peer sr-only"
                                            >
                                            <div class="h-5 w-9 rounded-full bg-slate-200 transition-colors peer-checked:bg-emerald-500 peer-disabled:cursor-not-allowed peer-disabled:opacity-50"></div>
                                            <div class="absolute left-0.5 top-0.5 size-4 rounded-full bg-white shadow-xs transition-transform peer-checked:translate-x-4"></div>
                                            <span class="text-xs font-medium {{ $user->is_active ? 'text-emerald-700' : 'text-slate-400' }}">
                                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </label>
                                    </form>
                                </td>

                                {{-- Kolom Tanggal Dibuat --}}
                                <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-500">
                                    {{ $user->created_at?->locale('id')->isoFormat('D MMM Y') ?? '-' }}
                                </td>

                                {{-- Kolom Aksi --}}
                                <td class="px-5 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a
                                            href="{{ route('admin.users.edit', $user) }}"
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 shadow-2xs transition hover:bg-slate-50 hover:text-slate-900"
                                            title="Edit Pengguna"
                                        >
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                            <span>Edit</span>
                                        </a>

                                        @if (! $isSelf)
                                            <form
                                                method="POST"
                                                action="{{ route('admin.users.destroy', $user) }}"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $user->name }}? Tindakan ini tidak dapat dibatalkan.')"
                                                class="inline-block"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-white px-2.5 py-1.5 text-xs font-medium text-rose-600 shadow-2xs transition hover:bg-rose-50 hover:text-rose-700"
                                                    title="Hapus Pengguna"
                                                >
                                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                    <span>Hapus</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center">
                                    <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                        </svg>
                                    </div>
                                    <p class="mt-3 text-sm font-medium text-slate-900">Tidak ada data pengguna</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $search !== '' ? "Tidak ditemukan pengguna dengan kata kunci '{$search}'." : 'Belum ada pengguna terdaftar untuk filter ini.' }}
                                    </p>
                                    @if ($search !== '' || $roleFilter !== 'all' || $statusFilter !== 'all')
                                        <div class="mt-4">
                                            <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">
                                                Hapus filter & tampilkan semua pengguna
                                            </a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginasi --}}
            @if ($users->hasPages())
                <div class="border-t border-slate-100 p-4">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts::app>
