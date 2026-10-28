<?php

namespace App\Http\Controllers;

use App\Models\VoucherPackage;
use App\Models\VoucherStockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoucherPackageController extends Controller
{
    public function index(Request $request)
    {
        $selectedPackage = $request->input('package_id');
        $selectedType = $request->input('type'); // 'all', 'restock', 'order'
        $selectedMonth = $request->input('month');
        $selectedYear = $request->input('year');
        $search = $request->input('search');

        $packages = VoucherPackage::withCount(['orderItems as order_items_count', 'orders as legacy_orders_count'])
            ->orderBy('printed_price')
            ->get()
            ->each(function ($p) {
                $p->orders_count = $p->order_items_count > 0 ? $p->order_items_count : $p->legacy_orders_count;
            });
        $currentPackage = $selectedPackage ? $packages->firstWhere('id', $selectedPackage) : null;

        $totalStock = $packages->sum('stock');
        $totalPrintedValue = $packages->sum(fn($p) => $p->stock * $p->printed_price);
        $totalOwnerValue = $packages->sum(fn($p) => $p->stock * $p->owner_price);
        $lowStockCount = $packages->filter(fn($p) => $p->stock <= 20)->count();
        $activePackagesCount = $packages->where('is_active', true)->count();
        $inactivePackagesCount = $packages->where('is_active', false)->count();
        $selectedTab = $request->input('tab', 'active');

        // Query untuk Riwayat & Analisis Arus Mutasi Stok
        $logsQuery = VoucherStockLog::with(['package', 'user'])->latest();

        if ($selectedPackage) {
            $logsQuery->where('voucher_package_id', $selectedPackage);
        }

        if ($selectedType === 'restock') {
            $logsQuery->where('quantity', '>', 0);
        } elseif ($selectedType === 'order') {
            $logsQuery->where('quantity', '<', 0);
        } elseif ($selectedType && $selectedType !== 'all') {
            $logsQuery->where('type', $selectedType);
        }

        if ($selectedYear && $selectedYear !== 'all') {
            $logsQuery->whereYear('created_at', $selectedYear);
        }
        if ($selectedMonth && $selectedMonth !== 'all') {
            $logsQuery->whereMonth('created_at', $selectedMonth);
        }

        if ($search) {
            $logsQuery->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('package', fn($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        // Metrik Analisis Arus Mutasi Stok Berdasarkan Filter Terpilih
        $totalAddedSheets = (int) (clone $logsQuery)->where('quantity', '>', 0)->sum('quantity');
        $totalDeductedSheets = abs((int) (clone $logsQuery)->where('quantity', '<', 0)->sum('quantity'));
        $netStockChange = $totalAddedSheets - $totalDeductedSheets;
        $totalLogsCount = (clone $logsQuery)->count();

        $stockLogs = $logsQuery->paginate(20)->withQueryString();

        $firstLog = VoucherStockLog::oldest('created_at')->first();
        $firstLogYear = $firstLog ? (int)$firstLog->created_at->format('Y') : (int)date('Y');
        $currentYear = (int) date('Y');
        $availableYears = range(max($currentYear, $firstLogYear), min($currentYear, $firstLogYear));

        return view('superadmin.packages.index', compact(
            'packages',
            'currentPackage',
            'stockLogs',
            'totalStock',
            'totalPrintedValue',
            'totalOwnerValue',
            'lowStockCount',
            'activePackagesCount',
            'inactivePackagesCount',
            'selectedTab',
            'selectedPackage',
            'selectedType',
            'selectedMonth',
            'selectedYear',
            'search',
            'totalAddedSheets',
            'totalDeductedSheets',
            'netStockChange',
            'totalLogsCount',
            'availableYears'
        ));
    }

    public function create()
    {
        return view('superadmin.packages.form', [
            'package' => new VoucherPackage(),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        if ($request->filled('printed_price')) {
            $request->merge([
                'printed_price' => str_replace(['.', ','], '', (string) $request->input('printed_price')),
            ]);
        }
        if ($request->filled('owner_price')) {
            $request->merge([
                'owner_price' => str_replace(['.', ','], '', (string) $request->input('owner_price')),
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'duration_info' => ['required', 'string', 'max:100'],
            'printed_price' => ['required', 'numeric', 'min:0'],
            'owner_price' => ['required', 'numeric', 'min:0', 'lte:printed_price'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'owner_price.lte' => 'Harga Owner (setor) tidak boleh lebih besar dari Harga Tertera di voucher.',
        ]);

        $initialStock = (int) ($validated['stock'] ?? 0);
        unset($validated['stock']);

        $validated['reseller_profit'] = $validated['printed_price'] - $validated['owner_price'];
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['stock'] = 0;

        $package = VoucherPackage::create($validated);

        if ($initialStock > 0) {
            $package->addStock($initialStock, Auth::id(), 'Stok awal saat penambahan paket baru');
        }

        return redirect()->route('packages.index')->with('success', 'Paket voucher baru berhasil ditambahkan!');
    }

    public function edit(VoucherPackage $package)
    {
        return view('superadmin.packages.form', [
            'package' => $package,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, VoucherPackage $package)
    {
        if ($request->filled('printed_price')) {
            $request->merge([
                'printed_price' => str_replace(['.', ','], '', (string) $request->input('printed_price')),
            ]);
        }
        if ($request->filled('owner_price')) {
            $request->merge([
                'owner_price' => str_replace(['.', ','], '', (string) $request->input('owner_price')),
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'duration_info' => ['required', 'string', 'max:100'],
            'printed_price' => ['required', 'numeric', 'min:0'],
            'owner_price' => ['required', 'numeric', 'min:0', 'lte:printed_price'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'owner_price.lte' => 'Harga Owner (setor) tidak boleh lebih besar dari Harga Tertera di voucher.',
        ]);

        $validated['reseller_profit'] = $validated['printed_price'] - $validated['owner_price'];
        $validated['is_active'] = $request->boolean('is_active', true);

        $package->update($validated);

        return redirect()->route('packages.index')->with('success', 'Paket voucher berhasil diperbarui!');
    }

    public function addStock(Request $request, VoucherPackage $package)
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'quantity.required' => 'Masukkan jumlah lembar voucher yang ditambahkan.',
            'quantity.min' => 'Jumlah penambahan minimal 1 lembar.',
        ]);

        $package->addStock((int) $request->quantity, Auth::id(), $request->notes);

        return redirect()->route('packages.index')->with('success', "Berhasil menambahkan {$request->quantity} lembar stok voucher untuk '{$package->name}'.");
    }

    public function toggle(VoucherPackage $package)
    {
        $package->is_active = ! $package->is_active;
        $package->save();

        $status = $package->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $targetTab = $package->is_active ? 'active' : 'inactive';
        return redirect()->route('packages.index', ['tab' => $targetTab])
            ->with('success', "Paket voucher '{$package->name}' berhasil {$status}.");
    }

    public function destroy(VoucherPackage $package)
    {
        if ($package->orderItems()->exists() || $package->orders()->exists()) {
            return back()->withErrors(['message' => "Paket '{$package->name}' tidak dapat dihapus karena sudah memiliki riwayat transaksi penjualan. Silakan nonaktifkan paket ini agar tidak muncul lagi pada form pemesanan."]);
        }

        try {
            $package->delete();
            return redirect()->route('packages.index')->with('success', "Paket voucher '{$package->name}' berhasil dihapus.");
        } catch (\Exception $e) {
            return back()->withErrors(['message' => "Gagal menghapus paket '{$package->name}' karena data masih terhubung dengan data lain. Silakan nonaktifkan paket ini saja."]);
        }
    }
}
