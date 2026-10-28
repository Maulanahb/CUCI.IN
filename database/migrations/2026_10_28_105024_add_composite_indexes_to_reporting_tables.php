<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan indeks komposit pada tabel transactions dan payments
     * sesuai standar teknis rules_milestone_2.md § 4.1 untuk mengoptimasi
     * kueri filter tanggal laporan dan status tanpa beban full table scan.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['created_at', 'status'], 'transactions_created_at_status_index');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['paid_at', 'verification_status'], 'payments_paid_at_verification_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_created_at_status_index');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_paid_at_verification_status_index');
        });
    }
};
