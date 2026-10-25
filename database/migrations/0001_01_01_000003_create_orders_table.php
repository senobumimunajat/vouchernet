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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('package_id')->constrained('voucher_packages')->onDelete('restrict');
            $table->integer('quantity');
            $table->decimal('printed_price_each', 12, 2);
            $table->decimal('owner_price_each', 12, 2);
            $table->decimal('total_printed_amount', 12, 2); // Omzet kotor fisik
            $table->decimal('total_owner_amount', 12, 2);   // Tagihan setor ke kas owner
            $table->decimal('total_reseller_profit', 12, 2);// Untung dinikmati tenant
            $table->enum('payment_method', ['unspecified', 'transfer', 'cash'])->default('unspecified');
            $table->string('payment_proof_file')->nullable();
            $table->string('voucher_pdf_file')->nullable();
            $table->enum('status', [
                'menunggu_pembayaran', 
                'lunas', 
                'menunggu_approval_hapus', 
                'dibatalkan'
            ])->default('menunggu_pembayaran');
            
            // Tracking Konfirmasi Bayar
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('confirmed_at')->nullable();
            
            // Tracking Pengajuan Hapus Bayar
            $table->foreignId('delete_requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('delete_reason')->nullable();
            $table->dateTime('delete_requested_at')->nullable();
            
            // Tracking Approval Hapus oleh Super Admin
            $table->foreignId('delete_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('delete_approved_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
