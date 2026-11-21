<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat daftar transaksi.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'staff', 'customer'], true);
    }

    /**
     * Tentukan apakah pengguna dapat melihat detail transaksi tertentu.
     * Mencegah kerentanan IDOR (Insecure Direct Object Reference) antar-pelanggan.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        // Staf dan Administrator berhak melihat seluruh transaksi operasional
        if (in_array($user->role, ['admin', 'staff'], true)) {
            return true;
        }

        // Pelanggan hanya berhak melihat transaksi yang tercatat atas nama akun miliknya
        if ($user->role === 'customer' && $user->customer !== null) {
            return $transaction->customer_id === $user->customer->id;
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna dapat membuat transaksi kasir baru.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'staff'], true);
    }

    /**
     * Tentukan apakah pengguna dapat memperbarui status atau detail transaksi.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        return in_array($user->role, ['admin', 'staff'], true);
    }

    /**
     * Tentukan apakah pengguna dapat membatalkan atau menghapus transaksi.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->role === 'admin';
    }
}

