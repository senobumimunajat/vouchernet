@extends('layouts.app')

@section('title', 'Deposit & Saldo Mitra')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-blue-600"></span>
                <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900">Deposit & Saldo Merchant</h1>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Kelola pembelian kuota saldo cetak mandiri dan penagihan tempo konsinyasi.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="openCreateDepositModal()" class="touch-target inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm shadow-blue-500/20 transition-all active:scale-95">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                + Input Saldo Baru (Walk-in / WA)
            </button>
        </div>
    </div>

    <!-- Operational KPI Mini Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Menunggu Konfirmasi -->
        <div class="bg-white p-5 rounded-[24px] border border-slate-200/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Menunggu Konfirmasi</div>
                <div class="text-2xl font-extrabold font-mono text-amber-600 mt-1">
                    {{ number_format($pendingCount) }} <span class="text-xs font-sans text-slate-400 font-normal">permintaan</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- 2. Konsinyasi Berjalan -->
        <div class="bg-white p-5 rounded-[24px] border border-slate-200/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Konsinyasi Tempo Aktif</div>
                <div class="text-2xl font-extrabold font-mono text-blue-600 mt-1">
                    {{ number_format($activeTempoCount) }} <span class="text-xs font-sans text-slate-400 font-normal">transaksi</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            </div>
        </div>

        <!-- 3. Piutang Saldo Tempo -->
        <div class="bg-white p-5 rounded-[24px] border border-slate-200/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tagihan Saldo Tempo</div>
                <div class="text-xl font-extrabold font-mono text-rose-600 mt-1 truncate">
                    Rp {{ number_format($activeTempoAmount, 0, ',', '.') }}
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6H1.5m8.25-1.5h1.5m-1.5 0v.75A.75.75 0 0012 6h1.5m6-1.5h.75a.75.75 0 01.75.75V6m0 0v1.5m0-1.5h-1.5"/></svg>
            </div>
        </div>

        <!-- 4. Kas Masuk Saldo Bulan Ini -->
        <div class="bg-white p-5 rounded-[24px] border border-slate-200/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kas Masuk Saldo (Bulan Ini)</div>
                <div class="text-xl font-extrabold font-mono text-emerald-600 mt-1 truncate">
                    Rp {{ number_format($currentMonthPaid, 0, ',', '.') }}
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-[0_2px_12px_rgb(0,0,0,0.02)]">
        <form method="GET" action="{{ route('admin.deposits.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Transaksi</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Deposit / Akun / Warung..."
                    class="w-full px-3.5 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Mitra Warung</label>
                <select name="tenant_id" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                    <option value="">-- Semua Mitra Warung --</option>
                    @foreach($tenants as $t)
                    <option value="{{ $t->id }}" {{ request('tenant_id') == $t->id ? 'selected' : '' }}>{{ $t->shop_name ?: $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                    <option value="">-- Semua Status --</option>
                    <option value="menunggu_konfirmasi" {{ request('status') === 'menunggu_konfirmasi' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                    <option value="tempo" {{ request('status') === 'tempo' ? 'selected' : '' }}>Konsinyasi (Tempo Aktif)</option>
                    <option value="lunas" {{ request('status') === 'lunas' ? 'selected' : '' }}>Lunas</option>
                    <option value="dibatalkan" {{ request('status') === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs">
                    Filter
                </button>
                <a href="{{ route('admin.deposits.index') }}" class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition-all">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table of Merchant Deposits -->
    <div class="bg-white rounded-[28px] border border-slate-200/80 shadow-[0_4px_24px_rgb(0,0,0,0.03)] overflow-hidden">
        
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="py-3.5 px-5">No. Deposit / Tanggal</th>
                        <th class="py-3.5 px-5">Mitra Warung</th>
                        <th class="py-3.5 px-5">Nominal Saldo</th>
                        <th class="py-3.5 px-5">Pembayaran</th>
                        <th class="py-3.5 px-5">Status</th>
                        <th class="py-3.5 px-5">Realisasi Setor & Untung</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($deposits as $dep)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="py-4 px-5">
                            <span class="font-mono font-bold text-slate-900 block">{{ $dep->deposit_number }}</span>
                            <span class="text-[10px] text-slate-400">{{ $dep->deposit_date->translatedFormat('d M Y') }}</span>
                            @if($dep->notes)
                            <span class="block text-[11px] text-slate-500 italic mt-0.5 max-w-xs truncate" title="{{ $dep->notes }}">
                                💬 {{ $dep->notes }}
                            </span>
                            @endif
                        </td>
                        <td class="py-4 px-5">
                            <span class="font-bold text-slate-900 block">{{ $dep->tenant->shop_name ?: $dep->tenant->name }}</span>
                            <span class="text-[11px] text-slate-400 font-mono">{{ $dep->tenant->phone }}</span>
                        </td>
                        <td class="py-4 px-5 font-mono font-extrabold text-blue-700 text-sm">
                            Rp {{ number_format($dep->amount, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-5">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $dep->status === 'tempo' ? 'bg-amber-50 text-amber-700 border border-amber-200/60' : 'bg-slate-100 text-slate-700' }}">
                                @if($dep->status === 'lunas')
                                    Lunas {{ strtoupper($dep->payment_method) }}
                                @else
                                    {{ $dep->payment_type === 'tempo' ? 'Konsinyasi (Tempo)' : 'Lunas ' . strtoupper($dep->payment_method) }}
                                @endif
                            </span>
                            @if($dep->payment_proof_file)
                            <a href="{{ Storage::url($dep->payment_proof_file) }}" target="_blank" class="block text-[10px] font-bold text-blue-600 hover:underline mt-1">
                                📎 Bukti Transfer
                            </a>
                            @endif
                        </td>
                        <td class="py-4 px-5">
                            @if($dep->status === 'lunas')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Lunas
                            </span>
                            @elseif($dep->status === 'tempo')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Tempo Aktif
                            </span>
                            @elseif($dep->status === 'menunggu_konfirmasi')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span> Menunggu Konfirmasi
                            </span>
                            @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">Dibatalkan</span>
                            @endif
                        </td>
                        <td class="py-4 px-5 font-mono">
                            @if($dep->actual_paid_amount !== null)
                            <div class="text-slate-900 font-extrabold text-xs">Kas: Rp {{ number_format($dep->actual_paid_amount, 0, ',', '.') }}</div>
                            @if($dep->merchant_profit > 0)
                            <div class="text-emerald-600 font-bold text-[10px] font-sans">Untung Mitra: Rp {{ number_format($dep->merchant_profit, 0, ',', '.') }}</div>
                            @endif
                            @else
                            <span class="text-xs font-bold text-amber-600">Belum Disetor</span>
                            @endif
                        </td>
                        <td class="py-4 px-5 text-right space-x-1">
                            @if($dep->status === 'menunggu_konfirmasi')
                            <!-- Tahap 1: Saldo Belum Diisi -> HANYA Tombol Verifikasi (Faktur BELUM Boleh Terbit) -->
                            <form action="{{ route('admin.deposits.confirm', $dep) }}" method="POST" class="inline" onsubmit="return confirm('Pastikan Anda sudah mengisikan saldo Rp {{ number_format($dep->amount, 0, ',', '.') }} ke akun mitra ini. Lanjutkan konfirmasi?');">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-all">
                                    Konfirmasi Saldo Diisi
                                </button>
                            </form>
                            <form action="{{ route('admin.deposits.cancel', $dep) }}" method="POST" class="inline" onsubmit="return confirm('Tolak permintaan saldo ini?');">
                                @csrf
                                <button type="submit" class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition-all">
                                    Tolak
                                </button>
                            </form>
                            @elseif($dep->status === 'tempo')
                            <!-- Tahap 2: Saldo Sudah Diisi (Tempo Aktif) -> HANYA 1 Tombol Faktur & Tombol Lunasi -->
                            <a href="{{ route('deposits.invoice', $dep) }}" target="_blank" class="px-2.5 py-1.5 rounded-xl bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200 font-bold text-xs shadow-2xs transition-all inline-flex items-center gap-1" title="Cetak Faktur Tagihan">
                                📄 Faktur Tagihan
                            </a>
                            <button type="button" onclick="openSettleModal({{ json_encode($dep) }}, '{{ addslashes($dep->tenant->shop_name ?: $dep->tenant->name) }}')"
                                class="px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition-all">
                                💵 Terima Setoran / Lunasi
                            </button>
                            @elseif($dep->status === 'lunas')
                            <!-- Tahap 3: Uang Sudah Disetor (Lunas) -> HANYA 1 Tombol Kwitansi Resmi -->
                            <a href="{{ route('deposits.invoice', $dep) }}" target="_blank" class="px-2.5 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 font-bold text-xs shadow-2xs transition-all inline-flex items-center gap-1" title="Cetak Kwitansi Pelunasan">
                                🧾 Kwitansi Lunas
                            </a>
                            @endif

                            @if(auth()->user()->isSuperAdmin() && $dep->status !== 'tempo')
                            <form action="{{ route('admin.deposits.destroy', $dep) }}" method="POST" class="inline" onsubmit="return confirm('Hapus permanen catatan saldo ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-300 hover:text-rose-600 p-1 text-xs" title="Hapus">✕</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400 font-medium">Belum ada catatan transaksi saldo merchant.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Adaptive Cards View -->
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($deposits as $dep)
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="font-bold text-slate-900 text-xs block">{{ $dep->tenant->shop_name ?: $dep->tenant->name }}</span>
                        <span class="text-[10px] text-slate-400 font-mono">{{ $dep->deposit_number }} • {{ $dep->deposit_date->translatedFormat('d M Y') }}</span>
                    </div>
                    <span class="font-mono font-extrabold text-blue-700 text-base">
                        Rp {{ number_format($dep->amount, 0, ',', '.') }}
                    </span>
                </div>

                <div class="flex items-center justify-between text-xs">
                    <div>
                        @if($dep->status === 'lunas')
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Lunas {{ strtoupper($dep->payment_method) }}
                        </span>
                        @elseif($dep->status === 'tempo')
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Tempo Aktif</span>
                        @elseif($dep->status === 'menunggu_konfirmasi')
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">Menunggu Diisi</span>
                        @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">Dibatalkan</span>
                        @endif

                        @if($dep->payment_proof_file)
                        <a href="{{ Storage::url($dep->payment_proof_file) }}" target="_blank" class="block text-[10px] font-bold text-blue-600 hover:underline mt-1">
                            📎 Bukti Transfer
                        </a>
                        @endif
                    </div>

                    <div class="text-right font-mono text-xs">
                        @if($dep->actual_paid_amount !== null)
                        <span class="font-bold text-slate-900">Kas: Rp {{ number_format($dep->actual_paid_amount, 0, ',', '.') }}</span>
                        @if($dep->merchant_profit > 0)
                        <span class="block text-[10px] font-sans font-bold text-emerald-600">+Untung Rp {{ number_format($dep->merchant_profit, 0, ',', '.') }}</span>
                        @endif
                        @else
                        <span class="text-amber-600 font-bold">Belum Setor</span>
                        @endif
                    </div>
                </div>

                <!-- Mobile Action Buttons -->
                <div class="pt-1 flex items-center justify-end gap-2 border-t border-slate-50 flex-wrap">
                    @if($dep->status === 'menunggu_konfirmasi')
                    <form action="{{ route('admin.deposits.confirm', $dep) }}" method="POST" class="inline" onsubmit="return confirm('Pastikan saldo sudah diisikan ke akun mitra. Konfirmasi?');">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-600 text-white font-bold text-xs">
                            Konfirmasi Diisi
                        </button>
                    </form>
                    <form action="{{ route('admin.deposits.cancel', $dep) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-2.5 py-1.5 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs">
                            Tolak
                        </button>
                    </form>
                    @elseif($dep->status === 'tempo')
                    <a href="{{ route('deposits.invoice', $dep) }}" target="_blank" class="px-2.5 py-1.5 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 font-bold text-xs inline-flex items-center gap-1">
                        📄 Faktur Tagihan
                    </a>
                    <button type="button" onclick="openSettleModal({{ json_encode($dep) }}, '{{ addslashes($dep->tenant->shop_name ?: $dep->tenant->name) }}')"
                        class="flex-1 py-1.5 px-3 rounded-xl bg-blue-600 text-white font-bold text-xs flex items-center justify-center gap-1.5">
                        💵 Terima Setoran / Lunasi
                    </button>
                    @elseif($dep->status === 'lunas')
                    <a href="{{ route('deposits.invoice', $dep) }}" target="_blank" class="px-2.5 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold text-xs inline-flex items-center gap-1">
                        🧾 Kwitansi Lunas
                    </a>
                    @endif
                </div>
            </div>
            @empty
            <div class="p-6 text-center text-slate-400 text-xs font-medium">Belum ada catatan transaksi saldo merchant.</div>
            @endforelse
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $deposits->links() }}
        </div>
    </div>

</div>

<!-- MODAL 1: Input Saldo Baru oleh Admin (Walk-In / WA) -->
<div id="modalCreateDeposit" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm hidden animate-fade-in">
    <div class="bg-white rounded-[28px] max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    Input Pembelian Saldo Baru
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Catat saldo kuota yang diisikan ke akun merchant.</p>
            </div>
            <button type="button" onclick="closeCreateDepositModal()" class="text-slate-400 hover:text-slate-600 text-base font-bold p-1">✕</button>
        </div>

        <form action="{{ route('admin.deposits.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <!-- Pilih Mitra -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Pilih Mitra Warung</label>
                <select name="tenant_id" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Mitra Warung --</option>
                    @foreach($tenants as $t)
                    <option value="{{ $t->id }}">{{ $t->shop_name ?: $t->name }} ({{ $t->phone }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Nominal Saldo -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nominal Saldo Diisi (Rp)</label>
                <input type="text" inputmode="numeric" name="amount" id="adminDepositAmount" required placeholder="Contoh: 500.000" oninput="formatRupiahInput(this); syncPaidAmount(this.value);"
                    class="rupiah-input w-full px-4 py-2.5 text-sm font-mono font-extrabold bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
            </div>

            <!-- Tipe Pembayaran -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Sistem Pembayaran</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 bg-blue-50/40 cursor-pointer">
                        <input type="radio" name="payment_type" value="lunas" checked onchange="toggleAdminPayType('lunas')">
                        <span class="text-xs font-bold text-slate-800">Lunas Langsung</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 cursor-pointer">
                        <input type="radio" name="payment_type" value="tempo" onchange="toggleAdminPayType('tempo')">
                        <span class="text-xs font-bold text-slate-800">Konsinyasi (Tempo)</span>
                    </label>
                </div>
            </div>

            <!-- Bagian jika Lunas: Nominal Riil Disetor & Metode -->
            <div id="sectionAdminLunas" class="space-y-3 bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/80">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Uang Disetor ke Owner (Rp)</label>
                        <input type="text" inputmode="numeric" name="actual_paid_amount" id="adminPaidAmount" placeholder="Sama dengan saldo" oninput="formatRupiahInput(this)"
                            class="rupiah-input w-full px-3 py-2 text-xs font-mono font-extrabold bg-white border border-slate-200 rounded-lg text-slate-900">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Metode Bayar</label>
                        <select name="payment_method" id="adminPaymentMethod" onchange="toggleAdminCreateProof(this.value)" class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-lg text-slate-800 font-medium">
                            <option value="cash">Tunai (Cash)</option>
                            <option value="transfer">Transfer Bank</option>
                        </select>
                    </div>
                </div>
                <div id="sectionAdminCreateProof" class="hidden">
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Upload Bukti Transfer <span class="text-slate-400 font-normal lowercase">(opsional)</span></label>
                    <input type="file" name="payment_proof_file" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                </div>
                <p class="text-[10px] text-slate-400 italic">*Jika uang disetor lebih kecil dari saldo, selisihnya otomatis dicatat sebagai keuntungan mitra.</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal</label>
                    <input type="date" name="deposit_date" required value="{{ date('Y-m-d') }}"
                        class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">ID Akun / Catatan</label>
                    <input type="text" name="notes" placeholder="ID Akun / Catatan..."
                        class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-medium">
                </div>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2">
                <button type="button" onclick="closeCreateDepositModal()" class="py-2.5 px-4 text-xs font-bold text-slate-500 hover:text-slate-800">Batal</button>
                <button type="submit" class="py-2.5 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm shadow-blue-500/20">
                    + Simpan Transaksi Saldo
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: Pelunasan Saldo Konsinyasi (Settlement Modal) -->
<div id="modalSettleDeposit" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm hidden animate-fade-in">
    <div class="bg-white rounded-[28px] max-w-md w-full p-6 sm:p-7 shadow-2xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    Pelunasan Saldo Konsinyasi
                </h3>
                <p class="text-xs text-slate-400 mt-0.5" id="settleShopInfo">Warung Bpk / Ibu</p>
            </div>
            <button type="button" onclick="closeSettleModal()" class="text-slate-400 hover:text-slate-600 text-base font-bold p-1">✕</button>
        </div>

        <form id="settleForm" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <!-- Info Nominal Saldo Awal -->
            <div class="p-3.5 bg-blue-50/60 rounded-2xl border border-blue-200/70 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-extrabold uppercase text-blue-600 tracking-wider">Saldo Kuota Awal</span>
                    <span class="font-mono font-extrabold text-blue-900 text-lg block" id="settleOriginalAmount">Rp 0</span>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-bold text-slate-400 font-mono block" id="settleDepositNumber">DEP-XXXX</span>
                    <span class="text-[10px] font-bold text-amber-700 bg-amber-100/80 px-2 py-0.5 rounded-full">Tempo Aktif</span>
                </div>
            </div>

            <!-- Input Uang Disetor Riil -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Jumlah Uang yang Disetor Mitra (Rp)
                </label>
                <input type="text" inputmode="numeric" name="actual_paid_amount" id="settlePaidInput" required oninput="formatRupiahInput(this); calculateSettleProfit();"
                    class="rupiah-input w-full px-4 py-3 text-base font-mono font-extrabold bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                <span class="text-[11px] text-slate-400 mt-1 block">Ketik nominal uang riil yang dibawa/ditransfer oleh mitra.</span>
            </div>

            <!-- Live Calculation Preview -->
            <div class="grid grid-cols-2 gap-2.5 p-3.5 bg-slate-50 rounded-2xl border border-slate-200/70">
                <div>
                    <span class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">Kas Masuk Owner</span>
                    <span class="font-mono font-extrabold text-emerald-700 text-sm block mt-0.5" id="calcOwnerCash">Rp 0</span>
                </div>
                <div class="border-l border-slate-200 pl-3">
                    <span class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">Keuntungan Mitra</span>
                    <span class="font-mono font-extrabold text-blue-700 text-sm block mt-0.5" id="calcMerchantProfit">Rp 0</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Metode Bayar</label>
                    <select name="payment_method" id="settlePaymentMethod" onchange="toggleSettleProofField(this.value)" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-medium">
                        <option value="cash">Tunai (Cash)</option>
                        <option value="transfer">Transfer Bank</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Setor</label>
                    <input type="date" name="settled_date" required value="{{ date('Y-m-d') }}"
                        class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-medium">
                </div>
            </div>

            <!-- Upload Bukti Transfer (Opsional jika Transfer) -->
            <div id="sectionSettleProof" class="hidden p-3.5 bg-blue-50/50 rounded-2xl border border-blue-200/70 space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Upload Bukti Transfer <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                    </label>
                    <span class="text-[10px] font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded-full">Transfer</span>
                </div>
                <input type="file" name="payment_proof_file" id="settleProofInput" accept="image/*"
                    class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                <p class="text-[10px] text-slate-400 leading-relaxed">
                    Format file gambar: JPG, PNG, WEBP (maks. 5MB). Boleh dikosongkan jika bukti belum tersedia.
                </p>
                <div id="settleExistingProof" class="hidden text-[11px] pt-1.5 border-t border-blue-200/60 flex items-center gap-1.5">
                    <span class="text-slate-500">Bukti tersimpan:</span>
                    <a href="#" id="settleExistingProofLink" target="_blank" class="font-bold text-blue-600 hover:underline">
                        📎 Buka File Struk
                    </a>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Catatan Pelunasan (Opsional)</label>
                <input type="text" name="settlement_notes" placeholder="Contoh: Lunas disetor transfer via BCA"
                    class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-medium">
            </div>

            <div class="pt-2 flex items-center justify-end gap-2">
                <button type="button" onclick="closeSettleModal()" class="py-2.5 px-4 text-xs font-bold text-slate-500 hover:text-slate-800">Batal</button>
                <button type="submit" class="py-2.5 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm shadow-blue-500/20">
                    Simpan Pelunasan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentSettleDeposit = null;

function formatRupiahInput(input) {
    let value = input.value.replace(/[^0-9]/g, '');
    if (value) {
        input.value = new Intl.NumberFormat('id-ID').format(value);
    } else {
        input.value = '';
    }
}

function syncPaidAmount(val) {
    const paidInput = document.getElementById('adminPaidAmount');
    if (!paidInput.value || paidInput.dataset.touched !== 'true') {
        paidInput.value = val;
    }
}

function openCreateDepositModal() {
    document.getElementById('modalCreateDeposit').classList.remove('hidden');
}

function closeCreateDepositModal() {
    document.getElementById('modalCreateDeposit').classList.add('hidden');
}

function toggleAdminPayType(type) {
    const sectionLunas = document.getElementById('sectionAdminLunas');
    if (type === 'lunas') {
        sectionLunas.classList.remove('hidden');
    } else {
        sectionLunas.classList.add('hidden');
    }
}

function toggleAdminCreateProof(method) {
    const section = document.getElementById('sectionAdminCreateProof');
    if (section) {
        if (method === 'transfer') {
            section.classList.remove('hidden');
        } else {
            section.classList.add('hidden');
        }
    }
}

function toggleSettleProofField(method) {
    const section = document.getElementById('sectionSettleProof');
    if (section) {
        if (method === 'transfer') {
            section.classList.remove('hidden');
        } else {
            section.classList.add('hidden');
        }
    }
}

function openSettleModal(dep, shopName) {
    currentSettleDeposit = dep;
    document.getElementById('settleShopInfo').innerText = shopName + ' • ' + dep.deposit_number;
    document.getElementById('settleDepositNumber').innerText = dep.deposit_number;
    document.getElementById('settleOriginalAmount').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(dep.amount);
    
    // Set default paid amount = deposit amount
    const paidInput = document.getElementById('settlePaidInput');
    paidInput.value = new Intl.NumberFormat('id-ID').format(dep.amount);
    
    // Set form action
    document.getElementById('settleForm').action = "{{ url('/admin/deposits') }}/" + dep.id + "/settle";

    // Metode Bayar & Upload Bukti Transfer (Opsional jika Transfer)
    const methodSelect = document.getElementById('settlePaymentMethod');
    methodSelect.value = dep.payment_method === 'transfer' ? 'transfer' : 'cash';
    toggleSettleProofField(methodSelect.value);

    // Reset file input
    const proofInput = document.getElementById('settleProofInput');
    if (proofInput) proofInput.value = '';

    // Bukti tersimpan sebelumnya jika ada
    const existingProofBox = document.getElementById('settleExistingProof');
    const existingProofLink = document.getElementById('settleExistingProofLink');
    if (dep.payment_proof_file) {
        existingProofBox.classList.remove('hidden');
        existingProofLink.href = "/storage/" + dep.payment_proof_file;
    } else {
        existingProofBox.classList.add('hidden');
    }

    calculateSettleProfit();
    document.getElementById('modalSettleDeposit').classList.remove('hidden');
}

function closeSettleModal() {
    document.getElementById('modalSettleDeposit').classList.add('hidden');
    currentSettleDeposit = null;
}

function calculateSettleProfit() {
    if (!currentSettleDeposit) return;
    
    const rawVal = document.getElementById('settlePaidInput').value.replace(/[^0-9]/g, '');
    const paid = parseFloat(rawVal) || 0;
    const amount = parseFloat(currentSettleDeposit.amount) || 0;
    
    const profit = Math.max(0, amount - paid);

    document.getElementById('calcOwnerCash').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(paid);
    document.getElementById('calcMerchantProfit').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(profit);
}
</script>
@endsection
