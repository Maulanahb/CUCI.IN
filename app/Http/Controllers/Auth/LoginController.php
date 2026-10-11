<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Tampilkan halaman formulir login.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Proses autentikasi login pengguna.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return $this->redirectBasedOnRole(Auth::user());
    }

    /**
     * Proses logout dan akhiri sesi pengguna.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Arahkan pengguna ke halaman tujuan berdasarkan role.
     */
    protected function redirectBasedOnRole($user): RedirectResponse
    {
        return match ($user->role) {
            'admin' => redirect()->intended(route('admin.dashboard')),
            'staff' => redirect()->intended(route('staff.dashboard')),
            'customer' => redirect()->intended(route('customer.dashboard')),
            default => redirect()->intended(route('home')),
        };
    }
}
