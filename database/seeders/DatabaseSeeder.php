<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use App\Models\VoucherPackage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Super Admin (Owner RT/RW Net)
        User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Owner RT/RW Net (Super Admin)',
                'email' => 'superadmin@wifinet.local',
                'phone' => '081200000001',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'shop_name' => null,
                'address' => 'Basecamp Server RT 04 RW 02',
                'is_active' => true,
            ]
        );

        // 2. Admin (Operator Kasir)
        User::updateOrCreate(
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

        // 3. 1 Mitra Warung (Reseller)
        User::updateOrCreate(
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

        // 4. Pengaturan Sistem
        Setting::set('admin_whatsapp_number', '6281234567890');
        Setting::set('bank_accounts', "BCA: 5410-988-123 a.n Wi-Fi RT 04\nBRI: 021-456-789-01 a.n Wi-Fi RT 04\nDANA / Gopay: 0812-3456-7890 (Admin)");
        Setting::set('app_name', 'VoucherNet RT/RW');

        // 5. Paket Voucher Standar
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
            VoucherPackage::updateOrCreate(['name' => $pkg['name']], $pkg);
        }
    }
}
