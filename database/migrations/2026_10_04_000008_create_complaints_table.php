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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_code', 20)->unique();
            $table->foreignId('transaction_id')->constrained('transactions')->restrictOnDelete();
            $table->string('subject', 150);
            $table->text('description');
            $table->enum('status', ['Menunggu', 'Diproses', 'Selesai']);
            $table->text('response')->nullable();
            $table->timestamp('deadline_at');
            $table->foreignId('handled_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
