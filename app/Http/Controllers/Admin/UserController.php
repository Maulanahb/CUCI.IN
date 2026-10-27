<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Tampilkan daftar seluruh pengguna dengan filter peran, status, dan pencarian.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $roleFilter = (string) $request->input('role', 'all');
        $statusFilter = (string) $request->input('status', 'all');

        $query = User::query()->latest('id');

        // Pencarian Nama atau Email
        if ($search !== '') {
            $searchLower = mb_strtolower($search);
            $query->where(function (Builder $q) use ($searchLower) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(email) LIKE ?', ["%{$searchLower}%"]);
            });
        }

        // Filter Peran
        if ($roleFilter !== 'all' && in_array($roleFilter, ['admin', 'staff', 'customer'], true)) {
            $query->where('role', $roleFilter);
        }

        // Filter Status Aktif
        if ($statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $users = $query->paginate(10)->withQueryString();

        // Statistik ringkas untuk tab filter
        $counts = [
            'all' => User::count(),
            'admin' => User::where('role', 'admin')->count(),
            'staff' => User::where('role', 'staff')->count(),
            'customer' => User::where('role', 'customer')->count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
        ];

        return view('admin.users.index', compact(
            'users',
            'counts',
            'search',
            'roleFilter',
            'statusFilter'
        ));
    }

    /**
     * Tampilkan formulir penambahan akun pengguna baru.
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Simpan akun pengguna baru ke basis data.
     */
    public function store(UserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active'),
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Akun pengguna baru berhasil ditambahkan.');
    }

    /**
     * Tampilkan formulir edit akun pengguna.
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Perbarui data akun pengguna yang sudah ada.
     */
    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        // Proteksi self-account: Admin tidak boleh mencabut role admin atau menonaktifkan diri sendiri
        if ($user->id === auth()->id()) {
            if ($validated['role'] !== 'admin') {
                return back()->withInput()->withErrors([
                    'role' => 'Aksi ditolak: Anda tidak dapat mengubah peran akun administrator Anda sendiri.',
                ]);
            }

            if (! $request->boolean('is_active')) {
                return back()->withInput()->withErrors([
                    'is_active' => 'Aksi ditolak: Anda tidak dapat menonaktifkan akun sendiri.',
                ]);
            }
        }

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active'),
        ];

        // Jika password diisi, perbarui kata sandi baru
        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * Sakelar cepat aktivasi atau deaktivasi status pengguna.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        // Proteksi mutlak: Admin tidak boleh menonaktifkan dirinya sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Aksi ditolak: Anda tidak dapat menonaktifkan akun administrator Anda sendiri.');
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status akun {$user->name} berhasil {$statusText}.");
    }

    /**
     * Hapus akun pengguna secara permanen.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Proteksi mutlak: Admin tidak boleh menghapus akun sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Aksi ditolak: Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Akun pengguna {$userName} berhasil dihapus dari sistem.");
    }
}
