<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Tampilkan riwayat transaksi milik pelanggan yang sedang login.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = auth()->user();
        /** @var Customer|null $customer */
        $customer = $user->customer;

        if (! $customer) {
            $stats = [
                'total_transactions' => 0,
                'active_orders' => 0,
                'in_progress_count' => 0,
                'ready_count' => 0,
                'unpaid_amount' => 0.0,
                'unpaid_count' => 0,
                'total_spent' => 0.0,
            ];

            return view('customer.transactions.index', [
                'customer' => null,
                'paginatedTransactions' => new LengthAwarePaginator([], 0, 8),
                'selectedTransaction' => null,
                'stats' => $stats,
            ]);
        }

        // Ambil seluruh transaksi customer untuk kalkulasi statistik ringkasan
        $allTransactions = $customer->transactions()
            ->with(['payments', 'details.service', 'statusHistories.changedBy'])
            ->latest('id')
            ->get();

        // 1. Total Transaksi
        $totalTransactions = $allTransactions->count();

        // 2. Cucian Berjalan (Status Diterima, Diproses, Selesai)
        $activeOrders = $allTransactions->whereIn('status', ['Diterima', 'Diproses', 'Selesai'])->count();
        $inProgressCount = $allTransactions->whereIn('status', ['Diterima', 'Diproses'])->count();
        $readyCount = $allTransactions->where('status', 'Selesai')->count();

        // 3. Menunggu Pelunasan
        $unpaidAmount = 0.0;
        $unpaidCount = 0;
        foreach ($allTransactions as $trx) {
            if ($trx->status === 'Dibatalkan') {
                continue;
            }

            $paid = (float) $trx->payments->where('verification_status', '!=', 'Ditolak')->sum('amount');
            $remaining = max(0.0, (float) $trx->total_amount - $paid);
            if ($remaining > 0) {
                $unpaidAmount += $remaining;
                $unpaidCount++;
            }
        }

        // 4. Total Pengeluaran (Akumulasi nilai transaksi yang tidak dibatalkan)
        $totalSpent = (float) $allTransactions->where('status', '!=', 'Dibatalkan')->sum('total_amount');

        $stats = [
            'total_transactions' => $totalTransactions,
            'active_orders' => $activeOrders,
            'in_progress_count' => $inProgressCount,
            'ready_count' => $readyCount,
            'unpaid_amount' => $unpaidAmount,
            'unpaid_count' => $unpaidCount,
            'total_spent' => $totalSpent,
        ];

        // Query daftar transaksi dengan filter
        $query = $customer->transactions()
            ->with(['payments', 'details.service', 'statusHistories.changedBy'])
            ->latest('id');

        // Filter kata kunci (kode transaksi atau nama layanan)
        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                    ->orWhereHas('details.service', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter status cucian
        if ($statusCucian = $request->query('status_cucian')) {
            if (in_array($statusCucian, ['Diterima', 'Diproses', 'Selesai', 'Diambil', 'Dibatalkan'], true)) {
                $query->where('status', $statusCucian);
            }
        }

        // Filter status pembayaran
        if ($statusPembayaran = $request->query('status_pembayaran')) {
            if ($statusPembayaran === 'Lunas') {
                $query->whereRaw(
                    '(SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payments.transaction_id = transactions.id AND payments.verification_status != ?) >= transactions.total_amount',
                    ['Ditolak']
                );
            } elseif ($statusPembayaran === 'Belum Lunas') {
                $query->whereRaw(
                    '(SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payments.transaction_id = transactions.id AND payments.verification_status != ?) < transactions.total_amount',
                    ['Ditolak']
                );
            }
        }

        /** @var LengthAwarePaginator $paginatedTransactions */
        $paginatedTransactions = $query->paginate(8)->withQueryString();

        // Tentukan transaksi yang ditampilkan di panel rincian dokumen
        $selectedCode = $request->query('selected');
        $selectedTransaction = null;

        if ($selectedCode) {
            $selectedTransaction = $allTransactions->firstWhere('transaction_code', $selectedCode);
        }

        if (! $selectedTransaction && $paginatedTransactions->isNotEmpty()) {
            $selectedTransaction = $paginatedTransactions->first();
        }

        return view('customer.transactions.index', [
            'customer' => $customer,
            'paginatedTransactions' => $paginatedTransactions,
            'selectedTransaction' => $selectedTransaction,
            'stats' => $stats,
        ]);
    }
}
