<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard untuk Administrator.
     */
    public function admin(): View
    {
        $stats = [
            'total_users' => User::count(),
            'total_customers' => Customer::count(),
            'total_services' => Service::where('is_active', true)->count(),
            'total_transactions' => Transaction::count(),
        ];

        $statusCounts = [
            'diterima' => Transaction::where('status', TransactionStatus::Diterima)->count(),
            'diproses' => Transaction::where('status', TransactionStatus::Diproses)->count(),
            'selesai' => Transaction::where('status', TransactionStatus::Selesai)->count(),
            'diambil' => Transaction::where('status', TransactionStatus::Diambil)->count(),
        ];

        $recentTransactions = Transaction::with(['customer', 'details.service'])
            ->latest('id')
            ->take(5)
            ->get();

        $activeServices = Service::where('is_active', true)
            ->latest('id')
            ->take(4)
            ->get();

        return view('admin.dashboard', compact('stats', 'statusCounts', 'recentTransactions', 'activeServices'));
    }

    /**
     * Tampilkan halaman dashboard untuk Staff.
     */
    public function staff(): View
    {
        $stats = [
            'total_customers' => Customer::count(),
            'active_services' => Service::where('is_active', true)->count(),
            'transactions_today' => Transaction::whereDate('created_at', today())->count(),
        ];

        $statusCounts = [
            'diterima' => Transaction::where('status', TransactionStatus::Diterima)->count(),
            'diproses' => Transaction::where('status', TransactionStatus::Diproses)->count(),
            'selesai' => Transaction::where('status', TransactionStatus::Selesai)->count(),
            'diambil' => Transaction::where('status', TransactionStatus::Diambil)->count(),
        ];

        $todayTransactions = Transaction::with(['customer', 'details.service'])
            ->latest('id')
            ->take(6)
            ->get();

        $activeServices = Service::where('is_active', true)
            ->latest('id')
            ->take(4)
            ->get();

        return view('staff.dashboard', compact('stats', 'statusCounts', 'todayTransactions', 'activeServices'));
    }

    /**
     * Tampilkan halaman dashboard untuk Pelanggan (Customer).
     */
    public function customer(): View
    {
        /** @var User $user */
        $user = auth()->user();
        /** @var Customer|null $customer */
        $customer = $user->customer;

        if (! $customer) {
            return view('customer.dashboard', [
                'customer' => null,
                'activeTransactions' => collect(),
                'recentTransactions' => collect(),
                'stats' => [
                    'active_count' => 0,
                    'unpaid_amount' => 0.0,
                    'total_count' => 0,
                ],
            ]);
        }

        $allTransactions = $customer->transactions()
            ->with(['payments', 'details.service', 'statusHistories.changedBy'])
            ->latest('id')
            ->get();

        // 1. Cucian Aktif (Status Diterima, Diproses, Selesai)
        $activeTransactions = $allTransactions->filter(function (Transaction $trx): bool {
            $status = $trx->status instanceof TransactionStatus ? $trx->status->value : (string) $trx->status;

            return in_array($status, ['Diterima', 'Diproses', 'Selesai'], true);
        })->values();

        // 2. Transaksi Terakhir (5 transaksi terbaru)
        $recentTransactions = $allTransactions->take(5);

        // 3. Kalkulasi statistik ringkasan
        $unpaidAmount = 0.0;
        foreach ($allTransactions as $trx) {
            $status = $trx->status instanceof TransactionStatus ? $trx->status->value : (string) $trx->status;
            if ($status === 'Dibatalkan') {
                continue;
            }

            $paid = (float) $trx->payments->where('verification_status', '!=', 'Ditolak')->sum('amount');
            $remaining = max(0.0, (float) $trx->total_amount - $paid);
            if ($remaining > 0) {
                $unpaidAmount += $remaining;
            }
        }

        $stats = [
            'active_count' => $activeTransactions->count(),
            'unpaid_amount' => $unpaidAmount,
            'total_count' => $allTransactions->count(),
        ];

        return view('customer.dashboard', [
            'customer' => $customer,
            'activeTransactions' => $activeTransactions,
            'recentTransactions' => $recentTransactions,
            'stats' => $stats,
        ]);
    }
}
