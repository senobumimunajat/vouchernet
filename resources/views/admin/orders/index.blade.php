@extends('layouts.app')

@section('title', 'Daftar Pesanan Voucher')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900">Daftar Pesanan Voucher</h1>
            <p class="text-xs text-slate-500 mt-0.5">Konfirmasi pembayaran, serahkan voucher fisik, dan ajukan koreksi pembatalan.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('finance.expenses') }}" class="touch-target inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs border border-slate-200/80 transition-all active:scale-95 shadow-xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6H1.5m8.25-1.5h1.5m-1.5 0v.75A.75.75 0 0012 6h1.5m6-1.5h.75a.75.75 0 01.75.75V6m0 0v1.5m0-1.5h-1.5"/></svg>
                Beban Pengeluaran
            </a>
            <a href="{{ route('admin.orders.create_offline') }}" class="touch-target inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm shadow-blue-500/20 transition-all active:scale-95">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Input Pesanan Offline (Walk-in)
            </a>
        </div>
    </div>

    <!-- Operational KPI Mini Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-[24px] border border-slate-200/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Menunggu Konfirmasi</div>
                <div class="text-2xl font-extrabold font-mono text-amber-600 mt-1">{{ number_format($pendingCount) }} <span class="text-xs font-sans text-slate-400 font-normal">pesanan</span></div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-[24px] border border-slate-200/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Voucher Terjual Hari Ini</div>
                <div class="text-2xl font-extrabold font-mono text-slate-900 mt-1">{{ number_format($todayVouchersSold) }} <span class="text-xs font-sans text-slate-400 font-normal">lembar</span></div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-[24px] border border-slate-200/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pengajuan Hapus Bayar</div>
                <div class="text-2xl font-extrabold font-mono text-rose-600 mt-1">{{ number_format($deleteRequestCount) }} <span class="text-xs font-sans text-slate-400 font-normal">antrean</span></div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-[0_2px_12px_rgb(0,0,0,0.02)] space-y-3">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Order / Telepon..."
                    class="w-full px-3.5 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
            </div>

            <div>
                <select name="tenant_id" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                    <option value="">-- Semua Warung & Pelanggan --</option>
                    @foreach($tenants as $t)
                    <option value="{{ $t->id }}" {{ request('tenant_id') == $t->id ? 'selected' : '' }}>
                        {{ $t->username === 'pelanggan_langsung' ? '👤 Pelanggan Langsung (Walk-In / Eceran)' : ($t->shop_name ?: $t->name) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="status" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                    <option value="">-- Semua Status --</option>
                    <option value="menunggu_pembayaran" {{ request('status') === 'menunggu_pembayaran' ? 'selected' : '' }}>⏳ Menunggu Bayar (Konsinyasi)</option>
                    <option value="lunas" {{ request('status') === 'lunas' ? 'selected' : '' }}>✅ Lunas</option>
                    <option value="menunggu_approval_hapus" {{ request('status') === 'menunggu_approval_hapus' ? 'selected' : '' }}>⚠️ Pengajuan Hapus</option>
                    <option value="dibatalkan" {{ request('status') === 'dibatalkan' ? 'selected' : '' }}>❌ Dibatalkan</option>
                </select>
            </div>

            <div>
                <select name="package_id" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                    <option value="">-- Semua Paket --</option>
                    @foreach($packages as $p)
                    <option value="{{ $p->id }}" {{ request('package_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <a href="{{ route('admin.orders.index') }}" class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 border border-slate-200/60 touch-target">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    Reset Filter
                </a>
            </div>
        </form>

        @if($selectedTenant && $tenantConsignmentStats)
        <!-- Mini Banner Ringkasan Konsinyasi / Tempo Warung Terpilih -->
        @if($tenantConsignmentStats['pending_orders_count'] > 0)
        <div class="p-4 rounded-2xl bg-amber-50/90 border border-amber-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                    🏪
                </div>
                <div>
                    <div class="text-xs font-bold text-amber-950 flex items-center gap-1.5">
                        <span>Konsinyasi Aktif: <strong>{{ $selectedTenant->shop_name ?: $selectedTenant->name }}</strong></span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-200/70 text-amber-900 font-extrabold">Belum Setor</span>
                    </div>
                    <div class="text-xs text-amber-800 mt-0.5 font-medium">
                        Sedang membawa <strong class="font-mono text-amber-950 font-extrabold">{{ number_format($tenantConsignmentStats['pending_vouchers_count']) }} lembar voucher</strong> dalam {{ $tenantConsignmentStats['pending_orders_count'] }} pesanan.
                    </div>
                </div>
            </div>
            <div class="text-left sm:text-right pt-2 sm:pt-0 border-t sm:border-t-0 border-amber-200/60">
                <span class="text-[10px] text-amber-700 block uppercase font-bold tracking-wider">Total Tagihan Setoran:</span>
                <span class="text-lg font-extrabold font-mono text-amber-950">
                    Rp {{ number_format($tenantConsignmentStats['pending_amount'], 0, ',', '.') }}
                </span>
            </div>
        </div>
        @else
        <div class="p-3.5 rounded-2xl bg-blue-50/70 border border-blue-200/60 flex items-center justify-between text-xs">
            <div class="flex items-center gap-2">
                <span class="font-bold text-blue-900">🏪 {{ $selectedTenant->shop_name ?: $selectedTenant->name }}</span>
                <span class="text-blue-700 font-medium">— Semua pesanan voucher warung ini sudah lunas disetorkan.</span>
            </div>
            <span class="text-[11px] font-bold text-blue-700 bg-blue-100/70 px-2.5 py-0.5 rounded-full">Lunas</span>
        </div>
        @endif
        @endif
    </div>

    <!-- Orders Data Table -->
    <div class="bg-white rounded-[28px] border border-slate-200/70 overflow-hidden shadow-[0_4px_20px_rgb(0,0,0,0.03)]">
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/90 border-b border-slate-200/80 text-slate-500 uppercase tracking-wider font-bold text-[10px]">
                        <th class="py-3.5 px-5 w-36">Order / Waktu</th>
                        <th class="py-3.5 px-5 w-44">Mitra / Pembeli</th>
                        <th class="py-3.5 px-5">Rincian Paket</th>
                        <th class="py-3.5 px-5 w-44">Tagihan & Status</th>
                        <th class="py-3.5 px-5 text-right w-52">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($orders as $ord)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-4 px-5 align-middle font-mono whitespace-nowrap">
                            <span class="font-extrabold text-slate-900 text-xs block tracking-tight">{{ $ord->order_number }}</span>
                            <span class="text-[11px] text-slate-400 font-sans block mt-0.5">{{ $ord->created_at->translatedFormat('d M Y, H:i') }}</span>
                        </td>
                        <td class="py-4 px-5 align-middle">
                            @if($ord->customer_type === 'direct')
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="inline-flex items-center px-2 py-0.2 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Eceran</span>
                                    <span class="font-bold text-slate-900 text-xs">{{ $ord->customer_name ?: 'Walk-In' }}</span>
                                </div>
                                <span class="text-[10px] text-slate-400 block">Penjualan Langsung</span>
                            </div>
                            @else
                            <div class="space-y-0.5">
                                <span class="font-bold text-slate-900 text-xs block">{{ $ord->tenant->shop_name ?: $ord->tenant->name }}</span>
                                <span class="text-[11px] text-slate-400 font-mono block">{{ $ord->tenant->phone }}</span>
                            </div>
                            @endif
                        </td>
                        <td class="py-4 px-5 align-middle">
                            <div class="space-y-1">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($ord->effective_items as $item)
                                    <div class="inline-flex items-center gap-1.5 bg-slate-100/90 px-2.5 py-0.5 rounded-lg border border-slate-200/70 shadow-2xs">
                                        <span class="font-semibold text-slate-800 text-xs">{{ $item->package?->name ?? 'Paket' }}</span>
                                        <span class="font-mono font-extrabold text-blue-700 text-[11px] bg-white px-1.5 py-0.2 rounded border border-slate-200/50">
                                            {{ number_format($item->quantity) }} lbr
                                        </span>
                                    </div>
                                    @endforeach
                                </div>

                                @if($ord->effective_items->count() > 1)
                                <div class="text-[11px] text-slate-400 font-medium flex items-center gap-1">
                                    <span>Total:</span>
                                    <strong class="text-slate-800 font-mono">{{ number_format($ord->quantity) }} lembar</strong>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="py-4 px-5 align-middle whitespace-nowrap">
                            <div class="space-y-1">
                                <div>
                                    <span class="font-mono font-extrabold text-sm block {{ $ord->status === 'menunggu_pembayaran' ? 'text-blue-700' : 'text-slate-900' }}">
                                        Rp {{ number_format($ord->total_owner_amount, 0, ',', '.') }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 block font-medium">
                                        {{ $ord->customer_type === 'direct' ? '100% Kas Masuk' : 'Wajib Setor Warung' }}
                                    </span>
                                </div>
                                <div class="pt-0.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $ord->status_badge['bg'] }} shadow-2xs">
                                        {{ $ord->status_badge['label'] }}
                                    </span>
                                    @if($ord->status === 'lunas')
                                    <div class="text-[10px] text-slate-500 mt-0.5 font-medium">
                                        {{ strtoupper($ord->payment_method) }}
                                        @if($ord->payment_proof_file)
                                        • <a href="{{ asset('storage/' . $ord->payment_proof_file) }}" target="_blank" class="text-blue-600 hover:underline">Bukti</a>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-5 align-middle text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <a href="{{ route('orders.invoice', $ord) }}" target="_blank"
                                    class="px-3 py-1.5 rounded-full {{ $ord->status === 'lunas' ? 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border-emerald-200/80' : 'text-slate-700 bg-slate-100 hover:bg-slate-200 border-slate-200' }} border font-bold text-xs transition-colors inline-flex items-center gap-1.5 shadow-2xs">
                                    <svg class="w-3.5 h-3.5 {{ $ord->status === 'lunas' ? 'text-emerald-600' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    {{ $ord->status === 'lunas' ? 'Kwitansi' : 'Faktur' }}
                                </a>

                                @if($ord->status === 'menunggu_pembayaran')
                                <button type="button" onclick="openConfirmModal({{ $ord->id }}, '{{ $ord->order_number }}', '{{ $ord->quantity }}', '{{ addslashes($ord->package_names_summary) }}', 'Rp {{ number_format($ord->total_owner_amount, 0, ',', '.') }}', '{{ $ord->customer_type === 'direct' ? 'Penjualan Eceran' : ($ord->effective_items->count() > 1 ? $ord->quantity . ' lembar total' : $ord->quantity . ' lbr x Rp ' . number_format($ord->owner_price_each, 0, ',', '.') . '/lbr') }}')"
                                    class="px-3.5 py-1.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm shadow-blue-500/20 transition-all active:scale-95 inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"/></svg>
                                    Bayar
                                </button>
                                <button type="button" onclick="openCancelUnpaidModal({{ $ord->id }}, '{{ $ord->order_number }}', {{ $ord->quantity }}, '{{ addslashes($ord->package_names_summary) }}')"
                                    class="px-3 py-1.5 rounded-full text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 font-bold text-xs transition-colors inline-flex items-center gap-1 border border-rose-200/60">
                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Batal
                                </button>
                                @endif

                                @if($ord->status === 'lunas')
                                <button type="button" onclick="openRequestDeleteModal({{ $ord->id }}, '{{ $ord->order_number }}')"
                                    class="px-3 py-1.5 rounded-full text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 font-semibold text-xs transition-colors">
                                    Ajukan Batal
                                </button>
                                @endif

                                @if($ord->status === 'menunggu_approval_hapus')
                                <span class="text-rose-600 italic text-[11px] font-semibold">Menunggu Super Admin</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-slate-400 font-medium">Tidak ada data pesanan yang sesuai filter.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Adaptive Cards View (Bebas Potong pada Smartphone) -->
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($orders as $ord)
            <div class="p-4 space-y-3 hover:bg-slate-50/50 transition-colors">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="font-mono font-bold text-slate-900 text-xs block">{{ $ord->order_number }}</span>
                        <span class="text-[10px] text-slate-400">{{ $ord->created_at->translatedFormat('d M Y, H:i') }}</span>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $ord->status_badge['bg'] }}">
                        {{ $ord->status_badge['label'] }}
                    </span>
                </div>

                <div class="p-3.5 bg-slate-50/90 rounded-2xl space-y-2.5 text-xs border border-slate-100">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 font-medium">{{ $ord->customer_type === 'direct' ? 'Pembeli:' : 'Warung:' }}</span>
                        @if($ord->customer_type === 'direct')
                        <div class="flex items-center gap-1.5">
                            <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Eceran</span>
                            <span class="font-bold text-slate-900">{{ $ord->customer_name ?: 'Pelanggan Walk-In' }}</span>
                        </div>
                        @else
                        <span class="font-bold text-slate-900">{{ $ord->tenant->shop_name ?: $ord->tenant->name }}</span>
                        @endif
                    </div>

                    <div class="pt-2 border-t border-slate-200/60 space-y-1.5">
                        <div class="flex items-center justify-between text-slate-500 text-[11px]">
                            <span class="font-medium">Rincian Paket:</span>
                            @if($ord->effective_items->count() > 1)
                            <span class="font-mono font-bold text-slate-700">{{ number_format($ord->quantity) }} lembar total</span>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($ord->effective_items as $item)
                            <div class="inline-flex items-center gap-1.5 bg-white px-2.5 py-1 rounded-lg border border-slate-200 text-xs shadow-2xs">
                                <span class="font-semibold text-slate-800">{{ $item->package?->name ?? 'Paket' }}</span>
                                <span class="font-mono font-extrabold text-blue-700 text-[11px] bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200/50">
                                    {{ number_format($item->quantity) }} lbr
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-2 border-t border-slate-200/60">
                        <span class="text-slate-500 font-medium">Tagihan Setor:</span>
                        <div class="text-right">
                            <span class="font-mono font-extrabold text-base block {{ $ord->status === 'menunggu_pembayaran' ? 'text-blue-700' : 'text-slate-900' }}">
                                Rp {{ number_format($ord->total_owner_amount, 0, ',', '.') }}
                            </span>
                            <span class="text-[10px] text-slate-400 block font-medium">
                                {{ $ord->customer_type === 'direct' ? '100% Kas Masuk' : 'Wajib Setor Warung' }}
                            </span>
                        </div>
                    </div>
                    @if($ord->status === 'lunas')
                    <div class="flex justify-between items-center pt-2 border-t border-slate-200/60">
                        <span class="text-slate-500 font-medium">Pembayaran:</span>
                        <span class="text-slate-700 font-semibold uppercase text-xs">
                            {{ $ord->payment_method }}
                            @if($ord->payment_proof_file)
                            • <a href="{{ asset('storage/' . $ord->payment_proof_file) }}" target="_blank" class="text-blue-600 hover:underline">Bukti</a>
                            @endif
                        </span>
                    </div>
                    @endif
                </div>

                <div class="pt-1 flex items-center gap-2 flex-wrap">
                    <a href="{{ route('orders.invoice', $ord) }}" target="_blank"
                        class="touch-target px-3.5 py-2.5 rounded-xl {{ $ord->status === 'lunas' ? 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border-emerald-200/80' : 'text-slate-700 bg-slate-100 hover:bg-slate-200 border-slate-200' }} border font-bold text-xs transition-colors inline-flex items-center justify-center gap-1.5 shrink-0">
                        <svg class="w-4 h-4 {{ $ord->status === 'lunas' ? 'text-emerald-600' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>{{ $ord->status === 'lunas' ? 'Kwitansi' : 'Faktur' }}</span>
                    </a>

                    @if($ord->status === 'menunggu_pembayaran')
                    <button type="button" onclick="openConfirmModal({{ $ord->id }}, '{{ $ord->order_number }}', '{{ $ord->quantity }}', '{{ addslashes($ord->package_names_summary) }}', 'Rp {{ number_format($ord->total_owner_amount, 0, ',', '.') }}', '{{ $ord->customer_type === 'direct' ? 'Penjualan Eceran' : ($ord->effective_items->count() > 1 ? $ord->quantity . ' lembar total' : $ord->quantity . ' lbr x Rp ' . number_format($ord->owner_price_each, 0, ',', '.') . '/lbr') }}')"
                        class="flex-1 touch-target py-2.5 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm shadow-blue-500/20 active:scale-95 transition-all text-center flex items-center justify-center gap-1.5 min-w-[130px]">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Konfirmasi Bayar
                    </button>
                    <button type="button" onclick="openCancelUnpaidModal({{ $ord->id }}, '{{ $ord->order_number }}', {{ $ord->quantity }}, '{{ addslashes($ord->package_names_summary) }}')"
                        class="py-2.5 px-3 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs text-center transition-colors flex items-center justify-center gap-1">
                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Batal
                    </button>
                    @endif

                    @if($ord->status === 'lunas')
                    <button type="button" onclick="openRequestDeleteModal({{ $ord->id }}, '{{ $ord->order_number }}')"
                        class="flex-1 touch-target px-4 py-2.5 rounded-xl text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 font-semibold text-xs transition-colors text-center">
                        Ajukan Batal
                    </button>
                    @endif

                    @if($ord->status === 'menunggu_approval_hapus')
                    <span class="w-full text-center text-rose-600 italic text-xs font-semibold">Menunggu Review Super Admin</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="p-6 text-center text-slate-400 text-xs font-medium">Tidak ada data pesanan yang sesuai filter.</div>
            @endforelse
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $orders->links() }}
        </div>
    </div>
</div>

<!-- 1. Modal Konfirmasi Bayar -->
<div id="confirmModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm transition-all">
    <div class="bg-white rounded-[28px] max-w-md w-full p-6 sm:p-7 shadow-floating border border-slate-200/80 space-y-4">
        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
            <h3 class="text-sm font-extrabold text-slate-900">Konfirmasi Pembayaran</h3>
            <button type="button" onclick="closeConfirmModal()" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-100 text-xs transition-colors">✕</button>
        </div>

        <form id="confirmForm" action="" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="p-4 bg-blue-50/70 border border-blue-200/80 rounded-2xl space-y-2 text-xs">
                <div class="flex justify-between items-center text-slate-600">
                    <span>No. Order:</span>
                    <span id="confirmOrderNumber" class="font-mono font-bold text-slate-900"></span>
                </div>
                <div class="flex justify-between items-center text-slate-600">
                    <span>Paket:</span>
                    <span id="confirmDetail" class="font-semibold text-slate-800"></span>
                </div>
                <div class="pt-2 border-t border-blue-200/70 flex justify-between items-center">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-900 block">Uang Kas yang Diterima</span>
                        <span id="confirmRate" class="text-[11px] text-blue-700"></span>
                    </div>
                    <div class="text-right">
                        <span id="confirmAmount" class="text-lg font-mono font-extrabold text-blue-900"></span>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Pilih Cara Pembayaran:</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center p-3 rounded-2xl border border-slate-200 cursor-pointer hover:bg-slate-50/70 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/40 transition-all">
                        <input type="radio" name="payment_method" value="cash" checked onchange="toggleModalProof(false)" class="text-blue-600">
                        <span class="ml-2 text-xs font-bold text-slate-800">💵 Tunai / Cash</span>
                    </label>
                    <label class="flex items-center p-3 rounded-2xl border border-slate-200 cursor-pointer hover:bg-slate-50/70 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/40 transition-all">
                        <input type="radio" name="payment_method" value="transfer" onchange="toggleModalProof(true)" class="text-blue-600">
                        <span class="ml-2 text-xs font-bold text-slate-800">🏦 Transfer Bank</span>
                    </label>
                </div>
            </div>

            <div id="modalProofBox" class="hidden space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Upload Foto Bukti Transfer (Wajib untuk transfer)</label>
                <input type="file" name="payment_proof_file" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
            </div>

            <div class="pt-3 flex justify-end gap-2.5 border-t border-slate-100">
                <button type="button" onclick="closeConfirmModal()" class="px-4 py-2 rounded-full text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-full bg-blue-600 text-white hover:bg-blue-700 font-bold text-xs shadow-sm shadow-blue-500/20 active:scale-[0.98] transition-all">
                    Selesaikan & Tandai Lunas
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Modal Batalkan Pesanan Langsung (Opsi A: Untuk Pesanan Belum Lunas / Salah Input) -->
<div id="cancelUnpaidModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm transition-all">
    <div class="bg-white rounded-[28px] max-w-md w-full p-6 shadow-floating border border-slate-200/80 space-y-4">
        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
            <div class="flex items-center gap-2 text-rose-600">
                <span class="w-8 h-8 rounded-full bg-rose-50 flex items-center justify-center font-bold text-sm">✕</span>
                <h3 class="text-sm font-extrabold text-slate-900">Batalkan Pesanan (Salah Input)</h3>
            </div>
            <button type="button" onclick="closeCancelUnpaidModal()" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-100 text-xs transition-colors">✕</button>
        </div>

        <form id="cancelUnpaidForm" action="" method="POST" class="space-y-4">
            @csrf
            <div class="p-4 rounded-2xl bg-amber-50/80 border border-amber-200/70 text-xs text-amber-900 leading-relaxed space-y-1">
                <div class="font-bold text-amber-950 flex items-center gap-1.5">
                    <span>⚡ Pembatalan Langsung (Tanpa Approval)</span>
                </div>
                <p>
                    Pesanan <strong id="cancelUnpaidOrderNumber" class="font-mono text-slate-900"></strong> berstatus <em>Belum Lunas</em>. Membatalkan pesanan ini akan <strong>otomatis mengembalikan <span id="cancelUnpaidQty" class="font-bold"></span> lembar stok voucher</strong> ke paket tanpa perlu persetujuan Super Admin.
                </p>
            </div>

            <div>
                <label for="cancel_reason" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alasan Pembatalan (Opsional)</label>
                <input type="text" name="cancel_reason" id="cancel_reason"
                    class="w-full p-3 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white"
                    placeholder="Contoh: Salah pilih warung / salah jumlah lembar / dibatalkan pembeli">
            </div>

            <div class="pt-3 flex justify-end gap-2.5 border-t border-slate-100">
                <button type="button" onclick="closeCancelUnpaidModal()" class="px-4 py-2 rounded-full text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">Kembali</button>
                <button type="submit" class="px-5 py-2.5 rounded-full bg-rose-600 text-white hover:bg-rose-700 font-bold text-xs shadow-sm shadow-rose-500/20 active:scale-[0.98] transition-all">
                    Ya, Batalkan & Kembalikan Stok
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 4. Modal Ajukan Hapus Pesanan (Khusus Pesanan Lunas) -->
<div id="requestDeleteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm transition-all">
    <div class="bg-white rounded-[28px] max-w-md w-full p-6 shadow-floating border border-slate-200/80 space-y-4">
        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
            <h3 class="text-sm font-extrabold text-slate-900">Pengajuan Pembatalan Transaksi Lunas</h3>
            <button type="button" onclick="closeRequestDeleteModal()" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-100 text-xs transition-colors">✕</button>
        </div>

        <form id="requestDeleteForm" action="" method="POST" class="space-y-4">
            @csrf
            <p class="text-xs text-slate-600 leading-relaxed">
                Anda mengajukan pembatalan untuk pesanan <strong id="deleteOrderNumber" class="text-slate-900 font-mono"></strong> yang sudah berstatus <em>Lunas</em>. Karena uang sudah masuk buku kas, transaksi membutuhkan persetujuan Super Admin.
            </p>

            <div>
                <label for="delete_reason" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alasan Pembatalan (Wajib Diisi)</label>
                <textarea name="delete_reason" id="delete_reason" required rows="3"
                    class="w-full p-3 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                    placeholder="Contoh: Warung salah sebut paket / voucher cacat cetak / uang ditarik kembali..."></textarea>
            </div>

            <div class="pt-3 flex justify-end gap-2.5 border-t border-slate-100">
                <button type="button" onclick="closeRequestDeleteModal()" class="px-4 py-2 rounded-full text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-full bg-rose-600 text-white hover:bg-rose-700 font-bold text-xs shadow-sm shadow-rose-500/20 active:scale-[0.98] transition-all">
                    Kirim Permohonan ke Super Admin
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openConfirmModal(id, orderNumber, qty, packageName, amountFormatted, rateText) {
        document.getElementById('confirmOrderNumber').innerText = orderNumber;
        document.getElementById('confirmDetail').innerText = packageName + ' (' + qty + ' Lembar)';
        document.getElementById('confirmAmount').innerText = amountFormatted || 'Rp 0';
        document.getElementById('confirmRate').innerText = rateText || '';
        document.getElementById('confirmForm').action = "/admin/orders/" + id + "/confirm-payment";
        document.getElementById('confirmModal').classList.remove('hidden');
    }
    function closeConfirmModal() {
        document.getElementById('confirmModal').classList.add('hidden');
    }
    function toggleModalProof(show) {
        document.getElementById('modalProofBox').classList.toggle('hidden', !show);
    }

    // Modal Batal Langsung (Opsi A)
    function openCancelUnpaidModal(id, orderNumber, qty, packageName) {
        document.getElementById('cancelUnpaidOrderNumber').innerText = orderNumber;
        document.getElementById('cancelUnpaidQty').innerText = qty;
        document.getElementById('cancelUnpaidForm').action = "/admin/orders/" + id + "/cancel-unpaid";
        document.getElementById('cancelUnpaidModal').classList.remove('hidden');
    }
    function closeCancelUnpaidModal() {
        document.getElementById('cancelUnpaidModal').classList.add('hidden');
    }

    // Modal Ajukan Hapus Lunas
    function openRequestDeleteModal(id, orderNumber) {
        document.getElementById('deleteOrderNumber').innerText = orderNumber;
        document.getElementById('requestDeleteForm').action = "/admin/orders/" + id + "/request-delete";
        document.getElementById('requestDeleteModal').classList.remove('hidden');
    }
    function closeRequestDeleteModal() {
        document.getElementById('requestDeleteModal').classList.add('hidden');
    }
</script>
@endsection
