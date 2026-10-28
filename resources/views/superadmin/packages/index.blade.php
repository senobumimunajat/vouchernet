@extends('layouts.app')

@section('title', 'Manajemen Paket & Stok Voucher')

@section('content')
<div class="space-y-8">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-600 animate-pulse"></span>
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-200/70">Inventory & Katalog</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 mt-1">Manajemen Paket & Stok Voucher</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola kuota, harga setor owner, dan monitoring stok fisik voucher siap edar.</p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <button type="button" onclick="openAddStockModal()" class="touch-target inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm shadow-blue-500/20 active:scale-[0.98] transition-all">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/></svg>
                Tambah Stok Voucher
            </button>
            @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('packages.create') }}" class="touch-target inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs border border-slate-200/90 shadow-2xs transition-all">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Buat Paket Baru
            </a>
            @endif
        </div>
    </div>

    <!-- Quick Metrics Summary Bar (3 Cards Fokus Inventaris Operasional) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- 1. Total Stok Fisik -->
        <div class="p-4 sm:p-5 rounded-[24px] bg-white border border-slate-200/80 shadow-[0_2px_12px_rgb(0,0,0,0.02)] flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Stok Fisik</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200/80 flex items-center justify-center font-bold text-sm">📦</span>
            </div>
            <div class="mt-3">
                <div class="text-xl sm:text-2xl font-black font-mono text-slate-900">
                    {{ number_format($totalStock) }} <span class="text-xs font-sans font-normal text-slate-500">lbr</span>
                </div>
                <span class="text-[10px] text-emerald-700 font-medium block mt-0.5">Siap didistribusikan ke warung</span>
            </div>
        </div>

        <!-- 2. Status Kesehatan Stok -->
        <div class="p-4 sm:p-5 rounded-[24px] bg-white border border-slate-200/80 shadow-[0_2px_12px_rgb(0,0,0,0.02)] flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kondisi Stok</span>
                @if($lowStockCount == 0)
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200/80 flex items-center justify-center font-bold text-sm">🛡️</span>
                @else
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 border border-amber-200/80 flex items-center justify-center font-bold text-sm">⚠️</span>
                @endif
            </div>
            <div class="mt-3">
                @if($lowStockCount == 0)
                <div class="text-lg sm:text-xl font-extrabold text-emerald-800 flex items-center gap-1.5">
                    <span>Semua Aman</span>
                </div>
                <span class="text-[10px] text-emerald-600 font-medium block mt-0.5">Tidak ada stok di bawah 20 lbr</span>
                @else
                <div class="text-lg sm:text-xl font-extrabold text-amber-900 flex items-center gap-1.5">
                    <span>{{ $lowStockCount }} Paket Kritis</span>
                </div>
                <span class="text-[10px] text-amber-700 font-medium block mt-0.5">Stok menipis, perlu dicetak</span>
                @endif
            </div>
        </div>

        <!-- 3. Paket Aktif -->
        <div class="p-4 sm:p-5 rounded-[24px] bg-white border border-slate-200/80 shadow-[0_2px_12px_rgb(0,0,0,0.02)] flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Katalog Hotspot</span>
                <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-700 border border-purple-200/80 flex items-center justify-center font-bold text-sm">📶</span>
            </div>
            <div class="mt-3">
                <div class="text-xl sm:text-2xl font-black font-mono text-purple-950">
                    {{ $activePackagesCount }} <span class="text-xs font-sans font-normal text-slate-500">/ {{ $packages->count() }} Paket</span>
                </div>
                <span class="text-[10px] text-purple-700 font-medium block mt-0.5">Aktif melayani pembelian</span>
            </div>
        </div>
    </div>

    <!-- Tabs Navigasi Status Paket (Aktif vs Nonaktif) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-200/80">
        <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-2xl border border-slate-200/80 text-xs shrink-0 self-start">
            <!-- Tab Paket Aktif -->
            <button type="button" onclick="switchPackageTab('active')" id="tabBtnActive"
                class="flex items-center gap-2 px-4 py-2 rounded-xl font-bold transition-all shadow-xs bg-white text-blue-700">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Paket Aktif</span>
                <span id="activeCountBadge" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200/60">
                    {{ $activePackagesCount }}
                </span>
            </button>

            <!-- Tab Paket Nonaktif -->
            <button type="button" onclick="switchPackageTab('inactive')" id="tabBtnInactive"
                class="flex items-center gap-2 px-4 py-2 rounded-xl font-medium transition-all text-slate-600 hover:text-slate-900">
                <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                <span>Paket Nonaktif</span>
                <span id="inactiveCountBadge" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $inactivePackagesCount > 0 ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-slate-200 text-slate-700' }}">
                    {{ $inactivePackagesCount }}
                </span>
            </button>

            <!-- Tab Semua Paket -->
            <button type="button" onclick="switchPackageTab('all')" id="tabBtnAll"
                class="flex items-center gap-2 px-3.5 py-2 rounded-xl font-medium transition-all text-slate-500 hover:text-slate-900">
                <span>Semua</span>
                <span class="text-[10px] text-slate-400">({{ $packages->count() }})</span>
            </button>
        </div>

        <div class="text-[11px] text-slate-400 font-medium">
            <span id="tabStatusDescription">Menampilkan paket yang aktif melayani pembelian.</span>
        </div>
    </div>

    <!-- Package Cards Grid (Spacious 3 Columns on Desktop, No Cramping) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="packagesGrid">
        @foreach($packages as $pkg)
        <div class="package-card bg-white rounded-[28px] border {{ $selectedPackage == $pkg->id ? 'border-blue-500 ring-2 ring-blue-500/30 shadow-md' : $pkg->theme['card_border'] . ' shadow-[0_4px_25px_rgb(0,0,0,0.04)]' }} hover:shadow-lg flex flex-col justify-between overflow-hidden transition-all duration-200 group relative {{ $pkg->is_active ? '' : 'opacity-90' }}"
            data-is-active="{{ $pkg->is_active ? '1' : '0' }}">
            
            <!-- Top Gradient Accent Bar -->
            <div class="h-2 w-full bg-gradient-to-r {{ $pkg->theme['gradient_bar'] }}"></div>

            <div class="p-6 space-y-4.5 flex-1 flex flex-col justify-between">
                
                <!-- Card Header -->
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-xl {{ $pkg->theme['icon_bg'] }} text-white shadow-sm shrink-0">
                                {{ $pkg->theme['icon_emoji'] }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider {{ $pkg->theme['badge_theme'] }}">
                                        {{ $pkg->duration_info }}
                                    </span>
                                    @if($selectedPackage == $pkg->id)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider bg-blue-600 text-white shadow-2xs">
                                        ✓ Dianalisis
                                    </span>
                                    @endif
                                </div>
                                <h3 class="text-base font-extrabold text-slate-900 tracking-tight leading-tight mt-1">{{ $pkg->name }}</h3>
                            </div>
                        </div>

                        <!-- Toggle Status Aktif / Nonaktif -->
                        @if(auth()->user()->isSuperAdmin())
                        <form action="{{ route('packages.toggle', $pkg) }}" method="POST" class="shrink-0" {!! $pkg->is_active ? 'onsubmit="return confirm(\'Nonaktifkan paket ' . addslashes($pkg->name) . '? Paket tidak akan muncul saat pembuatan pesanan baru.\');"' : '' !!}>
                            @csrf
                            <button type="submit" title="Klik untuk {{ $pkg->is_active ? 'menonaktifkan' : 'mengaktifkan' }} paket" class="text-[10px] font-bold px-2.5 py-1 rounded-full border transition-all flex items-center gap-1.5 shadow-2xs {{ $pkg->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100 hover:border-emerald-300' : 'bg-slate-100 text-slate-600 border-slate-300 hover:bg-slate-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $pkg->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                <span>{{ $pkg->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                            </button>
                        </form>
                        @else
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full border shrink-0 flex items-center gap-1.5 {{ $pkg->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-400 border-slate-200' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $pkg->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                            <span>{{ $pkg->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </span>
                        @endif
                    </div>

                    <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed min-h-[2.5rem]">
                        {{ $pkg->description ?: 'Paket hotspot Wi-Fi sistem cetak voucher fisik.' }}
                    </p>
                </div>

                <!-- Bandrol Harga Fisik Tertera -->
                <div class="p-3.5 rounded-2xl bg-slate-50/90 border border-slate-200/70 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Harga Fisik</span>
                        <span class="text-xs text-slate-600 font-medium">Banderol Cetak Voucher</span>
                    </div>
                    <div class="text-right">
                        <span class="text-xl font-mono font-black text-slate-900 block leading-tight">Rp {{ number_format($pkg->printed_price, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Real-time Physical Stock Box (Themed) -->
                <div class="p-4 rounded-2xl {{ $pkg->theme['stock_box'] }} space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-extrabold text-[11px] uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            Stok Fisik Tersedia
                        </span>
                        @if($pkg->stock > 50)
                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                            ● Aman
                        </span>
                        @elseif($pkg->stock > 0)
                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200 animate-pulse">
                            ▲ Menipis
                        </span>
                        @else
                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 border border-rose-200 animate-pulse">
                            ✕ Habis
                        </span>
                        @endif
                    </div>

                    <div class="flex items-baseline justify-between gap-2">
                        <div class="text-2xl font-black font-mono text-slate-900 tracking-tight">
                            {{ number_format($pkg->stock) }} <span class="text-xs font-sans text-slate-500 font-normal">lembar</span>
                        </div>
                        <span class="text-xs text-slate-400 font-medium">Siap Edar</span>
                    </div>

                    <!-- Mini Stock Gauge Bar -->
                    <div class="w-full bg-slate-200/80 rounded-full h-1.5 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ $pkg->theme['stock_bar'] }}" style="width: {{ min(100, max(6, ($pkg->stock / 500) * 100)) }}%"></div>
                    </div>

                    <!-- Tombol Tambah Stok (Single plus icon + clean label) -->
                    <button type="button" onclick="openAddStockModal({{ $pkg->id }}, '{{ addslashes($pkg->name) }}', {{ $pkg->stock }})"
                        class="w-full py-2.5 px-3 rounded-xl {{ $pkg->theme['btn_action'] }} font-bold text-xs transition-all flex items-center justify-center gap-1.5 shadow-sm active:scale-[0.98]">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah Stok</span>
                    </button>
                </div>

                <!-- Price & Profit Breakdown -->
                <div class="grid grid-cols-2 gap-2.5 text-xs">
                    <div class="p-3 rounded-2xl bg-blue-50/70 border border-blue-200/60">
                        <span class="text-[10px] font-bold text-blue-900 uppercase block tracking-wider">Setor Owner</span>
                        <span class="font-extrabold font-mono text-blue-950 text-base block leading-tight mt-0.5">Rp {{ number_format($pkg->owner_price, 0, ',', '.') }}</span>
                        <span class="text-[10px] text-blue-700 block mt-0.5">Uang Kas Masuk</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-emerald-50/70 border border-emerald-200/60">
                        <span class="text-[10px] font-bold text-emerald-900 uppercase block tracking-wider">Untung Warung</span>
                        <span class="font-extrabold font-mono text-emerald-700 text-base block leading-tight mt-0.5">+Rp {{ number_format($pkg->reseller_profit, 0, ',', '.') }}</span>
                        <span class="text-[10px] text-emerald-600 block mt-0.5">Komisi Reseller</span>
                    </div>
                </div>

                <!-- Bottom Meta & Actions -->
                <div class="pt-3 border-t border-slate-100/90 space-y-2.5">
                    <!-- Row 1: Informasi Transaksi & Analisis Stok -->
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 text-[11px] font-medium flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <strong class="font-bold text-slate-700">{{ $pkg->orders_count }}</strong> Transaksi
                        </span>
                        <a href="{{ route('packages.index', ['package_id' => $pkg->id]) }}#riwayat-stok" class="text-[11px] font-bold {{ $selectedPackage == $pkg->id ? 'text-blue-700 underline font-extrabold' : 'text-slate-500 hover:text-blue-600' }} transition-colors flex items-center gap-1">
                            <span>Analisis Stok</span>
                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </a>
                    </div>

                    <!-- Row 2: Tombol Aksi SuperAdmin (Rapi & Terpisah) -->
                    @if(auth()->user()->isSuperAdmin())
                    <div class="grid {{ $pkg->orders_count == 0 ? 'grid-cols-3' : 'grid-cols-2' }} gap-2 pt-1 border-t border-dashed border-slate-100">
                        <!-- Tombol Edit -->
                        <a href="{{ route('packages.edit', $pkg) }}" class="py-1.5 px-2 rounded-xl bg-slate-50 hover:bg-blue-50 text-slate-700 hover:text-blue-700 border border-slate-200/80 font-bold text-xs transition-all flex items-center justify-center gap-1 text-center shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Edit</span>
                        </a>

                        <!-- Tombol Nonaktifkan / Aktifkan -->
                        <form action="{{ route('packages.toggle', $pkg) }}" method="POST" class="w-full" {!! $pkg->is_active ? 'onsubmit="return confirm(\'Nonaktifkan paket ' . addslashes($pkg->name) . '? Paket tidak akan muncul saat pembuatan pesanan baru.\');"' : '' !!}>
                            @csrf
                            <button type="submit" class="w-full py-1.5 px-2 rounded-xl border font-bold text-xs transition-all flex items-center justify-center gap-1 text-center shadow-2xs {{ $pkg->is_active ? 'bg-amber-50/70 hover:bg-amber-100 text-amber-800 border-amber-200/80' : 'bg-emerald-50/70 hover:bg-emerald-100 text-emerald-800 border-emerald-200/80' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $pkg->is_active ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                                <span>{{ $pkg->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                            </button>
                        </form>

                        <!-- Tombol Hapus (Hanya Jika 0 Transaksi) -->
                        @if($pkg->orders_count == 0)
                        <form action="{{ route('packages.destroy', $pkg) }}" method="POST" onsubmit="return confirm('Hapus paket voucher ini?');" class="w-full">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full py-1.5 px-2 rounded-xl bg-rose-50/60 hover:bg-rose-100 text-rose-700 border border-rose-200/80 font-bold text-xs transition-all flex items-center justify-center gap-1 text-center shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Hapus</span>
                            </button>
                        </form>
                        @endif
                    </div>
                    @endif
                </div>

            </div>

        </div>
        @endforeach
    </div>

    <!-- Empty State: Paket Nonaktif Kosong -->
    <div id="emptyInactiveBox" class="hidden text-center py-12 px-6 rounded-[28px] border-2 border-dashed border-slate-200 bg-slate-50/50 my-4">
        <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl shadow-xs">
            ✨
        </div>
        <h4 class="text-sm font-bold text-slate-800">Semua Paket Saat Ini Aktif</h4>
        <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 leading-relaxed">
            Tidak ada paket voucher yang sedang dinonaktifkan. Semua paket siap melayani transaksi dan pembuatan pesanan mitra.
        </p>
    </div>

    <!-- Empty State: Paket Aktif Kosong -->
    <div id="emptyActiveBox" class="hidden text-center py-12 px-6 rounded-[28px] border-2 border-dashed border-amber-200 bg-amber-50/30 my-4">
        <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl shadow-xs">
            ⚠️
        </div>
        <h4 class="text-sm font-bold text-slate-800">Tidak Ada Paket Aktif</h4>
        <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 leading-relaxed">
            Semua paket saat ini dinonaktifkan. Buka tab <strong>"Paket Nonaktif"</strong> untuk mengaktifkan kembali paket voucher.
        </p>
    </div>

    <!-- Riwayat & Analisis Mutasi Stok Voucher (Audit Log & Inventory Movement) -->
    <div id="riwayat-stok" class="bg-white rounded-[28px] border border-slate-200/70 overflow-hidden shadow-[0_4px_20px_rgb(0,0,0,0.03)] space-y-6 p-5 sm:p-6">
        
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-5">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Riwayat & Analisis Mutasi Stok Voucher</h2>
                    @if($currentPackage)
                    <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                        {{ $currentPackage->name }}
                    </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Audit lengkap pencetakan (+), restock fisik, dan pengurangan (-) akibat pesanan mitra atau eceran per paket voucher.
                </p>
            </div>
            <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
                <span class="text-xs font-semibold px-3 py-1 bg-slate-100 text-slate-700 rounded-full border border-slate-200/60">
                    {{ number_format($totalLogsCount) }} Catatan Ditemukan
                </span>
            </div>
        </div>

        <!-- Filter Bar Form -->
        <form method="GET" action="{{ route('packages.index') }}#riwayat-stok" class="p-4 sm:p-5 rounded-2xl bg-slate-50/80 border border-slate-200/80 space-y-3">
            <input type="hidden" name="tab" id="formFilterTab" value="{{ $selectedTab }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                
                <!-- 1. Filter Paket Voucher -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Paket Voucher</label>
                    <select name="package_id" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Paket Voucher (Semua)</option>
                        @foreach($packages as $p)
                        <option value="{{ $p->id }}" {{ $selectedPackage == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} (Sisa: {{ number_format($p->stock) }} lbr)
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- 2. Filter Jenis Mutasi -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Jenis Mutasi</label>
                    <select name="type" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Jenis Mutasi</option>
                        <option value="restock" {{ $selectedType === 'restock' ? 'selected' : '' }}>+ Restock / Tambah Cetak</option>
                        <option value="order" {{ $selectedType === 'order' ? 'selected' : '' }}>- Pengurangan Pesanan / Terjual</option>
                    </select>
                </div>

                <!-- 3. Filter Bulan -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Bulan</label>
                    <select name="month" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Bulan</option>
                        @for($m = 1; $m <= 12; $m++)
                            @php $mVal = str_pad($m, 2, '0', STR_PAD_LEFT); @endphp
                            <option value="{{ $mVal }}" {{ $selectedMonth == $mVal ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <!-- 4. Filter Tahun -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tahun</label>
                    <select name="year" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Tahun</option>
                        @foreach($availableYears as $yr)
                        <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 5. Pencarian & Reset -->
                <div class="flex items-end gap-2">
                    <div class="flex-1">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Cari</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="No order, catatan..." class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    @if($selectedPackage || $selectedType || $selectedMonth || $selectedYear || $search)
                    <a href="{{ route('packages.index') }}#riwayat-stok" title="Reset filter" class="p-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-600 transition-colors shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                    @endif
                </div>

            </div>

            <!-- Quick Filter Chips: Klik Cepat Paket Voucher -->
            <div class="pt-2 border-t border-slate-200/60 flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none text-xs">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider shrink-0 mr-1">Klik Cepat Paket:</span>
                <a href="{{ route('packages.index', array_merge(request()->except('package_id', 'page'))) }}#riwayat-stok"
                    class="px-3 py-1 rounded-full text-xs font-bold transition-all shrink-0 {{ empty($selectedPackage) ? 'bg-blue-600 text-white shadow-xs' : 'bg-white hover:bg-slate-200 text-slate-700 border border-slate-200' }}">
                    Semua Paket ({{ number_format($totalStock) }})
                </a>
                @foreach($packages as $p)
                <a href="{{ route('packages.index', array_merge(request()->except('page'), ['package_id' => $p->id])) }}#riwayat-stok"
                    class="px-3 py-1 rounded-full text-xs font-bold transition-all shrink-0 flex items-center gap-1.5 {{ $selectedPackage == $p->id ? 'bg-blue-600 text-white shadow-xs' : 'bg-white hover:bg-slate-200 text-slate-700 border border-slate-200' }}">
                    <span>{{ $p->name }}</span>
                    <span class="text-[10px] font-mono opacity-80 font-normal">({{ number_format($p->stock) }} lbr)</span>
                </a>
                @endforeach
            </div>
        </form>

        <!-- Ringkasan Analisis Arus Mutasi Stok (Executive Analysis Bar) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            
            <!-- 1. Stok Fisik Saat Ini -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Stok Siap Edar</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                        {{ $currentPackage ? ($currentPackage->stock > 20 ? '● Aman' : ($currentPackage->stock > 0 ? '▲ Menipis' : '✕ Habis')) : 'Gudang' }}
                    </span>
                </div>
                <div class="mt-2">
                    <div class="text-xl font-black font-mono text-slate-900">
                        {{ number_format($currentPackage ? $currentPackage->stock : $totalStock) }} <span class="text-xs font-sans font-normal text-slate-500">lbr</span>
                    </div>
                    <span class="text-[11px] text-slate-500 block mt-0.5 font-medium">
                        @if($currentPackage)
                            Nilai Banderol: <strong class="font-mono text-slate-700">Rp {{ number_format($currentPackage->stock * $currentPackage->printed_price, 0, ',', '.') }}</strong>
                        @else
                            Total fisik di {{ $packages->count() }} jenis paket
                        @endif
                    </span>
                </div>
            </div>

            <!-- 2. Total Masuk / Restock Cetak -->
            <div class="p-4 rounded-2xl bg-blue-50/60 border border-blue-200/70 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-blue-800">Total Masuk (Restock)</span>
                    <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">＋</span>
                </div>
                <div class="mt-2">
                    <div class="text-xl font-black font-mono text-blue-900">
                        +{{ number_format($totalAddedSheets) }} <span class="text-xs font-sans font-normal text-blue-700">lbr</span>
                    </div>
                    <span class="text-[11px] text-blue-700/80 block mt-0.5 font-medium">Penambahan stok fisik tercetak</span>
                </div>
            </div>

            <!-- 3. Total Keluar / Terjual -->
            <div class="p-4 rounded-2xl bg-rose-50/60 border border-rose-200/70 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-800">Total Keluar (Terjual)</span>
                    <span class="w-6 h-6 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-xs">－</span>
                </div>
                <div class="mt-2">
                    <div class="text-xl font-black font-mono text-rose-900">
                        -{{ number_format($totalDeductedSheets) }} <span class="text-xs font-sans font-normal text-rose-700">lbr</span>
                    </div>
                    <span class="text-[11px] text-rose-700/80 block mt-0.5 font-medium">Pengurangan via pesanan/eceran</span>
                </div>
            </div>

            <!-- 4. Arus Bersih Stok (Net Movement) -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Arus Bersih (Net)</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $netStockChange >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ $netStockChange >= 0 ? 'Surplus Masuk' : 'Defisit Pengurangan' }}
                    </span>
                </div>
                <div class="mt-2">
                    <div class="text-xl font-black font-mono {{ $netStockChange >= 0 ? 'text-slate-900' : 'text-rose-700' }}">
                        {{ $netStockChange >= 0 ? '+' : '' }}{{ number_format($netStockChange) }} <span class="text-xs font-sans font-normal text-slate-500">lbr</span>
                    </div>
                    <span class="text-[11px] text-slate-500 block mt-0.5 font-medium">Selisih masuk vs keluar periode ini</span>
                </div>
            </div>

        </div>

        <!-- Tabel Riwayat Mutasi (Desktop) -->
        <div class="rounded-2xl border border-slate-200/80 overflow-hidden">
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/90 border-b border-slate-200 text-slate-600 font-bold text-[11px] uppercase tracking-wider">
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Paket Voucher</th>
                            <th class="py-3 px-4">Jenis & Jumlah Mutasi</th>
                            <th class="py-3 px-4">Perubahan Stok Fisik</th>
                            <th class="py-3 px-4">Dilakukan Oleh</th>
                            <th class="py-3 px-4">Keterangan / No. Order</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($stockLogs as $log)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono text-slate-500 whitespace-nowrap">
                                {{ $log->created_at->translatedFormat('d M Y, H:i') }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                <div class="flex items-center gap-1.5">
                                    <span>{{ $log->package?->name ?? 'Paket Dihapus' }}</span>
                                    @if($log->package)
                                    <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">
                                        {{ $log->package->duration_info }}
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($log->quantity > 0)
                                    <span class="inline-flex items-center gap-1 font-mono font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/80 px-2.5 py-0.5 rounded-full text-xs">
                                        +{{ number_format($log->quantity) }} lbr ({{ $log->type === 'restore' ? 'Dipulihkan' : 'Restock Cetak' }})
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 font-mono font-bold text-rose-700 bg-rose-50 border border-rose-200/80 px-2.5 py-0.5 rounded-full text-xs">
                                        {{ number_format($log->quantity) }} lbr (Pesanan Keluar)
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono whitespace-nowrap">
                                <span class="text-slate-500">{{ number_format($log->stock_before) }}</span>
                                <span class="text-slate-400 mx-1">→</span>
                                <span class="font-extrabold text-slate-900">{{ number_format($log->stock_after) }} lbr</span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($log->user)
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-slate-900">{{ $log->user->name }}</span>
                                    <span class="text-[9px] px-2 py-0.5 rounded-full font-bold {{ $log->user->isSuperAdmin() ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                        {{ $log->user->isSuperAdmin() ? 'Super Admin' : 'Admin' }}
                                    </span>
                                </div>
                                @else
                                <span class="text-slate-400 italic">Sistem</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 max-w-xs truncate font-medium">
                                {{ $log->notes ?: '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400 font-medium">
                                Belum ada riwayat mutasi stok voucher yang sesuai dengan filter.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Adaptive Cards View -->
            <div class="md:hidden divide-y divide-slate-100">
                @forelse($stockLogs as $log)
                <div class="p-4 space-y-2.5 hover:bg-slate-50/50 transition-colors">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="font-extrabold text-slate-900 text-xs block">{{ $log->package?->name ?? 'Paket Dihapus' }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $log->created_at->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                        @if($log->quantity > 0)
                        <span class="inline-flex items-center gap-1 font-mono font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/80 px-2 py-0.5 rounded-full text-xs">
                            +{{ number_format($log->quantity) }} lbr
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 font-mono font-bold text-rose-700 bg-rose-50 border border-rose-200/80 px-2 py-0.5 rounded-full text-xs">
                            {{ number_format($log->quantity) }} lbr
                        </span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100">
                        <span class="text-slate-500 font-mono">
                            {{ number_format($log->stock_before) }} → <strong class="text-slate-900">{{ number_format($log->stock_after) }} lbr</strong>
                        </span>
                        <span class="text-slate-600 font-medium text-[11px]">
                            Oleh: {{ $log->user?->name ?? 'Sistem' }}
                        </span>
                    </div>

                    @if($log->notes)
                    <p class="text-[11px] text-slate-500 bg-slate-50 p-2 rounded-lg leading-relaxed">{{ $log->notes }}</p>
                    @endif
                </div>
                @empty
                <div class="p-6 text-center text-slate-400 text-xs font-medium">Belum ada riwayat mutasi stok voucher yang sesuai filter.</div>
                @endforelse
            </div>

            @if($stockLogs->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $stockLogs->links() }}
            </div>
            @endif
        </div>

    </div>

</div>

<!-- Modal Tambah Stok Voucher -->
<div id="addStockModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm transition-all">
    <div class="bg-white rounded-[28px] max-w-md w-full p-6 sm:p-7 shadow-floating border border-slate-200/80 space-y-5">
        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Tambah Stok Voucher Fisik</h3>
                <p class="text-xs text-slate-500 mt-0.5">Catat hasil cetak voucher baru ke inventaris.</p>
            </div>
            <button type="button" onclick="closeAddStockModal()" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-100 text-xs transition-colors">✕</button>
        </div>

        <form id="addStockForm" action="" method="POST" class="space-y-4">
            @csrf
            
            <div>
                <label for="modal_package_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Pilih Paket Voucher</label>
                <select id="modal_package_id" name="package_id" onchange="onPackageSelectChange(this.value)" class="w-full px-4 py-3 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                    @foreach($packages as $p)
                    <option value="{{ $p->id }}" data-name="{{ $p->name }}" data-stock="{{ $p->stock }}">
                        {{ $p->name }} (Stok saat ini: {{ number_format($p->stock) }} lbr)
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="stock_quantity" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Jumlah Tambahan (Lembar)</label>
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="setAddStockQty(20)" class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700">+20</button>
                        <button type="button" onclick="setAddStockQty(50)" class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700">+50</button>
                        <button type="button" onclick="setAddStockQty(100)" class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700">+100</button>
                        <button type="button" onclick="setAddStockQty(200)" class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700">+200</button>
                    </div>
                </div>
                <input type="number" id="stock_quantity" name="quantity" value="50" min="1" max="100000" required
                    class="w-full px-4 py-3 font-mono text-base font-extrabold bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div>
                <label for="stock_notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keterangan / Catatan</label>
                <input type="text" id="stock_notes" name="notes" placeholder="Misal: Hasil cetak voucher Batch 4 posko RT..."
                    class="w-full px-4 py-3 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                <span class="text-[11px] text-slate-400 mt-1 block">Catatan ini akan tersimpan bersama nama akun Anda ({{ auth()->user()->name }}).</span>
            </div>

            <div class="pt-3 flex justify-end gap-2.5 border-t border-slate-100">
                <button type="button" onclick="closeAddStockModal()" class="px-4 py-2 rounded-full text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-full bg-blue-600 text-white hover:bg-blue-700 font-bold text-xs shadow-sm shadow-blue-500/20 active:scale-[0.98] transition-all">
                    Simpan Penambahan Stok
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddStockModal(packageId = null, packageName = '', currentStock = 0) {
        const select = document.getElementById('modal_package_id');
        if (packageId) {
            select.value = packageId;
        }
        updateFormAction();
        document.getElementById('addStockModal').classList.remove('hidden');
    }

    function closeAddStockModal() {
        document.getElementById('addStockModal').classList.add('hidden');
    }

    function onPackageSelectChange(val) {
        updateFormAction();
    }

    function updateFormAction() {
        const select = document.getElementById('modal_package_id');
        const packageId = select.value;
        document.getElementById('addStockForm').action = "/packages/" + packageId + "/add-stock";
    }

    function setAddStockQty(qty) {
        document.getElementById('stock_quantity').value = qty;
    }

    function switchPackageTab(tab) {
        const cards = document.querySelectorAll('.package-card');
        const btnActive = document.getElementById('tabBtnActive');
        const btnInactive = document.getElementById('tabBtnInactive');
        const btnAll = document.getElementById('tabBtnAll');
        const desc = document.getElementById('tabStatusDescription');
        const emptyInactiveBox = document.getElementById('emptyInactiveBox');
        const emptyActiveBox = document.getElementById('emptyActiveBox');
        const packagesGrid = document.getElementById('packagesGrid');
        const filterTabInput = document.getElementById('formFilterTab');

        if (filterTabInput) {
            filterTabInput.value = tab;
        }

        const baseClasses = 'flex items-center gap-2 px-4 py-2 rounded-xl transition-all';
        const activeBtnClass = `${baseClasses} font-bold shadow-xs bg-white text-blue-700`;
        const inactiveBtnClass = `${baseClasses} font-medium text-slate-600 hover:text-slate-900`;
        const allInactiveBtnClass = `flex items-center gap-2 px-3.5 py-2 rounded-xl transition-all font-medium text-slate-500 hover:text-slate-900`;
        const allActiveBtnClass = `flex items-center gap-2 px-3.5 py-2 rounded-xl transition-all font-bold shadow-xs bg-white text-blue-700`;

        if (btnActive) btnActive.className = (tab === 'active') ? activeBtnClass : inactiveBtnClass;
        if (btnInactive) btnInactive.className = (tab === 'inactive') ? activeBtnClass : inactiveBtnClass;
        if (btnAll) btnAll.className = (tab === 'all') ? allActiveBtnClass : allInactiveBtnClass;

        let visibleCount = 0;
        let activeCount = 0;
        let inactiveCount = 0;

        cards.forEach(card => {
            const isActive = card.getAttribute('data-is-active') === '1';
            if (isActive) activeCount++;
            else inactiveCount++;

            if (tab === 'active') {
                if (isActive) {
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                }
            } else if (tab === 'inactive') {
                if (!isActive) {
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                }
            } else {
                card.classList.remove('hidden');
                visibleCount++;
            }
        });

        if (desc) {
            if (tab === 'active') {
                desc.textContent = `Menampilkan ${activeCount} paket yang aktif melayani pembelian.`;
            } else if (tab === 'inactive') {
                desc.textContent = `Menampilkan ${inactiveCount} paket yang dinonaktifkan (diarsipkan).`;
            } else {
                desc.textContent = `Menampilkan seluruh ${activeCount + inactiveCount} paket voucher.`;
            }
        }

        if (emptyInactiveBox) {
            if (tab === 'inactive' && inactiveCount === 0) {
                emptyInactiveBox.classList.remove('hidden');
            } else {
                emptyInactiveBox.classList.add('hidden');
            }
        }

        if (emptyActiveBox) {
            if (tab === 'active' && activeCount === 0) {
                emptyActiveBox.classList.remove('hidden');
            } else {
                emptyActiveBox.classList.add('hidden');
            }
        }

        if (packagesGrid) {
            if (visibleCount === 0) {
                packagesGrid.classList.add('hidden');
            } else {
                packagesGrid.classList.remove('hidden');
            }
        }

        try {
            const url = new URL(window.location.href);
            if (url.searchParams.get('tab') !== tab) {
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url.toString());
            }
        } catch (e) {
            // Ignore if URL object unavailable
        }
    }

    // Initialize default action
    document.addEventListener('DOMContentLoaded', function() {
        updateFormAction();
        const urlParams = new URLSearchParams(window.location.search);
        const currentTab = urlParams.get('tab') || '{{ $selectedTab ?? "active" }}';
        switchPackageTab(currentTab);
    });
</script>
@endsection
