<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat daftar pengaduan.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'staff', 'customer'], true);
    }

    /**
     * Tentukan apakah pengguna dapat melihat detail tiket pengaduan tertentu.
     * Mencegah kerentanan IDOR (Insecure Direct Object Reference).
     */
    public function view(User $user, Complaint $complaint): bool
    {
        // Staf dan Administrator memiliki hak akses untuk memantau seluruh komplain operasional
        if (in_array($user->role, ['admin', 'staff'], true)) {
            return true;
        }

        // Pelanggan hanya dapat melihat tiket komplain yang terkait dengan transaksi miliknya sendiri
        if ($user->role === 'customer' && $user->customer !== null) {
            return $complaint->transaction?->customer_id === $user->customer->id;
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna dapat membuat tiket pengaduan baru.
     */
    public function create(User $user): bool
    {
        return $user->role === 'customer' && $user->customer !== null;
    }

    /**
     * Tentukan apakah pengguna dapat menindaklanjuti atau memperbarui status pengaduan.
     */
    public function update(User $user, Complaint $complaint): bool
    {
        return in_array($user->role, ['admin', 'staff'], true);
    }

    /**
     * Tentukan apakah pengguna dapat menghapus tiket pengaduan.
     */
    public function delete(User $user, Complaint $complaint): bool
    {
        return $user->role === 'admin';
    }
}

