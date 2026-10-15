<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Customer\ComplaintController as CustomerComplaintController;
use App\Http\Controllers\Customer\TransactionController as CustomerTransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Staff\CustomerController;
use App\Http\Controllers\Staff\TransactionController as StaffTransactionController;
use App\Http\Controllers\Staff\PaymentController;
use App\Http\Controllers\Staff\ServiceController;
use App\Http\Controllers\TransactionLookupController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Rute Publik: Lacak Cucian / Cek Transaksi Menggunakan Kode Nota
Route::get('/lookup', [TransactionLookupController::class, 'index'])
    ->name('transactions.lookup');

// Rute Home / Root
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'staff') {
            return redirect()->route('staff.dashboard');
        }

        if ($user->role === 'customer') {
            return redirect()->route('customer.dashboard');
        }
    }

    return redirect()->route('login');
})->name('home');

// Rute Autentikasi Pengguna Tamu
Route::middleware('guest')->group(function () {

    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->name('login.store');

    Route::get('/register', [RegisterController::class, 'create'])
        ->name('register');

    Route::post('/register', [RegisterController::class, 'store'])
        ->name('register.store');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])
        ->name('password.request');

    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('/reset-password', [ResetPasswordController::class, 'store'])
        ->name('password.update');
});


// Rute Khusus Pengguna Terautentikasi & Aktif
Route::middleware(['auth', 'active'])->group(function () {

    // Logout
    Route::post('/logout', [LoginController::class, 'destroy'])
        ->name('logout');


    // Shortcut dashboard umum sesuai role
    Route::get('/dashboard', function () {

        return match (Auth::user()->role) {

            'admin' => redirect()->route('admin.dashboard'),

            'staff' => redirect()->route('staff.dashboard'),

            'customer' => redirect()->route('customer.dashboard'),

            default => redirect()->route('login'),

        };

    })->name('dashboard');


    // =========================================================
    // AREA ADMINISTRATOR
    // =========================================================

    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::get('/dashboard', [DashboardController::class, 'admin'])
                ->name('dashboard');
        });


    // =========================================================
    // AREA STAFF OPERASIONAL
    // =========================================================

    Route::middleware('role:staff')
        ->prefix('staff')
        ->name('staff.')
        ->group(function () {

            // -------------------------------------------------
            // Dashboard Staff
            // -------------------------------------------------

            Route::get('/dashboard', [DashboardController::class, 'staff'])
                ->name('dashboard');


            // -------------------------------------------------
            // Kelola Transaksi
            // -------------------------------------------------

            // Daftar transaksi
            Route::get('/transactions', [StaffTransactionController::class, 'index'])
                ->name('transactions.index');

            // Detail transaksi
            Route::get('/transactions/{transaction}', [StaffTransactionController::class, 'show'])
                ->name('transactions.show');

            // Update status cucian
            Route::patch('/transactions/{transaction}/status', [StaffTransactionController::class, 'updateStatus'])
                ->name('transactions.status');

            // Membuat transaksi baru
            Route::post('/transactions', [StaffTransactionController::class, 'store'])
                ->name('transactions.store');


            // -------------------------------------------------
            // Pembayaran
            // -------------------------------------------------

            // Daftar pembayaran
            Route::get('/payments', [PaymentController::class, 'index'])
                ->name('payments.index');

            // Detail pembayaran
            Route::get('/payments/{payment}', [PaymentController::class, 'show'])
                ->name('payments.show');

            // Verifikasi pembayaran
            Route::patch('/payments/{payment}/verify', [PaymentController::class, 'verify'])
                ->name('payments.verify');


            // -------------------------------------------------
            // Layanan
            // -------------------------------------------------

            // Daftar layanan
            Route::get('/services', [ServiceController::class, 'index'])
                ->name('services.index');

            // Detail layanan
            Route::get('/services/{service}', [ServiceController::class, 'show'])
                ->name('services.show');
        });


    // =========================================================
    // AREA PENGELOLAAN PELANGGAN
    // ADMIN & STAFF
    // =========================================================

    Route::middleware('role:admin,staff')->group(function () {

        // -------------------------------------------------
        // Customer untuk Staff
        // -------------------------------------------------

        Route::prefix('staff')
            ->name('staff.')
            ->group(function () {

                Route::get('/customers', [CustomerController::class, 'index'])
                    ->name('customers.index');

                Route::post('/customers', [CustomerController::class, 'store'])
                    ->name('customers.store');

                Route::put('/customers/{customer}', [CustomerController::class, 'update'])
                    ->name('customers.update');

                Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])
                    ->name('customers.destroy');
            });


        // -------------------------------------------------
        // Customer untuk Admin
        // -------------------------------------------------

        Route::prefix('admin')
            ->name('admin.')
            ->group(function () {

                Route::get('/customers', [CustomerController::class, 'index'])
                    ->name('customers.index');

                Route::post('/customers', [CustomerController::class, 'store'])
                    ->name('customers.store');

                Route::put('/customers/{customer}', [CustomerController::class, 'update'])
                    ->name('customers.update');

                Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])
                    ->name('customers.destroy');
            });
    });


    // =========================================================
    // AREA CUSTOMER
    // =========================================================

    Route::middleware('role:customer')
        ->prefix('customer')
        ->name('customer.')
        ->group(function () {

            Route::get('/dashboard', [DashboardController::class, 'customer'])
                ->name('dashboard');

            Route::get('/transactions', [CustomerTransactionController::class, 'index'])
                ->name('transactions.index');

            Route::get('/complaints', [CustomerComplaintController::class, 'index'])
                ->name('complaints.index');

            Route::get('/complaints/create', [CustomerComplaintController::class, 'create'])
                ->name('complaints.create');

            Route::post('/complaints', [CustomerComplaintController::class, 'store'])
                ->name('complaints.store');

            Route::get('/complaints/{complaint}', [CustomerComplaintController::class, 'show'])
                ->name('complaints.show');
        });
});