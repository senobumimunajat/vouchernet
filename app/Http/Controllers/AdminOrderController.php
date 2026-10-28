<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\VoucherPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['tenant', 'package', 'items.package', 'confirmedByUser', 'deleteRequestedByUser']);

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by tenant / warung
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        // Search order number or tenant
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('tenant', function ($t) use ($search) {
                      $t->where('name', 'like', "%{$search}%")
                        ->orWhere('shop_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by package
        if ($request->filled('package_id')) {
            $pkgId = $request->package_id;
            $query->where(function ($q) use ($pkgId) {
                $q->where('package_id', $pkgId)
                  ->orWhereHas('items', function ($it) use ($pkgId) {
                      $it->where('package_id', $pkgId);
                  });
            });
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        // Operational stats (Note: Admin CANNOT see total revenue or cash amounts, only volume of vouchers and counts!)
        $pendingCount = Order::where('status', 'menunggu_pembayaran')->count();
        $deleteRequestCount = Order::where('status', 'menunggu_approval_hapus')->count();
        $todayVouchersSold = Order::where('status', 'lunas')
            ->whereDate('confirmed_at', now()->today())
            ->sum('quantity');

        $packages = VoucherPackage::where('is_active', true)->get();
        $tenants = User::where('role', 'tenant')->orderBy('name')->get();

        // Rekap konsinyasi / tempo khusus jika warung difilter
        $selectedTenant = null;
        $tenantConsignmentStats = null;
        if ($request->filled('tenant_id')) {
            $selectedTenant = User::find($request->tenant_id);
            if ($selectedTenant) {
                $pendingOrdersQuery = Order::where('tenant_id', $selectedTenant->id)
                    ->where('status', 'menunggu_pembayaran');

                $tenantConsignmentStats = [
                    'pending_orders_count' => (clone $pendingOrdersQuery)->count(),
                    'pending_vouchers_count' => (clone $pendingOrdersQuery)->sum('quantity'),
                    'pending_amount' => (clone $pendingOrdersQuery)->sum('total_owner_amount'),
                ];
            }
        }

        return view('admin.orders.index', compact(
            'orders',
            'pendingCount',
            'deleteRequestCount',
            'todayVouchersSold',
            'packages',
            'tenants',
            'selectedTenant',
            'tenantConsignmentStats'
        ));
    }

    public function createOffline()
    {
        $tenants = User::where('role', 'tenant')
            ->where('username', '!=', 'pelanggan_langsung')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $packages = VoucherPackage::where('is_active', true)->orderBy('printed_price')->get();

        return view('admin.orders.create_offline', compact('tenants', 'packages'));
    }

    public function storeOffline(Request $request)
    {
        $buyerType = $request->input('buyer_type', 'warung');

        // Parse items input (supports both items array [package_id => quantity] and legacy package_id + quantity)
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

        $packageIds = array_keys($itemsInput);
        $packages = VoucherPackage::whereIn('id', $packageIds)->get()->keyBy('id');

        foreach ($itemsInput as $pkgId => $qty) {
            $package = $packages->get($pkgId);
            if (! $package) {
                return back()->withErrors(['package_id' => "Paket voucher ID #{$pkgId} tidak ditemukan."])->withInput();
            }
            if ($package->stock < $qty) {
                return back()->withErrors([
                    'quantity' => "Stok voucher fisik untuk paket '{$package->name}' tidak mencukupi (Tersisa: {$package->stock} lembar, diminta {$qty} lembar).",
                ])->withInput();
            }
        }

        // ALUR 1: PEMBELI LANGSUNG / ECERAN (DIRECT WALK-IN)
        if ($buyerType === 'direct') {
            $request->validate([
                'customer_name' => ['nullable', 'string', 'max:100'],
                'payment_method' => ['nullable', 'in:cash,transfer'],
                'payment_proof_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            ]);

            // Dapatkan atau buat akun sistem pembeli langsung
            $tenant = User::firstOrCreate(
                ['username' => 'pelanggan_langsung'],
                [
                    'name' => 'Pelanggan Langsung (Walk-In)',
                    'shop_name' => 'Pelanggan Langsung',
                    'role' => 'tenant',
                    'phone' => '-',
                    'password' => bcrypt(Str::random(32)),
                    'is_active' => true,
                ]
            );

            $customerName = trim((string) $request->input('customer_name'));
            if ($customerName === '') {
                $customerName = 'Pelanggan Langsung (Walk-In)';
            }

            $totalPrinted = 0;
            $totalQty = 0;
            foreach ($itemsInput as $pkgId => $qty) {
                $package = $packages->get($pkgId);
                $totalPrinted += $package->printed_price * $qty;
                $totalQty += $qty;
            }
            $totalOwner = $totalPrinted;
            $totalProfit = 0;

            $proofPath = null;
            if ($request->hasFile('payment_proof_file')) {
                $proofPath = $request->file('payment_proof_file')->store('proofs', 'public');
            }

            $paymentMethod = $request->input('payment_method') ?: 'cash';
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            $firstPackageId = count($itemsInput) === 1 ? array_key_first($itemsInput) : null;
            $firstPackage = $firstPackageId ? $packages->get($firstPackageId) : null;

            $order = \Illuminate\Support\Facades\DB::transaction(function () use ($tenant, $customerName, $orderNumber, $firstPackageId, $firstPackage, $totalQty, $totalPrinted, $totalOwner, $totalProfit, $paymentMethod, $proofPath, $itemsInput, $packages) {
                $order = Order::create([
                    'order_number' => $orderNumber,
                    'tenant_id' => $tenant->id,
                    'created_by' => Auth::id(),
                    'customer_type' => 'direct',
                    'customer_name' => $customerName,
                    'package_id' => $firstPackageId,
                    'quantity' => $totalQty,
                    'printed_price_each' => $firstPackage ? $firstPackage->printed_price : ($totalQty > 0 ? $totalPrinted / $totalQty : 0),
                    'owner_price_each' => $firstPackage ? $firstPackage->printed_price : ($totalQty > 0 ? $totalOwner / $totalQty : 0),
                    'total_printed_amount' => $totalPrinted,
                    'total_owner_amount' => $totalOwner,
                    'total_reseller_profit' => $totalProfit,
                    'payment_method' => $paymentMethod,
                    'payment_proof_file' => $proofPath,
                    'status' => 'lunas',
                    'confirmed_by' => Auth::id(),
                    'confirmed_at' => now(),
                ]);

                foreach ($itemsInput as $pkgId => $qty) {
                    $pkg = $packages->get($pkgId);
                    $subPrinted = $pkg->printed_price * $qty;

                    \App\Models\OrderItem::create([
                        'order_id' => $order->id,
                        'package_id' => $pkg->id,
                        'quantity' => $qty,
                        'printed_price_each' => $pkg->printed_price,
                        'owner_price_each' => $pkg->printed_price,
                        'total_printed_amount' => $subPrinted,
                        'total_owner_amount' => $subPrinted,
                        'total_reseller_profit' => 0,
                    ]);

                    $pkg->deductStock($qty, Auth::id(), "Penjualan eceran walk-in #{$orderNumber} (Lunas) untuk {$customerName}");
                }

                return $order;
            });

            return redirect()->route('admin.orders.index')
                ->with('success', "Penjualan eceran untuk {$customerName} ({$totalQty} lembar - Rp " . number_format($totalOwner, 0, ',', '.') . ") berhasil dicatat & DIBAYAR LUNAS.");
        }

        // ALUR 2: MITRA WARUNG / RESELLER (GROSIR / KONSINYASI)
        $request->validate([
            'tenant_id' => ['required', 'exists:users,id'],
            'payment_status' => ['nullable', 'in:tempo,lunas'],
            'payment_method' => ['nullable', 'in:cash,transfer'],
            'payment_proof_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'mark_as_paid' => ['nullable', 'boolean'],
        ]);

        $tenant = User::findOrFail($request->tenant_id);

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

        // Tentukan apakah langsung lunas atau ambil dulu (tempo/konsinyasi)
        $isPaid = false;
        if ($request->filled('payment_status')) {
            $isPaid = ($request->payment_status === 'lunas');
        } elseif ($request->has('mark_as_paid')) {
            $isPaid = $request->boolean('mark_as_paid');
        }

        $proofPath = null;
        if ($isPaid && $request->hasFile('payment_proof_file')) {
            $proofPath = $request->file('payment_proof_file')->store('proofs', 'public');
        }

        $paymentMethod = $isPaid ? ($request->input('payment_method') ?: 'cash') : 'cash';
        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        $firstPackageId = count($itemsInput) === 1 ? array_key_first($itemsInput) : null;
        $firstPackage = $firstPackageId ? $packages->get($firstPackageId) : null;

        $order = \Illuminate\Support\Facades\DB::transaction(function () use ($tenant, $orderNumber, $firstPackageId, $firstPackage, $totalQty, $totalPrinted, $totalOwner, $totalProfit, $paymentMethod, $proofPath, $isPaid, $itemsInput, $packages) {
            $order = Order::create([
                'order_number' => $orderNumber,
                'tenant_id' => $tenant->id,
                'created_by' => Auth::id(),
                'customer_type' => 'warung',
                'customer_name' => $tenant->shop_name ?: $tenant->name,
                'package_id' => $firstPackageId,
                'quantity' => $totalQty,
                'printed_price_each' => $firstPackage ? $firstPackage->printed_price : ($totalQty > 0 ? $totalPrinted / $totalQty : 0),
                'owner_price_each' => $firstPackage ? $firstPackage->owner_price : ($totalQty > 0 ? $totalOwner / $totalQty : 0),
                'total_printed_amount' => $totalPrinted,
                'total_owner_amount' => $totalOwner,
                'total_reseller_profit' => $totalProfit,
                'payment_method' => $paymentMethod,
                'payment_proof_file' => $proofPath,
                'status' => $isPaid ? 'lunas' : 'menunggu_pembayaran',
                'confirmed_by' => $isPaid ? Auth::id() : null,
                'confirmed_at' => $isPaid ? now() : null,
            ]);

            $statusDesc = $isPaid ? 'Langsung Lunas' : 'Ambil Dulu / Tempo';

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

                $pkg->deductStock($qty, Auth::id(), "Penjualan offline walk-in #{$orderNumber} ({$statusDesc}) untuk {$tenant->name}");
            }

            return $order;
        });

        $successMessage = $isPaid
            ? "Pesanan offline untuk {$tenant->name} ({$totalQty} lembar) berhasil dicatat & DIBAYAR LUNAS."
            : "Pesanan offline untuk {$tenant->name} ({$totalQty} lembar) berhasil dicatat sebagai AMBIL DULU (KONSINYASI). Stok fisik diserahkan, menunggu setoran.";

        return redirect()->route('admin.orders.index')
            ->with('success', $successMessage);
    }

    public function confirmPayment(Request $request, Order $order)
    {
        if ($order->status === 'lunas') {
            return back()->with('info', 'Pesanan ini sudah berstatus lunas.');
        }

        $request->validate([
            'payment_method' => ['required', 'in:cash,transfer'],
            'payment_proof_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        if ($request->payment_method === 'transfer' && ! $request->hasFile('payment_proof_file') && ! $order->payment_proof_file) {
            return back()->withErrors(['payment_proof_file' => 'Untuk metode transfer, harap unggah foto/screenshot bukti pembayaran.']);
        }

        if ($request->hasFile('payment_proof_file')) {
            $path = $request->file('payment_proof_file')->store('proofs', 'public');
            $order->payment_proof_file = $path;
        }

        $order->payment_method = $request->payment_method;
        $order->status = 'lunas';
        $order->confirmed_by = Auth::id();
        $order->confirmed_at = now();
        $order->save();

        return back()->with('success', "Pembayaran untuk pesanan {$order->order_number} berhasil dikonfirmasi!");
    }

    public function cancelUnpaid(Request $request, Order $order)
    {
        if ($order->status !== 'menunggu_pembayaran') {
            return back()->withErrors(['message' => 'Hanya pesanan yang belum lunas yang dapat dibatalkan langsung. Untuk pesanan lunas, silakan gunakan fitur Ajukan Hapus.']);
        }

        $reason = $request->input('cancel_reason') ?: 'Salah input admin saat pencatatan pesanan';

        $order->status = 'dibatalkan';
        $order->delete_requested_by = Auth::id();
        $order->delete_reason = $reason;
        $order->delete_requested_at = now();
        $order->delete_approved_by = Auth::id();
        $order->delete_approved_at = now();
        $order->save();

        foreach ($order->effective_items as $item) {
            if ($item->package) {
                $item->package->restoreStock(
                    $item->quantity,
                    Auth::id(),
                    "Pengembalian stok pembatalan instan order #{$order->order_number}: {$reason}"
                );
            }
        }

        return back()->with('success', "Pesanan {$order->order_number} berhasil DIBATALKAN seketika dan {$order->quantity} lembar stok voucher telah dikembalikan ke inventaris.");
    }

    public function requestDelete(Request $request, Order $order)
    {
        $request->validate([
            'delete_reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'delete_reason.required' => 'Wajib mengisi alasan pengajuan pembatalan / hapus bayar.',
            'delete_reason.min' => 'Alasan minimal 5 karakter.',
        ]);

        $order->status = 'menunggu_approval_hapus';
        $order->delete_requested_by = Auth::id();
        $order->delete_reason = $request->delete_reason;
        $order->delete_requested_at = now();
        $order->save();

        return back()->with('success', "Pengajuan hapus bayar untuk {$order->order_number} telah dikirim ke Super Admin!");
    }
}
