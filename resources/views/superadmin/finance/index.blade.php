@extends('layouts.app')

@section('title', 'Buku Kas & Analisis Laba')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900">Buku Kas & Analisis Laba</h1>
            <p class="text-xs text-slate-500 mt-1">Laporan omzet voucher, pemasukan kas lain, beban pengeluaran, dan laba operasional {{ $globalAppName ?? 'usaha' }}.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('finance.print', request()->query()) }}" target="_blank"
               class="touch-target inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-sm shadow-slate-900/20 active:scale-[0.98] transition-all">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak PDF Laporan
            </a>
            <a href="{{ route('finance.expenses') }}"
               class="touch-target inline-flex items-center gap-1.5 px-4 py-2.5 rounded-full border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all shadow-xs">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Kelola Pengeluaran
            </a>
            <a href="{{ route('finance.incomes') }}"
               class="touch-target inline-flex items-center gap-1.5 px-4 py-2.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-sm shadow-blue-500/20 active:scale-[0.98]">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                + Pemasukan Manual
            </a>
        </div>
    </div>

    <!-- Filter Bar (Bulan/Tahun ATAU Rentang Tanggal Khusus, Instant Apply on Change) -->
    <div class="bg-white p-5 rounded-[26px] border border-slate-200/70 shadow-[0_4px_20px_rgb(0,0,0,0.03)] space-y-4">
        <form id="financeFilterForm" method="GET" action="{{ route('finance.index') }}" class="space-y-4">
            
            <!-- Mode Periode Toggle Tabs -->
            <div class="flex items-center justify-between flex-wrap gap-2 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Mode Periode:</span>
                    <div class="inline-flex p-1 bg-slate-100 rounded-xl text-xs">
                        <label class="cursor-pointer px-3 py-1.5 rounded-lg font-bold transition-all flex items-center gap-1.5 {{ $rangeType === 'month' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                            <input type="radio" name="range_type" value="month" {{ $rangeType === 'month' ? 'checked' : '' }} onchange="toggleRangeMode('month')" class="sr-only">
                            📅 Per Bulan
                        </label>
                        <label class="cursor-pointer px-3 py-1.5 rounded-lg font-bold transition-all flex items-center gap-1.5 {{ $rangeType === 'custom' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                            <input type="radio" name="range_type" value="custom" {{ $rangeType === 'custom' ? 'checked' : '' }} onchange="toggleRangeMode('custom')" class="sr-only">
                            📆 Rentang Tanggal
                        </label>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-xs">
                    <span class="text-slate-400 font-medium">Periode Aktif:</span>
                    <span class="font-bold text-slate-900 bg-blue-50 px-3 py-1 rounded-full border border-blue-200/60">{{ $periodLabel }}</span>
                </div>
            </div>

            <!-- Dynamic Inputs Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 items-end">
                
                <!-- Inputs Mode: Per Bulan -->
                <div id="monthInputBox" class="{{ $rangeType === 'custom' ? 'hidden' : 'block' }}">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Bulan</label>
                    <select name="month" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        <option value="all" {{ $selectedMonth === 'all' ? 'selected' : '' }}>Semua Bulan</option>
                        @for($m = 1; $m <= 12; $m++)
                            @php $val = sprintf('%02d', $m); @endphp
                            <option value="{{ $val }}" {{ $selectedMonth == $val ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div id="yearInputBox" class="{{ $rangeType === 'custom' ? 'hidden' : 'block' }}">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tahun</label>
                    <select name="year" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Inputs Mode: Rentang Tanggal Khusus -->
                <div id="startDateBox" class="{{ $rangeType === 'custom' ? 'block' : 'hidden' }}">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" onchange="autoSubmitIfRangeValid()" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>

                <div id="endDateBox" class="{{ $rangeType === 'custom' ? 'block' : 'hidden' }}">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" onchange="autoSubmitIfRangeValid()" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>

                <!-- Tenant Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tenant / Warung</label>
                    <select name="tenant_id" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        <option value="">Semua Warung</option>
                        @foreach($tenants as $t)
                            <option value="{{ $t->id }}" {{ $selectedTenant == $t->id ? 'selected' : '' }}>{{ $t->shop_name ?: $t->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Package Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Paket Voucher</label>
                    <select name="package_id" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        <option value="">Semua Paket</option>
                        @foreach($packages as $p)
                            <option value="{{ $p->id }}" {{ $selectedPackage == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Reset Filter Action -->
                <div>
                    <a href="{{ route('finance.index') }}" class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 rounded-2xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 border border-slate-200/60 touch-target">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                        Reset Filter
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- 3 Executive Financial Blocks: Total Kas Masuk (Voucher + Kas Lain Disatukan) + Beban Pengeluaran + Laba Bersih -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- 1. TOTAL KAS MASUK (Gabungan Kas Voucher & Pemasukan Lain - Mengambil 2 Kolom di Desktop) -->
        <div class="sm:col-span-2 bg-white p-5 sm:p-6 rounded-[28px] border border-slate-200/70 shadow-[0_4px_20px_rgb(0,0,0,0.03)] flex flex-col justify-between space-y-3">
            <div class="space-y-1">
                <div class="flex items-center justify-between text-slate-400">
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-700">Total Kas Masuk (Pemasukan)</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/80">Voucher + Kas Lain</span>
                    </div>
                    <span class="w-9 h-9 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold font-mono text-slate-900 tracking-tight">
                    Rp {{ number_format($totalCashIn, 0, ',', '.') }}
                </div>
            </div>

            <!-- Rincian Pemasukan Disatukan (3 Sub-panel Sejajar) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-2 border-t border-slate-100">
                <!-- Sub-panel A: Kas Masuk Voucher -->
                <div class="p-3 rounded-2xl bg-blue-50/50 border border-blue-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between text-[11px] font-bold text-blue-900 mb-1">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            <span>Kas Voucher</span>
                        </span>
                        <span class="text-[10px] text-blue-600 font-mono font-medium">{{ number_format($totalVouchersSold) }} lbr</span>
                    </div>
                    <div class="text-base font-extrabold font-mono text-blue-950">
                        Rp {{ number_format($netOwnerRevenue, 0, ',', '.') }}
                    </div>
                    <div class="text-[10px] text-blue-700/80 mt-0.5">
                        Nilai Fisik: Rp {{ number_format($grossTurnover, 0, ',', '.') }}
                    </div>
                </div>

                <!-- Sub-panel B: Kas Masuk Saldo Merchant -->
                <div class="p-3 rounded-2xl bg-emerald-50/50 border border-emerald-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between text-[11px] font-bold text-emerald-900 mb-1">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            <span>Kas Setoran Saldo</span>
                        </span>
                        <span class="text-[10px] text-emerald-600 font-semibold">Cetak Mandiri</span>
                    </div>
                    <div class="text-base font-extrabold font-mono text-emerald-950">
                        Rp {{ number_format($depositRevenue, 0, ',', '.') }}
                    </div>
                    <div class="text-[10px] text-emerald-700/80 mt-0.5 truncate" title="Untung Mitra: Rp {{ number_format($depositResellerProfit, 0, ',', '.') }}">
                        Untung Mitra: Rp {{ number_format($depositResellerProfit, 0, ',', '.') }}
                    </div>
                </div>

                <!-- Sub-panel C: Pemasukan Kas Lain -->
                <div class="p-3 rounded-2xl bg-sky-50/50 border border-sky-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between text-[11px] font-bold text-sky-900 mb-1">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-sky-600"></span>
                            <span>Pemasukan Lain</span>
                        </span>
                        <span class="text-[10px] text-sky-600 font-medium">Non-Voucher</span>
                    </div>
                    <div class="text-base font-extrabold font-mono text-sky-950">
                        Rp {{ number_format($otherIncome, 0, ',', '.') }}
                    </div>
                    <div class="text-[10px] text-sky-700/80 mt-0.5 truncate" title="Iuran warga, pasang baru, sewa tiang non-voucher">
                        Iuran, pasang baru, sewa
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Total Beban Pengeluaran Kas (1 Kolom) -->
        <div class="bg-white p-5 sm:p-6 rounded-[28px] border border-slate-200/70 shadow-[0_4px_20px_rgb(0,0,0,0.03)] flex flex-col justify-between space-y-2">
            <div>
                <div class="flex items-center justify-between text-slate-400">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Beban Pengeluaran Kas</span>
                    <span class="w-9 h-9 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-extrabold font-mono text-slate-900 tracking-tight mt-1">
                    Rp {{ number_format($totalExpenses, 0, ',', '.') }}
                </div>
            </div>
            <div class="pt-2 border-t border-slate-100">
                <p class="text-xs text-slate-500 font-medium">Bandwidth ISP, listrik, perbaikan tiang & kabel</p>
            </div>
        </div>

        <!-- 3. Laba Bersih RT/RW Net (1 Kolom) -->
        <div class="bg-white p-5 sm:p-6 rounded-[28px] border border-slate-200/70 shadow-[0_4px_20px_rgb(0,0,0,0.03)] flex flex-col justify-between space-y-2">
            <div>
                <div class="flex items-center justify-between text-slate-400">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Laba Bersih {{ $globalAppName ?? 'Usaha' }}</span>
                    <span class="w-9 h-9 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg>
                    </span>
                </div>
                <div class="flex items-baseline justify-between gap-2 flex-wrap mt-1">
                    <div class="text-2xl font-extrabold font-mono {{ $netProfit >= 0 ? 'text-slate-900' : 'text-rose-600' }} tracking-tight">
                        Rp {{ number_format($netProfit, 0, ',', '.') }}
                    </div>
                    @if($netProfit >= 0)
                    <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200/60">Surplus</span>
                    @else
                    <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200/60">Defisit</span>
                    @endif
                </div>
            </div>
            <div class="text-[11px] text-slate-500 font-medium flex items-center justify-between pt-2 border-t border-slate-100 flex-wrap gap-1">
                <span>Total Masuk: Rp {{ number_format($totalCashIn, 0, ',', '.') }}</span>
                <span>Beban: Rp {{ number_format($totalExpenses, 0, ',', '.') }}</span>
            </div>
        </div>

    </div>

    <!-- ========================================== -->
    <!-- SIMULASI ARUS KAS & PREDIKSI PELUNASAN TEMPO -->
    <!-- ========================================== -->
    <div class="bg-white p-5 sm:p-6 rounded-[28px] border border-slate-200/70 shadow-[0_4px_20px_rgb(0,0,0,0.03)] space-y-4">
        
        <!-- Header Kartu -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div class="flex items-start sm:items-center gap-3">
                <span class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-base font-extrabold text-slate-900 tracking-tight">
                            @if($selectedTenantUser)
                                Estimasi Pelunasan: {{ $selectedTenantUser->shop_name ?: $selectedTenantUser->name }}
                            @else
                                Simulasi Kas Masuk & Proyeksi Laba Bersih
                            @endif
                        </h2>
                        @if($selectedTenantUser)
                            <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200/60">
                                Mitra Warung
                            </span>
                        @else
                            <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200/60">
                                Akumulasi Semua Warung & Kas Lain
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        @if($selectedTenantUser)
                            Perhitungan setoran kas masuk jika seluruh voucher tempo yang dibawa mitra warung ini disetor lunas.
                        @else
                            Perkiraan arus kas masuk dan laba bersih jika seluruh tagihan voucher tempo warung disetorkan lunas.
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0 text-xs">
                <span class="text-slate-400 font-medium">Periode:</span>
                <span class="font-bold text-slate-900 bg-slate-50 px-3 py-1 rounded-full border border-slate-200">
                    {{ $periodLabel }}
                </span>
            </div>
        </div>

        @if($selectedTenantUser)
            <!-- ============================================== -->
            <!-- TAMPILAN KHUSUS FILTER MITRA WARUNG TERTENTU   -->
            <!-- ============================================== -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- 1. Sudah Disetor (Lunas) -->
                <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-200/70 space-y-1">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Sudah Disetor (Lunas)</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">Lunas</span>
                    </div>
                    <div class="text-xl font-extrabold font-mono text-slate-900">
                        Rp {{ number_format($netOwnerRevenue, 0, ',', '.') }}
                    </div>
                    <p class="text-[11px] text-slate-500 font-medium">{{ number_format($totalVouchersSold) }} lembar voucher</p>
                </div>

                <!-- 2. Belum Disetor (Tempo) -->
                <div class="p-4 rounded-2xl bg-amber-50/50 border border-amber-200/70 space-y-1">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800">Masih Belum Disetor (Tempo)</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-800 border border-amber-200">Piutang</span>
                    </div>
                    <div class="text-xl font-extrabold font-mono text-amber-900">
                        Rp {{ number_format($pendingOwnerAmount + $pendingDepositAmount, 0, ',', '.') }}
                    </div>
                    <p class="text-[11px] text-amber-700/90 font-medium">{{ number_format($pendingVouchersCount) }} lbr voucher{{ $pendingDepositAmount > 0 ? ' + Saldo Rp ' . number_format($pendingDepositAmount, 0, ',', '.') : '' }}</p>
                </div>

                <!-- 3. Perkiraan Total Pembayaran Warung (Jika Lunas Semua) -->
                <div class="p-4 rounded-2xl bg-blue-50/50 border border-blue-200/70 space-y-1">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900">Perkiraan Total Setoran (Jika Lunas)</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-800 border border-blue-200">100% Lunas</span>
                    </div>
                    <div class="text-xl font-extrabold font-mono text-blue-950">
                        Rp {{ number_format($projectedVoucherRevenue, 0, ',', '.') }}
                    </div>
                    <p class="text-[11px] text-blue-700/90 font-medium">{{ number_format($projectedVouchersSold) }} lbr total potensi setor</p>
                </div>
            </div>

            <!-- Status Pelunasan Warung -->
            @php
                $realizationPercent = $projectedVoucherRevenue > 0 ? min(100, round(($netOwnerRevenue / $projectedVoucherRevenue) * 100, 1)) : 100;
            @endphp
            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 text-xs flex items-center justify-between flex-wrap gap-2">
                <span class="text-slate-600 font-medium">
                    Tingkat Pelunasan Warung: <strong class="text-slate-900 font-mono">{{ $realizationPercent }}%</strong>
                    (Rp {{ number_format($netOwnerRevenue, 0, ',', '.') }} dari total Rp {{ number_format($projectedVoucherRevenue, 0, ',', '.') }})
                </span>
                @if($pendingOwnerAmount > 0)
                    <span class="text-[11px] text-amber-700 font-bold bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full">
                        Sisa tagihan tempo: Rp {{ number_format($pendingOwnerAmount, 0, ',', '.') }}
                    </span>
                @else
                    <span class="text-[11px] text-emerald-700 font-bold bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                        Seluruh pesanan warung ini sudah lunas
                    </span>
                @endif
            </div>

        @else

            <!-- ============================================== -->
            <!-- TAMPILAN GLOBAL (SEMUA WARUNG + KAS LAIN)      -->
            <!-- ============================================== -->
            <!-- Rincian Komponen Arus Kas Masuk (5 Kolom Transparan) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                
                <!-- Komponen A: Setoran Voucher Lunas -->
                <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-200/70 space-y-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block">1. Kas Voucher Lunas</span>
                    <div class="text-xl font-extrabold font-mono text-slate-900">
                        Rp {{ number_format($netOwnerRevenue, 0, ',', '.') }}
                    </div>
                    <p class="text-[11px] text-slate-500">{{ number_format($totalVouchersSold) }} lbr disetor</p>
                </div>

                <!-- Komponen B: Tagihan Tempo Warung (Piutang) -->
                <div class="p-4 rounded-2xl bg-amber-50/50 border border-amber-200/70 space-y-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800 block">2. Tagihan Tempo Warung</span>
                    <div class="text-xl font-extrabold font-mono text-amber-900">
                        +Rp {{ number_format($pendingOwnerAmount + $pendingDepositAmount, 0, ',', '.') }}
                    </div>
                    <p class="text-[11px] text-amber-700/90">{{ number_format($pendingVouchersCount) }} lbr{{ $pendingDepositAmount > 0 ? ' + Saldo Rp ' . number_format($pendingDepositAmount, 0, ',', '.') : '' }}</p>
                </div>

                <!-- Komponen C: Pemasukan Kas Lain -->
                <div class="p-4 rounded-2xl bg-sky-50/50 border border-sky-200/70 space-y-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-sky-800 block">3. Pemasukan Kas Lain</span>
                    <div class="text-xl font-extrabold font-mono text-sky-900">
                        +Rp {{ number_format($otherIncome, 0, ',', '.') }}
                    </div>
                    <p class="text-[11px] text-sky-700/90">{{ $otherIncomesCount }} transaksi non-voucher</p>
                </div>

                <!-- Komponen D: Setoran Saldo Cetak Mandiri -->
                <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-200/70 space-y-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800 block">Kas Saldo Cetak Mandiri</span>
                    <div class="text-xl font-extrabold font-mono text-emerald-950">
                        Rp {{ number_format($depositRevenue, 0, ',', '.') }}
                    </div>
                    <p class="text-[11px] text-emerald-700/90">Saldo Cetak Mandiri</p>
                </div>

                <!-- Komponen E: Proyeksi Total Kas Masuk -->
                <div class="p-4 rounded-2xl bg-blue-50/50 border border-blue-200/70 space-y-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900 block">Proyeksi Total Kas Masuk</span>
                    <div class="text-xl font-extrabold font-mono text-blue-950">
                        Rp {{ number_format($projectedTotalCashIn, 0, ',', '.') }}
                    </div>
                    <p class="text-[11px] text-blue-700/90">Akumulasi Seluruh Komponen</p>
                </div>
            </div>

            <!-- Baris Kalkulasi Proyeksi Laba Bersih yang Jelas & Gamblang -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-4 flex-wrap">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Proyeksi Total Masuk</span>
                        <span class="font-mono font-extrabold text-slate-900 text-sm">Rp {{ number_format($projectedTotalCashIn, 0, ',', '.') }}</span>
                    </div>
                    <span class="text-slate-400 font-bold text-base hidden sm:inline">-</span>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Beban Pengeluaran Kas</span>
                        <span class="font-mono font-extrabold text-rose-600 text-sm">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</span>
                    </div>
                    <span class="text-slate-400 font-bold text-base hidden sm:inline">=</span>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Proyeksi Laba Bersih Akhir</span>
                        <span class="font-mono font-extrabold text-sm {{ $projectedNetProfit >= 0 ? 'text-indigo-900' : 'text-rose-600' }}">
                            Rp {{ number_format($projectedNetProfit, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
                <div>
                    @if($projectedNetProfit >= 0)
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        Surplus Proyeksi
                    </span>
                    @else
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        Defisit Proyeksi
                    </span>
                    @endif
                </div>
            </div>

        @endif

    </div>

    <!-- ========================================== -->
    <!-- SEKSI 1: PEMASUKAN PENJUALAN VOUCHER       -->
    <!-- ========================================== -->
    <div class="space-y-6">
        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    Seksi 1: Pemasukan Penjualan Voucher Hotspot
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Setoran tunai/transfer dari reseller warung atas penjualan lembar voucher fisik.</p>
            </div>
            <span class="text-xs font-mono font-extrabold text-blue-700 bg-blue-50 border border-blue-200/60 px-3 py-1 rounded-full">
                Rp {{ number_format($netOwnerRevenue, 0, ',', '.') }}
            </span>
        </div>

        <!-- HIGHLIGHT: PAKET TERLARIS & MITRA TERLARIS (OPSI 1) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- KARTU 1: PAKET VOUCHER PALING LAKU -->
            <div class="bg-white rounded-[24px] border border-slate-200/80 p-5 shadow-[0_4px_20px_rgb(0,0,0,0.03)] flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800">
                                    Paket Voucher Paling Laku
                                </h3>
                                <p class="text-[11px] text-slate-400">
                                    @if($selectedTenantUser)
                                        Khusus {{ $selectedTenantUser->shop_name ?: $selectedTenantUser->name }} ({{ $periodLabel }})
                                    @else
                                        Volume tertinggi periode {{ $periodLabel }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        @if($bestSellingPackage)
                            <span class="text-[10px] font-extrabold uppercase px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                                #1 Terlaris
                            </span>
                        @endif
                    </div>

                    @if($bestSellingPackage)
                        @php
                            $packageShare = $totalVouchersSold > 0 ? round((($bestSellingPackage->total_quantity ?? 0) / $totalVouchersSold) * 100, 1) : 0;
                        @endphp
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 mb-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="text-base font-extrabold text-slate-900 leading-tight">
                                        {{ $bestSellingPackage->name }}
                                    </h4>
                                    <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-500 flex-wrap">
                                        <span>Harga Jual: <strong class="font-mono text-slate-700">Rp {{ number_format($bestSellingPackage->printed_price, 0, ',', '.') }}</strong></span>
                                        <span>•</span>
                                        <span>Setor Owner: <strong class="font-mono text-slate-700">Rp {{ number_format($bestSellingPackage->owner_price, 0, ',', '.') }}</strong></span>
                                    </div>
                                </div>
                                <span class="text-[11px] font-mono font-bold px-2 py-0.5 rounded-md bg-blue-100/70 text-blue-800 shrink-0">
                                    {{ $packageShare }}% pangsa
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-200/60">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Terjual</span>
                                    <span class="text-base font-mono font-extrabold text-slate-900">
                                        {{ number_format($bestSellingPackage->total_quantity ?? 0, 0, ',', '.') }}
                                        <span class="text-xs font-medium text-slate-500">lembar</span>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Setor Kas Masuk</span>
                                    <span class="text-base font-mono font-extrabold text-blue-700">
                                        Rp {{ number_format($bestSellingPackage->total_owner_amount ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Runner-up Top Packages -->
                        @if($topPackagesList->count() > 1)
                            <div class="space-y-1.5 pt-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">
                                    Peringkat Paket Lainnya
                                </span>
                                @foreach($topPackagesList->skip(1) as $pkg)
                                    <div class="flex items-center justify-between text-xs py-1.5 px-3 rounded-xl bg-slate-50/60 hover:bg-slate-50 border border-slate-100 transition-colors">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="w-5 h-5 rounded-md bg-slate-200 text-slate-700 text-[10px] font-extrabold flex items-center justify-center shrink-0">
                                                #{{ $loop->iteration + 1 }}
                                            </span>
                                            <span class="font-bold text-slate-800 truncate">{{ $pkg->name }}</span>
                                        </div>
                                        <div class="flex items-center gap-3 shrink-0 font-mono text-[11px]">
                                            <span class="font-bold text-slate-700">{{ number_format($pkg->total_quantity, 0, ',', '.') }} lbr</span>
                                            <span class="text-slate-500">Rp {{ number_format($pkg->total_owner_amount, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="py-8 text-center text-xs text-slate-400 rounded-2xl bg-slate-50 border border-dashed border-slate-200">
                            Belum ada lembar voucher terjual pada periode ini.
                        </div>
                    @endif
                </div>
            </div>

            <!-- KARTU 2: WARUNG PENJUALAN TERBANYAK -->
            <div class="bg-white rounded-[24px] border border-slate-200/80 p-5 shadow-[0_4px_20px_rgb(0,0,0,0.03)] flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800">
                                    @if($selectedTenantUser)
                                        Performa Mitra Warung Ini
                                    @else
                                        Warung Penjualan Terbanyak
                                    @endif
                                </h3>
                                <p class="text-[11px] text-slate-400">
                                    @if($selectedTenantUser)
                                        Evaluasi kontribusi reseller terpilih ({{ $periodLabel }})
                                    @else
                                        Mitra paling aktif menyetor lunas di {{ $periodLabel }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        @if($selectedTenantUser)
                            @if($selectedTenantRank)
                                <span class="text-[10px] font-extrabold uppercase px-2.5 py-1 rounded-full {{ $selectedTenantRank == 1 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }} shrink-0">
                                    Peringkat #{{ $selectedTenantRank }}
                                </span>
                            @else
                                <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 shrink-0">
                                    Belum Ada Setoran
                                </span>
                            @endif
                        @elseif($topPerformingTenant)
                            <span class="text-[10px] font-extrabold uppercase px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 shrink-0">
                                #1 Top Reseller
                            </span>
                        @endif
                    </div>

                    @if($selectedTenantUser)
                        <!-- Tampilan Jika Sedang Memfilter Warung Tertentu -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 mb-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="text-base font-extrabold text-slate-900 leading-tight">
                                        {{ $selectedTenantUser->shop_name ?: $selectedTenantUser->name }}
                                    </h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        Pemilik: {{ $selectedTenantUser->name }} • {{ $selectedTenantUser->phone_number ?: 'Tanpa nomor' }}
                                    </p>
                                </div>
                                @if($selectedTenantRank)
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 border border-amber-200 shrink-0">
                                        Posisi #{{ $selectedTenantRank }} dari {{ $sortedTenants->count() }} warung
                                    </span>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-200/60">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Voucher Disetor Lunas</span>
                                    <span class="text-base font-mono font-extrabold text-slate-900">
                                        {{ number_format($totalVouchersSold, 0, ',', '.') }}
                                        <span class="text-xs font-medium text-slate-500">lembar</span>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Kas Masuk ke Owner</span>
                                    <span class="text-base font-mono font-extrabold text-blue-700">
                                        Rp {{ number_format($netOwnerRevenue, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if($topPerformingTenant && $topPerformingTenant->id !== $selectedTenantUser->id)
                            <div class="p-3 rounded-xl bg-blue-50/50 border border-blue-100 text-xs text-slate-600 flex items-center justify-between gap-3">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-800 block">Pemuncak Periode Ini:</span>
                                    <span class="font-bold text-slate-900">{{ $topPerformingTenant->shop_name ?: $topPerformingTenant->name }}</span>
                                </div>
                                <span class="font-mono text-[11px] font-extrabold text-blue-800">
                                    {{ number_format($topPerformingTenant->total_vouchers, 0, ',', '.') }} lembar (Rp {{ number_format($topPerformingTenant->total_settled_amount, 0, ',', '.') }})
                                </span>
                            </div>
                        @endif

                    @elseif($topPerformingTenant)
                        <!-- Tampilan Global (Semua Warung) -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 mb-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="text-base font-extrabold text-slate-900 leading-tight">
                                        {{ $topPerformingTenant->shop_name ?: $topPerformingTenant->name }}
                                    </h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        Pemilik: {{ $topPerformingTenant->name }} • {{ $topPerformingTenant->address ?: 'Alamat belum diatur' }}
                                    </p>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 border border-amber-200 shrink-0">
                                    {{ $topPerformingTenant->orders_count }}x setoran
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-200/60">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Disetor Lunas</span>
                                    <span class="text-base font-mono font-extrabold text-slate-900">
                                        {{ number_format($topPerformingTenant->total_vouchers, 0, ',', '.') }}
                                        <span class="text-xs font-medium text-slate-500">lembar</span>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Kas ke Owner</span>
                                    <span class="text-base font-mono font-extrabold text-blue-700">
                                        Rp {{ number_format($topPerformingTenant->total_settled_amount, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Runner-up Top Resellers -->
                        @if($topTenantsList->count() > 1)
                            <div class="space-y-1.5 pt-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">
                                    Peringkat Mitra Lainnya
                                </span>
                                @foreach($topTenantsList->skip(1) as $tenant)
                                    <div class="flex items-center justify-between text-xs py-1.5 px-3 rounded-xl bg-slate-50/60 hover:bg-slate-50 border border-slate-100 transition-colors">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="w-5 h-5 rounded-md bg-slate-200 text-slate-700 text-[10px] font-extrabold flex items-center justify-center shrink-0">
                                                #{{ $loop->iteration + 1 }}
                                            </span>
                                            <span class="font-bold text-slate-800 truncate">{{ $tenant->shop_name ?: $tenant->name }}</span>
                                        </div>
                                        <div class="flex items-center gap-3 shrink-0 font-mono text-[11px]">
                                            <span class="font-bold text-slate-700">{{ number_format($tenant->total_vouchers, 0, ',', '.') }} lbr</span>
                                            <span class="text-slate-500">Rp {{ number_format($tenant->total_settled_amount, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="py-8 text-center text-xs text-slate-400 rounded-2xl bg-slate-50 border border-dashed border-slate-200">
                            Belum ada mitra yang menyetorkan voucher lunas pada periode ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 1.A. Rekap Penjualan per Paket Voucher (Adaptive Table & Mobile Cards) -->
        <div class="bg-white rounded-[28px] border border-slate-200/70 overflow-hidden shadow-[0_4px_20px_rgb(0,0,0,0.03)]">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h3 class="text-sm font-extrabold text-slate-900">Rekap Penjualan per Paket Voucher</h3>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">
                        Diurutkan dari Terlaris
                    </span>
                    @if($selectedTenantUser)
                        <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                            Mitra: {{ $selectedTenantUser->shop_name ?: $selectedTenantUser->name }}
                        </span>
                    @else
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                            Semua Mitra Warung & Eceran
                        </span>
                    @endif
                </div>
                <span class="text-xs text-slate-400 font-medium">Hanya paket aktif terjual di periode ini</span>
            </div>

            <!-- Desktop View (Tabel Lebar) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/70 text-slate-600 font-bold text-[11px] uppercase tracking-wider">
                            <th class="py-3.5 px-5">Nama Paket</th>
                            <th class="py-3.5 px-5">Harga Tertera</th>
                            <th class="py-3.5 px-5">Harga Setor Owner</th>
                            <th class="py-3.5 px-5">Untung / Lembar</th>
                            <th class="py-3.5 px-5 text-center">Lembar Terjual</th>
                            <th class="py-3.5 px-5 text-right">Total Setor Kas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($packagesBreakdown as $pb)
                        @php $qty = $pb->total_quantity ?? 0; @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3.5 px-5 font-bold text-slate-900">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-md {{ $loop->iteration == 1 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }} text-[10px] font-extrabold flex items-center justify-center shrink-0">
                                        #{{ $loop->iteration }}
                                    </span>
                                    <span>{{ $pb->name }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-5 font-mono text-slate-600">Rp {{ number_format($pb->printed_price, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-5 font-mono font-bold text-slate-900">Rp {{ number_format($pb->owner_price, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-5">
                                <span class="font-mono text-blue-700 bg-blue-50 border border-blue-200/60 font-bold px-2.5 py-0.5 rounded-full text-xs">
                                    +Rp {{ number_format($pb->reseller_profit, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 font-mono text-center font-bold text-slate-900">{{ number_format($qty) }}</td>
                            <td class="py-3.5 px-5 font-mono text-right font-extrabold text-slate-900">Rp {{ number_format($pb->total_owner_amount ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400 font-medium">Belum ada paket voucher yang terjual pada periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($packagesBreakdown->count() > 0)
                    <tfoot class="bg-slate-50 border-t-2 border-slate-200 text-xs font-extrabold text-slate-900">
                        <tr>
                            <td colspan="4" class="py-3.5 px-5 text-slate-700 uppercase tracking-wider">
                                TOTAL KESELURUHAN @if($selectedTenantUser) <span class="text-blue-600 font-bold">({{ $selectedTenantUser->shop_name ?: $selectedTenantUser->name }})</span> @endif
                            </td>
                            <td class="py-3.5 px-5 text-center font-mono font-extrabold text-slate-900">
                                {{ number_format($packagesBreakdown->sum('total_quantity')) }} lbr
                            </td>
                            <td class="py-3.5 px-5 text-right font-mono font-extrabold text-blue-900 text-sm">
                                Rp {{ number_format($packagesBreakdown->sum('total_owner_amount'), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            <!-- Mobile View (Adaptive Stacked Cards: Tanpa Terpotong pada HP) -->
            <div class="md:hidden divide-y divide-slate-100">
                @forelse($packagesBreakdown as $pb)
                @php $qty = $pb->total_quantity ?? 0; @endphp
                <div class="p-4 space-y-2.5 hover:bg-slate-50/50 transition-colors">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="font-extrabold text-slate-900 text-sm flex items-center gap-1.5">
                                <span class="w-4 h-4 rounded text-[9px] font-extrabold {{ $loop->iteration == 1 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center">
                                    #{{ $loop->iteration }}
                                </span>
                                {{ $pb->name }}
                            </span>
                            <span class="text-[11px] text-slate-500">{{ $pb->duration_info }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-slate-400 block uppercase font-bold">Total Setor Kas</span>
                            <span class="font-mono font-extrabold text-slate-900 text-sm">
                                Rp {{ number_format($pb->total_owner_amount ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 p-2.5 bg-slate-50 rounded-xl text-xs">
                        <div>
                            <span class="text-[10px] text-slate-400 block">Fisik</span>
                            <span class="font-mono font-semibold text-slate-700">Rp {{ number_format($pb->printed_price, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Setor</span>
                            <span class="font-mono font-bold text-slate-900">Rp {{ number_format($pb->owner_price, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Terjual</span>
                            <span class="font-mono font-extrabold text-blue-700">{{ number_format($qty) }} lbr</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-slate-400 text-xs">Belum ada paket voucher yang terjual pada periode ini.</div>
                @endforelse

                @if($packagesBreakdown->count() > 0)
                <div class="p-4 bg-slate-50/95 border-t-2 border-slate-200 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Total Terjual</span>
                        <span class="font-mono font-extrabold text-slate-900 text-sm">{{ number_format($packagesBreakdown->sum('total_quantity')) }} lbr</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Total Setor Kas</span>
                        <span class="font-mono font-extrabold text-blue-900 text-sm">
                            Rp {{ number_format($packagesBreakdown->sum('total_owner_amount'), 0, ',', '.') }}
                        </span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- 1.B. Transaksi Penjualan Voucher Terakhir (Adaptive Table & Mobile Cards) -->
        <div class="bg-white rounded-[28px] border border-slate-200/70 overflow-hidden shadow-[0_4px_20px_rgb(0,0,0,0.03)]">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Transaksi Voucher Terbaru (Lunas)</h3>
                    <p class="text-[11px] text-slate-400">10 data pembayaran terakhir dari warung.</p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors">Semua Pesanan →</a>
            </div>

            <!-- Desktop View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/70 text-slate-600 font-bold text-[11px] uppercase tracking-wider">
                            <th class="py-3.5 px-5">No. Order</th>
                            <th class="py-3.5 px-5">Warung / Tenant</th>
                            <th class="py-3.5 px-5">Paket & Qty</th>
                            <th class="py-3.5 px-5">Omzet Fisik</th>
                            <th class="py-3.5 px-5">Setoran Kas Owner</th>
                            <th class="py-3.5 px-5">Untung Reseller</th>
                            <th class="py-3.5 px-5">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($recentOrders as $ro)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3.5 px-5 font-mono font-bold text-slate-900">{{ $ro->order_number }}</td>
                            <td class="py-3.5 px-5 font-bold text-slate-900">
                                @if($ro->customer_type === 'direct')
                                <span class="inline-flex items-center gap-1">
                                    <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Eceran</span>
                                    <span>{{ $ro->customer_name ?: 'Pelanggan Langsung' }}</span>
                                </span>
                                @else
                                {{ $ro->tenant->shop_name ?: $ro->tenant->name }}
                                @endif
                            <td class="py-3.5 px-5 text-slate-600 font-medium">
                                <div class="space-y-1">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($ro->effective_items as $item)
                                        <span class="inline-flex items-center gap-1.5 bg-slate-100 px-2 py-0.5 rounded text-[11px] font-semibold text-slate-800">
                                            {{ $item->package?->name ?? 'Paket' }}
                                            <span class="text-blue-700 font-bold font-mono">{{ number_format($item->quantity) }} lbr</span>
                                        </span>
                                        @endforeach
                                    </div>
                                    @if($ro->effective_items->count() > 1)
                                    <span class="text-[10px] text-slate-400 font-medium block">Total: {{ number_format($ro->quantity) }} lembar</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-5 font-mono text-slate-500">Rp {{ number_format($ro->total_printed_amount, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-5 font-mono font-extrabold text-slate-900">Rp {{ number_format($ro->total_owner_amount, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-5 font-mono">
                                <span class="text-blue-700 bg-blue-50 border border-blue-200/60 font-bold px-2 py-0.5 rounded-full text-[11px]">
                                    +Rp {{ number_format($ro->total_reseller_profit, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-slate-400 font-medium">{{ $ro->confirmed_at?->translatedFormat('d M H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400 font-medium">Belum ada transaksi voucher lunas pada periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($recentOrders->count() > 0)
                    <tfoot class="bg-slate-50 border-t-2 border-slate-200 text-xs font-extrabold text-slate-900">
                        <tr>
                            <td colspan="2" class="py-3.5 px-5 text-slate-700 uppercase tracking-wider">
                                TOTAL ({{ $recentOrders->count() }} Data Terbaru Ini)
                            </td>
                            <td class="py-3.5 px-5 font-mono text-slate-800">
                                {{ number_format($recentOrders->sum('quantity')) }} lbr
                            </td>
                            <td class="py-3.5 px-5 font-mono text-slate-600">
                                Rp {{ number_format($recentOrders->sum('total_printed_amount'), 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-5 font-mono font-extrabold text-blue-950 text-sm">
                                Rp {{ number_format($recentOrders->sum('total_owner_amount'), 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-5 font-mono text-blue-700">
                                +Rp {{ number_format($recentOrders->sum('total_reseller_profit'), 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-5"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            <!-- Mobile View (Adaptive Stacked Cards) -->
            <div class="md:hidden divide-y divide-slate-100">
                @forelse($recentOrders as $ro)
                <div class="p-4 space-y-2 hover:bg-slate-50/50 transition-colors">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="font-mono font-bold text-slate-900 text-xs block">{{ $ro->order_number }}</span>
                            @if($ro->customer_type === 'direct')
                            <span class="inline-flex items-center gap-1">
                                <span class="px-1 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Eceran</span>
                                <span class="text-xs font-semibold text-slate-800">{{ $ro->customer_name ?: 'Pelanggan Langsung' }}</span>
                            </span>
                            @else
                            <span class="text-xs font-semibold text-slate-800">{{ $ro->tenant->shop_name ?: $ro->tenant->name }}</span>
                            @endif
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-extrabold text-slate-900 text-sm block">Rp {{ number_format($ro->total_owner_amount, 0, ',', '.') }}</span>
                            <span class="text-[10px] text-slate-400">{{ $ro->confirmed_at?->translatedFormat('d M H:i') }}</span>
                        </div>
                    </div>
                    <div class="pt-1 space-y-1">
                        <div class="flex flex-wrap gap-1">
                            @foreach($ro->effective_items as $item)
                            <span class="inline-flex items-center gap-1 bg-slate-100 px-2 py-0.5 rounded text-[10px] font-semibold text-slate-800">
                                {{ $item->package?->name ?? 'Paket' }}
                                <span class="text-blue-700 font-bold font-mono">{{ number_format($item->quantity) }} lbr</span>
                            </span>
                            @endforeach
                        </div>
                        <div class="flex items-center justify-between text-xs pt-0.5">
                            @if($ro->effective_items->count() > 1)
                            <span class="text-[10px] text-slate-400 font-medium">Total: {{ number_format($ro->quantity) }} lbr</span>
                            @else
                            <span></span>
                            @endif
                            <span class="text-blue-700 font-mono font-bold text-[11px] bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200/60">
                                Reseller +Rp {{ number_format($ro->total_reseller_profit, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-slate-400 text-xs">Belum ada transaksi voucher lunas.</div>
                @endforelse

                @if($recentOrders->count() > 0)
                <div class="p-4 bg-slate-50/95 border-t-2 border-slate-200 space-y-1.5 text-xs">
                    <div class="flex items-center justify-between text-slate-500 text-[10px] font-bold uppercase tracking-wider">
                        <span>Total ({{ $recentOrders->count() }} Transaksi Ini)</span>
                        <span class="font-mono text-slate-700">{{ number_format($recentOrders->sum('quantity')) }} lbr</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600 font-medium">Setoran Kas Owner:</span>
                        <span class="font-mono font-extrabold text-blue-950 text-sm">Rp {{ number_format($recentOrders->sum('total_owner_amount'), 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1 border-t border-slate-200/50">
                        <span>Fisik: Rp {{ number_format($recentOrders->sum('total_printed_amount'), 0, ',', '.') }}</span>
                        <span class="text-blue-700 font-bold">Untung Reseller: +Rp {{ number_format($recentOrders->sum('total_reseller_profit'), 0, ',', '.') }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- SEKSI 2: DEPOSIT & SALDO CETAK MANDIRI     -->
    <!-- ========================================== -->
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-2">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    Seksi 2: Pemasukan Saldo Cetak Mandiri
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Setoran uang tunai/transfer dari mitra warung atas pembelian kuota saldo cetak voucher mandiri.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-mono font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200/60 px-3 py-1 rounded-full">
                    Kas Masuk: Rp {{ number_format($depositRevenue, 0, ',', '.') }}
                </span>
                @if($pendingDepositAmount > 0)
                <span class="text-xs font-mono font-extrabold text-amber-700 bg-amber-50 border border-amber-200/60 px-3 py-1 rounded-full">
                    Tempo: Rp {{ number_format($pendingDepositAmount, 0, ',', '.') }} ({{ $pendingDepositCount }})
                </span>
                @endif
                <a href="{{ route('admin.deposits.index') }}" class="text-xs font-bold px-3 py-1.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white transition-all shadow-xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Kelola Saldo Mitra</span>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-[28px] border border-slate-200/70 overflow-hidden shadow-[0_4px_20px_rgb(0,0,0,0.03)]">
            <!-- Desktop View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/70 text-slate-600 font-bold text-[11px] uppercase tracking-wider">
                            <th class="py-3.5 px-5">No. Deposit</th>
                            <th class="py-3.5 px-5">Tanggal</th>
                            <th class="py-3.5 px-5">Mitra Warung</th>
                            <th class="py-3.5 px-5">Nominal Saldo</th>
                            <th class="py-3.5 px-5">Sistem & Status</th>
                            <th class="py-3.5 px-5">Kas Masuk Owner</th>
                            <th class="py-3.5 px-5 text-right">Untung Mitra</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($recentDeposits as $dep)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3.5 px-5 font-mono font-bold text-slate-900">
                                @if(in_array($dep->status, ['lunas', 'tempo']))
                                <a href="{{ route('deposits.invoice', $dep) }}" target="_blank" class="hover:text-blue-600 hover:underline flex items-center gap-1.5" title="Buka Dokumen Bukti">
                                    <span>{{ $dep->deposit_number }}</span>
                                    <span class="text-[9px] px-1.5 py-0.2 rounded font-sans font-bold {{ $dep->status === 'lunas' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                        {{ $dep->status === 'lunas' ? 'Kwitansi' : 'Faktur' }}
                                    </span>
                                </a>
                                @else
                                <span>{{ $dep->deposit_number }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-slate-500 font-medium">{{ $dep->deposit_date->translatedFormat('d M Y') }}</td>
                            <td class="py-3.5 px-5 font-semibold text-slate-900">
                                {{ $dep->tenant->shop_name ?: $dep->tenant->name }}
                                <span class="block text-[11px] text-slate-400 font-normal">{{ '@' . $dep->tenant->username }}</span>
                            </td>
                            <td class="py-3.5 px-5 font-mono font-bold text-slate-900">
                                Rp {{ number_format($dep->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-5">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $dep->payment_type === 'tempo' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $dep->payment_type === 'tempo' ? 'Konsinyasi (Tempo)' : 'Bayar Lunas' }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $dep->status_badge_class }}">
                                        {{ $dep->status_label }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3.5 px-5 font-mono font-extrabold {{ $dep->actual_paid_amount > 0 ? 'text-emerald-700' : 'text-slate-400' }}">
                                Rp {{ number_format($dep->actual_paid_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-5 font-mono text-right font-bold text-blue-600">
                                @if($dep->merchant_profit > 0)
                                +Rp {{ number_format($dep->merchant_profit, 0, ',', '.') }}
                                @else
                                <span class="text-slate-400 font-normal">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400 font-medium">Belum ada transaksi deposit saldo merchant pada periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($recentDeposits->count() > 0)
                    <tfoot class="bg-slate-50 border-t-2 border-slate-200 text-xs font-extrabold text-slate-900">
                        <tr>
                            <td colspan="3" class="py-3.5 px-5 text-slate-700 uppercase tracking-wider">
                                TOTAL REALISASI KAS SALDO (PERIODE INI)
                                @if($recentDepositsCount > $recentDeposits->count())
                                    <span class="text-[10px] font-normal text-slate-400 lowercase font-medium">(menampilkan 10 dari {{ $recentDepositsCount }} data)</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 font-mono text-slate-800">
                                Rp {{ number_format($recentDeposits->sum('amount'), 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-5"></td>
                            <td class="py-3.5 px-5 font-mono font-extrabold text-emerald-800 text-sm">
                                Rp {{ number_format($depositRevenue, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-5 font-mono text-right font-bold text-blue-700">
                                +Rp {{ number_format($depositResellerProfit, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            <!-- Mobile View (Adaptive Stacked Cards) -->
            <div class="md:hidden divide-y divide-slate-100">
                @forelse($recentDeposits as $dep)
                <div class="p-4 space-y-2 hover:bg-slate-50/50 transition-colors">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="text-xs font-bold text-slate-900 block">{{ $dep->tenant->shop_name ?: $dep->tenant->name }}</span>
                            @if(in_array($dep->status, ['lunas', 'tempo']))
                            <a href="{{ route('deposits.invoice', $dep) }}" target="_blank" class="text-[10px] text-blue-600 hover:underline font-mono inline-block">
                                {{ $dep->deposit_number }} • {{ $dep->status === 'lunas' ? '🧾 Kwitansi ↗' : '📄 Faktur ↗' }}
                            </a>
                            @else
                            <span class="text-[10px] text-slate-700 font-mono inline-block">{{ $dep->deposit_number }}</span>
                            @endif
                            <span class="text-[10px] text-slate-400 block">{{ $dep->deposit_date->translatedFormat('d M Y') }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-extrabold {{ $dep->actual_paid_amount > 0 ? 'text-emerald-700' : 'text-slate-500' }} text-sm block">
                                Rp {{ number_format($dep->actual_paid_amount, 0, ',', '.') }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $dep->status_badge_class }}">
                                {{ $dep->status_label }}
                            </span>
                        </div>
                    </div>
                    <div class="pt-1 flex items-center justify-between text-xs border-t border-slate-100">
                        <span class="text-slate-500 text-[11px]">
                            Saldo: <strong class="font-mono text-slate-800">Rp {{ number_format($dep->amount, 0, ',', '.') }}</strong>
                            ({{ $dep->payment_type === 'tempo' ? 'Tempo' : 'Lunas' }})
                        </span>
                        @if($dep->merchant_profit > 0)
                        <span class="text-blue-600 font-mono font-bold text-[11px]">
                            Mitra: +Rp {{ number_format($dep->merchant_profit, 0, ',', '.') }}
                        </span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-slate-400 text-xs">Belum ada transaksi deposit saldo merchant.</div>
                @endforelse

                @if($recentDeposits->count() > 0)
                <div class="p-4 bg-slate-50/95 border-t-2 border-slate-200 flex items-center justify-between text-xs">
                    <div>
                        <span class="font-bold text-slate-700 uppercase tracking-wider text-[10px] block">Total Kas Saldo Masuk</span>
                        @if($recentDepositsCount > $recentDeposits->count())
                            <span class="text-[10px] text-slate-400 font-normal">10 dari {{ $recentDepositsCount }} data</span>
                        @endif
                    </div>
                    <span class="font-mono font-extrabold text-emerald-700 text-sm">Rp {{ number_format($depositRevenue, 0, ',', '.') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- SEKSI 3: PEMASUKAN KAS LAIN-LAIN (TERPISAH)-->
    <!-- ========================================== -->
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-2">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                    Seksi 3: Pemasukan Kas Lain-Lain (Di Luar Voucher)
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Iuran bulanan warga, biaya pasang baru, sewa tiang/port, atau pemasukan non-voucher lainnya.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-mono font-extrabold text-sky-700 bg-sky-50 border border-sky-200/60 px-3 py-1 rounded-full">
                    Rp {{ number_format($otherIncome, 0, ',', '.') }}
                </span>
                <a href="{{ route('finance.incomes') }}" class="text-xs font-bold px-3 py-1.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white transition-all shadow-xs">
                    + Catat Pemasukan
                </a>
            </div>
        </div>

        <div class="bg-white rounded-[28px] border border-slate-200/70 overflow-hidden shadow-[0_4px_20px_rgb(0,0,0,0.03)]">
            <!-- Desktop View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/70 text-slate-600 font-bold text-[11px] uppercase tracking-wider">
                            <th class="py-3.5 px-5">No. Ref</th>
                            <th class="py-3.5 px-5">Tanggal</th>
                            <th class="py-3.5 px-5">Keterangan Pemasukan</th>
                            <th class="py-3.5 px-5">Kategori</th>
                            <th class="py-3.5 px-5">Dicatat Oleh</th>
                            <th class="py-3.5 px-5 text-right">Nominal Masuk</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($otherIncomes as $inc)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3.5 px-5 font-mono font-bold text-slate-900">{{ $inc->income_number }}</td>
                            <td class="py-3.5 px-5 text-slate-500 font-medium">{{ $inc->income_date->translatedFormat('d M Y') }}</td>
                            <td class="py-3.5 px-5 font-semibold text-slate-900">
                                {{ $inc->title }}
                                @if($inc->notes)
                                <span class="block text-[11px] text-slate-400 font-normal">{{ $inc->notes }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200/60">
                                    {{ $inc->category }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-slate-500">{{ $inc->creator?->name ?? 'Admin' }}</td>
                            <td class="py-3.5 px-5 font-mono text-right font-extrabold text-sky-700">Rp {{ number_format($inc->amount, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400 font-medium">Belum ada catatan pemasukan kas lain pada periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($otherIncomes->count() > 0)
                    <tfoot class="bg-slate-50 border-t-2 border-slate-200 text-xs font-extrabold text-slate-900">
                        <tr>
                            <td colspan="5" class="py-3.5 px-5 text-slate-700 uppercase tracking-wider">
                                TOTAL PEMASUKAN KAS LAIN (PERIODE INI)
                                @if($otherIncomesCount > $otherIncomes->count())
                                    <span class="text-[10px] font-normal text-slate-400 lowercase font-medium">(menampilkan 10 dari {{ $otherIncomesCount }} data)</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 font-mono text-right font-extrabold text-sky-800 text-sm">
                                Rp {{ number_format($otherIncome, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            <!-- Mobile View (Adaptive Stacked Cards) -->
            <div class="md:hidden divide-y divide-slate-100">
                @forelse($otherIncomes as $inc)
                <div class="p-4 space-y-2 hover:bg-slate-50/50 transition-colors">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="text-xs font-bold text-slate-900 block">{{ $inc->title }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $inc->income_number }} • {{ $inc->income_date->translatedFormat('d M Y') }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-extrabold text-sky-700 text-sm block">+Rp {{ number_format($inc->amount, 0, ',', '.') }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200/60">
                                {{ $inc->category }}
                            </span>
                        </div>
                    </div>
                    @if($inc->notes)
                    <p class="text-[11px] text-slate-500 bg-slate-50 p-2 rounded-lg">{{ $inc->notes }}</p>
                    @endif
                </div>
                @empty
                <div class="p-6 text-center text-slate-400 text-xs">Belum ada catatan pemasukan kas lain.</div>
                @endforelse

                @if($otherIncomes->count() > 0)
                <div class="p-4 bg-slate-50/95 border-t-2 border-slate-200 flex items-center justify-between text-xs">
                    <div>
                        <span class="font-bold text-slate-700 uppercase tracking-wider text-[10px] block">Total Kas Lain Periode Ini</span>
                        @if($otherIncomesCount > $otherIncomes->count())
                            <span class="text-[10px] text-slate-400 font-normal">10 dari {{ $otherIncomesCount }} data</span>
                        @endif
                    </div>
                    <span class="font-mono font-extrabold text-sky-700 text-sm">Rp {{ number_format($otherIncome, 0, ',', '.') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- SEKSI 4: BEBAN PENGELUARAN KAS OPERASIONAL -->
    <!-- ========================================== -->
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-2">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                    Seksi 4: Beban Pengeluaran Kas Operasional
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Biaya bandwidth ISP, tagihan listrik, perbaikan tiang/kabel FO, dan transport operasional.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-mono font-extrabold text-rose-700 bg-rose-50 border border-rose-200/60 px-3 py-1 rounded-full">
                    Rp {{ number_format($totalExpenses, 0, ',', '.') }}
                </span>
                <a href="{{ route('finance.expenses') }}" class="text-xs font-bold px-3 py-1.5 rounded-full bg-slate-800 hover:bg-slate-900 text-white transition-all shadow-xs">
                    Kelola Pengeluaran
                </a>
            </div>
        </div>

        <div class="bg-white rounded-[28px] border border-slate-200/70 overflow-hidden shadow-[0_4px_20px_rgb(0,0,0,0.03)]">
            <!-- Desktop View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/70 text-slate-600 font-bold text-[11px] uppercase tracking-wider">
                            <th class="py-3.5 px-5">No. Bukti</th>
                            <th class="py-3.5 px-5">Tanggal</th>
                            <th class="py-3.5 px-5">Keterangan Beban</th>
                            <th class="py-3.5 px-5">Kategori</th>
                            <th class="py-3.5 px-5">Dicatat Oleh</th>
                            <th class="py-3.5 px-5 text-right">Nominal Beban</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($recentExpenses as $exp)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3.5 px-5 font-mono font-bold text-slate-900">{{ $exp->expense_number }}</td>
                            <td class="py-3.5 px-5 text-slate-500 font-medium">{{ $exp->expense_date->translatedFormat('d M Y') }}</td>
                            <td class="py-3.5 px-5 font-semibold text-slate-900">
                                {{ $exp->title }}
                                @if($exp->notes)
                                <span class="block text-[11px] text-slate-400 font-normal">{{ $exp->notes }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200/60">
                                    {{ $exp->category }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-slate-500">{{ $exp->creator?->name ?? 'Admin' }}</td>
                            <td class="py-3.5 px-5 font-mono text-right font-extrabold text-rose-600">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400 font-medium">Belum ada beban pengeluaran kas pada periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($recentExpenses->count() > 0)
                    <tfoot class="bg-slate-50 border-t-2 border-slate-200 text-xs font-extrabold text-slate-900">
                        <tr>
                            <td colspan="5" class="py-3.5 px-5 text-slate-700 uppercase tracking-wider">
                                TOTAL BEBAN PENGELUARAN KAS (PERIODE INI)
                                @if($recentExpensesCount > $recentExpenses->count())
                                    <span class="text-[10px] font-normal text-slate-400 lowercase font-medium">(menampilkan 10 dari {{ $recentExpensesCount }} data)</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 font-mono text-right font-extrabold text-rose-700 text-sm">
                                Rp {{ number_format($totalExpenses, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            <!-- Mobile View (Adaptive Stacked Cards) -->
            <div class="md:hidden divide-y divide-slate-100">
                @forelse($recentExpenses as $exp)
                <div class="p-4 space-y-2 hover:bg-slate-50/50 transition-colors">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="text-xs font-bold text-slate-900 block">{{ $exp->title }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $exp->expense_number }} • {{ $exp->expense_date->translatedFormat('d M Y') }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-extrabold text-rose-600 text-sm block">-Rp {{ number_format($exp->amount, 0, ',', '.') }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200/60">
                                {{ $exp->category }}
                            </span>
                        </div>
                    </div>
                    @if($exp->notes)
                    <p class="text-[11px] text-slate-500 bg-slate-50 p-2 rounded-lg">{{ $exp->notes }}</p>
                    @endif
                </div>
                @empty
                <div class="p-6 text-center text-slate-400 text-xs">Belum ada beban pengeluaran kas.</div>
                @endforelse

                @if($recentExpenses->count() > 0)
                <div class="p-4 bg-slate-50/95 border-t-2 border-slate-200 flex items-center justify-between text-xs">
                    <div>
                        <span class="font-bold text-slate-700 uppercase tracking-wider text-[10px] block">Total Beban Periode Ini</span>
                        @if($recentExpensesCount > $recentExpenses->count())
                            <span class="text-[10px] text-slate-400 font-normal">10 dari {{ $recentExpensesCount }} data</span>
                        @endif
                    </div>
                    <span class="font-mono font-extrabold text-rose-600 text-sm">-Rp {{ number_format($totalExpenses, 0, ',', '.') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

<script>
    function toggleRangeMode(mode) {
        const monthBox = document.getElementById('monthInputBox');
        const yearBox = document.getElementById('yearInputBox');
        const startBox = document.getElementById('startDateBox');
        const endBox = document.getElementById('endDateBox');

        if (mode === 'custom') {
            monthBox.classList.add('hidden');
            yearBox.classList.add('hidden');
            startBox.classList.remove('hidden');
            endBox.classList.remove('hidden');
        } else {
            monthBox.classList.remove('hidden');
            yearBox.classList.remove('hidden');
            startBox.classList.add('hidden');
            endBox.classList.add('hidden');
            document.getElementById('financeFilterForm').submit();
        }
    }

    function autoSubmitIfRangeValid() {
        const start = document.querySelector('input[name="start_date"]').value;
        const end = document.querySelector('input[name="end_date"]').value;
        if (start && end) {
            document.getElementById('financeFilterForm').submit();
        }
    }
</script>
@endsection
