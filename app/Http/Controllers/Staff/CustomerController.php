<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
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

        // Query daftar pelanggan
        $customersQuery = Customer::query()
            ->with(['user'])
            ->withCount('transactions');

        // Pencarian berdasarkan Nama atau No. Telepon atau Alamat
        if ($search !== '') {
            $customersQuery->where(function (Builder $query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
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

        // Urutkan berdasarkan transaksi terbaru atau id
        $customers = $customersQuery
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        // Tentukan pelanggan yang sedang aktif / dilihat detailnya (hanya jika ada permintaan customer_id)
        $selectedCustomer = null;
        if ($selectedId) {
            $selectedCustomer = Customer::with(['user'])->find($selectedId);
        }

        // Siapkan data transaksi untuk pelanggan terpilih
        $activeTransaction = null;
        $previousTransactions = collect();
        $totalSpent = 0;

        if ($selectedCustomer) {
            // Muat seluruh transaksi untuk kalkulasi total pengeluaran
            $totalSpent = (float) $selectedCustomer->transactions()->sum('total_amount');

            // Detail riwayat order HANYA muncul jika salah satu baris transaksi di tabel diklik
            $selectedTransactionId = $request->input('transaction_id');
            if ($selectedTransactionId) {
                $activeTransaction = $selectedCustomer->transactions()
                    ->with(['details.service', 'payments.creator'])
                    ->find($selectedTransactionId);
            }

            // Seluruh daftar riwayat transaksi pelanggan untuk tabel
            $previousTransactions = $selectedCustomer->transactions()
                ->with(['details.service', 'payments'])
                ->latest()
                ->take(15)
                ->get();
        }

        return view('staff.customers.index', compact(
            'customers',
            'selectedCustomer',
            'activeTransaction',
            'previousTransactions',
            'totalCustomersCount',
            'counts',
            'search',
            'statusFilter',
            'totalSpent'
        ));
    }
}
