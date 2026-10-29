<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\User;
use App\Models\VoucherPackage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed starter/demo data for local testing and development.
     */
    public function run(): void
    {
        // 1. Admin Operator (Demo)
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin Operator',
                'email' => 'admin@wifinet.local',
                'phone' => '081200000002',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'shop_name' => null,
                'address' => 'Pos Operasional Utama',
                'is_active' => true,
            ]
        );

        // 2. Mitra Warung (Demo)
        User::firstOrCreate(
            ['username' => 'warung_siti'],
            [
                'name' => 'Ibu Siti',
                'shop_name' => 'Warung Sembako Bu Siti',
                'email' => 'warung_siti@wifinet.local',
                'phone' => '081211110001',
                'address' => 'Jl. Mawar No. 12 RT 04',
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'is_active' => true,
            ]
        );

        // 3. Paket Voucher Standar (Demo)
        $packages = [
            [
                'name' => 'Paket 3 Jam',
                'duration_info' => '3 Jam Nonstop',
                'printed_price' => 3000,
                'owner_price' => 2500,
                'reseller_profit' => 500,
                'stock' => 500,
                'is_active' => true,
                'description' => 'Speed 5 Mbps. Kuota unlimited, cocok untuk browsing & YouTube santai.',
            ],
            [
                'name' => 'Paket 6 Jam',
                'duration_info' => '6 Jam Aktif',
                'printed_price' => 5000,
                'owner_price' => 4000,
                'reseller_profit' => 1000,
                'stock' => 400,
                'is_active' => true,
                'description' => 'Speed 5 Mbps. Pilihan pas untuk nonton bola atau mabar.',
            ],
            [
                'name' => 'Paket 24 Jam (1 Hari)',
                'duration_info' => '24 Jam Penuh',
                'printed_price' => 10000,
                'owner_price' => 8000,
                'reseller_profit' => 2000,
                'stock' => 350,
                'is_active' => true,
                'description' => 'Paket harian favorit warga, kuota unlimited 24 jam.',
            ],
            [
                'name' => 'Paket Mingguan (7 Hari)',
                'duration_info' => '7 Hari Aktif',
                'printed_price' => 35000,
                'owner_price' => 28000,
                'reseller_profit' => 7000,
                'stock' => 200,
                'is_active' => true,
                'description' => 'Hemat untuk 1 minggu pemakaian keluarga & anak sekolah.',
            ],
            [
                'name' => 'Paket Bulanan (30 Hari)',
                'duration_info' => '30 Hari Aktif',
                'printed_price' => 100000,
                'owner_price' => 85000,
                'reseller_profit' => 15000,
                'stock' => 100,
                'is_active' => true,
                'description' => 'Paket bulanan paling ekonomis untuk warung & kos-kosan.',
            ],
        ];

        foreach ($packages as $pkg) {
            VoucherPackage::firstOrCreate(['name' => $pkg['name']], $pkg);
        }

        // 4. Master Inventaris Barang Awal (Demo)
        $inventoryItems = [
            [
                'item_code' => 'INV-RTR-001',
                'name' => 'Router MikroTik RB750Gr3 (hEX)',
                'category' => 'Router & Gateway',
                'unit' => 'unit',
                'stock' => 5,
                'min_stock' => 2,
                'unit_price' => 780000,
                'location' => 'Rak Server Utama',
                'description' => 'Router Gigabit utama pembagi bandwidth dan server hotspot.',
                'is_active' => true,
            ],
            [
                'item_code' => 'INV-APW-001',
                'name' => 'Access Point Ruijie Reyee RG-RAP1200(F)',
                'category' => 'Access Point / WiFi',
                'unit' => 'unit',
                'stock' => 8,
                'min_stock' => 3,
                'unit_price' => 450000,
                'location' => 'Gudang Posko',
                'description' => 'AP dual-band untuk dipasang di warung mitra dan tiang RT.',
                'is_active' => true,
            ],
            [
                'item_code' => 'INV-SWT-001',
                'name' => 'Switch Hub 8-Port Gigabit TP-Link',
                'category' => 'Switch & Hub',
                'unit' => 'unit',
                'stock' => 4,
                'min_stock' => 2,
                'unit_price' => 230000,
                'location' => 'Gudang Posko',
                'description' => 'Switch distribusi tiang percabangan antar gang.',
                'is_active' => true,
            ],
            [
                'item_code' => 'INV-KBL-001',
                'name' => 'Kabel FO Dropcore 1 Core 3 Seling (1000m)',
                'category' => 'Kabel & Fiber Optic',
                'unit' => 'roll',
                'stock' => 3,
                'min_stock' => 1,
                'unit_price' => 420000,
                'location' => 'Gudang Posko',
                'description' => 'Kabel optik outdoor tahan tarik untuk tiang ke rumah mitra.',
                'is_active' => true,
            ],
            [
                'item_code' => 'INV-CVT-001',
                'name' => 'Media Converter Netlink HTB-3100 Pair (A+B)',
                'category' => 'Konverter & SFP',
                'unit' => 'set',
                'stock' => 6,
                'min_stock' => 2,
                'unit_price' => 135000,
                'location' => 'Box Komponen',
                'description' => 'Konverter FO ke LAN RJ45 100Mbps.',
                'is_active' => true,
            ],
            [
                'item_code' => 'INV-PWR-001',
                'name' => 'Adaptor Switching 12V 1.5A Jack DC',
                'category' => 'Power & Adaptor',
                'unit' => 'unit',
                'stock' => 12,
                'min_stock' => 4,
                'unit_price' => 35000,
                'location' => 'Box Komponen',
                'description' => 'Power supply pengganti untuk HTB atau router.',
                'is_active' => true,
            ],
        ];

        foreach ($inventoryItems as $itemData) {
            InventoryItem::firstOrCreate(
                ['item_code' => $itemData['item_code']],
                $itemData
            );
        }
    }
}
