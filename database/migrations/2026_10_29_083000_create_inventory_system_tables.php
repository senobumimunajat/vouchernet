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
        // 1. Master Data Barang & Aset Inventaris
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('name');
            $table->string('category')->default('Umum'); // Router & Gateway, Access Point, Switch, Kabel FO, dll
            $table->string('unit')->default('unit'); // unit, pcs, meter, roll, set, pack
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(2);
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->string('location')->nullable(); // Gudang Posko, Tiang Utama, Rak Server
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Kolom Integrasi di Tabel Expenses (Pengeluaran Kas)
        Schema::table('expenses', function (Blueprint $table) {
            $table->boolean('is_inventory')->default(false)->after('receipt_file');
            $table->foreignId('inventory_item_id')->nullable()->after('is_inventory')->constrained('inventory_items')->nullOnDelete();
            $table->integer('inventory_quantity')->nullable()->after('inventory_item_id');
        });

        // 3. Log Riwayat Mutasi Barang Keluar - Masuk (Movement Log)
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->enum('type', ['in', 'out', 'adjustment'])->default('in'); // in = masuk, out = keluar, adjustment = opname
            $table->integer('quantity'); // jumlah barang (positif)
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('purpose')->nullable(); // Keperluan / Alasan (misal: Pemasangan Mitra Bu Siti, Ganti Router Rusak)
            $table->string('recipient')->nullable(); // Penerima / Mitra / Lokasi Pasang / Teknisi
            $table->date('movement_date');
            $table->text('notes')->nullable(); // Serial Number (SN), MAC, Keterangan teknis
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['inventory_item_id']);
            $table->dropColumn(['is_inventory', 'inventory_item_id', 'inventory_quantity']);
        });

        Schema::dropIfExists('inventory_items');
    }
};
