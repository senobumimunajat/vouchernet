@extends('layouts.app')

@section('title', 'Pengaturan Sistem')

@section('content')
<div class="max-w-2xl mx-auto py-2">
    <div class="bg-white p-6 sm:p-8 rounded-[28px] border border-slate-200/70 shadow-[0_8px_30px_rgb(0,0,0,0.04)] space-y-6">
        
        <!-- Header -->
        <div class="flex items-center gap-3.5 pb-5 border-b border-slate-100">
            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <h1 class="text-lg font-extrabold text-slate-900 tracking-tight">Pengaturan WhatsApp & Rekening</h1>
                <p class="text-xs text-slate-500 mt-0.5">Konfigurasi nomor WhatsApp admin tujuan pesanan warung dan rekening pembayaran.</p>
            </div>
        </div>

        <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="app_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Hotspot RT/RW Net</label>
                <input type="text" name="app_name" id="app_name" value="{{ old('app_name', $appName) }}" required
                    class="w-full px-4 py-3 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                    placeholder="Contoh: VoucherNet RT 04">
            </div>

            <div>
                <label for="admin_whatsapp_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Nomor WhatsApp Admin Penerima Pesanan Warung
                </label>
                <div class="relative">
                    <input type="text" name="admin_whatsapp_number" id="admin_whatsapp_number" value="{{ old('admin_whatsapp_number', $adminWhatsapp) }}" required
                        class="w-full px-4 py-3 font-mono text-sm font-bold bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                        placeholder="Contoh: 6281234567890 (Gunakan kode negara 62)">
                </div>
                <div class="p-3 bg-blue-50/70 border border-blue-200/60 rounded-2xl mt-2 flex items-start gap-2.5">
                    <span class="text-blue-600 text-sm">💡</span>
                    <p class="text-[11px] text-blue-900 leading-relaxed">
                        Saat warung/tenant memesan voucher di HP mereka, tombol WhatsApp otomatis membuka chat ke nomor ini dengan format draft pesanan resmi.
                    </p>
                </div>
            </div>

            <div>
                <label for="bank_accounts" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Petunjuk Rekening Pembayaran (Bank / QRIS / E-Wallet)
                </label>
                <textarea name="bank_accounts" id="bank_accounts" rows="4" required
                    class="w-full p-4 font-mono text-xs bg-slate-50/80 border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all leading-relaxed"
                    placeholder="Contoh:&#10;BCA: 123456789 a.n Wi-Fi RT 04&#10;BRI: 987654321 a.n Wi-Fi RT 04&#10;DANA / GoPay: 081234567890">{{ old('bank_accounts', $bankAccounts) }}</textarea>
                <p class="text-[11px] text-slate-400 mt-1.5">Informasi rekening ini akan ditampilkan kepada warung/tenant saat checkout memilih transfer.</p>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
                <button type="submit" class="px-6 py-2.5 rounded-full bg-blue-600 text-white hover:bg-blue-700 text-xs font-bold shadow-md shadow-blue-500/20 active:scale-[0.98] transition-all">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>

    @if(auth()->user()->isSuperAdmin())
    <!-- Backup Database Mandiri (SQLite 1-Click Backup) -->
    <div class="bg-white p-6 sm:p-8 rounded-[28px] border border-slate-200/70 shadow-[0_8px_30px_rgb(0,0,0,0.04)] space-y-4">
        <div class="flex items-center justify-between gap-3 pb-4 border-b border-slate-100 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/></svg>
                </div>
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">Cadangan Database Mandiri (.sqlite)</h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Seluruh rekapan pesanan, stok, kas, dan laba tersimpan rapi dalam 1 file database.</p>
                </div>
            </div>
            @php
                $dbFile = database_path('database.sqlite');
                $dbSize = file_exists($dbFile) ? round(filesize($dbFile) / 1024, 1) . ' KB' : 'N/A';
            @endphp
            <span class="text-[11px] font-mono font-bold px-2.5 py-1 bg-slate-100 text-slate-700 rounded-full border border-slate-200">
                Ukuran: {{ $dbSize }}
            </span>
        </div>

        <div class="p-3.5 bg-emerald-50/60 border border-emerald-200/60 rounded-2xl flex items-start gap-2.5">
            <span class="text-emerald-600 text-sm">🛡️</span>
            <p class="text-[11px] text-emerald-950 leading-relaxed">
                Selain backup otomatis dari pihak hosting, Anda bisa mengunduh salinan database kapan saja ke laptop/HP. Jika suatu saat ingin berganti hosting atau pindah laptop, Anda cukup meng-upload file hasil unduhan ini.
            </p>
        </div>

        <div class="pt-1 flex justify-end">
            <a href="{{ route('settings.backup_database') }}" class="touch-target inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 active:scale-95 transition-all">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Unduh Cadangan Database Sekarang (.sqlite)
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
