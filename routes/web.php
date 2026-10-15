<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Customer\ComplaintController as CustomerComplaintController;
use App\Http\Controllers\Customer\TransactionController as CustomerTransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\Staff\CustomerController;
use App\Http\Controllers\Staff\PaymentController;
use App\Http\Controllers\Staff\TransactionController as StaffTransactionController;
use App\Http\Controllers\TransactionLookupController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Rute Publik: Lacak Cucian / Cek Transaksi Menggunakan Kode Nota (Tanpa Login)
Route::get('/lookup', [TransactionLookupController::class, 'index'])
    ->name('transactions.lookup');

// Rute Home / Root: Mengarahkan pengguna sesuai status login & role
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

// Rute Autentikasi Pengguna Tamu (Guest)
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
            Route::get('/dashboard', [DashboardController::class, 'staff'])
                ->name('dashboard');
        });

    // =========================================================
    // AREA PENGELOLAAN PELANGGAN & TRANSAKSI (ADMIN & STAFF)
    // =========================================================
    Route::middleware('role:admin,staff')->group(function () {

        // -------------------------------------------------
        // Pengelolaan untuk Staff (dapat diakses Admin juga)
        // -------------------------------------------------
        Route::prefix('staff')
            ->name('staff.')
            ->group(function () {
                // Customer
                Route::get('/customers', [CustomerController::class, 'index'])
                    ->name('customers.index');

                Route::post('/customers', [CustomerController::class, 'store'])
                    ->name('customers.store');

                Route::put('/customers/{customer}', [CustomerController::class, 'update'])
                    ->name('customers.update');

                Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])
                    ->name('customers.destroy');

                // Transaksi
                Route::get('/transactions/create', [StaffTransactionController::class, 'create'])
                    ->name('transactions.create');

                Route::get('/transactions', [StaffTransactionController::class, 'index'])
                    ->name('transactions.index');

                Route::post('/transactions', [StaffTransactionController::class, 'store'])
                    ->name('transactions.store');

                Route::get('/transactions/{transaction}', [StaffTransactionController::class, 'show'])
                    ->name('transactions.show');

                Route::get('/transactions/{transaction}/print', [StaffTransactionController::class, 'print'])
                    ->name('transactions.print');

                Route::patch('/transactions/{transaction}/status', [StaffTransactionController::class, 'updateStatus'])
                    ->name('transactions.status');

                Route::post('/transactions/{transaction}/payments', [PaymentController::class, 'store'])
                    ->name('transactions.payments.store');

                // Layanan (Katalog untuk Staff)
                Route::get('/services', [ServiceController::class, 'index'])
                    ->name('services.index');
            });

        // -------------------------------------------------
        // Customer, Transaksi & Layanan untuk Admin
        // -------------------------------------------------
        Route::prefix('admin')
            ->name('admin.')
            ->group(function () {
                // Customer
                Route::get('/customers', [CustomerController::class, 'index'])
                    ->name('customers.index');

                Route::post('/customers', [CustomerController::class, 'store'])
                    ->name('customers.store');

                Route::put('/customers/{customer}', [CustomerController::class, 'update'])
                    ->name('customers.update');

                Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])
                    ->name('customers.destroy');

                // Transaksi
                Route::get('/transactions', [StaffTransactionController::class, 'index'])
                    ->name('transactions.index');

                Route::get('/transactions/create', [StaffTransactionController::class, 'create'])
                    ->name('transactions.create');

                Route::post('/transactions', [StaffTransactionController::class, 'store'])
                    ->name('transactions.store');

                Route::get('/transactions/{transaction}', [StaffTransactionController::class, 'show'])
                    ->name('transactions.show');

                Route::get('/transactions/{transaction}/print', [StaffTransactionController::class, 'print'])
                    ->name('transactions.print');

                Route::post('/transactions/{transaction}/payments', [PaymentController::class, 'store'])
                    ->name('transactions.payments.store');

                // Layanan (CRUD Master Layanan untuk Admin)
                Route::get('/services', [ServiceController::class, 'index'])
                    ->name('services.index');

                Route::post('/services', [ServiceController::class, 'store'])
                    ->name('services.store');

                Route::put('/services/{service}', [ServiceController::class, 'update'])
                    ->name('services.update');

                Route::patch('/services/{service}/toggle', [ServiceController::class, 'toggle'])
                    ->name('services.toggle');

                Route::delete('/services/{service}', [ServiceController::class, 'destroy'])
                    ->name('services.destroy');
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
