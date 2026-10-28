<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use App\Models\VoucherPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TenantOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $packages = VoucherPackage::where('is_active', true)->orderBy('printed_price')->get();

        $statusFilter = $request->query('status');

        $ordersQuery = Order::where('tenant_id', $user->id)
            ->with(['package', 'confirmedByUser'])
            ->latest();

        if ($statusFilter && in_array($statusFilter, ['menunggu_pembayaran', 'lunas'])) {
            $ordersQuery->where('status', $statusFilter);
        }

        $orders = $ordersQuery->paginate(15)->withQueryString();

        // 1. Total Voucher yang Sedang Dibawa / Belum Disetor (Konsinyasi / Ambil Dulu)
        $pendingVouchersCount = (int) Order::where('tenant_id', $user->id)
            ->where('status', 'menunggu_pembayaran')
            ->sum('quantity');

        // 2. Total Nominal Tagihan yang HARUS DISETORKAN ke Admin
        $pendingBillAmount = (float) Order::where('tenant_id', $user->id)
            ->where('status', 'menunggu_pembayaran')
            ->sum('total_owner_amount');

        // 3. Total Voucher yang Sudah Disetor (Lunas)
        $paidVouchersCount = (int) Order::where('tenant_id', $user->id)
            ->where('status', 'lunas')
            ->sum('quantity');

        $paidBillAmount = (float) Order::where('tenant_id', $user->id)
            ->where('status', 'lunas')
            ->sum('total_owner_amount');

        // 4. Total Keseluruhan Voucher yang Pernah Diambil (Aktif: Lunas + Menunggu)
        $totalVouchersTaken = (int) Order::where('tenant_id', $user->id)
            ->whereIn('status', ['lunas', 'menunggu_pembayaran'])
            ->sum('quantity');

        // 5. Total Untung Reseller yang Didapat dari Voucher Lunas
        $totalProfitEarned = (float) Order::where('tenant_id', $user->id)
            ->where('status', 'lunas')
            ->sum('total_reseller_profit');

        // 6. Potensi Keuntungan dari Voucher yang Sedang Dibawa
        $pendingProfitPotential = (float) Order::where('tenant_id', $user->id)
            ->where('status', 'menunggu_pembayaran')
            ->sum('total_reseller_profit');

        // Jumlah pesanan per status untuk tab filter
        $totalOrdersCount = Order::where('tenant_id', $user->id)->count();
        $pendingOrdersCount = Order::where('tenant_id', $user->id)->where('status', 'menunggu_pembayaran')->count();
        $paidOrdersCount = Order::where('tenant_id', $user->id)->where('status', 'lunas')->count();

        $adminPhone = Setting::get('admin_whatsapp_number', '6281234567890');
        $adminPhoneClean = preg_replace('/[^0-9]/', '', $adminPhone);
        if (str_starts_with($adminPhoneClean, '0')) {
            $adminPhoneClean = '62' . substr($adminPhoneClean, 1);
        }

        $bankAccounts = Setting::get('bank_accounts', "BCA: 123456789 a.n RT RW Net\nBRI: 987654321 a.n RT RW Net\nDANA / GoPay: 081234567890");

        $activeTab = $request->query('tab');
        if (! in_array($activeTab, ['katalog', 'pesanan'])) {
            $activeTab = ($request->filled('status') || $request->filled('page')) ? 'pesanan' : 'katalog';
        }

        // Backwards compatibility aliases
        $totalOrders = $totalOrdersCount;
        $totalVouchersBought = $paidVouchersCount;

        return view('tenant.index', compact(
            'user',
            'packages',
            'orders',
            'activeTab',
            'statusFilter',
            'totalVouchersTaken',
            'pendingVouchersCount',
            'pendingBillAmount',
            'paidVouchersCount',
            'paidBillAmount',
            'totalProfitEarned',
            'pendingProfitPotential',
            'totalOrdersCount',
            'pendingOrdersCount',
            'paidOrdersCount',
            'totalOrders',
            'totalVouchersBought',
            'adminPhone',
            'adminPhoneClean',
            'bankAccounts'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Support both items array [package_id => quantity] and legacy single package_id + quantity
        $itemsInput = [];
        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $pkgId => $qty) {
                $q = (int) $qty;
                if ($q > 0) {
                    $itemsInput[(int) $pkgId] = $q;
                }
            }
        } elseif ($request->filled('package_id') && (int) $request->quantity > 0) {
            $itemsInput[(int) $request->package_id] = (int) $request->quantity;
        }

        if (empty($itemsInput)) {
            return back()->withErrors([
                'quantity' => 'Mohon tentukan jumlah lembar minimal 1 lembar pada paket yang ingin dipesan.',
            ])->withInput();
        }

        // Fetch packages and validate stock
        $packageIds = array_keys($itemsInput);
        $packages = VoucherPackage::whereIn('id', $packageIds)->get()->keyBy('id');

        foreach ($itemsInput as $pkgId => $qty) {
            $package = $packages->get($pkgId);
            if (! $package) {
                return back()->withErrors(['package_id' => "Paket voucher ID #{$pkgId} tidak ditemukan."])->withInput();
            }
            if ($qty > 5000) {
                return back()->withErrors(['quantity' => "Maksimal pemesanan per paket adalah 5.000 lembar."])->withInput();
            }
            if ($package->stock < $qty) {
                return back()->withErrors([
                    'quantity' => "Maaf, stok voucher fisik untuk paket '{$package->name}' hanya tersisa {$package->stock} lembar (diminta {$qty} lembar). Mohon sesuaikan jumlah pesanan Anda.",
                ])->withInput();
            }
        }

        // Calculate totals
        $totalPrinted = 0;
        $totalOwner = 0;
        $totalProfit = 0;
        $totalQty = 0;

        foreach ($itemsInput as $pkgId => $qty) {
            $package = $packages->get($pkgId);
            $totalPrinted += $package->printed_price * $qty;
            $totalOwner += $package->owner_price * $qty;
            $totalProfit += ($package->printed_price - $package->owner_price) * $qty;
            $totalQty += $qty;
        }

        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        $firstPackageId = count($itemsInput) === 1 ? array_key_first($itemsInput) : null;
        $firstPackage = $firstPackageId ? $packages->get($firstPackageId) : null;

        $order = \Illuminate\Support\Facades\DB::transaction(function () use ($user, $orderNumber, $firstPackageId, $firstPackage, $totalQty, $totalPrinted, $totalOwner, $totalProfit, $itemsInput, $packages) {
            $order = Order::create([
                'order_number' => $orderNumber,
                'tenant_id' => $user->id,
                'created_by' => $user->id,
                'customer_type' => 'warung',
                'package_id' => $firstPackageId,
                'quantity' => $totalQty,
                'printed_price_each' => $firstPackage ? $firstPackage->printed_price : ($totalQty > 0 ? $totalPrinted / $totalQty : 0),
                'owner_price_each' => $firstPackage ? $firstPackage->owner_price : ($totalQty > 0 ? $totalOwner / $totalQty : 0),
                'total_printed_amount' => $totalPrinted,
                'total_owner_amount' => $totalOwner,
                'total_reseller_profit' => $totalProfit,
                'payment_method' => 'unspecified',
                'status' => 'menunggu_pembayaran',
            ]);

            foreach ($itemsInput as $pkgId => $qty) {
                $pkg = $packages->get($pkgId);
                $printedEach = $pkg->printed_price;
                $ownerEach = $pkg->owner_price;
                $subPrinted = $printedEach * $qty;
                $subOwner = $ownerEach * $qty;
                $subProfit = ($printedEach - $ownerEach) * $qty;

                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'package_id' => $pkg->id,
                    'quantity' => $qty,
                    'printed_price_each' => $printedEach,
                    'owner_price_each' => $ownerEach,
                    'total_printed_amount' => $subPrinted,
                    'total_owner_amount' => $subOwner,
                    'total_reseller_profit' => $subProfit,
                ]);

                $pkg->deductStock($qty, $user->id, "Pesanan online mitra warung #{$orderNumber}");
            }

            return $order;
        });

        // Generate WA message
        $adminPhone = preg_replace('/[^0-9]/', '', Setting::get('admin_whatsapp_number', '6281234567890'));
        if (str_starts_with($adminPhone, '0')) {
            $adminPhone = '62' . substr($adminPhone, 1);
        }

        $shop = $user->shop_name ?: $user->name;
        $itemLines = [];
        foreach ($itemsInput as $pkgId => $qty) {
            $pkg = $packages->get($pkgId);
            $sub = $pkg->owner_price * $qty;
            $itemLines[] = "• {$pkg->name}: *{$qty} lbr* (Rp " . number_format($sub, 0, ',', '.') . ")";
        }
        $itemsText = implode("\n", $itemLines);

        $text = "*Halo Admin VoucherNet,*\n" .
                "Saya ingin memesan voucher hotspot:\n\n" .
                "• *Mitra:* {$shop} ({$user->phone})\n" .
                "• *Order ID:* {$order->order_number}\n\n" .
                "*Rincian Paket:*\n" .
                $itemsText . "\n\n" .
                "• *Total Lembar:* *{$totalQty} Lembar*\n" .
                "• *Total Wajib Setor:* *Rp " . number_format($totalOwner, 0, ',', '.') . "*\n\n" .
                "Mohon konfirmasi rekening pembayaran & cetak vouchernya ya min. Terima kasih!";

        $whatsappUrl = 'https://api.whatsapp.com/send?phone=' . $adminPhone . '&text=' . rawurlencode($text);

        return redirect()->route('tenant.orders.index', ['tab' => 'pesanan'])
            ->with('success', 'Pesanan berhasil dibuat!')
            ->with('whatsapp_url', $whatsappUrl)
            ->with('whatsapp_text', $text)
            ->with('latest_order_id', $order->id);
    }

    public function downloadPdf(Order $order)
    {
        $user = Auth::user();

        // Security check: Only owner of order, admin, or super admin can download
        if ($user->isTenant() && $order->tenant_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke berkas voucher ini.');
        }

        if (! $order->voucher_pdf_file || ! Storage::disk('public')->exists($order->voucher_pdf_file)) {
            return back()->withErrors(['message' => 'Berkas PDF voucher belum diunggah oleh admin.']);
        }

        return Storage::disk('public')->download(
            $order->voucher_pdf_file,
            'Voucher_' . $order->order_number . '.pdf'
        );
    }
}
