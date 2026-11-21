<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Tampilkan halaman data master pelanggan dengan fitur pencarian, filter status, dan detail lengkap pelanggan terpilih.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $statusFilter = (string) $request->input('status', 'all');
        $selectedId = $request->input('customer_id');
        $routePrefix = $request->routeIs('admin.*') ? 'admin' : 'staff';

        // Total seluruh pelanggan terdaftar
        $totalCustomersCount = Customer::count();

        // Hitung count untuk tab filter berdasarkan transaksi terakhir pelanggan
        $counts = [
            'all' => Customer::count(),
            'processing' => Customer::whereHas('transactions', function (Builder $query) {
                $query->whereIn('status', ['Diterima', 'Diproses']);
            })->count(),
            'ready' => Customer::whereHas('transactions', function (Builder $query) {
                $query->where('status', 'Selesai');
            })->count(),
            'completed' => Customer::whereHas('transactions', function (Builder $query) {
                $query->where('status', 'Diambil');
            })->count(),
        ];

        // Query daftar pelanggan dengan eager load transaksi terbaru untuk drawer instan (0ms)
        $customersQuery = Customer::query()
            ->with([
                'user',
                'transactions' => fn ($q) => $q->latest()->take(10)->with(['details.service', 'payments']),
            ])
            ->withCount('transactions');

        // Pencarian case-insensitive berdasarkan Nama, No. Telepon, atau Alamat
        if ($search !== '') {
            $searchLower = mb_strtolower($search);
            $customersQuery->where(function (Builder $query) use ($search, $searchLower) {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereRaw('LOWER(address) LIKE ?', ["%{$searchLower}%"]);
            });
        }

        // Filter berdasarkan status transaksi
        if ($statusFilter === 'processing') {
            $customersQuery->whereHas('transactions', function (Builder $query) {
                $query->whereIn('status', ['Diterima', 'Diproses']);
            });
        } elseif ($statusFilter === 'ready') {
            $customersQuery->whereHas('transactions', function (Builder $query) {
                $query->where('status', 'Selesai');
            });
        } elseif ($statusFilter === 'completed') {
            $customersQuery->whereHas('transactions', function (Builder $query) {
                $query->where('status', 'Diambil');
            });
        }

        // Urutkan berdasarkan id desc
        $customers = $customersQuery
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        // Siapkan payload Map untuk Alpine drawer agar 0ms latency tanpa extra network request
        $customersPayloadMap = $customers->getCollection()->keyBy('id')->map(function ($c) use ($routePrefix) {
            $initials = collect(explode(' ', $c->name))
                ->map(fn ($part) => mb_substr($part, 0, 1))
                ->take(2)
                ->implode('');

            $activeTrx = $c->transactions->first(fn ($t) => in_array($t->status?->value ?? (string) $t->status, ['Diterima', 'Diproses', 'Selesai'], true));
            $totalSpent = (float) $c->transactions->sum('total_amount');

            return [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'address' => $c->address ?: '-',
                'initials' => strtoupper($initials ?: 'CU'),
                'registered_at' => $c->created_at?->translatedFormat('d M Y') ?? '-',
                'whatsapp_url' => $c->whatsapp_url,
                'total_transactions' => $c->transactions_count,
                'total_spent_formatted' => 'Rp '.number_format($totalSpent, 0, ',', '.'),
                'create_order_url' => route("{$routePrefix}.transactions.create", ['customer_id' => $c->id]),
                'update_url' => route("{$routePrefix}.customers.update", $c),
                'delete_url' => route("{$routePrefix}.customers.destroy", $c),
                'has_transactions' => $c->transactions_count > 0,
                'active_status' => $activeTrx ? ($activeTrx->status?->value ?? (string) $activeTrx->status) : null,
                'transactions' => $c->transactions->map(fn ($t) => [
                    'id' => $t->id,
                    'code' => $t->transaction_code,
                    'date' => $t->created_at?->translatedFormat('d M Y, H:i') ?? '-',
                    'status' => $t->status?->value ?? (string) $t->status,
                    'payment_status' => $t->payment_status?->value ?? (string) $t->payment_status,
                    'total_formatted' => 'Rp '.number_format((float) $t->total_amount, 0, ',', '.'),
                    'show_url' => route("{$routePrefix}.transactions.show", $t),
                    'services_summary' => $t->details->map(fn ($d) => $d->service?->name)->filter()->implode(', ') ?: 'Layanan Laundry',
                ])->values()->all(),
            ];
        });

        // Ambil ID pelanggan jika ada parameter customer_id (untuk auto-open drawer jika dibuka via direct link)
        $autoOpenCustomerId = $selectedId && $customersPayloadMap->has((int) $selectedId) ? (int) $selectedId : null;

        return view('staff.customers.index', compact(
            'customers',
            'customersPayloadMap',
            'autoOpenCustomerId',
            'totalCustomersCount',
            'counts',
            'search',
            'statusFilter',
            'routePrefix'
        ));
    }

    /**
     * Simpan data pelanggan baru ke database.
     */
    public function store(CustomerRequest $request): RedirectResponse|JsonResponse
    {
        $customer = Customer::create($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data pelanggan berhasil ditambahkan.',
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                ],
            ], 201);
        }

        $routePrefix = $request->routeIs('admin.*') ? 'admin' : 'staff';

        return redirect()->route("{$routePrefix}.customers.index", ['customer_id' => $customer->id])
            ->with('status', 'Data pelanggan berhasil ditambahkan.');
    }

    /**
     * Perbarui data pelanggan yang sudah ada.
     */
    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        $routePrefix = $request->routeIs('admin.*') ? 'admin' : 'staff';

        return redirect()->route("{$routePrefix}.customers.index", ['customer_id' => $customer->id])
            ->with('status', 'Data pelanggan berhasil diperbarui.');
    }

    /**
     * Hapus data pelanggan dari database jika tidak memiliki riwayat transaksi.
     */
    public function destroy(Request $request, Customer $customer): RedirectResponse
    {
        $routePrefix = $request->routeIs('admin.*') ? 'admin' : 'staff';

        if ($customer->transactions()->exists()) {
            return redirect()->route("{$routePrefix}.customers.index", ['customer_id' => $customer->id])
                ->withErrors(['error' => 'Pelanggan tidak dapat dihapus karena memiliki riwayat transaksi aktif.']);
        }

        $customer->delete();

        return redirect()->route("{$routePrefix}.customers.index")
            ->with('status', 'Data pelanggan berhasil dihapus.');
    }
}
