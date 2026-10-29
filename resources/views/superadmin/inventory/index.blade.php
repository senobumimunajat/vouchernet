@extends('layouts.app')

@section('title', 'Inventaris & Logistik Barang')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-blue-600"></span>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-900">Inventaris & Logistik Barang</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Kelola stok perangkat fisik RT/RW Net (router, AP, switch, kabel FO, dll) dan pantau arus barang keluar-masuk pemasangan mitra.
            </p>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="openStockOutModal()" class="px-3.5 py-2 rounded-2xl bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200/80 font-bold text-xs transition-all flex items-center gap-1.5 shadow-2xs active:scale-95">
                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                <span>- Catat Barang Keluar</span>
            </button>

            <button type="button" onclick="openStockInModal()" class="px-3.5 py-2 rounded-2xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200/80 font-bold text-xs transition-all flex items-center gap-1.5 shadow-2xs active:scale-95">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
                <span>+ Catat Barang Masuk</span>
            </button>

            <button type="button" onclick="openCreateItemModal()" class="px-4 py-2 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm shadow-blue-500/20 transition-all flex items-center gap-1.5 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>+ Tambah Master Barang</span>
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
                <span class="text-[11px] font-bold uppercase tracking-wider">Total Item Barang</span>
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
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Stok Fisik</span>
                <span class="text-base p-1.5 rounded-xl bg-blue-50 text-blue-600">🔢</span>
            </div>
            <div class="text-xl sm:text-2xl font-black font-mono text-blue-900">
                {{ number_format($totalPhysicalStock) }} <span class="text-xs font-sans font-normal text-slate-400">Unit / Roll</span>
            </div>
            <span class="text-[10px] text-blue-600 font-medium block">Siap pakai di gudang</span>
        </div>

        <!-- 3. Stok Menipis / Kritis -->
        <a href="{{ route('inventory.index', ['tab' => 'items', 'stock_status' => 'low']) }}"
            class="p-4 sm:p-5 rounded-[24px] border transition-all space-y-2 {{ $lowStockCount > 0 ? 'bg-amber-50/60 border-amber-200 shadow-sm hover:bg-amber-100/60' : 'bg-white border-slate-200/70 shadow-[0_4px_20px_rgb(0,0,0,0.02)]' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider {{ $lowStockCount > 0 ? 'text-amber-800' : 'text-slate-500' }}">Stok Menipis / Habis</span>
                <span class="text-base p-1.5 rounded-xl {{ $lowStockCount > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }}">⚠️</span>
            </div>
            <div class="text-xl sm:text-2xl font-black font-mono {{ $lowStockCount > 0 ? 'text-amber-900' : 'text-slate-900' }}">
                {{ number_format($lowStockCount) }} <span class="text-xs font-sans font-normal text-slate-400">Item</span>
            </div>
            <span class="text-[10px] {{ $lowStockCount > 0 ? 'text-amber-700 font-bold' : 'text-slate-400 font-medium' }} block">
                {{ $lowStockCount > 0 ? 'Perlu segera restock!' : 'Semua stok dalam batas aman' }}
            </span>
        </a>

        <!-- 4. Mutasi Bulan Ini -->
        <div class="bg-white p-4 sm:p-5 rounded-[24px] border border-slate-200/70 shadow-[0_4px_20px_rgb(0,0,0,0.02)] space-y-2">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-[11px] font-bold uppercase tracking-wider">Mutasi Bulan Ini</span>
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
            <span class="text-[10px] text-slate-400 font-medium block">Bulan {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</span>
        </div>
    </div>

    <!-- Navigation Tabs (Daftar Barang vs Log Mutasi) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-200/80">
        <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-2xl border border-slate-200/80 text-xs shrink-0 self-start">
            <!-- Tab 1: Daftar Barang -->
            <a href="{{ route('inventory.index', ['tab' => 'items']) }}"
                class="flex items-center gap-2 px-4 py-2 rounded-xl transition-all {{ $tab === 'items' ? 'font-bold shadow-xs bg-white text-blue-700' : 'font-medium text-slate-600 hover:text-slate-900' }}">
                <span>📦</span>
                <span>Daftar Barang & Stok</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $tab === 'items' ? 'bg-blue-50 text-blue-700 border border-blue-200/60' : 'bg-slate-200 text-slate-700' }}">
                    {{ $items->count() }}
                </span>
            </a>

            <!-- Tab 2: Log Mutasi -->
            <a href="{{ route('inventory.index', ['tab' => 'movements']) }}"
                class="flex items-center gap-2 px-4 py-2 rounded-xl transition-all {{ $tab === 'movements' ? 'font-bold shadow-xs bg-white text-blue-700' : 'font-medium text-slate-600 hover:text-slate-900' }}">
                <span>🔄</span>
                <span>Riwayat Keluar - Masuk</span>
                @if($tab === 'movements')
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200/60">
                    {{ $movements->total() }}
                </span>
                @endif
            </a>
        </div>

        <div class="text-[11px] text-slate-400 font-medium">
            @if($tab === 'items')
            <span>Menampilkan data katalog dan saldo stok fisik inventaris.</span>
            @else
            <span>Audit lengkap pengeluaran (pemasangan mitra) & pemasukan (restock) barang.</span>
            @endif
        </div>
    </div>

    @if($tab === 'items')
    <!-- ==================== TAB 1: DAFTAR BARANG & STOK ==================== -->
    <div class="space-y-4">
        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-[0_2px_12px_rgb(0,0,0,0.02)]">
            <form method="GET" action="{{ route('inventory.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                <input type="hidden" name="tab" value="items">
                
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Barang / Kode / Lokasi</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama atau kode barang..."
                        class="w-full px-3.5 py-2 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Kategori</label>
                    <select name="category" onchange="this.form.submit()" class="w-full px-3.5 py-2 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                        <option value="">-- Semua Kategori --</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ $selectedCategory === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status Stok</label>
                    <select name="stock_status" onchange="this.form.submit()" class="w-full px-3.5 py-2 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                        <option value="">Semua Status Stok</option>
                        <option value="low" {{ $selectedStockStatus === 'low' ? 'selected' : '' }}>⚠️ Menipis / Kritis (≤ Min)</option>
                        <option value="available" {{ $selectedStockStatus === 'available' ? 'selected' : '' }}>Tersedia (> 0)</option>
                        <option value="empty" {{ $selectedStockStatus === 'empty' ? 'selected' : '' }}>Stok Habis (0)</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs">
                        Terapkan
                    </button>
                    <a href="{{ route('inventory.index', ['tab' => 'items']) }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 rounded-xl text-xs font-bold transition-all border border-slate-200/60">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Daftar Barang (Desktop) -->
        <div class="bg-white rounded-[28px] border border-slate-200/70 overflow-hidden shadow-[0_4px_20px_rgb(0,0,0,0.03)]">
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/70 text-slate-600 font-bold text-[11px] uppercase tracking-wider">
                            <th class="py-3.5 px-5">Kode & Nama Barang</th>
                            <th class="py-3.5 px-4">Kategori</th>
                            <th class="py-3.5 px-4">Lokasi Simpan</th>
                            <th class="py-3.5 px-4 text-center">Batas Min</th>
                            <th class="py-3.5 px-4 text-right">Stok Fisik</th>
                            <th class="py-3.5 px-4 text-right">Taksiran Nilai</th>
                            <th class="py-3.5 px-5 text-right">Aksi Cepat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($items as $item)
                        @php
                            $theme = $item->category_theme;
                            $isLow = $item->isLowStock();
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors {{ !$item->is_active ? 'opacity-60 bg-slate-50/30' : '' }}">
                            <td class="py-3.5 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg {{ $theme['bg'] }} shrink-0">
                                        {{ $theme['icon'] }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-mono text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ $item->item_code }}</span>
                                            @if(!$item->is_active)
                                            <span class="text-[9px] font-extrabold px-1.5 py-0.2 rounded bg-slate-200 text-slate-600">Nonaktif</span>
                                            @endif
                                        </div>
                                        <span class="font-bold text-slate-900 text-xs block truncate mt-0.5">{{ $item->name }}</span>
                                        @if($item->description)
                                        <span class="text-[11px] text-slate-400 block truncate max-w-xs">{{ $item->description }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $theme['bg'] }}">
                                    {{ $item->category }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                <span class="inline-flex items-center gap-1 text-[11px]">
                                    <span class="text-slate-400">📍</span>
                                    <span>{{ $item->location ?: 'Gudang Posko' }}</span>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono text-slate-500">
                                {{ $item->min_stock }} {{ $item->unit }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex flex-col items-end">
                                    <span class="text-sm font-mono font-black {{ $isLow ? 'text-amber-600' : 'text-slate-900' }}">
                                        {{ number_format($item->stock) }} <span class="text-xs font-normal text-slate-500">{{ $item->unit }}</span>
                                    </span>
                                    @if($item->stock == 0)
                                    <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200">Habis</span>
                                    @elseif($isLow)
                                    <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">⚠️ Menipis</span>
                                    @else
                                    <span class="text-[9px] font-extrabold text-emerald-600">✓ Aman</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-slate-700">
                                @if($item->unit_price)
                                <span>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                @else
                                <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Tombol Masuk Cepat -->
                                    <button type="button" onclick="openStockInModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->unit }}')" title="Catat barang masuk / restock" class="p-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    </button>

                                    <!-- Tombol Keluar Cepat -->
                                    <button type="button" onclick="openStockOutModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->unit }}', {{ $item->stock }})" title="Catat barang keluar / pemasangan mitra" class="p-1.5 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15"/></svg>
                                    </button>

                                    <!-- Tombol Edit -->
                                    <button type="button" onclick="openEditItemModal({{ json_encode($item) }})" title="Edit data barang" class="p-1.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200/60 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/></svg>
                                    </button>

                                    <!-- Riwayat Link -->
                                    <a href="{{ route('inventory.index', ['tab' => 'movements', 'item_id' => $item->id]) }}" title="Lihat riwayat pergerakan barang ini" class="p-1.5 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="p-10 text-center">
                                <div class="w-12 h-12 mx-auto mb-2 rounded-2xl bg-slate-100 flex items-center justify-center text-xl">📦</div>
                                <h4 class="text-xs font-bold text-slate-800">Tidak ada barang inventaris yang sesuai</h4>
                                <p class="text-[11px] text-slate-400 mt-0.5">Silakan reset filter atau klik tombol "+ Tambah Master Barang" di atas.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards View -->
            <div class="lg:hidden divide-y divide-slate-100">
                @forelse($items as $item)
                @php
                    $theme = $item->category_theme;
                    $isLow = $item->isLowStock();
                @endphp
                <div class="p-4 space-y-3 hover:bg-slate-50/50 transition-colors {{ !$item->is_active ? 'opacity-60 bg-slate-50/30' : '' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-base {{ $theme['bg'] }} shrink-0">
                                {{ $theme['icon'] }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-mono text-[9px] font-bold text-slate-400 uppercase tracking-wider">{{ $item->item_code }}</span>
                                    <span class="px-2 py-0.2 rounded-full text-[9px] font-bold border {{ $theme['bg'] }}">
                                        {{ $item->category }}
                                    </span>
                                </div>
                                <h4 class="font-bold text-slate-900 text-xs truncate mt-0.5">{{ $item->name }}</h4>
                            </div>
                        </div>

                        <!-- Stok Saat Ini -->
                        <div class="text-right shrink-0">
                            <span class="text-sm font-mono font-black {{ $isLow ? 'text-amber-600' : 'text-slate-900' }} block">
                                {{ number_format($item->stock) }} <span class="text-xs font-normal text-slate-400">{{ $item->unit }}</span>
                            </span>
                            @if($item->stock == 0)
                            <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200">Habis</span>
                            @elseif($isLow)
                            <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">⚠️ Menipis</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1 border-t border-slate-100">
                        <span>📍 {{ $item->location ?: 'Gudang Posko' }}</span>
                        <span>Min: {{ $item->min_stock }} {{ $item->unit }}</span>
                    </div>

                    <div class="grid grid-cols-4 gap-1.5 pt-1">
                        <button type="button" onclick="openStockInModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->unit }}')" class="py-1.5 px-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center gap-1 border border-emerald-200">
                            <span>+ Masuk</span>
                        </button>
                        <button type="button" onclick="openStockOutModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->unit }}', {{ $item->stock }})" class="py-1.5 px-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs flex items-center justify-center gap-1 border border-rose-200">
                            <span>- Keluar</span>
                        </button>
                        <button type="button" onclick="openEditItemModal({{ json_encode($item) }})" class="py-1.5 px-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center gap-1 border border-slate-200">
                            <span>Edit</span>
                        </button>
                        <a href="{{ route('inventory.index', ['tab' => 'movements', 'item_id' => $item->id]) }}" class="py-1.5 px-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center gap-1 border border-blue-200">
                            <span>Riwayat</span>
                        </a>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-slate-400 text-xs">Belum ada barang inventaris.</div>
                @endforelse
            </div>
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
                    <select name="item_id" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
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
                    <select name="type" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                        <option value="all">Semua Jenis</option>
                        <option value="in" {{ $selectedType === 'in' ? 'selected' : '' }}>⬇️ Barang Masuk (+)</option>
                        <option value="out" {{ $selectedType === 'out' ? 'selected' : '' }}>⬆️ Barang Keluar (-)</option>
                        <option value="adjustment" {{ $selectedType === 'adjustment' ? 'selected' : '' }}>🔄 Penyesuaian</option>
                    </select>
                </div>

                <!-- Filter Bulan -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Bulan</label>
                    <select name="month" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
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
                    <select name="year" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                        @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs">
                        Filter
                    </button>
                    <a href="{{ route('inventory.index', ['tab' => 'movements']) }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 rounded-xl text-xs font-bold transition-all border border-slate-200/60">
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
                                <span class="font-bold text-slate-900 block">{{ $m->item->name ?? '-' }}</span>
                                <span class="text-[10px] font-mono text-slate-400">{{ $m->item->item_code ?? '' }}</span>
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
                        <div>
                            <span class="font-bold text-slate-900 text-xs block">{{ $m->item->name ?? '-' }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $m->movement_date->translatedFormat('d M Y') }} • {{ $m->created_at->format('H:i') }}</span>
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
                    <div class="text-[11px] text-slate-500 bg-slate-50 p-2 rounded-xl border border-slate-200/60 space-y-0.5">
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
                            <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold text-[11px]">Batalkan</button>
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

<!-- ==================== MODAL 1: TAMBAH MASTER BARANG BARU ==================== -->
<div id="createItemModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-[28px] border border-slate-200 shadow-2xl max-w-lg w-full p-6 sm:p-7 space-y-5 animate-in fade-in zoom-in duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">📦</span>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Tambah Master Barang Baru</h3>
                    <p class="text-[11px] text-slate-400">Daftarkan jenis perangkat / aset baru ke inventaris.</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateItemModal()" class="text-slate-400 hover:text-slate-600 p-1 font-bold text-sm">✕</button>
        </div>

        <form action="{{ route('inventory.items.store') }}" method="POST" class="space-y-4">
            @csrf
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

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Stok Awal Fisik</label>
                    <input type="number" name="initial_stock" value="0" min="0" max="10000"
                        class="w-full px-4 py-2.5 text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Batas Minimum</label>
                    <input type="number" name="min_stock" value="2" min="0" max="1000" required
                        class="w-full px-4 py-2.5 text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Harga Beli Satuan (Rp)</label>
                    <input type="text" name="unit_price" placeholder="Misal: 750.000" oninput="formatRupiahInput(this)"
                        class="rupiah-input w-full px-4 py-2.5 text-xs font-mono font-bold bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Lokasi Penyimpanan</label>
                <input type="text" name="location" placeholder="Misal: Gudang Posko RT / Rak Server"
                    class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Deskripsi / Spesifikasi</label>
                <input type="text" name="description" placeholder="Catatan spesifikasi teknis barang..."
                    class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div class="pt-3 flex justify-end gap-2.5 border-t border-slate-100">
                <button type="button" onclick="closeCreateItemModal()" class="px-4 py-2 rounded-full text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-full bg-blue-600 text-white hover:bg-blue-700 font-bold text-xs shadow-sm shadow-blue-500/20 active:scale-[0.98] transition-all">
                    Simpan Master Barang
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL 2: CATAT BARANG MASUK ==================== -->
<div id="stockInModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-[28px] border border-slate-200 shadow-2xl max-w-md w-full p-6 sm:p-7 space-y-5 animate-in fade-in zoom-in duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base">⬇️</span>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Catat Barang Masuk (+ Restock)</h3>
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
                        {{ $i->name }} (Sisa stok: {{ $i->stock }} {{ $i->unit }})
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
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keperluan / Sumber Pemasukan</label>
                <input type="text" name="purpose" placeholder="Misal: Restock Pembelian Baru / Retur dari Mitra Toko A"
                    class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Catatan Tambahan (Nomor Seri / Resi)</label>
                <input type="text" name="notes" placeholder="Nomor SN perangkat atau nota..."
                    class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div class="pt-3 flex justify-end gap-2.5 border-t border-slate-100">
                <button type="button" onclick="closeStockInModal()" class="px-4 py-2 rounded-full text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-full bg-emerald-600 text-white hover:bg-emerald-700 font-bold text-xs shadow-sm shadow-emerald-500/20 active:scale-[0.98] transition-all">
                    + Simpan Barang Masuk
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL 3: CATAT BARANG KELUAR ==================== -->
<div id="stockOutModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-[28px] border border-slate-200 shadow-2xl max-w-md w-full p-6 sm:p-7 space-y-5 animate-in fade-in zoom-in duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-base">⬆️</span>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Catat Barang Keluar (- Pemasangan)</h3>
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
                        {{ $i->name }} (Sisa stok: {{ $i->stock }} {{ $i->unit }})
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
                <button type="button" onclick="closeStockOutModal()" class="px-4 py-2 rounded-full text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-full bg-rose-600 text-white hover:bg-rose-700 font-bold text-xs shadow-sm shadow-rose-500/20 active:scale-[0.98] transition-all">
                    - Simpan Barang Keluar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL 4: EDIT MASTER BARANG ==================== -->
<div id="editItemModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-[28px] border border-slate-200 shadow-2xl max-w-lg w-full p-6 sm:p-7 space-y-5 animate-in fade-in zoom-in duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-base">✏️</span>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Edit Data Barang</h3>
                    <p class="text-[11px] text-slate-400 font-mono" id="editItemCodeDisplay">INV-...</p>
                </div>
            </div>
            <button type="button" onclick="closeEditItemModal()" class="text-slate-400 hover:text-slate-600 p-1 font-bold text-sm">✕</button>
        </div>

        <form id="editItemForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

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
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Taksiran Harga Satuan (Rp)</label>
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
                    <span class="text-[10px] text-slate-400">Jika dinonaktifkan, tidak akan muncul pada pilihan pemakaian baru.</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" id="editIsActive" value="1" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>

            <div class="pt-3 flex justify-between items-center border-t border-slate-100">
                <button type="button" onclick="confirmDeleteCurrentItem()" class="text-rose-600 hover:text-rose-800 font-bold text-xs transition-colors">
                    Hapus Barang
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="closeEditItemModal()" class="px-4 py-2 rounded-full text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">Batal</button>
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

    let currentEditingItemId = null;

    function openEditItemModal(item) {
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

        document.getElementById('editItemForm').action = '/inventory/items/' + item.id;
        document.getElementById('deleteItemForm').action = '/inventory/items/' + item.id;

        document.getElementById('editItemModal').classList.remove('hidden');
    }

    function closeEditItemModal() {
        document.getElementById('editItemModal').classList.add('hidden');
    }

    function confirmDeleteCurrentItem() {
        if (confirm('Hapus master barang ini dari inventaris? (Hanya dapat dihapus jika belum memiliki riwayat mutasi/pengeluaran)')) {
            document.getElementById('deleteItemForm').submit();
        }
    }
</script>
@endsection
