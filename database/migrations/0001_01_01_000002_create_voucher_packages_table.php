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
        Schema::create('voucher_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('duration_info')->default('Aktif');
            $table->decimal('printed_price', 12, 2); // Harga tertera di voucher (misal Rp 3.000)
            $table->decimal('owner_price', 12, 2);   // Harga disetor ke owner (misal Rp 2.500)
            $table->decimal('reseller_profit', 12, 2)->default(0); // Margin reseller (misal Rp 500)
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_packages');
    }
};
