<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'items'); // 'items' or 'movements'
        $selectedCategory = $request->input('category');
        $selectedStockStatus = $request->input('stock_status'); // 'all', 'low', 'empty', 'available'
        $search = $request->input('search');

        // Movements filters
        $selectedItem = $request->input('item_id');
        $selectedType = $request->input('type'); // 'all', 'in', 'out', 'adjustment'
        $selectedMonth = $request->input('month', date('m'));
        $selectedYear = $request->input('year', date('Y'));

        // 1. Metric Overview Stats
        $totalItemsCount = InventoryItem::where('is_active', true)->count();
        $totalPhysicalStock = InventoryItem::where('is_active', true)->sum('stock');
        $lowStockCount = InventoryItem::where('is_active', true)->whereColumn('stock', '<=', 'min_stock')->count();

        $currentMonth = date('m');
        $currentYear = date('Y');
        $monthlyIn = InventoryMovement::where('type', 'in')
            ->whereMonth('movement_date', $currentMonth)
            ->whereYear('movement_date', $currentYear)
            ->sum('quantity');

        $monthlyOut = InventoryMovement::where('type', 'out')
            ->whereMonth('movement_date', $currentMonth)
            ->whereYear('movement_date', $currentYear)
            ->sum('quantity');

        // 2. Query Daftar Barang
        $itemsQuery = InventoryItem::withCount(['movements'])->orderBy('category')->orderBy('name');

        if ($selectedCategory) {
            $itemsQuery->where('category', $selectedCategory);
        }

        if ($search) {
            $itemsQuery->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($selectedStockStatus === 'low') {
            $itemsQuery->whereColumn('stock', '<=', 'min_stock');
        } elseif ($selectedStockStatus === 'empty') {
            $itemsQuery->where('stock', 0);
        } elseif ($selectedStockStatus === 'available') {
            $itemsQuery->where('stock', '>', 0);
        }

        $items = $itemsQuery->get();

        // 3. Query Riwayat Mutasi (Log Keluar Masuk)
        $movementsQuery = InventoryMovement::with(['item', 'creator', 'expense'])
            ->latest('movement_date')
            ->latest('id');

        if ($selectedItem) {
            $movementsQuery->where('inventory_item_id', $selectedItem);
        }

        if ($selectedType && $selectedType !== 'all') {
            $movementsQuery->where('type', $selectedType);
        }

        if ($selectedMonth && $selectedMonth !== 'all') {
            $movementsQuery->whereMonth('movement_date', $selectedMonth);
        }

        if ($selectedYear && $selectedYear !== 'all') {
            $movementsQuery->whereYear('movement_date', $selectedYear);
        }

        if ($search && $tab === 'movements') {
            $movementsQuery->where(function ($sub) use ($search) {
                $sub->where('purpose', 'like', "%{$search}%")
                    ->orWhere('recipient', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('item', fn($iq) => $iq->where('name', 'like', "%{$search}%")->orWhere('item_code', 'like', "%{$search}%"));
            });
        }

        $movements = $movementsQuery->paginate(15)->withQueryString();

        // 4. Reference Data
        $allItems = InventoryItem::where('is_active', true)->orderBy('name')->get();

        $defaultCategories = [
            'Router & Gateway',
            'Access Point / WiFi',
            'Switch & Hub',
            'Kabel & Fiber Optic',
            'Konverter & SFP',
            'Power & Adaptor',
            'Alat & Tang Crimping',
            'Aksesoris & Konektor',
            'Lain-lain',
        ];
        $existingCategories = InventoryItem::distinct()->pluck('category')->filter()->toArray();
        $categories = array_values(array_unique(array_merge($defaultCategories, $existingCategories)));
        $units = ['unit', 'pcs', 'meter', 'roll', 'set', 'pack', 'box'];

        return view('superadmin.inventory.index', compact(
            'tab',
            'items',
            'movements',
            'allItems',
            'categories',
            'units',
            'totalItemsCount',
            'totalPhysicalStock',
            'lowStockCount',
            'monthlyIn',
            'monthlyOut',
            'selectedCategory',
            'selectedStockStatus',
            'selectedItem',
            'selectedType',
            'selectedMonth',
            'selectedYear',
            'search'
        ));
    }

    public function storeItem(Request $request)
    {
        if ($request->filled('unit_price')) {
            $request->merge([
                'unit_price' => str_replace(['.', ','], '', (string) $request->input('unit_price')),
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:30'],
            'initial_stock' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'min_stock' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'location' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:20480'],
        ], [
            'photo.image' => 'File yang diunggah harus berupa gambar.',
            'photo.mimes' => 'Format foto harus berupa JPG, PNG, atau WebP.',
            'photo.max' => 'Ukuran foto maksimal 20MB.',
            'photo.uploaded' => 'Foto gagal diunggah karena ukuran melebihi batas sistem. Sistem sekarang sudah mendukung otomatisasi kompresi foto kamera.',
        ]);

        $itemCode = InventoryItem::generateCode($validated['category']);
        $initialStock = (int) ($validated['initial_stock'] ?? 0);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('inventory_photos', 'public');
        }

        DB::transaction(function () use ($validated, $itemCode, $initialStock, $photoPath, &$item) {
            $item = InventoryItem::create([
                'item_code' => $itemCode,
                'name' => $validated['name'],
                'category' => $validated['category'],
                'unit' => $validated['unit'],
                'stock' => 0,
                'min_stock' => (int) ($validated['min_stock'] ?? 2),
                'unit_price' => !empty($validated['unit_price']) ? (float) $validated['unit_price'] : null,
                'location' => $validated['location'] ?? null,
                'description' => $validated['description'] ?? null,
                'photo' => $photoPath,
                'is_active' => true,
            ]);

            if ($initialStock > 0) {
                $item->addStock(
                    $initialStock,
                    'Stok Awal Pendaftaran Barang',
                    $item->location ?? 'Gudang Utama',
                    null,
                    'Input saldo awal inventaris baru',
                    Auth::id()
                );
            }
        });

        return redirect()->route('inventory.index', ['tab' => 'items'])
            ->with('success', "Barang '{$item->name}' ({$item->item_code}) berhasil didaftarkan ke inventaris!");
    }

    public function updateItem(Request $request, InventoryItem $item)
    {
        if ($request->filled('unit_price')) {
            $request->merge([
                'unit_price' => str_replace(['.', ','], '', (string) $request->input('unit_price')),
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:30'],
            'min_stock' => ['required', 'integer', 'min:0', 'max:10000'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'location' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:20480'],
            'remove_photo' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'photo.image' => 'File yang diunggah harus berupa gambar.',
            'photo.mimes' => 'Format foto harus berupa JPG, PNG, atau WebP.',
            'photo.max' => 'Ukuran foto maksimal 20MB.',
            'photo.uploaded' => 'Foto gagal diunggah karena ukuran melebihi batas sistem.',
        ]);

        if ($request->boolean('remove_photo') && $item->photo) {
            Storage::disk('public')->delete($item->photo);
            $item->photo = null;
        }

        if ($request->hasFile('photo')) {
            if ($item->photo) {
                Storage::disk('public')->delete($item->photo);
            }
            $item->photo = $request->file('photo')->store('inventory_photos', 'public');
        }

        $item->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'unit' => $validated['unit'],
            'min_stock' => (int) $validated['min_stock'],
            'unit_price' => !empty($validated['unit_price']) ? (float) $validated['unit_price'] : null,
            'location' => $validated['location'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : true,
        ]);

        return redirect()->route('inventory.index', ['tab' => 'items'])
            ->with('success', "Data barang '{$item->name}' berhasil diperbarui.");
    }

    public function destroyItem(InventoryItem $item)
    {
        if ($item->expenses()->exists() || $item->movements()->count() > 1) {
            return back()->withErrors([
                'message' => "Barang '{$item->name}' tidak dapat dihapus karena sudah memiliki riwayat mutasi / terhubung ke catatan pengeluaran kas. Anda dapat menonaktifkannya melalui tombol Edit.",
            ]);
        }

        if ($item->photo) {
            Storage::disk('public')->delete($item->photo);
        }

        $name = $item->name;
        $item->delete();

        return redirect()->route('inventory.index', ['tab' => 'items'])
            ->with('success', "Barang '{$name}' berhasil dihapus dari inventaris.");
    }

    /**
     * Catat Barang Masuk (Restock / Retur / Pengadaan langsung)
     */
    public function storeStockIn(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'movement_date' => ['required', 'date'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'supplier_or_source' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $item = InventoryItem::findOrFail($validated['inventory_item_id']);

        $purpose = !empty($validated['purpose']) ? $validated['purpose'] : 'Pemasukan / Restock Barang';
        $source = !empty($validated['supplier_or_source']) ? $validated['supplier_or_source'] : 'Gudang Posko';
        $notes = $validated['notes'] ?? null;

        $item->addStock(
            (int) $validated['quantity'],
            $purpose,
            $source,
            null,
            $notes,
            Auth::id(),
            $validated['movement_date']
        );

        return redirect()->route('inventory.index', ['tab' => 'movements', 'item_id' => $item->id])
            ->with('success', "Berhasil menambahkan {$validated['quantity']} {$item->unit} stok untuk '{$item->name}'.");
    }

    /**
     * Catat Barang Keluar (Pemasangan di Mitra / Warga / Penggantian)
     */
    public function storeStockOut(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'movement_date' => ['required', 'date'],
            'purpose' => ['required', 'string', 'max:255'],
            'recipient' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'purpose.required' => 'Keperluan / Tujuan pengeluaran barang wajib diisi (misal: Pemasangan di Mitra Warung Bu Siti).',
        ]);

        $item = InventoryItem::findOrFail($validated['inventory_item_id']);

        if ($item->stock < (int) $validated['quantity']) {
            return back()->withErrors([
                'message' => "Stok '{$item->name}' tidak mencukupi! Sisa stok saat ini hanya {$item->stock} {$item->unit}, sedangkan permintaan keluar sejumlah {$validated['quantity']} {$item->unit}.",
            ])->withInput();
        }

        $recipient = !empty($validated['recipient']) ? $validated['recipient'] : 'Mitra / Operasional';
        $notes = $validated['notes'] ?? null;

        $item->deductStock(
            (int) $validated['quantity'],
            $validated['purpose'],
            $recipient,
            $notes,
            Auth::id(),
            $validated['movement_date']
        );

        return redirect()->route('inventory.index', ['tab' => 'movements', 'item_id' => $item->id])
            ->with('success', "Berhasil mencatat pengeluaran {$validated['quantity']} {$item->unit} '{$item->name}' untuk: {$validated['purpose']}.");
    }

    /**
     * Hapus / Batalkan Catatan Mutasi (Khusus Super Admin)
     */
    public function destroyMovement(InventoryMovement $movement)
    {
        $item = $movement->item;

        if ($movement->type === 'in') {
            if ($item->stock < $movement->quantity) {
                return back()->withErrors([
                    'message' => "Tidak dapat membatalkan mutasi masuk ini karena sisa stok ({$item->stock}) lebih kecil dari jumlah yang ingin dikurangkan ({$movement->quantity}).",
                ]);
            }
            $item->decrement('stock', $movement->quantity);
        } elseif ($movement->type === 'out') {
            $item->increment('stock', $movement->quantity);
        }

        $movement->delete();

        return back()->with('success', 'Catatan mutasi berhasil dibatalkan dan stok dikembalikan.');
    }
}
