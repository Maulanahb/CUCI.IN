<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('payment_type', ['Pembayaran Penuh', 'DP', 'Pelunasan']);
            $table->enum('method', ['Tunai', 'Transfer', 'QRIS']);
            $table->string('proof_file', 255)->nullable();
            $table->enum('verification_status', ['Tidak Diperlukan', 'Menunggu Verifikasi', 'Terverifikasi', 'Ditolak']);
            $table->timestamp('paid_at');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
