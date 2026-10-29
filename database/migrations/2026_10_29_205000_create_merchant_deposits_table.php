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
        Schema::create('merchant_deposits', function (Blueprint $table) {
            $table->id();
            $table->string('deposit_number')->unique();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2); // Nominal saldo kuota yang diisikan (misal: 500.000)
            $table->enum('payment_type', ['lunas', 'tempo'])->default('lunas');
            $table->enum('payment_method', ['cash', 'transfer', 'unspecified'])->default('unspecified');
            $table->enum('status', ['menunggu_konfirmasi', 'tempo', 'lunas', 'dibatalkan'])->default('menunggu_konfirmasi');
            $table->decimal('actual_paid_amount', 12, 2)->nullable(); // Uang riil yang disetor ke owner (misal: 455.000)
            $table->decimal('merchant_profit', 12, 2)->default(0);    // Keuntungan mitra (amount - actual_paid_amount)
            $table->string('payment_proof_file')->nullable();
            $table->text('notes')->nullable(); // ID Akun Mikhmon / keterangan
            $table->date('deposit_date');
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('confirmed_at')->nullable();
            $table->foreignId('settled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('settled_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_deposits');
    }
};
