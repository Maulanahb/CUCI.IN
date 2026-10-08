<?php

namespace App\Http\Controllers;

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

        return view('admin.dashboard', compact('stats'));
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

        return view('staff.dashboard', compact('stats'));
    }

    /**
     * Tampilkan halaman dashboard untuk Pelanggan (Customer).
     */
    public function customer(): View
    {
        /** @var User $user */
        $user = auth()->user();
        $customer = $user->customer;
        $recentTransactions = $customer ? $customer->transactions()->latest()->take(5)->get() : collect();

        return view('customer.dashboard', compact('customer', 'recentTransactions'));
    }
}
