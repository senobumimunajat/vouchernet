<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\User;
use App\Models\VoucherPackage;
use App\Models\VoucherStockLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VoucherNetTest extends TestCase
{
    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee(Setting::get('app_name', 'VoucherNet'));
    }

    public function test_super_admin_redirected_to_finance_dashboard(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $response = $this->actingAs($superAdmin)->get('/dashboard');
        $response->assertRedirect(route('finance.index'));
    }

    public function test_admin_redirected_to_admin_orders(): void
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertRedirect(route('admin.orders.index'));
    }

    public function test_tenant_redirected_to_tenant_orders(): void
    {
        $tenant = User::where('role', 'tenant')->first();
        $response = $this->actingAs($tenant)->get('/dashboard');
        $response->assertRedirect(route('tenant.orders.index'));
    }

    public function test_admin_is_forbidden_from_financial_reports(): void
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/finances');
        $response->assertStatus(403);
    }

    public function test_tenant_is_forbidden_from_admin_orders(): void
    {
        $tenant = User::where('role', 'tenant')->first();
        $response = $this->actingAs($tenant)->get('/admin/orders');
        $response->assertStatus(403);
    }

    public function test_tenant_can_place_an_order_and_stock_is_deducted(): void
    {
        $tenant = User::where('role', 'tenant')->first();
        $package = VoucherPackage::first();
        
        // Ensure sufficient stock
        $package->stock = 100;
        $package->save();

        $initialStock = $package->stock;

        $response = $this->actingAs($tenant)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post('/tenant/orders', [
                'package_id' => $package->id,
                'quantity' => 20,
            ]);

        $response->assertRedirect(route('tenant.orders.index', ['tab' => 'pesanan']));
        $this->assertDatabaseHas('orders', [
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
            'quantity' => 20,
            'status' => 'menunggu_pembayaran',
        ]);

        $package->refresh();
        $this->assertEquals($initialStock - 20, $package->stock);
    }

    public function test_user_can_add_voucher_stock_and_audit_log_is_recorded(): void
    {
        $admin = User::where('role', 'admin')->first();
        $package = VoucherPackage::first();
        $initialStock = $package->stock;

        $response = $this->actingAs($admin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post("/packages/{$package->id}/add-stock", [
                'quantity' => 50,
                'notes' => 'Cetak fisik Batch Tambahan RT02',
            ]);

        $response->assertRedirect(route('packages.index'));
        $package->refresh();
        $this->assertEquals($initialStock + 50, $package->stock);

        $this->assertDatabaseHas('voucher_stock_logs', [
            'voucher_package_id' => $package->id,
            'user_id' => $admin->id,
            'quantity' => 50,
            'type' => 'restock',
            'notes' => 'Cetak fisik Batch Tambahan RT02',
        ]);
    }

    public function test_super_admin_can_view_all_management_pages(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();

        // Finance
        $this->actingAs($superAdmin)->get('/finances')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/finances/expenses')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/finances/incomes')->assertStatus(200);

        // Finance with custom date range
        $this->actingAs($superAdmin)->get('/finances?range_type=custom&start_date=2026-01-01&end_date=2026-12-31')->assertStatus(200);

        // Finance Print PDF page
        $this->actingAs($superAdmin)->get('/finances/print?range_type=month&month=10&year=2026')->assertStatus(200)->assertSee('LAPORAN ARUS KAS');
        $this->actingAs($superAdmin)->get('/finances/print?range_type=custom&start_date=2026-10-01&end_date=2026-10-15')->assertStatus(200)->assertSee('LAPORAN ARUS KAS');

        // Packages
        $this->actingAs($superAdmin)->get('/packages')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/packages/create')->assertStatus(200);

        // Users & Settings
        $this->actingAs($superAdmin)->get('/users')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/settings')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/password')->assertStatus(200);
    }

    public function test_admin_can_view_offline_order_page_and_packages(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin)->get('/admin/orders/offline')->assertStatus(200);
        $this->actingAs($admin)->get('/packages')->assertStatus(200);
    }

    public function test_admin_can_filter_orders_by_tenant(): void
    {
        $admin = User::where('role', 'admin')->first();
        $tenant = User::where('role', 'tenant')->first();

        $response = $this->actingAs($admin)->get("/admin/orders?tenant_id={$tenant->id}");
        $response->assertStatus(200);
        $response->assertSee($tenant->shop_name ?: $tenant->name);
    }

    public function test_rupiah_formatted_input_is_sanitized_correctly(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();

        // 1. Package creation with formatted string e.g. "20.000" and "15.000"
        $response = $this->actingAs($superAdmin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post('/packages', [
                'name' => 'Paket Hemat 10 Jam',
                'duration_info' => '10 Jam Aktif',
                'printed_price' => '20.000',
                'owner_price' => '15.000',
                'stock' => 10,
            ]);

        $response->assertRedirect(route('packages.index'));
        $this->assertDatabaseHas('voucher_packages', [
            'name' => 'Paket Hemat 10 Jam',
            'printed_price' => 20000,
            'owner_price' => 15000,
            'reseller_profit' => 5000,
        ]);

        // 2. Income creation with formatted amount e.g. "150.000"
        $this->actingAs($superAdmin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post('/finances/incomes', [
                'title' => 'Iuran Warga RT 02',
                'category' => 'Iuran Bulanan',
                'amount' => '150.000',
                'income_date' => now()->format('Y-m-d'),
            ]);

        $this->assertDatabaseHas('incomes', [
            'title' => 'Iuran Warga RT 02',
            'amount' => 150000,
        ]);

        // 3. Expense creation with formatted amount e.g. "750.000"
        $this->actingAs($superAdmin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post('/finances/expenses', [
                'title' => 'Bandwidth Dedicated NOC',
                'category' => 'Bandwidth ISP',
                'amount' => '750.000',
                'expense_date' => now()->format('Y-m-d'),
            ]);

        $this->assertDatabaseHas('expenses', [
            'title' => 'Bandwidth Dedicated NOC',
            'amount' => 750000,
        ]);
    }

    public function test_custom_category_can_be_saved_and_appears_in_dropdown_list(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();

        // 1. Simpan pengeluaran dengan kategori kustom baru "Gaji Karyawan NOC"
        $this->actingAs($superAdmin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post('/finances/expenses', [
                'title' => 'Gaji Mas Bagas Teknisi',
                'category' => 'Gaji Karyawan NOC',
                'amount' => '1.500.000',
                'expense_date' => now()->format('Y-m-d'),
            ]);

        $this->assertDatabaseHas('expenses', [
            'title' => 'Gaji Mas Bagas Teknisi',
            'category' => 'Gaji Karyawan NOC',
            'amount' => 1500000,
        ]);

        // Cek halaman expenses apakah kategori baru otomatis termuat di dropdown
        $resExpenses = $this->actingAs($superAdmin)->get('/finances/expenses');
        $resExpenses->assertStatus(200);
        $resExpenses->assertSee('Gaji Karyawan NOC');

        // 2. Simpan pemasukan dengan kategori kustom baru "Sponsor Turnamen Game"
        $this->actingAs($superAdmin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post('/finances/incomes', [
                'title' => 'Dana Sponsor Turnamen ML',
                'category' => 'Sponsor Turnamen Game',
                'amount' => '500.000',
                'income_date' => now()->format('Y-m-d'),
            ]);

        $this->assertDatabaseHas('incomes', [
            'title' => 'Dana Sponsor Turnamen ML',
            'category' => 'Sponsor Turnamen Game',
            'amount' => 500000,
        ]);

        // Cek halaman incomes apakah kategori baru otomatis termuat di dropdown
        $resIncomes = $this->actingAs($superAdmin)->get('/finances/incomes');
        $resIncomes->assertStatus(200);
        $resIncomes->assertSee('Sponsor Turnamen Game');
    }

    public function test_offline_order_can_be_placed_as_tempo_or_lunas(): void
    {
        $admin = User::where('role', 'admin')->first();
        $tenant = User::where('role', 'tenant')->first();
        $package = VoucherPackage::create([
            'name' => 'Paket Malam 20 Jam',
            'printed_price' => 25000,
            'owner_price' => 20000,
            'reseller_profit' => 5000,
            'stock' => 100,
            'is_active' => true,
        ]);

        // 1. Order offline dengan status 'tempo' (Ambil Dulu / Konsinyasi)
        $respTempo = $this->actingAs($admin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post('/admin/orders/offline', [
                'tenant_id' => $tenant->id,
                'package_id' => $package->id,
                'quantity' => 20,
                'payment_status' => 'tempo',
            ]);

        $respTempo->assertRedirect('/admin/orders');
        $this->assertDatabaseHas('orders', [
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
            'quantity' => 20,
            'status' => 'menunggu_pembayaran',
        ]);

        // Stok fisik berkurang 20 (100 -> 80)
        $this->assertEquals(80, $package->fresh()->stock);

        // 2. Order offline dengan status 'lunas' (Bayar di Tempat)
        $respLunas = $this->actingAs($admin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post('/admin/orders/offline', [
                'tenant_id' => $tenant->id,
                'package_id' => $package->id,
                'quantity' => 10,
                'payment_status' => 'lunas',
                'payment_method' => 'cash',
            ]);

        $respLunas->assertRedirect('/admin/orders');
        $this->assertDatabaseHas('orders', [
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
            'quantity' => 10,
            'status' => 'lunas',
            'confirmed_by' => $admin->id,
        ]);

        // Stok fisik berkurang lagi 10 (80 -> 70)
        $this->assertEquals(70, $package->fresh()->stock);
    }

    public function test_tenant_order_history_shows_vouchers_taken_and_settlement_due(): void
    {
        $tenant = User::where('role', 'tenant')->first();
        $package = VoucherPackage::first();

        $tempoOrderNumber = 'ORD-TEMPO-' . uniqid();
        $lunasOrderNumber = 'ORD-LUNAS-' . uniqid();

        // 1. Order 1: Status menunggu_pembayaran (Tempo) - 30 lembar
        Order::create([
            'order_number' => $tempoOrderNumber,
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
            'quantity' => 30,
            'printed_price_each' => 10000,
            'owner_price_each' => 8000,
            'total_printed_amount' => 300000,
            'total_owner_amount' => 240000,
            'total_reseller_profit' => 60000,
            'status' => 'menunggu_pembayaran',
        ]);

        // 2. Order 2: Status lunas - 15 lembar
        Order::create([
            'order_number' => $lunasOrderNumber,
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
            'quantity' => 15,
            'printed_price_each' => 10000,
            'owner_price_each' => 8000,
            'total_printed_amount' => 150000,
            'total_owner_amount' => 120000,
            'total_reseller_profit' => 30000,
            'status' => 'lunas',
        ]);

        $response = $this->actingAs($tenant)->get('/tenant/orders?tab=pesanan');
        $response->assertStatus(200);

        // Harus menampilkan informasi rekap pengambilan dan wajib setor
        $response->assertSee('Total Voucher Diambil');
        $response->assertSee('Harus Disetorkan (Tempo)');
        $response->assertSee('Sudah Disetor Lunas');
        $response->assertSee('240.000'); // Tagihan tempo 240.000
        $response->assertSee($tempoOrderNumber);
        $response->assertSee($lunasOrderNumber);

        // Test filter tab status=menunggu_pembayaran
        $responseTempo = $this->actingAs($tenant)->get('/tenant/orders?tab=pesanan&status=menunggu_pembayaran');
        $responseTempo->assertStatus(200);
        $responseTempo->assertSee($tempoOrderNumber);
        $responseTempo->assertDontSee($lunasOrderNumber);
    }

    public function test_admin_can_cancel_unpaid_order_instantly_and_stock_is_restored(): void
    {
        $admin = User::where('role', 'admin')->first();
        $tenant = User::where('role', 'tenant')->first();
        $package = VoucherPackage::create([
            'name' => 'Paket Batal Test',
            'printed_price' => 5000,
            'owner_price' => 4000,
            'reseller_profit' => 1000,
            'stock' => 50,
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-CANCEL-' . uniqid(),
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
            'quantity' => 20,
            'printed_price_each' => 5000,
            'owner_price_each' => 4000,
            'total_printed_amount' => 100000,
            'total_owner_amount' => 80000,
            'total_reseller_profit' => 20000,
            'status' => 'menunggu_pembayaran',
        ]);

        // Simulasikan stok terpotong 20 lembar (50 -> 30)
        $package->update(['stock' => 30]);

        // Admin membatalkan langsung tanpa approval Super Admin
        $resp = $this->actingAs($admin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post("/admin/orders/{$order->id}/cancel-unpaid", [
                'cancel_reason' => 'Salah pilih warung bu siti',
            ]);

        $resp->assertRedirect();
        $this->assertEquals('dibatalkan', $order->fresh()->status);
        // Stok harus otomatis kembali 20 lembar (30 -> 50)
        $this->assertEquals(50, $package->fresh()->stock);
    }

    public function test_admin_can_place_direct_walk_in_retail_order(): void
    {
        $admin = User::where('role', 'admin')->first();
        $package = VoucherPackage::create([
            'name' => 'Paket Eceran 5 Jam',
            'printed_price' => 5000,
            'owner_price' => 4000,
            'reseller_profit' => 1000,
            'stock' => 10,
            'is_active' => true,
        ]);

        // Simulasikan admin menjual 2 lembar eceran langsung ke pembeli umum walk-in
        $response = $this->actingAs($admin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post('/admin/orders/offline', [
                'buyer_type' => 'direct',
                'customer_name' => 'Mas Budi (Tamu)',
                'package_id' => $package->id,
                'quantity' => 2,
                'payment_method' => 'cash',
            ]);

        $response->assertRedirect('/admin/orders');

        // Pastikan order tersimpan dengan customer_type direct, status lunas, uang masuk kas 100%
        $this->assertDatabaseHas('orders', [
            'customer_type' => 'direct',
            'customer_name' => 'Mas Budi (Tamu)',
            'package_id' => $package->id,
            'quantity' => 2,
            'printed_price_each' => 5000,
            'owner_price_each' => 5000,          // 100% hak owner
            'total_printed_amount' => 10000,     // 2 * 5000
            'total_owner_amount' => 10000,       // Masuk kas penuh 10000
            'total_reseller_profit' => 0,        // 0 komisi reseller
            'status' => 'lunas',
            'payment_method' => 'cash',
        ]);

        // Stok voucher berkurang 2 lembar (10 -> 8)
        $this->assertEquals(8, $package->fresh()->stock);

        // Halaman admin order menampilkan badge Eceran dan nama pembeli
        $ordersPage = $this->actingAs($admin)->get('/admin/orders');
        $ordersPage->assertStatus(200);
        $ordersPage->assertSee('Mas Budi (Tamu)');
        $ordersPage->assertSee('Eceran');
    }

    public function test_admin_can_reset_password_for_tenant_but_forbidden_for_admin_or_superadmin(): void
    {
        $admin = User::where('role', 'admin')->first();
        $superAdmin = User::where('role', 'super_admin')->first();
        $tenant = User::where('role', 'tenant')->where('username', '!=', 'pelanggan_langsung')->first();

        // 1. Admin bisa reset password untuk warung (tenant)
        $respTenant = $this->actingAs($admin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post("/users/{$tenant->id}/reset-password", [
                'new_password' => 'passwordbaru123',
            ]);

        $respTenant->assertRedirect();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('passwordbaru123', $tenant->fresh()->password));

        // 2. Admin dilarang reset password untuk Super Admin
        $respSuper = $this->actingAs($admin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post("/users/{$superAdmin->id}/reset-password", [
                'new_password' => 'hacked123',
            ]);

        $respSuper->assertStatus(403);

        // 3. Halaman users menampilkan tombol Reset Password untuk admin
        $usersPage = $this->actingAs($admin)->get('/users');
        $usersPage->assertStatus(200);
        $usersPage->assertSee('Reset Password');
    }

    public function test_finance_index_filters_package_breakdown_by_tenant_and_shows_shop_name(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $package = VoucherPackage::first();

        $tenantA = User::create([
            'name' => 'Mitra Warung Alpha',
            'username' => 'warung_alpha_' . uniqid(),
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'shop_name' => 'Toko Alpha Maju',
            'phone' => '081211112222',
        ]);

        $tenantB = User::create([
            'name' => 'Mitra Warung Beta',
            'username' => 'warung_beta_' . uniqid(),
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'shop_name' => 'Warung Beta Sejahtera',
            'phone' => '081233334444',
        ]);

        // Order lunas Tenant A: 12 lembar
        Order::create([
            'order_number' => 'ORD-ALPHA-' . uniqid(),
            'tenant_id' => $tenantA->id,
            'package_id' => $package->id,
            'quantity' => 12,
            'printed_price_each' => 10000,
            'owner_price_each' => 8000,
            'total_printed_amount' => 120000,
            'total_owner_amount' => 96000,
            'total_reseller_profit' => 24000,
            'status' => 'lunas',
            'confirmed_at' => now(),
        ]);

        // Order lunas Tenant B: 25 lembar
        Order::create([
            'order_number' => 'ORD-BETA-' . uniqid(),
            'tenant_id' => $tenantB->id,
            'package_id' => $package->id,
            'quantity' => 25,
            'printed_price_each' => 10000,
            'owner_price_each' => 8000,
            'total_printed_amount' => 250000,
            'total_owner_amount' => 200000,
            'total_reseller_profit' => 50000,
            'status' => 'lunas',
            'confirmed_at' => now(),
        ]);

        // 1. Tanpa filter: Menampilkan 'Semua Mitra Warung & Eceran'
        $respGlobal = $this->actingAs($superAdmin)->get('/finances');
        $respGlobal->assertStatus(200);
        $respGlobal->assertSee('Semua Mitra Warung & Eceran', false);
        $respGlobal->assertSee('Simulasi Kas Masuk & Proyeksi Laba Bersih', false);
        $respGlobal->assertSee('1. Kas Voucher Lunas');
        $respGlobal->assertSee('2. Tagihan Tempo Warung');
        $respGlobal->assertSee('3. Pemasukan Kas Lain');
        $respGlobal->assertSee('Proyeksi Total Kas Masuk');
        $respGlobal->assertSee('Proyeksi Laba Bersih Akhir');

        // Highlight Cards Opsi 1 (Global)
        $respGlobal->assertSee('Paket Voucher Paling Laku');
        $respGlobal->assertSee('Warung Penjualan Terbanyak');
        $respGlobal->assertSee('Warung Beta Sejahtera');
        $respGlobal->assertSee('#1 Top Reseller');

        // 2. Difilter Tenant A:
        $respFiltered = $this->actingAs($superAdmin)->get('/finances?tenant_id=' . $tenantA->id);
        $respFiltered->assertStatus(200);
        // Header Rekap Paket menampilkan nama warung terpilih
        $respFiltered->assertSee('Mitra: Toko Alpha Maju');
        // Highlight Cards Opsi 1 (Khusus Tenant A)
        $respFiltered->assertSee('Performa Mitra Warung Ini');
        $selectedRank = $respFiltered->viewData('selectedTenantRank');
        $this->assertNotNull($selectedRank);
        $respFiltered->assertSee("Posisi #{$selectedRank} dari");
        $respFiltered->assertSee('Pemuncak Periode Ini:');
        $respFiltered->assertSee('Warung Beta Sejahtera');

        // Tabel Rekap Paket terfilter khusus Tenant A (12 lembar, bukan 37 lembar!)
        $packagesBreakdown = $respFiltered->viewData('packagesBreakdown');
        $pkgAlpha = $packagesBreakdown->firstWhere('id', $package->id);
        $this->assertEquals(12, $pkgAlpha->total_quantity);

        // Paket yang 0 penjualan tidak masuk ke dalam tabel rekap (Opsi A)
        $this->assertTrue($packagesBreakdown->every(fn($p) => ($p->total_quantity ?? 0) > 0));
        $respFiltered->assertSee('Diurutkan dari Terlaris');
        $respFiltered->assertSee('Hanya paket aktif terjual di periode ini');

        // Baris footer total (tfoot) menampilkan 12 lbr dan Rp 96.000
        $respFiltered->assertSee('TOTAL KESELURUHAN');
        $respFiltered->assertSee('96.000');
    }

    public function test_finance_consignment_prediction_shows_projected_cash_when_unpaid_orders_exist(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $package = VoucherPackage::first();

        $tenant = User::create([
            'name' => 'Warung Sumber Rejeki',
            'username' => 'rejeki_' . uniqid(),
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'shop_name' => 'Kios Rejeki',
            'phone' => '081299998888',
        ]);

        // Order 1: Lunas 10 lembar (Setor kas Rp 80.000)
        Order::create([
            'order_number' => 'ORD-LUNAS-' . uniqid(),
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
            'quantity' => 10,
            'printed_price_each' => 10000,
            'owner_price_each' => 8000,
            'total_printed_amount' => 100000,
            'total_owner_amount' => 80000,
            'total_reseller_profit' => 20000,
            'status' => 'lunas',
            'confirmed_at' => now(),
        ]);

        // Order 2: Tempo / Menunggu Pembayaran 20 lembar (Tagihan kas Rp 160.000)
        Order::create([
            'order_number' => 'ORD-TEMPO-' . uniqid(),
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
            'quantity' => 20,
            'printed_price_each' => 10000,
            'owner_price_each' => 8000,
            'total_printed_amount' => 200000,
            'total_owner_amount' => 160000,
            'total_reseller_profit' => 40000,
            'status' => 'menunggu_pembayaran',
            'created_at' => now(),
        ]);

        // 1. Cek halaman Buku Kas difilter Warung Rejeki
        $response = $this->actingAs($superAdmin)->get('/finances?tenant_id=' . $tenant->id);
        $response->assertStatus(200);

        // Memastikan widget estimasi pelunasan khusus warung muncul
        $response->assertSee('Estimasi Pelunasan: Kios Rejeki');
        $response->assertSee('Sudah Disetor (Lunas)');
        $response->assertSee('80.000'); // Sudah lunas Rp 80.000
        $response->assertSee('Masih Belum Disetor (Tempo)');
        $response->assertSee('160.000'); // Tagihan tempo Rp 160.000
        $response->assertSee('Perkiraan Total Setoran (Jika Lunas)');
        $response->assertSee('240.000'); // Prediksi total jika semua lunas (80.000 + 160.000 = 240.000)

        // Memastikan tfoot hadir di tabel transaksi terbaru
        $response->assertSee('TOTAL (');
    }

    public function test_tenant_can_view_own_order_invoice_and_receipt(): void
    {
        $tenant1 = User::where('role', 'tenant')->first();
        $tenant2 = User::where('role', 'tenant')->where('id', '!=', $tenant1->id)->first();
        $admin = User::where('role', 'admin')->first();
        $package = VoucherPackage::first();

        $randUnpaid = 'ORD-INV-' . strtoupper(\Illuminate\Support\Str::random(5));
        $randPaid = 'ORD-KWT-' . strtoupper(\Illuminate\Support\Str::random(5));

        // 1. Order Belum Lunas (Menunggu Pembayaran)
        $unpaidOrder = Order::create([
            'order_number' => $randUnpaid,
            'tenant_id' => $tenant1->id,
            'customer_type' => 'warung',
            'package_id' => $package->id,
            'quantity' => 50,
            'printed_price_each' => 3000,
            'owner_price_each' => 2500,
            'total_printed_amount' => 150000,
            'total_owner_amount' => 125000,
            'total_reseller_profit' => 25000,
            'status' => 'menunggu_pembayaran',
        ]);

        // Tenant pemilik bisa lihat faktur tagihan
        $response = $this->actingAs($tenant1)->get(route('orders.invoice', $unpaidOrder));
        $response->assertStatus(200);
        $response->assertSee('Faktur Tagihan');
        $response->assertSee($randUnpaid);
        $response->assertSee('MENUNGGU SETORAN');
        $response->assertSee('125.000');
        $response->assertSee('Seratus Dua Puluh Lima Ribu Rupiah');

        // Admin bisa lihat faktur tagihan
        $responseAdmin = $this->actingAs($admin)->get(route('orders.invoice', $unpaidOrder));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Faktur Tagihan');

        // Tenant lain TIDAK BISA lihat (403 Forbidden)
        if ($tenant2) {
            $responseTenant2 = $this->actingAs($tenant2)->get(route('orders.invoice', $unpaidOrder));
            $responseTenant2->assertStatus(403);
        }

        // 2. Order Lunas (Kwitansi Pelunasan)
        $paidOrder = Order::create([
            'order_number' => $randPaid,
            'tenant_id' => $tenant1->id,
            'customer_type' => 'warung',
            'package_id' => $package->id,
            'quantity' => 20,
            'printed_price_each' => 3000,
            'owner_price_each' => 2500,
            'total_printed_amount' => 60000,
            'total_owner_amount' => 50000,
            'total_reseller_profit' => 10000,
            'payment_method' => 'transfer',
            'status' => 'lunas',
            'confirmed_by' => $admin->id,
            'confirmed_at' => now(),
        ]);

        $responsePaid = $this->actingAs($tenant1)->get(route('orders.invoice', $paidOrder));
        $responsePaid->assertStatus(200);
        $responsePaid->assertSee('Kwitansi Pelunasan');
        $responsePaid->assertSee($randPaid);
        $responsePaid->assertSee('LUNAS');
        $responsePaid->assertSee('TRANSFER');
        $responsePaid->assertSee('Lima Puluh Ribu Rupiah');

        // Memastikan opsi cetak Kertas Setengah A4 dan Struk Thermal hadir
        $responsePaid->assertSee('Kertas Setengah A4');
        $responsePaid->assertSee('Struk Thermal (POS)');
        $responsePaid->assertSee('thermal-receipt');
        $responsePaid->assertDontSee('Potong di sini');
    }

    public function test_super_admin_can_update_admin_name_for_receipts(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $admin = User::where('role', 'admin')->first();

        // Super Admin mengubah nama admin menjadi nama kasir resmi
        $response = $this->actingAs($superAdmin)->put(route('users.update', $admin), [
            'name' => 'Budi Santoso (Kasir Shift Pagi)',
            'username' => $admin->username,
            'phone' => '081299998888',
            'is_active' => '1',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Budi Santoso (Kasir Shift Pagi)',
            'phone' => '081299998888',
        ]);

        // Verifikasi ketika admin yang telah diupdate dikaitkan dengan transaksi, nama kasir muncul sesuai nama baru
        $admin->refresh();
        $sampleOrder = Order::where('confirmed_by', $admin->id)->first();
        if (! $sampleOrder) {
            $sampleOrder = Order::create([
                'order_number' => 'ORD-SAMPLE-RECEIPT-' . uniqid(),
                'tenant_id' => User::where('role', 'tenant')->first()->id,
                'created_by' => $admin->id,
                'confirmed_by' => $admin->id,
                'package_id' => VoucherPackage::first()->id,
                'quantity' => 10,
                'printed_price_each' => 3000,
                'owner_price_each' => 2500,
                'total_printed_amount' => 30000,
                'total_owner_amount' => 25000,
                'total_reseller_profit' => 5000,
                'status' => 'lunas',
            ]);
        }
        $invoiceResponse = $this->actingAs($admin)->get(route('orders.invoice', $sampleOrder));
        $invoiceResponse->assertStatus(200);
        $invoiceResponse->assertSee('Budi Santoso (Kasir Shift Pagi)');
    }

    public function test_navbar_always_renders_logout_and_user_info_for_both_admin_and_tenant(): void
    {
        $tenant = User::where('role', 'tenant')->first();
        $admin = User::where('role', 'admin')->first();
        $superAdmin = User::where('role', 'super_admin')->first();

        // 1. Tenant navbar
        $respTenant = $this->actingAs($tenant)->get(route('tenant.orders.index'));
        $respTenant->assertStatus(200);
        $respTenant->assertSee(route('logout'));
        $respTenant->assertSee('Keluar');
        $respTenant->assertSee(route('password.edit'));
        $respTenant->assertSee($tenant->shop_name ?: $tenant->name);

        // 2. Admin navbar
        $respAdmin = $this->actingAs($admin)->get(route('admin.orders.index'));
        $respAdmin->assertStatus(200);
        $respAdmin->assertSee(route('logout'));
        $respAdmin->assertSee('Keluar');
        $respAdmin->assertSee(route('password.edit'));
        $respAdmin->assertSee('adminMobileNav');

        // 3. Super Admin navbar
        $respSuper = $this->actingAs($superAdmin)->get(route('finance.index'));
        $respSuper->assertStatus(200);
        $respSuper->assertSee(route('logout'));
        $respSuper->assertSee('Keluar');
        $respSuper->assertSee(route('password.edit'));
    }
    public function test_tenant_can_place_multi_item_order_and_stock_deducted_per_package(): void
    {
        $tenant = User::where('role', 'tenant')->first();
        $pkg1 = VoucherPackage::create([
            'name' => 'Paket 3 Jam Multi',
            'printed_price' => 3000,
            'owner_price' => 2000,
            'reseller_profit' => 1000,
            'stock' => 100,
            'duration_info' => '3 Jam',
            'is_active' => true,
        ]);
        $pkg2 = VoucherPackage::create([
            'name' => 'Paket 6 Jam Multi',
            'printed_price' => 5000,
            'owner_price' => 3500,
            'reseller_profit' => 1500,
            'stock' => 100,
            'duration_info' => '6 Jam',
            'is_active' => true,
        ]);

        $response = $this->actingAs($tenant)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post('/tenant/orders', [
                'items' => [
                    $pkg1->id => 20,
                    $pkg2->id => 24,
                ],
            ]);

        $response->assertRedirect('/tenant/orders?tab=pesanan');
        $this->assertTrue(session()->has('whatsapp_url'));

        // Verify order created
        $order = Order::where('tenant_id', $tenant->id)->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals(44, $order->quantity);
        // pkg1: 20 * 2000 = 40.000; pkg2: 24 * 3500 = 84.000 -> total owner = 124.000
        $this->assertEquals(124000, $order->total_owner_amount);
        // pkg1 printed: 20 * 3000 = 60.000; pkg2 printed: 24 * 5000 = 120.000 -> total printed = 180.000
        $this->assertEquals(180000, $order->total_printed_amount);
        $this->assertEquals(56000, $order->total_reseller_profit);

        // Verify order items in database
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'package_id' => $pkg1->id,
            'quantity' => 20,
            'total_owner_amount' => 40000,
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'package_id' => $pkg2->id,
            'quantity' => 24,
            'total_owner_amount' => 84000,
        ]);

        // Stocks deducted
        $this->assertEquals(80, $pkg1->fresh()->stock);
        $this->assertEquals(76, $pkg2->fresh()->stock);

        // Summary string accessor
        $this->assertStringContainsString('Paket 3 Jam Multi (20 lbr)', $order->package_names_summary);
        $this->assertStringContainsString('Paket 6 Jam Multi (24 lbr)', $order->package_names_summary);
    }

    public function test_admin_can_place_multi_item_offline_order_and_cancel_restores_all_stocks(): void
    {
        $admin = User::where('role', 'admin')->first();
        $tenant = User::where('role', 'tenant')->first();
        $pkg1 = VoucherPackage::create([
            'name' => 'Paket A Offline Multi',
            'printed_price' => 4000,
            'owner_price' => 3000,
            'reseller_profit' => 1000,
            'stock' => 50,
            'duration_info' => '1 Hari',
            'is_active' => true,
        ]);
        $pkg2 = VoucherPackage::create([
            'name' => 'Paket B Offline Multi',
            'printed_price' => 7000,
            'owner_price' => 5000,
            'reseller_profit' => 2000,
            'stock' => 50,
            'duration_info' => '3 Hari',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post('/admin/orders/offline', [
                'buyer_type' => 'warung',
                'tenant_id' => $tenant->id,
                'payment_status' => 'tempo',
                'items' => [
                    $pkg1->id => 10,
                    $pkg2->id => 15,
                ],
            ]);

        $response->assertRedirect('/admin/orders');

        $order = Order::where('tenant_id', $tenant->id)->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals(25, $order->quantity);
        $this->assertEquals('menunggu_pembayaran', $order->status);

        // Stocks deducted
        $this->assertEquals(40, $pkg1->fresh()->stock);
        $this->assertEquals(35, $pkg2->fresh()->stock);

        // Invoice test
        $invoiceResp = $this->actingAs($admin)->get(route('orders.invoice', $order));
        $invoiceResp->assertStatus(200);
        $invoiceResp->assertSee('Paket A Offline Multi');
        $invoiceResp->assertSee('Paket B Offline Multi');

        // Admin cancels unpaid order
        $cancelResp = $this->actingAs($admin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post("/admin/orders/{$order->id}/cancel-unpaid", [
                'cancel_reason' => 'Salah input paket',
            ]);

        $cancelResp->assertRedirect();
        $this->assertEquals('dibatalkan', $order->fresh()->status);

        // Stocks of both packages restored
        $this->assertEquals(50, $pkg1->fresh()->stock);
        $this->assertEquals(50, $pkg2->fresh()->stock);
    }

    public function test_partner_registration_automatically_assigns_default_password(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();

        // Pendaftaran tanpa input password otomatis diberi password 'password'
        $response = $this->actingAs($superAdmin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post('/users', [
                'name' => 'Mitra Sukses Kilat',
                'username' => 'mitra_kilat',
                'phone' => '081299990005',
                'role' => 'tenant',
                'shop_name' => 'Warung Kilat Berkah',
                'address' => 'Jl. Mawar Indah RT 02',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'username' => 'mitra_kilat',
            'shop_name' => 'Warung Kilat Berkah',
            'role' => 'tenant',
        ]);

        $created = User::where('username', 'mitra_kilat')->first();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password', $created->password));
    }

    public function test_users_page_displays_default_password_info(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $response = $this->actingAs($superAdmin)->get('/users');

        $response->assertStatus(200);
        $response->assertSee('Password Awal Otomatis:');
        $response->assertSee('password');
        // Tidak ada input password pada form tambah user
        $response->assertDontSee('name="password"', false);
    }

    public function test_finance_package_breakdown_aggregates_multi_item_orders_correctly(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $tenant = User::where('role', 'tenant')->first();

        $pkgA = VoucherPackage::firstOrCreate(
            ['name' => 'Paket A 2 Jam Test'],
            [
                'duration_info' => '2 Jam',
                'printed_price' => 3000,
                'owner_price' => 2500,
                'reseller_profit' => 500,
                'stock' => 100,
                'is_active' => true,
            ]
        );

        $pkgB = VoucherPackage::firstOrCreate(
            ['name' => 'Paket B 5 Jam Test'],
            [
                'duration_info' => '5 Jam',
                'printed_price' => 5000,
                'owner_price' => 4000,
                'reseller_profit' => 1000,
                'stock' => 100,
                'is_active' => true,
            ]
        );

        // Multi-item order: package_id is null, items stored in order_items
        $multiOrder = Order::create([
            'order_number' => 'ORD-FIN-' . \Illuminate\Support\Str::random(8),
            'tenant_id' => $tenant->id,
            'customer_type' => 'warung',
            'package_id' => null,
            'quantity' => 40,
            'printed_price_each' => 3500,
            'owner_price_each' => 2875,
            'total_printed_amount' => 140000,
            'total_owner_amount' => 115000,
            'total_reseller_profit' => 25000,
            'payment_method' => 'cash',
            'status' => 'lunas',
            'confirmed_by' => $superAdmin->id,
            'confirmed_at' => now(),
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $multiOrder->id,
            'package_id' => $pkgA->id,
            'quantity' => 30,
            'printed_price_each' => 3000,
            'owner_price_each' => 2500,
            'total_printed_amount' => 90000,
            'total_owner_amount' => 75000,
            'total_reseller_profit' => 15000,
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $multiOrder->id,
            'package_id' => $pkgB->id,
            'quantity' => 10,
            'printed_price_each' => 5000,
            'owner_price_each' => 4000,
            'total_printed_amount' => 50000,
            'total_owner_amount' => 40000,
            'total_reseller_profit' => 10000,
        ]);

        $response = $this->actingAs($superAdmin)->get('/finances?year=' . now()->year . '&month=' . now()->month);
        $response->assertStatus(200);
        $response->assertSee('Paket Voucher Paling Laku');
        $response->assertSee('#1 Terlaris');
        $response->assertSee('Paket A 2 Jam Test');
        $response->assertSee('Paket B 5 Jam Test');
    }

    public function test_packages_index_filters_by_package_and_calculates_stock_analysis(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();

        $pkg = VoucherPackage::firstOrCreate(
            ['name' => 'Paket Analisis Test'],
            [
                'duration_info' => '12 Jam',
                'printed_price' => 15000,
                'owner_price' => 12000,
                'reseller_profit' => 3000,
                'stock' => 50,
                'is_active' => true,
            ]
        );

        $pkg->addStock(100, $superAdmin->id, 'Cetak batch baru untuk analisis');
        $pkg->deductStock(20, $superAdmin->id, 'Pesanan warung offline #TEST-ANALISIS');

        $response = $this->actingAs($superAdmin)->get('/packages?package_id=' . $pkg->id);
        $response->assertStatus(200);
        $response->assertSee('Riwayat & Analisis', false);
        $response->assertSee('Paket Analisis Test');
        $response->assertSee('+100 lbr');
        $response->assertSee('-20 lbr');
        $response->assertSee('Cetak batch baru untuk analisis');
    }

    public function test_admin_can_access_expenses_page_and_record_daily_expense(): void
    {
        $admin = User::where('role', 'admin')->first();

        // 1. Admin can access expenses page
        $response = $this->actingAs($admin)->get(route('finance.expenses'));
        $response->assertStatus(200);
        $response->assertSee('Beban & Pengeluaran Operasional', false);
        $response->assertSee('Catat Pengeluaran Baru');

        // 2. Admin can store a daily expense
        $postResponse = $this->actingAs($admin)->post(route('finance.expenses.store'), [
            'title' => 'Beli Bensin & Kertas Thermal Harian',
            'category' => 'Bensin & Transportasi',
            'amount' => '35.000',
            'expense_date' => now()->format('Y-m-d'),
            'notes' => 'Pengeluaran harian oleh admin operasional',
        ]);

        $postResponse->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'title' => 'Beli Bensin & Kertas Thermal Harian',
            'category' => 'Bensin & Transportasi',
            'amount' => 35000,
            'created_by' => $admin->id,
        ]);
    }

    public function test_admin_can_delete_own_expense_but_forbidden_from_deleting_others(): void
    {
        $admin = User::where('role', 'admin')->first();
        $superAdmin = User::where('role', 'super_admin')->first();

        $adminExpense = Expense::create([
            'expense_number' => 'EXP-TEST-ADMIN',
            'title' => 'Admin Own Expense',
            'category' => 'Operasional & Konsumsi',
            'amount' => 20000,
            'expense_date' => now()->format('Y-m-d'),
            'created_by' => $admin->id,
        ]);

        $superExpense = Expense::create([
            'expense_number' => 'EXP-TEST-SUPER',
            'title' => 'Super Admin ISP Bill',
            'category' => 'ISP Bandwidth',
            'amount' => 1500000,
            'expense_date' => now()->format('Y-m-d'),
            'created_by' => $superAdmin->id,
        ]);

        // Admin attempting to delete Super Admin expense -> denied
        $failResponse = $this->actingAs($admin)->delete(route('finance.expenses.destroy', $superExpense));
        $failResponse->assertSessionHas('error');
        $this->assertDatabaseHas('expenses', ['id' => $superExpense->id]);

        // Admin deleting their own expense -> allowed
        $successResponse = $this->actingAs($admin)->delete(route('finance.expenses.destroy', $adminExpense));
        $successResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('expenses', ['id' => $adminExpense->id]);

        // Clean up
        $superExpense->delete();
    }

    public function test_super_admin_can_download_database_backup_and_admin_is_forbidden(): void
    {
        $admin = User::where('role', 'admin')->first();
        $superAdmin = User::where('role', 'super_admin')->first();

        // 1. Admin is forbidden
        $forbiddenResponse = $this->actingAs($admin)->get(route('settings.backup_database'));
        $forbiddenResponse->assertStatus(403);

        // 2. Super Admin can download
        $downloadResponse = $this->actingAs($superAdmin)->get(route('settings.backup_database'));
        $downloadResponse->assertStatus(200);
        $downloadResponse->assertHeader('content-disposition');
        $this->assertTrue(str_contains(
            $downloadResponse->headers->get('content-disposition'),
            'vouchernet-backup-'
        ));
    }

    public function test_cannot_delete_package_with_order_items_and_shows_error(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $pkg = VoucherPackage::has('orderItems')->first();
        $this->assertNotNull($pkg);

        $response = $this->actingAs($superAdmin)->delete(route('packages.destroy', $pkg));

        $response->assertSessionHasErrors('message');
        $this->assertDatabaseHas('voucher_packages', ['id' => $pkg->id]);
    }

    public function test_can_delete_unused_package(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $newPkg = VoucherPackage::create([
            'name' => 'Paket Percobaan Hapus',
            'duration_info' => '1 Jam',
            'printed_price' => 1000,
            'owner_price' => 800,
            'reseller_profit' => 200,
            'stock' => 0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($superAdmin)->delete(route('packages.destroy', $newPkg));

        $response->assertRedirect(route('packages.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('voucher_packages', ['id' => $newPkg->id]);
    }

    public function test_editing_package_does_not_alter_historical_order_item_prices(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $pkg = VoucherPackage::has('orderItems')->first();
        $orderItem = OrderItem::where('package_id', $pkg->id)->first();
        $originalPrintedPrice = $orderItem->printed_price_each;
        $originalOwnerPrice = $orderItem->owner_price_each;

        // Edit package price to new values
        $response = $this->actingAs($superAdmin)->put(route('packages.update', $pkg), [
            'name' => $pkg->name,
            'duration_info' => $pkg->duration_info,
            'printed_price' => 99000,
            'owner_price' => 88000,
            'description' => 'Updated description',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('packages.index'));
        $pkg->refresh();
        $this->assertEquals(99000, (float)$pkg->printed_price);

        // Historical order item must remain completely untouched
        $orderItem->refresh();
        $this->assertEquals($originalPrintedPrice, $orderItem->printed_price_each);
        $this->assertEquals($originalOwnerPrice, $orderItem->owner_price_each);

        // Restore package price
        $pkg->update([
            'printed_price' => $originalPrintedPrice,
            'owner_price' => $originalOwnerPrice,
            'reseller_profit' => $originalPrintedPrice - $originalOwnerPrice,
        ]);
    }

    public function test_super_admin_can_toggle_package_active_status(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $pkg = VoucherPackage::first();
        $originalStatus = $pkg->is_active;

        // Toggle to opposite status
        $response = $this->actingAs($superAdmin)->post(route('packages.toggle', $pkg));
        $response->assertSessionHas('success');
        $expectedTab = !$originalStatus ? 'active' : 'inactive';
        $response->assertRedirect(route('packages.index', ['tab' => $expectedTab]));
        $this->assertEquals(!$originalStatus, $pkg->fresh()->is_active);

        // Toggle back
        $response = $this->actingAs($superAdmin)->post(route('packages.toggle', $pkg));
        $response->assertSessionHas('success');
        $expectedTabBack = $originalStatus ? 'active' : 'inactive';
        $response->assertRedirect(route('packages.index', ['tab' => $expectedTabBack]));
        $this->assertEquals($originalStatus, $pkg->fresh()->is_active);

        // View index page with tabs
        $indexResponse = $this->actingAs($superAdmin)->get(route('packages.index', ['tab' => 'inactive']));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Paket Nonaktif');
        $indexResponse->assertSee('Paket Aktif');
        $this->assertEquals('inactive', $indexResponse->viewData('selectedTab'));
    }

    public function test_super_admin_can_update_white_label_branding_and_upload_logo(): void
    {
        Storage::fake('public');
        $superAdmin = User::where('role', 'super_admin')->first();

        $logoFile = UploadedFile::fake()->image('custom-logo.png', 200, 80);
        $faviconFile = UploadedFile::fake()->create('custom-favicon.ico', 32, 'image/x-icon');

        $response = $this->actingAs($superAdmin)->post(route('settings.update'), [
            'app_name' => 'Berkah WiFi Net',
            'app_subtitle' => 'Internet Cepat Warga RW 05',
            'admin_whatsapp_number' => '6289912345678',
            'bank_accounts' => 'BCA: 999111222',
            'app_logo' => $logoFile,
            'app_favicon' => $faviconFile,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('Berkah WiFi Net', Setting::get('app_name'));
        $this->assertEquals('Internet Cepat Warga RW 05', Setting::get('app_subtitle'));
        $this->assertNotNull(Setting::get('app_logo_path'));
        $this->assertNotNull(Setting::get('app_favicon_path'));
        Storage::disk('public')->assertExists(Setting::get('app_logo_path'));
        Storage::disk('public')->assertExists(Setting::get('app_favicon_path'));

        // Check if reflected on pages
        $pageResponse = $this->actingAs($superAdmin)->get('/settings');
        $pageResponse->assertSee('Berkah WiFi Net');
        $pageResponse->assertSee('Internet Cepat Warga RW 05');
        $pageResponse->assertSee(asset('storage/' . Setting::get('app_logo_path')));

        // Check on invoice
        $order = Order::first();
        if ($order) {
            $invoiceResponse = $this->actingAs($superAdmin)->get(route('orders.invoice', $order));
            $invoiceResponse->assertSee('Berkah WiFi Net');
            $invoiceResponse->assertSee('Internet Cepat Warga RW 05');
        }
    }

    public function test_super_admin_can_remove_custom_logo_and_reset_to_default(): void
    {
        Storage::fake('public');
        $superAdmin = User::where('role', 'super_admin')->first();

        // Setup logo first
        $logoFile = UploadedFile::fake()->image('my-logo.png');
        $this->actingAs($superAdmin)->post(route('settings.update'), [
            'app_name' => 'VoucherNet Custom',
            'app_subtitle' => 'Sistem Kustom',
            'admin_whatsapp_number' => '6281234567890',
            'bank_accounts' => 'BCA: 123456',
            'app_logo' => $logoFile,
        ]);

        $this->assertNotNull(Setting::get('app_logo_path'));

        // Remove logo
        $response = $this->actingAs($superAdmin)->post(route('settings.update'), [
            'app_name' => 'VoucherNet Custom',
            'app_subtitle' => 'Sistem Kustom',
            'admin_whatsapp_number' => '6281234567890',
            'bank_accounts' => 'BCA: 123456',
            'remove_logo' => 1,
        ]);

        $response->assertSessionHas('success');
        $this->assertNull(Setting::get('app_logo_path'));
        Setting::set('app_name', 'VoucherNet');
    }

    public function test_financial_report_shows_owner_from_super_admin_account_and_custom_app_name(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $originalName = $superAdmin->name;
        $originalAppName = Setting::get('app_name', 'VoucherNet RT/RW');

        $superAdmin->update(['name' => 'Seno Bumi Munajat']);
        Setting::set('app_name', 'Berkah WiFi Net');

        $printResponse = $this->actingAs($superAdmin)->get('/finances/print?range_type=month&month=10&year=2026');
        $printResponse->assertStatus(200);
        $printResponse->assertSee('Mengetahui,');
        $printResponse->assertSee('Owner / Pengelola Berkah WiFi Net');
        $printResponse->assertSee('Seno Bumi Munajat');
        $printResponse->assertDontSee('Ketua / Pengelola RT-RW Net');
        $printResponse->assertSee('Dibuat Oleh,');
        $printResponse->assertSee('Bendahara / Bagian Keuangan');

        // Restore original values
        $superAdmin->update(['name' => $originalName]);
        Setting::set('app_name', $originalAppName);
    }

    public function test_invoice_and_receipt_signatory_name_is_locked_to_original_creator_and_never_changes(): void
    {
        $admin = User::where('role', 'admin')->first();
        $superAdmin = User::where('role', 'super_admin')->first();
        $tenant = User::where('role', 'tenant')->first();
        $pkg = VoucherPackage::first();

        // 1. Admin creates an offline order (tempo / unpaid)
        $this->actingAs($admin)->post(route('admin.orders.store_offline'), [
            'tenant_id' => $tenant->id,
            'is_paid' => 0,
            'items' => [$pkg->id => 5],
        ]);

        $order = Order::latest('id')->first();
        $this->assertEquals($admin->id, $order->created_by);

        // Viewed by Admin: Signatory should be Admin
        $respAdmin = $this->actingAs($admin)->get(route('orders.invoice', $order));
        $respAdmin->assertSee($admin->name);

        // Viewed by Super Admin: Signatory MUST STILL be Admin (does NOT switch to Super Admin)
        $respSuper = $this->actingAs($superAdmin)->get(route('orders.invoice', $order));
        $respSuper->assertSee($admin->name);
        $respSuper->assertDontSee($superAdmin->name);

        // Viewed by Tenant: Signatory MUST STILL be Admin
        $respTenant = $this->actingAs($tenant)->get(route('orders.invoice', $order));
        $respTenant->assertSee($admin->name);

        // 2. Tenant creates an online order independently
        $this->actingAs($tenant)->post(route('tenant.orders.store'), [
            'items' => [$pkg->id => 10],
        ]);
        $onlineOrder = Order::latest('id')->first();
        $this->assertEquals($tenant->id, $onlineOrder->created_by);

        // Before payment confirmation: Invoice shows dotted line for manual verification
        $respOnlineFaktur = $this->actingAs($superAdmin)->get(route('orders.invoice', $onlineOrder));
        $respOnlineFaktur->assertSee('( .................................... )');

        // After Admin confirms payment: Receipt locks to Admin who confirmed
        $this->actingAs($admin)->post(route('admin.orders.confirm', $onlineOrder), [
            'payment_method' => 'cash',
        ]);
        $onlineOrder->refresh();
        $this->assertEquals('lunas', $onlineOrder->status);
        $this->assertEquals($admin->id, $onlineOrder->confirmed_by);

        $respOnlineKwitansi = $this->actingAs($superAdmin)->get(route('orders.invoice', $onlineOrder));
        $respOnlineKwitansi->assertSee($admin->name);
        $respOnlineKwitansi->assertSee(strtoupper(Setting::get('app_name', 'VoucherNet')));
    }

    public function test_admin_and_super_admin_can_access_inventory_and_tenant_is_forbidden(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $admin = User::where('role', 'admin')->first();
        $tenant = User::where('role', 'tenant')->first();

        // Super Admin access
        $respSuper = $this->actingAs($superAdmin)->get(route('inventory.index'));
        $respSuper->assertStatus(200);
        $respSuper->assertSee('Inventaris & Logistik Barang');
        $respSuper->assertSee('Router MikroTik RB750Gr3');

        // Admin access
        $respAdmin = $this->actingAs($admin)->get(route('inventory.index'));
        $respAdmin->assertStatus(200);
        $respAdmin->assertSee('Inventaris & Logistik Barang');

        // Tenant forbidden
        $respTenant = $this->actingAs($tenant)->get(route('inventory.index'));
        $respTenant->assertStatus(403);

        // Test creating item with empty location and initial_stock 0 defaults to Gudang and 0 min_stock
        $testUniqueName = 'Switch Hub Posko Test ' . rand(1000, 9999);
        $respCreate = $this->actingAs($superAdmin)->post(route('inventory.items.store'), [
            'name' => $testUniqueName,
            'category' => 'Switch & Hub',
            'unit' => 'unit',
            'initial_stock' => 0,
            'location' => '', // empty
        ]);
        $respCreate->assertSessionHas('success');
        $created = InventoryItem::where('name', $testUniqueName)->first();
        $this->assertNotNull($created);
        $this->assertEquals('Gudang', $created->location);
        $this->assertEquals(0, $created->stock);
        $this->assertEquals(0, $created->min_stock);
        $created->delete();
    }

    public function test_admin_and_super_admin_can_record_stock_out_and_stock_is_deducted(): void
    {
        $admin = User::where('role', 'admin')->first();
        $item = InventoryItem::where('stock', '>', 2)->first();
        $this->assertNotNull($item);
        $initialStock = $item->stock;

        // Valid stock out
        $response = $this->actingAs($admin)->post(route('inventory.movements.out'), [
            'inventory_item_id' => $item->id,
            'quantity' => 2,
            'movement_date' => date('Y-m-d'),
            'purpose' => 'Pemasangan di Mitra Toko Berkah',
            'recipient' => 'Warung Toko Berkah / RT 03',
            'notes' => 'SN: 9812738912',
        ]);

        $response->assertSessionHas('success');
        $item->refresh();
        $this->assertEquals($initialStock - 2, $item->stock);

        $movement = InventoryMovement::latest('id')->first();
        $this->assertEquals('out', $movement->type);
        $this->assertEquals(2, $movement->quantity);
        $this->assertEquals($admin->id, $movement->created_by);
        $this->assertEquals('Pemasangan di Mitra Toko Berkah', $movement->purpose);

        // Exceeding stock should fail
        $failResponse = $this->actingAs($admin)->post(route('inventory.movements.out'), [
            'inventory_item_id' => $item->id,
            'quantity' => 99999,
            'movement_date' => date('Y-m-d'),
            'purpose' => 'Permintaan Berlebih',
        ]);
        $failResponse->assertSessionHasErrors('message');
    }

    public function test_admin_and_super_admin_can_record_stock_in_and_stock_is_added(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $item = InventoryItem::first();
        $initialStock = $item->stock;

        $response = $this->actingAs($superAdmin)->post(route('inventory.movements.in'), [
            'inventory_item_id' => $item->id,
            'quantity' => 5,
            'movement_date' => date('Y-m-d'),
            'purpose' => 'Restock Pengadaan Baru Toko Komputer',
            'supplier_or_source' => 'Gudang Pusat',
            'notes' => 'Batch 2 Nota 112',
        ]);

        $response->assertSessionHas('success');
        $item->refresh();
        $this->assertEquals($initialStock + 5, $item->stock);

        $movement = InventoryMovement::latest('id')->first();
        $this->assertEquals('in', $movement->type);
        $this->assertEquals(5, $movement->quantity);

        // Also test stock in when optional fields (supplier_or_source, purpose, notes) are omitted
        $respMinimal = $this->actingAs($superAdmin)->post(route('inventory.movements.in'), [
            'inventory_item_id' => $item->id,
            'quantity' => 2,
            'movement_date' => date('Y-m-d'),
        ]);
        $respMinimal->assertSessionHas('success');
        $item->refresh();
        $this->assertEquals($initialStock + 7, $item->stock);
    }

    public function test_expense_can_be_checked_to_automatically_add_stock_to_existing_inventory(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $item = InventoryItem::first();
        $initialStock = $item->stock;

        $response = $this->actingAs($superAdmin)->post(route('finance.expenses.store'), [
            'title' => 'Beli Switch Hub Tambahan',
            'category' => 'Perangkat / Router / AP',
            'amount' => '450.000',
            'expense_date' => date('Y-m-d'),
            'notes' => 'Pembelian toko elektronik',
            'is_inventory' => 1,
            'inventory_mode' => 'existing',
            'inventory_item_id' => $item->id,
            'inventory_quantity' => 3,
        ]);

        $response->assertSessionHas('success');
        $item->refresh();
        $this->assertEquals($initialStock + 3, $item->stock);

        $expense = Expense::latest('id')->first();
        $this->assertTrue((bool) $expense->is_inventory);
        $this->assertEquals($item->id, $expense->inventory_item_id);
        $this->assertEquals(3, $expense->inventory_quantity);

        // Check view displays inventory badge
        $viewResp = $this->actingAs($superAdmin)->get(route('finance.expenses'));
        $viewResp->assertSee('Masuk Inventory: ' . $item->name);

        // Deleting the expense rolls back inventory stock
        $delResp = $this->actingAs($superAdmin)->delete(route('finance.expenses.destroy', $expense));
        $delResp->assertSessionHas('success');
        $item->refresh();
        $this->assertEquals($initialStock, $item->stock);
    }

    public function test_expense_can_be_checked_to_create_brand_new_inventory_item_and_add_stock(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $uniqueName = 'Patch Cord SC-SC ' . rand(100, 999);

        $response = $this->actingAs($superAdmin)->post(route('finance.expenses.store'), [
            'title' => 'Beli Kabel Patch Cord FO 3 Meter',
            'category' => 'Kabel FO & Aksesoris',
            'amount' => '75.000',
            'expense_date' => date('Y-m-d'),
            'notes' => 'Kabel jumper antar rack',
            'is_inventory' => 1,
            'inventory_mode' => 'new',
            'new_item_name' => $uniqueName,
            'new_item_category' => 'Kabel & Fiber Optic',
            'new_item_unit' => 'pcs',
            'inventory_quantity' => 5,
            'inventory_location' => 'Rak Server Utama',
        ]);

        $response->assertSessionHas('success');

        $newItem = InventoryItem::where('name', $uniqueName)->first();
        $this->assertNotNull($newItem);
        $this->assertEquals(5, $newItem->stock);
        $this->assertEquals('Kabel & Fiber Optic', $newItem->category);
        $this->assertEquals('pcs', $newItem->unit);
        $this->assertEquals('Rak Server Utama', $newItem->location);

        $expense = Expense::latest('id')->first();
        $this->assertEquals($newItem->id, $expense->inventory_item_id);

        // Cleanup
        $expense->delete();
        $newItem->delete();
    }

    public function test_admin_and_super_admin_can_upload_and_update_inventory_photo(): void
    {
        Storage::fake('public');
        $superAdmin = User::where('role', 'super_admin')->first();

        $photoFile = UploadedFile::fake()->image('router-mikrotik.png', 400, 300);

        // 1. Create item with photo upload
        $response = $this->actingAs($superAdmin)->post(route('inventory.items.store'), [
            'name' => 'Router Baru Foto Test ' . rand(100, 999),
            'category' => 'Router & Gateway',
            'unit' => 'unit',
            'initial_stock' => 10,
            'min_stock' => 2,
            'unit_price' => '850.000',
            'location' => 'Gudang Posko',
            'description' => 'Router test upload foto',
            'photo' => $photoFile,
        ]);

        $response->assertSessionHas('success');
        $item = InventoryItem::latest('id')->first();
        $this->assertNotNull($item->photo);
        $this->assertNotNull($item->photo_url);
        Storage::disk('public')->assertExists($item->photo);

        // 2. Index renders card with photo
        $indexResp = $this->actingAs($superAdmin)->get(route('inventory.index'));
        $indexResp->assertStatus(200);
        $indexResp->assertSee($item->photo_url);

        // 3. Update with new photo
        $newPhotoFile = UploadedFile::fake()->image('router-new.png', 400, 300);
        $oldPhoto = $item->photo;
        $upResp = $this->actingAs($superAdmin)->put(route('inventory.items.update', $item), [
            'name' => $item->name,
            'category' => $item->category,
            'unit' => $item->unit,
            'min_stock' => $item->min_stock,
            'photo' => $newPhotoFile,
        ]);
        $upResp->assertSessionHas('success');
        $item->refresh();
        Storage::disk('public')->assertMissing($oldPhoto);
        Storage::disk('public')->assertExists($item->photo);

        // 4. Remove photo
        $removeResp = $this->actingAs($superAdmin)->put(route('inventory.items.update', $item), [
            'name' => $item->name,
            'category' => $item->category,
            'unit' => $item->unit,
            'min_stock' => $item->min_stock,
            'remove_photo' => 1,
        ]);
        $removeResp->assertSessionHas('success');
        $item->refresh();
        $this->assertNull($item->photo);

        // Cleanup
        $item->movements()->delete();
        $item->delete();
    }

    public function test_inventory_item_can_only_be_deleted_when_stock_is_zero(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();

        // 1. Item with stock > 0 cannot be deleted
        $itemWithStock = InventoryItem::create([
            'item_code' => 'DEL-001',
            'name' => 'Barang Ada Stok',
            'category' => 'Umum',
            'unit' => 'unit',
            'stock' => 5,
            'min_stock' => 0,
            'is_active' => true,
        ]);

        $failResp = $this->actingAs($superAdmin)->delete(route('inventory.items.destroy', $itemWithStock));
        $failResp->assertSessionHasErrors(['message']);
        $this->assertDatabaseHas('inventory_items', ['id' => $itemWithStock->id]);

        // 2. Item with stock 0 can be deleted successfully
        $itemWithStock->update(['stock' => 0]);
        $successResp = $this->actingAs($superAdmin)->delete(route('inventory.items.destroy', $itemWithStock));
        $successResp->assertSessionHas('success');
        $this->assertDatabaseMissing('inventory_items', ['id' => $itemWithStock->id]);
    }
}






