@extends('layouts.app')

@section('title', 'Inventaris & Logistik Barang')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-blue-600"></span>
                <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900">Inventaris & Logistik Barang</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Kelola stok fisik perangkat (router, AP, switch, kabel FO) dengan kartu visual dan pantau mutasi pemasangan mitra.
            </p>
        </div>

        <!-- Header Action Button -->
        <div>
            <button type="button" onclick="openCreateItemModal()" class="w-full sm:w-auto py-2.5 px-4 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm shadow-blue-500/20 transition-all flex items-center justify-center gap-1.5 active:scale-95 touch-target">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Tambah Master Barang</span>
            </button>
        </div>
    </div>

    <!-- Error Validation Display -->
    @if($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50/90 border border-rose-200/80 text-rose-800 text-xs font-semibold space-y-1 shadow-2xs">
        <div class="font-bold flex items-center gap-1.5 text-rose-900">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Terdapat kendala pada proses data:</span>
        </div>
        <ul class="list-disc list-inside space-y-0.5 text-rose-700">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Metric Overview Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- 1. Total Jenis Barang -->
        <div class="bg-white p-4 sm:p-5 rounded-[24px] border border-slate-200/70 shadow-[0_4px_20px_rgb(0,0,0,0.02)] space-y-2">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider">Total Item Barang</span>
                <span class="text-base p-1.5 rounded-xl bg-slate-100 text-slate-700">📦</span>
            </div>
            <div class="text-xl sm:text-2xl font-black font-mono text-slate-900">
                {{ number_format($totalItemsCount) }} <span class="text-xs font-sans font-normal text-slate-400">Jenis</span>
            </div>
            <span class="text-[10px] text-slate-400 font-medium block">Katalog aset terdaftar</span>
        </div>

        <!-- 2. Total Stok Unit Fisik -->
        <div class="bg-white p-4 sm:p-5 rounded-[24px] border border-slate-200/70 shadow-[0_4px_20px_rgb(0,0,0,0.02)] space-y-2">
            <div class="flex items-center justify-between text-blue-600">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Stok Fisik</span>
                <span class="text-base p-1.5 rounded-xl bg-blue-50 text-blue-600">🔢</span>
            </div>
            <div class="text-xl sm:text-2xl font-black font-mono text-blue-900">
                {{ number_format($totalPhysicalStock) }} <span class="text-xs font-sans font-normal text-slate-400">Unit/Pcs</span>
            </div>
            <span class="text-[10px] text-blue-600 font-medium block">Siap pakai di gudang</span>
        </div>

        <!-- 3. Stok Menipis / Kritis -->
        <a href="{{ route('inventory.index', ['tab' => 'items', 'stock_status' => 'low']) }}"
            class="p-4 sm:p-5 rounded-[24px] border transition-all space-y-2 {{ $lowStockCount > 0 ? 'bg-amber-50/70 border-amber-200 shadow-sm hover:bg-amber-100/70' : 'bg-white border-slate-200/70 shadow-[0_4px_20px_rgb(0,0,0,0.02)]' }}">
            <div class="flex items-center justify-between">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider {{ $lowStockCount > 0 ? 'text-amber-800' : 'text-slate-500' }}">Stok Menipis</span>
                <span class="text-base p-1.5 rounded-xl {{ $lowStockCount > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }}">⚠️</span>
            </div>
            <div class="text-xl sm:text-2xl font-black font-mono {{ $lowStockCount > 0 ? 'text-amber-900' : 'text-slate-900' }}">
                {{ number_format($lowStockCount) }} <span class="text-xs font-sans font-normal text-slate-400">Item</span>
            </div>
            <span class="text-[10px] {{ $lowStockCount > 0 ? 'text-amber-700 font-bold' : 'text-slate-400 font-medium' }} block truncate">
                {{ $lowStockCount > 0 ? 'Perlu segera restock!' : 'Stok dalam batas aman' }}
            </span>
        </a>

        <!-- 4. Mutasi Bulan Ini -->
        <div class="bg-white p-4 sm:p-5 rounded-[24px] border border-slate-200/70 shadow-[0_4px_20px_rgb(0,0,0,0.02)] space-y-2">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider">Mutasi Bulan Ini</span>
                <span class="text-base p-1.5 rounded-xl bg-purple-50 text-purple-600">📊</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-xs font-bold text-emerald-700 flex items-center gap-1">
                    <span>⬇️</span>
                    <span class="font-mono text-base font-black">+{{ number_format($monthlyIn) }}</span>
                </div>
                <div class="text-xs font-bold text-rose-700 flex items-center gap-1">
                    <span>⬆️</span>
                    <span class="font-mono text-base font-black">-{{ number_format($monthlyOut) }}</span>
                </div>
            </div>
            <span class="text-[10px] text-slate-400 font-medium block truncate">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</span>
        </div>
    </div>

    <!-- Navigation Tabs (Daftar Barang vs Log Mutasi) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-200/80">
        <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-2xl border border-slate-200/80 text-xs shrink-0 self-start w-full sm:w-auto">
            <!-- Tab 1: Daftar Barang -->
            <a href="{{ route('inventory.index', ['tab' => 'items']) }}"
                class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2 rounded-xl transition-all {{ $tab === 'items' ? 'font-bold shadow-xs bg-white text-blue-700' : 'font-medium text-slate-600 hover:text-slate-900' }}">
                <span>📦</span>
                <span>Katalog Barang & Stok</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $tab === 'items' ? 'bg-blue-50 text-blue-700 border border-blue-200/60' : 'bg-slate-200 text-slate-700' }}">
                    {{ $items->count() }}
                </span>
            </a>

            <!-- Tab 2: Log Mutasi -->
            <a href="{{ route('inventory.index', ['tab' => 'movements']) }}"
                class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2 rounded-xl transition-all {{ $tab === 'movements' ? 'font-bold shadow-xs bg-white text-blue-700' : 'font-medium text-slate-600 hover:text-slate-900' }}">
                <span>🔄</span>
                <span>Log Keluar - Masuk</span>
                @if($tab === 'movements')
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200/60">
                    {{ $movements->total() }}
                </span>
                @endif
            </a>
        </div>

        <div class="text-[11px] text-slate-400 font-medium hidden sm:block">
            @if($tab === 'items')
            <span>Tampilan kartu visual kotak-kotak dengan foto dan stok fisik real-time.</span>
            @else
            <span>Audit lengkap pengeluaran (pemasangan mitra) & pemasukan (restock) barang.</span>
            @endif
        </div>
    </div>

    @if($tab === 'items')
    <!-- ==================== TAB 1: DAFTAR BARANG (CARD GRID KOTAK-KOTAK SEPERTI VOUCHER) ==================== -->
    <div class="space-y-5">
        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-[0_2px_12px_rgb(0,0,0,0.02)]">
            <form method="GET" action="{{ route('inventory.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                <input type="hidden" name="tab" value="items">
                
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Barang / Kode / Lokasi</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama atau kode barang..."
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Kategori</label>
                    <select name="category" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        <option value="">-- Semua Kategori --</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ $selectedCategory === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status Stok</label>
                    <select name="stock_status" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        <option value="">Semua Status Stok</option>
                        <option value="low" {{ $selectedStockStatus === 'low' ? 'selected' : '' }}>⚠️ Menipis / Kritis (≤ Min)</option>
                        <option value="available" {{ $selectedStockStatus === 'available' ? 'selected' : '' }}>Tersedia (> 0)</option>
                        <option value="empty" {{ $selectedStockStatus === 'empty' ? 'selected' : '' }}>Stok Habis (0)</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2.5 px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs touch-target">
                        Terapkan
                    </button>
                    <a href="{{ route('inventory.index', ['tab' => 'items']) }}" class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 rounded-xl text-xs font-bold transition-all border border-slate-200/60 touch-target">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Cards Grid (Kotak-Kotak Seperti Voucher, Responsive 1 Kolom Mobile, 2 Tablet, 3 Desktop) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6" id="inventoryGrid">
            @forelse($items as $item)
            @php
                $theme = $item->category_theme;
                $isLow = $item->isLowStock();
            @endphp
            <div class="bg-white rounded-[28px] border {{ $theme['card_border'] }} shadow-[0_4px_25px_rgb(0,0,0,0.04)] hover:shadow-lg flex flex-col justify-between overflow-hidden transition-all duration-200 group relative {{ !$item->is_active ? 'opacity-85' : '' }}">
                
                <!-- Top Accent Gradient Bar -->
                <div class="h-2 w-full bg-gradient-to-r {{ $theme['gradient'] }}"></div>

                <!-- Bagian Foto Barang / Ilustrasi Visual -->
                <div class="relative w-full h-44 bg-slate-100 overflow-hidden border-b border-slate-100">
                    @if($item->photo)
                    <img src="{{ $item->photo_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                    <!-- Fallback Ilustrasi Kategori Jika Belum Ada Foto -->
                    <div class="w-full h-full flex flex-col items-center justify-center p-4 bg-gradient-to-br from-slate-50 to-slate-100/80 group-hover:bg-slate-100/70 transition-colors">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl {{ $theme['bg'] }} shadow-xs mb-1 group-hover:scale-110 transition-transform">
                            {{ $theme['icon'] }}
                        </div>
                        <button type="button" onclick="openEditItemModal({{ json_encode($item) }})" class="text-[10px] font-bold text-blue-600 hover:text-blue-800 mt-1 flex items-center gap-1 opacity-80 group-hover:opacity-100 transition-opacity">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Unggah Foto Barang</span>
                        </button>
                    </div>
                    @endif

                    <!-- Floating Badges on Top of Photo -->
                    <div class="absolute top-3 left-3 flex items-center gap-1.5 flex-wrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider {{ $theme['badge'] }} shadow-xs bg-white/95 backdrop-blur-xs">
                            {{ $item->category }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-mono font-bold bg-slate-900/80 text-white backdrop-blur-xs shadow-2xs">
                            {{ $item->item_code }}
                        </span>
                    </div>

                    <div class="absolute top-3 right-3">
                        @if(!$item->is_active)
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-slate-900/80 text-white backdrop-blur-xs shadow-2xs">
                            Nonaktif
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Card Content Body -->
                <div class="p-5 sm:p-6 space-y-4 flex-1 flex flex-col justify-between">
                    
                    <!-- Title & Details -->
                    <div class="space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-base font-extrabold text-slate-900 tracking-tight leading-tight group-hover:text-blue-600 transition-colors">
                                {{ $item->name }}
                            </h3>
                        </div>

                        <!-- Location & Notes -->
                        <div class="flex items-center gap-2 text-xs text-slate-500 flex-wrap">
                            <span class="inline-flex items-center gap-1">
                                <span>📍</span>
                                <span class="font-semibold text-slate-700">{{ $item->location ?: 'Gudang' }}</span>
                            </span>
                            @if($item->min_stock > 0)
                            <span class="text-slate-300">•</span>
                            <span class="text-[11px] text-slate-400">Min: <strong class="text-slate-600 font-mono">{{ $item->min_stock }} {{ $item->unit }}</strong></span>
                            @endif
                        </div>

                        @if($item->description)
                        <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                            {{ $item->description }}
                        </p>
                        @endif
                    </div>

                    <!-- Price & Estimation (If Present) -->
                    @if($item->unit_price)
                    <div class="p-3 rounded-2xl bg-slate-50/90 border border-slate-200/70 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Taksiran Nilai</span>
                            <span class="text-xs text-slate-600 font-medium">Harga Per {{ ucfirst($item->unit) }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-base sm:text-lg font-mono font-black text-slate-900 block leading-tight">
                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    @endif

                    <!-- Real-time Physical Stock Box (Mirip Kartu Voucher) -->
                    <div class="p-4 rounded-2xl {{ $theme['stock_box'] }} space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-extrabold text-[11px] uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                Stok Fisik Tersedia
                            </span>

                            @if($item->stock == 0)
                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 border border-rose-200 animate-pulse">
                                ✕ Habis
                            </span>
                            @elseif($isLow)
                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200 animate-pulse">
                                ▲ Menipis
                            </span>
                            @else
                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                                ● Aman
                            </span>
                            @endif
                        </div>

                        <div class="flex items-baseline justify-between gap-2">
                            <div class="text-2xl sm:text-3xl font-black font-mono {{ $isLow ? 'text-amber-600' : 'text-slate-900' }} tracking-tight">
                                {{ number_format($item->stock) }} <span class="text-xs font-sans text-slate-500 font-normal">{{ $item->unit }}</span>
                            </div>
                            <span class="text-[11px] text-slate-400 font-medium">Siap Digunakan</span>
                        </div>

                        <!-- Mini Stock Gauge Bar -->
                        <div class="w-full bg-slate-200/80 rounded-full h-1.5 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 {{ $theme['stock_bar'] }}"
                                style="width: {{ min(100, max(6, ($item->stock / max(1, $item->min_stock * 3)) * 100)) }}%"></div>
                        </div>

                        <!-- Tombol Aksi Transaksi Cepat (Masuk & Keluar) -->
                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <button type="button" onclick="openStockInModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->unit }}')"
                                class="py-2.5 px-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-all flex items-center justify-center gap-1 shadow-sm shadow-emerald-500/20 active:scale-95 touch-target">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Masuk</span>
                            </button>

                            <button type="button" onclick="openStockOutModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->unit }}', {{ $item->stock }})"
                                class="py-2.5 px-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition-all flex items-center justify-center gap-1 shadow-sm shadow-rose-500/20 active:scale-95 touch-target">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 12h-15"/></svg>
                                <span>Keluar</span>
                            </button>
                        </div>
                    </div>

                    <!-- Bottom Utility Buttons (Edit, Riwayat) -->
                    <div class="pt-2 flex items-center justify-between border-t border-slate-100 text-xs">
                        <button type="button" onclick="openEditItemModal({{ json_encode($item) }})" class="font-bold text-slate-600 hover:text-slate-900 flex items-center gap-1.5 py-1.5 px-2 rounded-xl hover:bg-slate-100 transition-colors touch-target">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/></svg>
                            <span>Edit Barang</span>
                        </button>

                        <a href="{{ route('inventory.index', ['tab' => 'movements', 'item_id' => $item->id]) }}" class="font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1.5 py-1.5 px-2 rounded-xl hover:bg-blue-50 transition-colors touch-target">
                            <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Log Mutasi ({{ $item->movements_count ?? 0 }})</span>
                        </a>
                    </div>

                </div>

            </div>
            @empty
            <div class="col-span-full bg-white rounded-[28px] border-2 border-dashed border-slate-200 p-12 text-center">
                <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl shadow-xs">
                    📦
                </div>
                <h4 class="text-sm font-bold text-slate-800">Tidak ada barang inventaris yang sesuai</h4>
                <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1 leading-relaxed">
                    Silakan reset filter pencarian atau klik tombol <strong>"Tambah Master Barang"</strong> untuk menambahkan aset perangkat fisik baru.
                </p>
                <div class="mt-4">
                    <button type="button" onclick="openCreateItemModal()" class="px-5 py-2.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition-all touch-target">
                        Tambah Master Barang Sekarang
                    </button>
                </div>
            </div>
            @endforelse
        </div>
    </div>

    @else
    <!-- ==================== TAB 2: RIWAYAT KELUAR - MASUK (LOG MUTASI) ==================== -->
    <div class="space-y-4">
        <!-- Filter Bar Riwayat Mutasi -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-[0_2px_12px_rgb(0,0,0,0.02)]">
            <form method="GET" action="{{ route('inventory.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
                <input type="hidden" name="tab" value="movements">

                <!-- Filter Barang -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Pilih Barang</label>
                    <select name="item_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        <option value="">Semua Barang</option>
                        @foreach($allItems as $i)
                        <option value="{{ $i->id }}" {{ $selectedItem == $i->id ? 'selected' : '' }}>
                            {{ $i->name }} ({{ $i->item_code }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Jenis Mutasi -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Jenis Mutasi</label>
                    <select name="type" onchange="this.form.submit()" class="w-full px-3 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        <option value="all">Semua Jenis</option>
                        <option value="in" {{ $selectedType === 'in' ? 'selected' : '' }}>⬇️ Barang Masuk (+)</option>
                        <option value="out" {{ $selectedType === 'out' ? 'selected' : '' }}>⬆️ Barang Keluar (-)</option>
                        <option value="adjustment" {{ $selectedType === 'adjustment' ? 'selected' : '' }}>🔄 Penyesuaian</option>
                    </select>
                </div>

                <!-- Filter Bulan -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Bulan</label>
                    <select name="month" onchange="this.form.submit()" class="w-full px-3 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        <option value="all">Semua Bulan</option>
                        @for($m = 1; $m <= 12; $m++)
                            @php $val = sprintf('%02d', $m); @endphp
                            <option value="{{ $val }}" {{ $selectedMonth == $val ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <!-- Filter Tahun -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tahun</label>
                    <select name="year" onchange="this.form.submit()" class="w-full px-3 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2.5 px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs touch-target">
                        Filter
                    </button>
                    <a href="{{ route('inventory.index', ['tab' => 'movements']) }}" class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 rounded-xl text-xs font-bold transition-all border border-slate-200/60 touch-target">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Riwayat Mutasi (Desktop) -->
        <div class="bg-white rounded-[28px] border border-slate-200/70 overflow-hidden shadow-[0_4px_20px_rgb(0,0,0,0.03)]">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-xs sm:text-sm font-extrabold text-slate-900">Log Mutasi Fisik Keluar & Masuk</h3>
                    <p class="text-[11px] text-slate-400">Total {{ number_format($movements->total()) }} catatan mutasi ditemukan.</p>
                </div>
            </div>

            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/70 text-slate-600 font-bold text-[11px] uppercase tracking-wider">
                            <th class="py-3.5 px-5">Waktu / Tanggal</th>
                            <th class="py-3.5 px-4">Barang</th>
                            <th class="py-3.5 px-4">Jenis Mutasi</th>
                            <th class="py-3.5 px-4 text-center">Jumlah</th>
                            <th class="py-3.5 px-4">Keperluan / Alasan</th>
                            <th class="py-3.5 px-4">Penerima / Lokasi</th>
                            <th class="py-3.5 px-4">Referensi Kas</th>
                            <th class="py-3.5 px-4">Petugas</th>
                            @if(auth()->user()->isSuperAdmin())
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($movements as $m)
                        @php
                            $theme = $m->type_theme;
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3.5 px-5 text-slate-500 font-medium">
                                <span class="font-bold text-slate-800 block">{{ $m->movement_date->translatedFormat('d M Y') }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $m->created_at->format('H:i') }} WIB</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2.5">
                                    @if($m->item && $m->item->photo)
                                    <img src="{{ $m->item->photo_url }}" alt="{{ $m->item->name }}" class="w-8 h-8 rounded-lg object-cover border border-slate-100 shrink-0">
                                    @endif
                                    <div>
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-bold text-slate-900 block">{{ $m->item->name ?? '-' }}</span>
                                            @if($m->item && $m->item->trashed())
                                            <span class="px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-500 text-[9px] font-semibold">Dihapus</span>
                                            @endif
                                        </div>
                                        <span class="text-[10px] font-mono text-slate-400">{{ $m->item->item_code ?? '' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold border {{ $theme['badge'] }}">
                                    {{ $theme['icon'] }} {{ $m->type_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-mono text-xs font-black {{ $theme['color'] }}">
                                    {{ $theme['sign'] }}{{ number_format($m->quantity) }} <span class="text-[10px] font-normal text-slate-500">{{ $m->item->unit ?? 'unit' }}</span>
                                </span>
                                <span class="text-[10px] text-slate-400 block font-mono">
                                    {{ $m->stock_before }} → {{ $m->stock_after }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-800 block">{{ $m->purpose ?: '-' }}</span>
                                @if($m->notes)
                                <span class="text-[11px] text-slate-400 italic block">{{ $m->notes }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                <span class="inline-flex items-center gap-1 text-[11px]">
                                    <span>📍</span>
                                    <span>{{ $m->recipient ?: '-' }}</span>
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($m->expense)
                                <a href="{{ route('finance.expenses', ['category' => $m->expense->category]) }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 font-mono text-[10px] font-bold">
                                    <span>🧾 {{ $m->expense->expense_number }}</span>
                                </a>
                                @else
                                <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1.5 text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-full border border-slate-200">
                                    <span class="w-1.5 h-1.5 rounded-full {{ ($m->creator->role ?? '') === 'super_admin' ? 'bg-purple-500' : 'bg-sky-500' }}"></span>
                                    {{ $m->creator->name ?? 'Admin' }}
                                </span>
                            </td>
                            @if(auth()->user()->isSuperAdmin())
                            <td class="py-3.5 px-4 text-right">
                                <form action="{{ route('inventory.movements.destroy', $m) }}" method="POST" onsubmit="return confirm('Batalkan catatan mutasi ini dan kembalikan stok fisik?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold text-xs transition-colors">
                                        Batal
                                    </button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-400 font-medium">Belum ada riwayat mutasi pada periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards View for Movements -->
            <div class="lg:hidden divide-y divide-slate-100">
                @forelse($movements as $m)
                @php $theme = $m->type_theme; @endphp
                <div class="p-4 space-y-2.5 hover:bg-slate-50/50 transition-colors">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2">
                            @if($m->item && $m->item->photo)
                            <img src="{{ $m->item->photo_url }}" alt="{{ $m->item->name }}" class="w-9 h-9 rounded-lg object-cover border border-slate-100 shrink-0">
                            @endif
                            <div>
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-bold text-slate-900 text-xs block">{{ $m->item->name ?? '-' }}</span>
                                    @if($m->item && $m->item->trashed())
                                    <span class="px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-500 text-[9px] font-semibold">Dihapus</span>
                                    @endif
                                </div>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $m->movement_date->translatedFormat('d M Y') }} • {{ $m->created_at->format('H:i') }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-mono text-sm font-black {{ $theme['color'] }}">
                                {{ $theme['sign'] }}{{ number_format($m->quantity) }} {{ $m->item->unit ?? 'unit' }}
                            </span>
                            <span class="text-[10px] text-slate-400 block font-mono">
                                ({{ $m->stock_before }} → {{ $m->stock_after }})
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold border {{ $theme['badge'] }}">
                            {{ $theme['icon'] }} {{ $m->type_label }}
                        </span>
                        <span class="text-xs font-semibold text-slate-800">{{ $m->purpose ?: '-' }}</span>
                    </div>

                    @if($m->recipient || $m->notes)
                    <div class="text-[11px] text-slate-500 bg-slate-50 p-2.5 rounded-xl border border-slate-200/60 space-y-0.5">
                        @if($m->recipient)
                        <div>📍 <strong>Lokasi/Penerima:</strong> {{ $m->recipient }}</div>
                        @endif
                        @if($m->notes)
                        <div>📝 <strong>Catatan:</strong> {{ $m->notes }}</div>
                        @endif
                    </div>
                    @endif

                    <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100">
                        <span class="inline-flex items-center gap-1 text-[10px] font-medium text-slate-500">
                            <span class="w-1.5 h-1.5 rounded-full {{ ($m->creator->role ?? '') === 'super_admin' ? 'bg-purple-500' : 'bg-sky-500' }}"></span>
                            {{ $m->creator->name ?? 'Admin' }}
                        </span>

                        @if(auth()->user()->isSuperAdmin())
                        <form action="{{ route('inventory.movements.destroy', $m) }}" method="POST" onsubmit="return confirm('Batalkan mutasi ini?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold text-[11px] py-1 px-2">Batalkan</button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-slate-400 text-xs">Belum ada catatan mutasi.</div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($movements->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $movements->links() }}
            </div>
            @endif
        </div>
    </div>
    @endif

</div>

<!-- ==================== MODAL 1: TAMBAH MASTER BARANG BARU (DENGAN UPLOAD FOTO) ==================== -->
<div id="createItemModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-[28px] border border-slate-200 shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-5 sm:p-7 space-y-5 animate-in fade-in zoom-in duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 sticky top-0 bg-white z-10">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">📦</span>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Tambah Master Barang Baru</h3>
                    <p class="text-[11px] text-slate-400">Daftarkan perangkat/alat baru lengkap dengan foto.</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateItemModal()" class="text-slate-400 hover:text-slate-600 p-1 font-bold text-sm">✕</button>
        </div>

        <form action="{{ route('inventory.items.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <!-- Upload Foto Preview Section -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Foto Barang (Opsional)</label>
                <div class="flex items-center gap-3">
                    <div id="createPhotoPreviewBox" class="w-20 h-20 rounded-2xl bg-slate-100 border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden shrink-0">
                        <span class="text-2xl text-slate-300">📷</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <input type="file" name="photo" id="createPhotoInput" accept="image/jpeg,image/png,image/jpg,image/webp" onchange="previewCreatePhoto(this)"
                            class="block w-full text-xs text-slate-500 file:mr-2.5 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all cursor-pointer">
                        <span class="text-[10px] text-slate-400 mt-1 block">JPG, PNG, atau WebP (Maks 5MB). Foto akan tampil pada kartu barang.</span>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Barang</label>
                <input type="text" name="name" required placeholder="Misal: Router MikroTik RB750Gr3"
                    class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kategori</label>
                    <select name="category" required class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Satuan</label>
                    <select name="unit" required class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        @foreach($units as $u)
                        <option value="{{ $u }}">{{ $u }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Stok Awal Fisik</label>
                    <input type="number" name="initial_stock" value="0" min="0" max="100000" placeholder="0"
                        class="w-full px-4 py-2.5 text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                    <span class="text-[10px] text-slate-400 mt-1 block">Boleh 0 jika stok barang belum ada di posko.</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Harga Beli Satuan (Opsional)</label>
                    <input type="text" name="unit_price" placeholder="Misal: 750.000" oninput="formatRupiahInput(this)"
                        class="rupiah-input w-full px-4 py-2.5 text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                    <span class="text-[10px] text-slate-400 mt-1 block">Taksiran nilai aset pengadaan barang.</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Lokasi Penyimpanan</label>
                <input type="text" name="location" placeholder="Gudang (Bawaan otomatis jika kosong)"
                    class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                <span class="text-[10px] text-slate-400 mt-1 block">Jika kosong, langsung terdeteksi berada di <strong>Gudang</strong>.</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Deskripsi / Spesifikasi</label>
                <input type="text" name="description" placeholder="Catatan spesifikasi teknis barang..."
                    class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div class="pt-3 flex justify-end gap-2.5 border-t border-slate-100">
                <button type="button" onclick="closeCreateItemModal()" class="px-4 py-2.5 rounded-full text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-full bg-blue-600 text-white hover:bg-blue-700 font-bold text-xs shadow-sm shadow-blue-500/20 active:scale-[0.98] transition-all">
                    Simpan Master Barang
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL 2: CATAT BARANG MASUK ==================== -->
<div id="stockInModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-[28px] border border-slate-200 shadow-2xl max-w-md w-full p-5 sm:p-7 space-y-5 animate-in fade-in zoom-in duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base">⬇️</span>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Catat Barang Masuk (Restock)</h3>
                    <p class="text-[11px] text-slate-400">Pemasukan stok fisik tanpa pengeluaran kas atau retur.</p>
                </div>
            </div>
            <button type="button" onclick="closeStockInModal()" class="text-slate-400 hover:text-slate-600 p-1 font-bold text-sm">✕</button>
        </div>

        <form action="{{ route('inventory.movements.in') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Pilih Barang</label>
                <select name="inventory_item_id" id="stockInItemId" required class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                    @foreach($allItems as $i)
                    <option value="{{ $i->id }}">
                        {{ $i->name }} (Sisa: {{ $i->stock }} {{ $i->unit }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jumlah Masuk (Qty)</label>
                    <input type="number" name="quantity" required value="1" min="1" max="10000"
                        class="w-full px-4 py-2.5 text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Masuk</label>
                    <input type="date" name="movement_date" required value="{{ date('Y-m-d') }}"
                        class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keperluan / Alasan Masuk</label>
                <input type="text" name="purpose" placeholder="Misal: Restock Pembelian Baru / Retur dari Mitra Toko A"
                    class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Asal Barang / Toko / Supplier</label>
                <input type="text" name="supplier_or_source" placeholder="Misal: Toko Komputer Jaya / Posko Pusat / Mitra..."
                    class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Catatan Tambahan (Nomor Seri / Resi)</label>
                <input type="text" name="notes" placeholder="Nomor SN perangkat atau nota..."
                    class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div class="pt-3 flex justify-end gap-2.5 border-t border-slate-100">
                <button type="button" onclick="closeStockInModal()" class="px-4 py-2.5 rounded-full text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-full bg-emerald-600 text-white hover:bg-emerald-700 font-bold text-xs shadow-sm shadow-emerald-500/20 active:scale-[0.98] transition-all">
                    Simpan Barang Masuk
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL 3: CATAT BARANG KELUAR ==================== -->
<div id="stockOutModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-[28px] border border-slate-200 shadow-2xl max-w-md w-full p-5 sm:p-7 space-y-5 animate-in fade-in zoom-in duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-base">⬆️</span>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Catat Barang Keluar (Pemasangan)</h3>
                    <p class="text-[11px] text-slate-400">Pengurangan stok untuk pemasangan di mitra, tiang, atau warga.</p>
                </div>
            </div>
            <button type="button" onclick="closeStockOutModal()" class="text-slate-400 hover:text-slate-600 p-1 font-bold text-sm">✕</button>
        </div>

        <form action="{{ route('inventory.movements.out') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Pilih Barang yang Keluar</label>
                <select name="inventory_item_id" id="stockOutItemId" required class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                    @foreach($allItems as $i)
                    <option value="{{ $i->id }}" data-stock="{{ $i->stock }}" data-unit="{{ $i->unit }}">
                        {{ $i->name }} (Sisa: {{ $i->stock }} {{ $i->unit }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jumlah Keluar (Qty)</label>
                    <input type="number" name="quantity" required value="1" min="1" max="10000"
                        class="w-full px-4 py-2.5 text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Keluar</label>
                    <input type="date" name="movement_date" required value="{{ date('Y-m-d') }}"
                        class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keperluan / Alasan Keluar</label>
                <input type="text" name="purpose" required placeholder="Contoh: Pemasangan di Mitra Toko Berkah / Penggantian AP Rusak"
                    class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Penerima / Lokasi Pasang / Teknisi</label>
                <input type="text" name="recipient" placeholder="Contoh: Toko Berkah (Mitra) / Tiang 04 RT 02"
                    class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Catatan Teknis (Serial Number / MAC)</label>
                <input type="text" name="notes" placeholder="Misal SN: 9812739812 / MAC: 00:11:22:33:44:55..."
                    class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div class="pt-3 flex justify-end gap-2.5 border-t border-slate-100">
                <button type="button" onclick="closeStockOutModal()" class="px-4 py-2.5 rounded-full text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-full bg-rose-600 text-white hover:bg-rose-700 font-bold text-xs shadow-sm shadow-rose-500/20 active:scale-[0.98] transition-all">
                    Simpan Barang Keluar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL 4: EDIT MASTER BARANG (DENGAN UPLOAD & UBAH FOTO) ==================== -->
<div id="editItemModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-[28px] border border-slate-200 shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-5 sm:p-7 space-y-5 animate-in fade-in zoom-in duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 sticky top-0 bg-white z-10">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-base">✏️</span>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Edit Data Barang</h3>
                    <p class="text-[11px] text-slate-400 font-mono" id="editItemCodeDisplay">INV-...</p>
                </div>
            </div>
            <button type="button" onclick="closeEditItemModal()" class="text-slate-400 hover:text-slate-600 p-1 font-bold text-sm">✕</button>
        </div>

        <form id="editItemForm" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Foto Barang Saat Ini & Penggantian -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Foto Barang</label>
                <div class="flex items-start gap-3">
                    <div id="editCurrentPhotoWrapper" class="w-20 h-20 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden shrink-0">
                        <img id="editPhotoImg" src="" alt="Preview" class="w-full h-full object-cover hidden">
                        <span id="editPhotoEmptyIcon" class="text-2xl text-slate-300">📷</span>
                    </div>

                    <div class="flex-1 min-w-0 space-y-2">
                        <input type="file" name="photo" id="editPhotoInput" accept="image/jpeg,image/png,image/jpg,image/webp" onchange="previewEditPhoto(this)"
                            class="block w-full text-xs text-slate-500 file:mr-2.5 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all cursor-pointer">
                        
                        <div id="removePhotoWrapper" class="hidden">
                            <label class="inline-flex items-center gap-1.5 text-xs text-rose-600 cursor-pointer select-none">
                                <input type="checkbox" name="remove_photo" id="removePhotoCheckbox" value="1" class="rounded text-rose-600 focus:ring-rose-500">
                                <span>Hapus foto saat ini (kembali ke icon default)</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Barang</label>
                <input type="text" name="name" id="editName" required
                    class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kategori</label>
                    <select name="category" id="editCategory" required class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Satuan</label>
                    <select name="unit" id="editUnit" required class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        @foreach($units as $u)
                        <option value="{{ $u }}">{{ $u }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Batas Minimum Peringatan</label>
                    <input type="number" name="min_stock" id="editMinStock" required min="0" max="1000"
                        class="w-full px-4 py-2.5 text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Harga Satuan (Rp)</label>
                    <input type="text" name="unit_price" id="editUnitPrice" oninput="formatRupiahInput(this)"
                        class="rupiah-input w-full px-4 py-2.5 text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Lokasi Penyimpanan</label>
                <input type="text" name="location" id="editLocation"
                    class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Deskripsi / Spesifikasi</label>
                <input type="text" name="description" id="editDescription"
                    class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-800 block">Status Keaktifan</span>
                    <span class="text-[10px] text-slate-400">Jika dinonaktifkan, barang tidak akan muncul di form pengeluaran/pemakaian.</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" id="editIsActive" value="1" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>

            <div class="pt-3 flex justify-between items-center border-t border-slate-100">
                <div class="flex items-center gap-1.5">
                    <button type="button" id="deleteItemBtn" onclick="confirmDeleteCurrentItem()" class="text-rose-600 hover:text-rose-800 font-bold text-xs transition-colors py-2 flex items-center gap-1">
                        Hapus Barang
                    </button>
                    <span id="deleteItemNotice" class="text-[10px] text-slate-400 hidden">(Hanya jika stok 0)</span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="closeEditItemModal()" class="px-4 py-2.5 rounded-full text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-full bg-blue-600 text-white hover:bg-blue-700 font-bold text-xs shadow-sm shadow-blue-500/20 active:scale-[0.98] transition-all">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>

        <form id="deleteItemForm" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

<script>
    function openCreateItemModal() {
        document.getElementById('createItemModal').classList.remove('hidden');
    }

    function closeCreateItemModal() {
        document.getElementById('createItemModal').classList.add('hidden');
    }

    function compressImageFile(file, maxWidth = 1280, maxHeight = 1280, quality = 0.85) {
        return new Promise((resolve) => {
            if (!file || !file.type.match(/image.*/)) {
                resolve(file);
                return;
            }
            if (file.size < 500 * 1024) {
                resolve(file);
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    let width = img.width;
                    let height = img.height;
                    if (width > maxWidth || height > maxHeight) {
                        if (width > height) {
                            height = Math.round((height * maxWidth) / width);
                            width = maxWidth;
                        } else {
                            width = Math.round((width * maxHeight) / height);
                            height = maxHeight;
                        }
                    }
                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    canvas.toBlob((blob) => {
                        if (blob && blob.size < file.size) {
                            const compressed = new File([blob], file.name.replace(/\.[^/.]+$/, ".jpg"), {
                                type: 'image/jpeg',
                                lastModified: Date.now()
                            });
                            resolve(compressed);
                        } else {
                            resolve(file);
                        }
                    }, 'image/jpeg', quality);
                };
                img.onerror = () => resolve(file);
                img.src = e.target.result;
            };
            reader.onerror = () => resolve(file);
            reader.readAsDataURL(file);
        });
    }

    async function previewCreatePhoto(input) {
        const box = document.getElementById('createPhotoPreviewBox');
        if (input.files && input.files[0]) {
            const rawFile = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                box.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-cover">';
            };
            reader.readAsDataURL(rawFile);

            if (rawFile.size > 600 * 1024) {
                try {
                    const compressed = await compressImageFile(rawFile);
                    const dt = new DataTransfer();
                    dt.items.add(compressed);
                    input.files = dt.files;
                } catch (e) {
                    console.warn('Kompresi client-side dilewati:', e);
                }
            }
        }
    }

    function openStockInModal(itemId = null, itemName = '', unit = '') {
        const select = document.getElementById('stockInItemId');
        if (itemId && select) {
            select.value = itemId;
        }
        document.getElementById('stockInModal').classList.remove('hidden');
    }

    function closeStockInModal() {
        document.getElementById('stockInModal').classList.add('hidden');
    }

    function openStockOutModal(itemId = null, itemName = '', unit = '', currentStock = 0) {
        const select = document.getElementById('stockOutItemId');
        if (itemId && select) {
            select.value = itemId;
        }
        document.getElementById('stockOutModal').classList.remove('hidden');
    }

    function closeStockOutModal() {
        document.getElementById('stockOutModal').classList.add('hidden');
    }

    let currentEditingItem = null;
    let currentEditingItemId = null;

    function openEditItemModal(item) {
        currentEditingItem = item;
        currentEditingItemId = item.id;
        document.getElementById('editItemCodeDisplay').textContent = item.item_code;
        document.getElementById('editName').value = item.name;
        document.getElementById('editCategory').value = item.category;
        document.getElementById('editUnit').value = item.unit;
        document.getElementById('editMinStock').value = item.min_stock;
        document.getElementById('editUnitPrice').value = item.unit_price ? Math.round(item.unit_price) : '';
        document.getElementById('editLocation').value = item.location || '';
        document.getElementById('editDescription').value = item.description || '';
        document.getElementById('editIsActive').checked = Boolean(item.is_active);

        // Deletion only allowed if stock is 0
        const deleteBtn = document.getElementById('deleteItemBtn');
        const deleteNotice = document.getElementById('deleteItemNotice');
        if (item.stock > 0) {
            deleteBtn.classList.add('opacity-40');
            deleteNotice.textContent = '(Stok: ' + item.stock + ' ' + item.unit + ', belum 0)';
            deleteNotice.classList.remove('hidden');
        } else {
            deleteBtn.classList.remove('opacity-40');
            deleteNotice.classList.add('hidden');
        }

        // Photo preview handling
        const img = document.getElementById('editPhotoImg');
        const emptyIcon = document.getElementById('editPhotoEmptyIcon');
        const removeWrapper = document.getElementById('removePhotoWrapper');
        const removeCheckbox = document.getElementById('removePhotoCheckbox');
        removeCheckbox.checked = false;

        if (item.photo) {
            img.src = '/storage/' + item.photo;
            img.classList.remove('hidden');
            emptyIcon.classList.add('hidden');
            removeWrapper.classList.remove('hidden');
        } else {
            img.src = '';
            img.classList.add('hidden');
            emptyIcon.classList.remove('hidden');
            removeWrapper.classList.add('hidden');
        }

        document.getElementById('editItemForm').action = '/inventory/items/' + item.id;
        document.getElementById('deleteItemForm').action = '/inventory/items/' + item.id;

        document.getElementById('editItemModal').classList.remove('hidden');
    }

    function closeEditItemModal() {
        document.getElementById('editItemModal').classList.add('hidden');
    }

    async function previewEditPhoto(input) {
        const img = document.getElementById('editPhotoImg');
        const emptyIcon = document.getElementById('editPhotoEmptyIcon');
        if (input.files && input.files[0]) {
            const rawFile = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                img.classList.remove('hidden');
                emptyIcon.classList.add('hidden');
            };
            reader.readAsDataURL(rawFile);

            if (rawFile.size > 600 * 1024) {
                try {
                    const compressed = await compressImageFile(rawFile);
                    const dt = new DataTransfer();
                    dt.items.add(compressed);
                    input.files = dt.files;
                } catch (e) {
                    console.warn('Kompresi edit photo dilewati:', e);
                }
            }
        }
    }

    function confirmDeleteCurrentItem() {
        if (!currentEditingItem) return;
        if (currentEditingItem.stock > 0) {
            alert('Barang "' + currentEditingItem.name + '" tidak dapat dihapus karena masih memiliki sisa stok sebanyak ' + currentEditingItem.stock + ' ' + currentEditingItem.unit + '.\n\nPenghapusan master barang hanya dapat dilakukan jika sisa stok sudah 0.');
            return;
        }
        if (confirm('Hapus master barang "' + currentEditingItem.name + '" dari inventaris?\n\nTindakan ini akan menghapus data barang secara permanen.')) {
            document.getElementById('deleteItemForm').submit();
        }
    }
</script>
@endsection
