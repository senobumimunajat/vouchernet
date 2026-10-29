<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's essential database setup.
     */
    public function run(): void
    {
        // 1. Super Admin (Owner RT/RW Net) - Hanya dibuat jika belum pernah ada
        User::firstOrCreate(
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

        // 2. Pengaturan Dasar Sistem (Hanya diisi jika belum ada)
        if (!Setting::get('admin_whatsapp_number')) {
            Setting::set('admin_whatsapp_number', '6281234567890');
        }

        if (!Setting::get('bank_accounts')) {
            Setting::set('bank_accounts', "BCA: 5410-988-123 a.n Wi-Fi RT 04\nBRI: 021-456-789-01 a.n Wi-Fi RT 04\nDANA / Gopay: 0812-3456-7890 (Admin)");
        }

        if (!Setting::get('app_name')) {
            Setting::set('app_name', 'VoucherNet RT/RW');
        }

        // 3. Data Dummy (Akun demo, paket contoh, inventaris contoh)
        // HANYA dijalankan di environment local atau testing.
        // DI SERVER PRODUCTION TIDAK AKAN PERNAH DIJALANKAN.
        if (app()->environment('local', 'testing')) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
