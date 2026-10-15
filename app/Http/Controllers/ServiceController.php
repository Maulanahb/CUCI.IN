<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceRequest;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    /**
     * Tampilkan katalog dan daftar master layanan laundry (Admin & Staff).
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $statusFilter = (string) $request->input('status', 'all');
        $routePrefix = $request->routeIs('admin.*') ? 'admin' : 'staff';

        // Hitung statistik ringkas untuk KPI card
        $totalCount = Service::count();
        $activeCount = Service::where('is_active', true)->count();
        $inactiveCount = Service::where('is_active', false)->count();
        $avgPrice = (float) Service::where('is_active', true)->avg('price');
        $minDuration = (int) Service::where('is_active', true)->min('estimated_duration');

        $stats = [
            'total' => $totalCount,
            'active' => $activeCount,
            'inactive' => $inactiveCount,
            'avg_price' => $avgPrice,
            'min_duration' => $minDuration,
        ];

        // Query daftar layanan dengan relasi count transaksi
        $servicesQuery = Service::query()
            ->withCount('transactionDetails');

        // Pencarian case-insensitive berdasarkan Nama atau Deskripsi layanan
        if ($search !== '') {
            $searchLower = mb_strtolower($search);
            $servicesQuery->where(function (Builder $query) use ($searchLower) {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$searchLower}%"]);
            });
        }

        // Filter status aktif / nonaktif
        if ($statusFilter === 'active') {
            $servicesQuery->where('is_active', true);
        } elseif ($statusFilter === 'inactive') {
            $servicesQuery->where('is_active', false);
        }

        // Urutan: Layanan aktif di atas, lalu urut abjad nama layanan
        $services = $servicesQuery
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('services.index', compact(
            'services',
            'stats',
            'search',
            'statusFilter',
            'routePrefix'
        ));
    }

    /**
     * Simpan layanan laundry baru ke database (Hanya Admin).
     */
    public function store(ServiceRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (! array_key_exists('is_active', $validated)) {
            $validated['is_active'] = true;
        }

        $service = Service::create($validated);
        $routePrefix = $request->routeIs('admin.*') ? 'admin' : 'staff';

        return redirect()->route("{$routePrefix}.services.index")
            ->with('status', "Layanan '{$service->name}' berhasil ditambahkan ke dalam sistem.");
    }

    /**
     * Perbarui data tarif, durasi, atau status layanan laundry (Hanya Admin).
     */
    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active');

        $service->update($validated);
        $routePrefix = $request->routeIs('admin.*') ? 'admin' : 'staff';

        return redirect()->route("{$routePrefix}.services.index")
            ->with('status', "Data layanan '{$service->name}' berhasil diperbarui.");
    }

    /**
     * Beralih cepat status aktif atau nonaktif layanan laundry (Hanya Admin).
     */
    public function toggle(Request $request, Service $service): RedirectResponse
    {
        if ($request->user()?->role !== 'admin') {
            abort(403, 'Aksi ini hanya dapat dilakukan oleh Administrator.');
        }

        $service->is_active = ! $service->is_active;
        $service->save();

        $statusText = $service->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';
        $routePrefix = $request->routeIs('admin.*') ? 'admin' : 'staff';

        return redirect()->route("{$routePrefix}.services.index")
            ->with('status', "Layanan '{$service->name}' berhasil {$statusText}.");
    }

    /**
     * Hapus layanan laundry dari database dengan memverifikasi data transaksi (Hanya Admin).
     */
    public function destroy(Request $request, Service $service): RedirectResponse
    {
        if ($request->user()?->role !== 'admin') {
            abort(403, 'Aksi ini hanya dapat dilakukan oleh Administrator.');
        }

        $routePrefix = $request->routeIs('admin.*') ? 'admin' : 'staff';

        // Aturan Kamus Data: Layanan yang sudah digunakan transaksi tidak boleh dihapus
        if ($service->transactionDetails()->exists()) {
            return redirect()->route("{$routePrefix}.services.index")
                ->withErrors([
                    'error' => "Layanan '{$service->name}' tidak dapat dihapus karena sudah tercatat dalam riwayat transaksi. Silakan nonaktifkan layanan jika sudah tidak lagi beroperasi.",
                ]);
        }

        $serviceName = $service->name;
        $service->delete();

        return redirect()->route("{$routePrefix}.services.index")
            ->with('status', "Layanan '{$serviceName}' berhasil dihapus dari sistem.");
    }
}
