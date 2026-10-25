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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('package_id')->nullable()->change();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('voucher_packages')->restrictOnDelete();
            $table->integer('quantity');
            $table->decimal('printed_price_each', 12, 2);
            $table->decimal('owner_price_each', 12, 2);
            $table->decimal('total_printed_amount', 12, 2);
            $table->decimal('total_owner_amount', 12, 2);
            $table->decimal('total_reseller_profit', 12, 2);
            $table->timestamps();
        });

        // Backfill existing orders
        $existingOrders = \Illuminate\Support\Facades\DB::table('orders')->whereNotNull('package_id')->get();
        foreach ($existingOrders as $order) {
            \Illuminate\Support\Facades\DB::table('order_items')->insert([
                'order_id' => $order->id,
                'package_id' => $order->package_id,
                'quantity' => $order->quantity,
                'printed_price_each' => $order->printed_price_each,
                'owner_price_each' => $order->owner_price_each,
                'total_printed_amount' => $order->total_printed_amount,
                'total_owner_amount' => $order->total_owner_amount,
                'total_reseller_profit' => $order->total_reseller_profit,
                'created_at' => $order->created_at ?? now(),
                'updated_at' => $order->updated_at ?? now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
