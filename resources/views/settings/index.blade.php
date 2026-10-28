@extends('layouts.app')

@section('title', 'Identitas Brand & Pengaturan')

@section('content')
<div class="max-w-2xl mx-auto py-2 space-y-6">
    <div class="bg-white p-6 sm:p-8 rounded-[28px] border border-slate-200/70 shadow-[0_8px_30px_rgb(0,0,0,0.04)] space-y-6">
        
        <!-- Header -->
        <div class="flex items-center gap-3.5 pb-5 border-b border-slate-100">
            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <h1 class="text-lg font-extrabold text-slate-900 tracking-tight">Identitas Brand & Pengaturan</h1>
                <p class="text-xs text-slate-500 mt-0.5">Kustomisasi logo, nama usaha, favicon, kontak WhatsApp, dan rekening pembayaran.</p>
            </div>
        </div>

        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Section 1: Identitas Merek & White-Label -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    <h2 class="text-xs font-black uppercase tracking-wider text-slate-800">1. Identitas Brand & Penanggung Jawab Usaha</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="app_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Brand / Usaha</label>
                        <input type="text" name="app_name" id="app_name" value="{{ old('app_name', $appName) }}" required
                            class="w-full px-4 py-3 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                            placeholder="Contoh: Berkah Net / RT 04 WiFi">
                        <p class="text-[10px] text-slate-400 mt-1">Menggantikan sebutan RT/RW Net di header, faktur, struk kasir, dan laporan.</p>
                    </div>

                    <div>
                        <label for="app_subtitle" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Slogan / Deskripsi Singkat</label>
                        <input type="text" name="app_subtitle" id="app_subtitle" value="{{ old('app_subtitle', $appSubtitle) }}"
                            class="w-full px-4 py-3 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                            placeholder="Contoh: Layanan Hotspot & Voucher Murah">
                        <p class="text-[10px] text-slate-400 mt-1">Teks pelengkap di bawah nama usaha.</p>
                    </div>

                    <div>
                        <label for="owner_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Pemilik / Owner Usaha</label>
                        <input type="text" name="owner_name" id="owner_name" value="{{ old('owner_name', $ownerName) }}"
                            class="w-full px-4 py-3 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                            placeholder="Contoh: Seno Bumi Munajat / H. Ahmad">
                        <p class="text-[10px] text-slate-400 mt-1">Dicetak pada kolom tanda tangan: <b>Mengetahui, Owner / Pengelola [Nama Brand]</b>.</p>
                    </div>

                    <div>
                        <label for="owner_title" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Sebutan Jabatan Owner</label>
                        <input type="text" name="owner_title" id="owner_title" value="{{ old('owner_title', $ownerTitle ?? 'Owner / Pengelola') }}"
                            class="w-full px-4 py-3 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                            placeholder="Contoh: Owner / Pengelola">
                        <p class="text-[10px] text-slate-400 mt-1">Menggantikan teks 'Ketua / Pengelola'. Bawaan: <b>Owner / Pengelola</b>.</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="finance_officer_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Bendahara / Petugas Keuangan (Opsional)</label>
                        <input type="text" name="finance_officer_name" id="finance_officer_name" value="{{ old('finance_officer_name', $financeOfficerName) }}"
                            class="w-full px-4 py-3 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                            placeholder="Kosongkan jika ingin otomatis menggunakan nama akun yang mencetak laporan">
                        <p class="text-[10px] text-slate-400 mt-1">Dicetak pada kolom tanda tangan: <b>Dibuat Oleh, Bendahara / Bagian Keuangan</b>.</p>
                    </div>
                </div>

                <!-- Custom Logo Upload Box with Live Preview -->
                <div class="p-4 bg-slate-50/80 border border-slate-200/80 rounded-2xl space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-extrabold text-slate-800 block">Logo Utama (Navbar, Login, Faktur)</span>
                            <span class="text-[10px] text-slate-400">Format: PNG, JPG, SVG, WebP (Maks. 2 MB). Disarankan latar transparan.</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-4 pt-2">
                        <!-- Preview Box -->
                        <div class="w-32 h-16 rounded-xl border-2 border-dashed border-slate-300 bg-white flex items-center justify-center p-2 relative overflow-hidden shrink-0 shadow-2xs">
                            <img id="logoPreviewImg" src="{{ $appLogoUrl ?: '' }}" alt="Pratinjau Logo" class="{{ $appLogoUrl ? '' : 'hidden' }} max-h-full max-w-full object-contain">
                            <div id="logoDefaultPlaceholder" class="{{ $appLogoUrl ? 'hidden' : 'flex' }} flex-col items-center justify-center text-slate-400 text-center">
                                <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="text-[9px] font-bold text-slate-400 mt-0.5">Logo Bawaan</span>
                            </div>
                        </div>

                        <!-- Upload Controls -->
                        <div class="flex-1 w-full space-y-2">
                            <input type="file" name="app_logo" id="app_logo" accept="image/png,image/jpeg,image/webp,image/svg+xml" onchange="previewImage(this, 'logoPreviewImg', 'logoDefaultPlaceholder')"
                                class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                            @if($appLogoUrl)
                            <label class="inline-flex items-center gap-2 cursor-pointer pt-1">
                                <input type="checkbox" name="remove_logo" value="1" class="w-3.5 h-3.5 rounded text-rose-600 border-slate-300 focus:ring-rose-500">
                                <span class="text-xs font-bold text-rose-600 hover:text-rose-700">Hapus Logo Kustom (Gunakan Logo Bawaan)</span>
                            </label>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Custom Favicon Upload Box with Live Preview -->
                <div class="p-4 bg-slate-50/80 border border-slate-200/80 rounded-2xl space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-extrabold text-slate-800 block">Favicon Browser Tab</span>
                            <span class="text-[10px] text-slate-400">Ikon kecil di tab browser (Format: ICO, PNG, SVG, maks. 1 MB).</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-4 pt-1">
                        <!-- Favicon Preview Box -->
                        <div class="w-12 h-12 rounded-xl border-2 border-dashed border-slate-300 bg-white flex items-center justify-center p-1.5 relative overflow-hidden shrink-0 shadow-2xs">
                            <img id="faviconPreviewImg" src="{{ $appFaviconUrl ?: asset('favicon.svg') }}" alt="Favicon" class="max-h-full max-w-full object-contain">
                        </div>

                        <!-- Favicon Controls -->
                        <div class="flex-1 w-full space-y-2">
                            <input type="file" name="app_favicon" id="app_favicon" accept="image/x-icon,image/png,image/svg+xml" onchange="previewImage(this, 'faviconPreviewImg')"
                                class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                            @if($appFaviconUrl)
                            <label class="inline-flex items-center gap-2 cursor-pointer pt-1">
                                <input type="checkbox" name="remove_favicon" value="1" class="w-3.5 h-3.5 rounded text-rose-600 border-slate-300 focus:ring-rose-500">
                                <span class="text-xs font-bold text-rose-600 hover:text-rose-700">Kembalikan Favicon ke Bawaan</span>
                            </label>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: WhatsApp & Rekening Pembayaran -->
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    <h2 class="text-xs font-black uppercase tracking-wider text-slate-800">2. Kontak & Rekening Kasir</h2>
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
                            Saat warung memesan voucher di HP mereka, tombol WhatsApp otomatis membuka chat ke nomor ini dengan format draft pesanan resmi.
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
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
                <button type="submit" class="px-6 py-2.5 rounded-full bg-blue-600 text-white hover:bg-blue-700 text-xs font-bold shadow-md shadow-blue-500/20 active:scale-[0.98] transition-all">
                    Simpan Semua Perubahan
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

<script>
function previewImage(input, previewImgId, placeholderId = null) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById(previewImgId);
            if (img) {
                img.src = e.target.result;
                img.classList.remove('hidden');
            }
            if (placeholderId) {
                const ph = document.getElementById(placeholderId);
                if (ph) ph.classList.add('hidden');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
