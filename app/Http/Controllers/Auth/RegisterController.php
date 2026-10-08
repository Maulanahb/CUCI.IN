<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Tampilkan formulir pendaftaran akun pelanggan baru.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    /**
     * Tangani permintaan registrasi pelanggan baru.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => $request->validated('password'),
                'role' => 'customer',
                'is_active' => true,
            ]);

            Customer::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'phone' => $request->validated('phone'),
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('customer.dashboard')->with('status', 'Pendaftaran akun berhasil! Selamat datang di CUCI.IN.');
    }
}
