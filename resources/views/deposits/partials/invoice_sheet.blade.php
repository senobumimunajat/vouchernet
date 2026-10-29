{{-- Dokumen Faktur / Kwitansi Saldo Mitra (Format Siap Cetak Kertas Setengah A4 / A5) --}}
<div class="half-sheet bg-white rounded-2xl p-4 sm:p-5 border border-slate-300 print:border-slate-300 print:p-3 print:rounded-xl shadow-sm print:shadow-none flex flex-col justify-between">
    
    <!-- HEADER KOP DOKUMEN -->
    <div class="flex items-start justify-between pb-2.5 border-b border-slate-200 gap-3">
        <div class="flex items-center gap-2.5">
            @if(!empty($globalAppLogo))
            <div class="h-9 sm:h-10 w-auto flex items-center justify-center shrink-0">
                <img src="{{ $globalAppLogo }}" alt="{{ $appName }}" class="h-8 sm:h-9 w-auto max-w-[130px] object-contain rounded-lg">
            </div>
            @else
            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm print:shadow-none">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            @endif
            <div>
                <h1 class="text-base sm:text-lg font-black tracking-tight text-slate-900 leading-none">{{ $appName }}</h1>
                <p class="text-[10px] text-slate-500 font-medium leading-tight mt-0.5">{{ $globalAppSubtitle ?? 'Layanan Hotspot & Distribusi Voucher Jaringan' }}</p>
                <p class="text-[9px] text-slate-400 font-mono">CS/Admin WA: {{ $adminPhone }}</p>
            </div>
        </div>

        <div class="text-right">
            <span class="inline-block text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full {{ $isLunas ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">
                {{ $docType }}
            </span>
            <div class="mt-1">
                <span class="text-xs sm:text-sm font-mono font-extrabold text-slate-900 tracking-tight block leading-tight">{{ $docNumber }}</span>
                <span class="text-[9px] text-slate-500 font-mono">Ref Transaksi: <strong class="text-slate-700">{{ $deposit->deposit_number }}</strong></span>
            </div>
        </div>
    </div>

    <!-- METADATA INFORMATION: MITRA & TRANSAKSI -->
    <div class="grid grid-cols-2 gap-2.5 py-2.5 border-b border-slate-200 text-[11px]">
        <!-- Kolom 1: Mitra / Warung -->
        <div class="space-y-0.5 bg-slate-50/90 p-2 sm:p-2.5 rounded-xl border border-slate-200/70">
            <div class="text-[9px] uppercase font-bold tracking-wider text-slate-400 leading-none mb-1">
                {{ $isLunas ? 'Telah Diterima Dari:' : 'Tagihan Ditujukan Kepada:' }}
            </div>
            <div class="text-xs font-black text-slate-900 truncate">
                {{ $customerName }}
            </div>
            @if($deposit->tenant)
            <div class="text-slate-600 truncate text-[10px]">
                <span class="font-semibold">Pemilik:</span> {{ $deposit->tenant->name }} (@ {{ $deposit->tenant->username }})
            </div>
            <div class="text-slate-600 font-mono text-[10px]">
                <span class="font-semibold font-sans">No. HP/WA:</span> {{ $deposit->tenant->phone ?: '-' }}
            </div>
            @if($deposit->tenant->address)
            <div class="text-slate-500 text-[9px] truncate">
                <span class="font-semibold">Alamat:</span> {{ $deposit->tenant->address }}
            </div>
            @endif
            @endif
        </div>

        <!-- Kolom 2: Status & Pembayaran -->
        <div class="space-y-0.5 bg-slate-50/90 p-2 sm:p-2.5 rounded-xl border border-slate-200/70 text-[10px]">
            <div class="text-[9px] uppercase font-bold tracking-wider text-slate-400 leading-none mb-1">
                Informasi Transaksi:
            </div>
            <div class="flex justify-between items-center py-0.2">
                <span class="text-slate-500">Tgl Permintaan:</span>
                <span class="font-semibold text-slate-800">{{ $deposit->created_at->translatedFormat('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between items-center py-0.2">
                <span class="text-slate-500">Status Pembayaran:</span>
                @if($isLunas)
                <span class="font-extrabold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200 text-[9px]">LUNAS (SAH)</span>
                @elseif($deposit->status === 'tempo')
                <span class="font-extrabold text-amber-800 bg-amber-50 px-1.5 py-0.2 rounded border border-amber-200 text-[9px]">TEMPO (KONSINYASI)</span>
                @else
                <span class="font-extrabold text-sky-800 bg-sky-50 px-1.5 py-0.2 rounded border border-sky-200 text-[9px]">MENUNGGU KONFIRMASI</span>
                @endif
            </div>
            @if($isLunas)
            <div class="flex justify-between items-center py-0.2">
                <span class="text-slate-500">Tgl Lunas:</span>
                <span class="font-semibold text-slate-800">{{ $deposit->settled_at ? $deposit->settled_at->translatedFormat('d/m/Y H:i') : ($deposit->confirmed_at ? $deposit->confirmed_at->translatedFormat('d/m/Y H:i') : '-') }}</span>
            </div>
            <div class="flex justify-between items-center py-0.2">
                <span class="text-slate-500">Metode Bayar:</span>
                <span class="font-bold uppercase text-slate-800">{{ $deposit->payment_method === 'transfer' ? 'TRANSFER BANK' : 'TUNAI (CASH)' }}</span>
            </div>
            @if($deposit->payment_proof_file)
            <div class="flex justify-between items-center py-0.2">
                <span class="text-slate-500">Bukti Struk:</span>
                <span class="font-semibold text-blue-700">Terlampir</span>
            </div>
            @endif
            <div class="flex justify-between items-center py-0.2">
                <span class="text-slate-500">Penerima Kas:</span>
                <span class="font-semibold text-slate-800 truncate max-w-[130px]">{{ $cashierName }}</span>
            </div>
            @else
            <div class="flex justify-between items-center py-0.2">
                <span class="text-slate-500">Sistem:</span>
                <span class="font-semibold text-amber-800">Konsinyasi / Bayar Nanti</span>
            </div>
            <div class="flex justify-between items-center py-0.2">
                <span class="text-slate-500">Petugas Saldo:</span>
                <span class="font-semibold text-slate-800 truncate max-w-[130px]">{{ $cashierName }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- DETAIL ITEM TABEL -->
    <div class="my-3">
        <table class="w-full text-left border-collapse text-[11px]">
            <thead>
                <tr class="border-b-2 border-slate-900 bg-slate-100/90 text-slate-700 font-bold uppercase text-[9px] tracking-wider">
                    <th class="py-1.5 px-2 text-center w-8">No</th>
                    <th class="py-1.5 px-2">Rincian Saldo / Layanan</th>
                    <th class="py-1.5 px-2 text-right">Nominal Saldo Kuota</th>
                    @if($isLunas && $deposit->merchant_profit > 0)
                    <th class="py-1.5 px-2 text-right">Potongan Bagi Hasil</th>
                    @endif
                    <th class="py-1.5 px-2 text-right">{{ $isLunas ? 'Kas Masuk Disetor' : 'Wajib Disetor' }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-slate-800">
                <tr>
                    <td class="py-2.5 px-2 text-center font-mono text-slate-500">1</td>
                    <td class="py-2.5 px-2">
                        <div class="font-extrabold text-slate-900 text-xs">
                            Top Up Saldo Kuota Voucher Mandiri
                        </div>
                        <div class="text-[10px] text-slate-500 mt-0.5">
                            Saldo kuota untuk cetak voucher mandiri
                        </div>
                        @if($deposit->notes)
                        <div class="text-[9px] text-slate-400 italic mt-0.5">
                            Catatan: {{ $deposit->notes }}
                        </div>
                        @endif
                    </td>
                    <td class="py-2.5 px-2 text-right font-mono font-bold text-slate-900">
                        Rp {{ number_format($deposit->amount, 0, ',', '.') }}
                    </td>
                    @if($isLunas && $deposit->merchant_profit > 0)
                    <td class="py-2.5 px-2 text-right font-mono font-bold text-blue-600">
                        -Rp {{ number_format($deposit->merchant_profit, 0, ',', '.') }}
                    </td>
                    @endif
                    <td class="py-2.5 px-2 text-right font-mono font-extrabold text-slate-900">
                        Rp {{ number_format($nominalCash, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
            <tfoot class="border-t-2 border-slate-900 text-[11px] font-bold">
                <tr>
                    <td colspan="{{ $isLunas && $deposit->merchant_profit > 0 ? '4' : '3' }}" class="py-2 px-2 text-right uppercase text-slate-700 text-[10px]">
                        {{ $isLunas ? 'TOTAL KAS DITERIMA DARI MITRA:' : 'TOTAL ESTIMASI WAJIB SETOR:' }}
                    </td>
                    <td class="py-2 px-2 text-right font-mono font-black text-slate-900 text-sm">
                        Rp {{ number_format($nominalCash, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- TERBILANG & CATATAN PELUNASAN -->
    <div class="bg-slate-50 p-2 sm:p-2.5 rounded-xl border border-slate-200/80 mb-3 text-[10px]">
        <div class="text-[9px] uppercase font-bold tracking-wider text-slate-400 mb-0.5">
            Terbilang:
        </div>
        <div class="italic font-bold text-slate-800 text-xs">
            # {{ $terbilang }} #
        </div>
        @if(!$isLunas)
        <div class="mt-1.5 pt-1.5 border-t border-slate-200 text-slate-500 text-[9px]">
            <span class="font-bold text-amber-800">Catatan Pelunasan:</span>
            Saldo kuota telah diserahkan/diisikan. Setoran uang dapat diserahkan tunai ke kasir atau via transfer bank ke rekening resmi RT/RW Net saat saldo telah laku terjual.
        </div>
        @endif
    </div>

    <!-- FOOTER STEMPEL & TANDA TANGAN (DUA PIHAK) -->
    <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-xs">
        
        <!-- Pihak 1: Penerima / Mitra Warung -->
        <div class="text-center w-40 sm:w-48 flex flex-col justify-between h-24">
            <span class="text-[10px] text-slate-500 font-medium">
                {{ $isLunas ? 'Penyetor / Mitra Warung,' : 'Penerima Saldo,' }}
            </span>
            <div class="mt-auto">
                <div class="border-b border-slate-400 w-32 sm:w-40 mx-auto"></div>
                <span class="font-extrabold text-slate-800 text-[11px] mt-1 block truncate">{{ $customerName }}</span>
            </div>
        </div>

        <!-- Cap Digital Tengah -->
        <div class="text-center px-2">
            @if($isLunas)
            <div class="stamp-lunas px-4 py-1.5 text-xs sm:text-sm shadow-xs">
                LUNAS (SAH)
                <span class="block text-[8px] font-mono tracking-tight font-normal text-emerald-800">
                    {{ $deposit->settled_at ? $deposit->settled_at->format('d/m/Y') : date('d/m/Y') }}
                </span>
            </div>
            @else
            <div class="stamp-tempo px-3.5 py-1 text-[11px] sm:text-xs">
                TEMPO
                <span class="block text-[8px] font-mono tracking-tight font-normal text-amber-800">
                    BELUM SETOR
                </span>
            </div>
            @endif
        </div>

        <!-- Pihak 2: Pengelola / Kasir Petugas -->
        <div class="text-center w-40 sm:w-48 flex flex-col justify-between h-24">
            <span class="text-[10px] text-slate-500 font-medium">
                {{ $isLunas ? 'Penerima Kas / Kasir,' : 'Petugas Pengelola Hotspot,' }}
            </span>
            <div class="mt-auto">
                <div class="border-b border-slate-400 w-32 sm:w-40 mx-auto"></div>
                <span class="font-extrabold text-slate-800 text-[11px] mt-1 block truncate">
                    {{ $cashierSigner ?? $cashierName }}
                </span>
            </div>
        </div>

    </div>

</div>
