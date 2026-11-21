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
        Schema::table('services', function (Blueprint $table) {
            $table->string('unit', 20)->default('kg')->after('price');
            $table->string('service_type', 30)->default('reguler')->after('unit');
            $table->boolean('is_express')->default(false)->after('service_type');
            $table->decimal('minimum_order', 8, 2)->default(1.00)->after('is_express');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['unit', 'service_type', 'is_express', 'minimum_order']);
        });
    }
};
