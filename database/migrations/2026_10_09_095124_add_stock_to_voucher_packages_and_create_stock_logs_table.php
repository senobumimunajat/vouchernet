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
        Schema::table('voucher_packages', function (Blueprint $table) {
            $table->unsignedInteger('stock')->default(0)->after('reseller_profit');
        });

        Schema::create('voucher_stock_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_package_id')->constrained('voucher_packages')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('quantity');
            $table->integer('stock_before')->default(0);
            $table->integer('stock_after')->default(0);
            $table->string('type')->default('restock'); // restock, order, refund, adjustment
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_stock_logs');

        Schema::table('voucher_packages', function (Blueprint $table) {
            $table->dropColumn('stock');
        });
    }
};
