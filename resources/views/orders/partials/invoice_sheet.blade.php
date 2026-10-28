{{-- Dokumen Faktur / Kwitansi (Format Siap Cetak Kertas Setengah A4) --}}
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
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" viewBox="0 0 256 256" fill="currentColor">
                    <rect x="66" y="44" width="34" height="168" rx="17" transform="rotate(-15 83 128)"/>
                    <circle cx="138" cy="188" r="15"/>
                    <rect x="146" y="112" width="28" height="54" rx="14" transform="rotate(15 160 139)"/>
                    <rect x="172" y="46" width="28" height="96" rx="14" transform="rotate(15 186 94)"/>
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
                <span class="text-[9px] text-slate-500 font-mono">Ref Order: <strong class="text-slate-700">{{ $order->order_number }}</strong></span>
            </div>
        </div>
    </div>

    <!-- METADATA INFORMATION: MITRA & TRANSAKSI -->
    <div class="grid grid-cols-2 gap-2.5 py-2 border-b border-slate-200 text-[11px]">
        <!-- Kolom 1: Penerima / Warung -->
        <div class="space-y-0.5 bg-slate-50/90 p-2 sm:p-2.5 rounded-xl border border-slate-200/70">
            <div class="text-[9px] uppercase font-bold tracking-wider text-slate-400 leading-none mb-1">
                {{ $isLunas ? 'Telah Diterima Dari:' : 'Tagihan Ditujukan Kepada:' }}
            </div>
            <div class="text-xs font-black text-slate-900 truncate">
                {{ $order->customer_display_name }}
            </div>
            @if($order->tenant)
            <div class="text-slate-600 truncate text-[10px]">
                <span class="font-semibold">Pemilik:</span> {{ $order->tenant->name }}
            </div>
            <div class="text-slate-600 font-mono text-[10px]">
                <span class="font-semibold font-sans">No. HP/WA:</span> {{ $order->tenant->phone ?: '-' }}
            </div>
            @if($order->tenant->address)
            <div class="text-slate-500 text-[9px] truncate">
                <span class="font-semibold">Alamat:</span> {{ $order->tenant->address }}
            </div>
            @endif
            @else
            <div class="text-slate-600 text-[10px]">
                <span class="font-semibold">Kategori:</span> Pelanggan Langsung (Walk-In)
            </div>
            @endif
        </div>

        <!-- Kolom 2: Status & Pembayaran -->
        <div class="space-y-0.5 bg-slate-50/90 p-2 sm:p-2.5 rounded-xl border border-slate-200/70 text-[10px]">
            <div class="text-[9px] uppercase font-bold tracking-wider text-slate-400 leading-none mb-1">
                Informasi Transaksi:
            </div>
            <div class="flex justify-between items-center py-0.2">
                <span class="text-slate-500">Tgl Terbit:</span>
                <span class="font-semibold text-slate-800">{{ $order->created_at->translatedFormat('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between items-center py-0.2">
                <span class="text-slate-500">Status Bayar:</span>
                @if($isLunas)
                <span class="font-extrabold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200 text-[9px]">LUNAS</span>
                @else
                <span class="font-extrabold text-amber-800 bg-amber-50 px-1.5 py-0.2 rounded border border-amber-200 text-[9px]">TEMPO (BELUM SETOR)</span>
                @endif
            </div>
            @if($isLunas)
            <div class="flex justify-between items-center py-0.2">
                <span class="text-slate-500">Tgl Lunas:</span>
                <span class="font-semibold text-slate-800">{{ $order->confirmed_at ? $order->confirmed_at->translatedFormat('d/m/Y H:i') : '-' }}</span>
            </div>
            <div class="flex justify-between items-center py-0.2">
                <span class="text-slate-500">Metode:</span>
                <span class="font-bold uppercase text-slate-800">{{ $order->payment_method ?? 'TUNAI' }}</span>
            </div>
            <div class="flex justify-between items-center py-0.2">
                <span class="text-slate-500">Kasir:</span>
                <span class="font-semibold text-slate-800 truncate max-w-[130px]">{{ $cashierName }}</span>
            </div>
            @else
            <div class="flex justify-between items-center py-0.2">
                <span class="text-slate-500">Transaksi:</span>
                <span class="font-semibold text-slate-800">Konsinyasi (Ambil Dulu)</span>
            </div>
            <div class="flex justify-between items-center py-0.2">
                <span class="text-slate-500">Batas Waktu:</span>
                <span class="font-semibold text-slate-800">Setelah Voucher Terjual</span>
            </div>
            <div class="flex justify-between items-center py-0.2">
                <span class="text-slate-500">Petugas:</span>
                <span class="font-semibold text-slate-800 truncate max-w-[130px]">{{ $cashierName }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- RINCIAN TABEL ITEM PESANAN -->
    <div class="py-2">
        <div class="border border-slate-200 rounded-xl overflow-hidden">
            <table class="w-full text-left text-[11px] border-collapse">
                <thead>
                    <tr class="bg-slate-100/80 text-slate-700 font-bold border-b border-slate-200 text-[10px]">
                        <th class="py-1 px-3">Paket Voucher</th>
                        <th class="py-1 px-3">Durasi</th>
                        <th class="py-1 px-3 text-center">Jumlah</th>
                        <th class="py-1 px-3 text-right">Harga Setor/Lbr</th>
                        <th class="py-1 px-3 text-right">Subtotal Setoran</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->effective_items as $item)
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="py-1.5 px-3 font-bold text-slate-900">
                            {{ $item->package?->name ?? 'Paket Voucher' }}
                        </td>
                        <td class="py-1.5 px-3 text-slate-600 text-[10px]">
                            {{ $item->package?->duration_info ?? '-' }}
                        </td>
                        <td class="py-1.5 px-3 text-center font-mono font-bold text-slate-900">
                            {{ number_format($item->quantity) }} lbr
                        </td>
                        <td class="py-1.5 px-3 text-right font-mono text-slate-700">
                            Rp {{ number_format($item->owner_price_each, 0, ',', '.') }}
                        </td>
                        <td class="py-1.5 px-3 text-right font-mono font-extrabold text-slate-900">
                            Rp {{ number_format($item->total_owner_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(!$order->isDirectCustomer())
        <div class="mt-0.5 px-1 flex items-center justify-between text-[9px] text-slate-500">
            <div>
                *Total Omzet Fisik: <span class="font-mono font-semibold text-slate-700">Rp {{ number_format($order->total_printed_amount, 0, ',', '.') }}</span>
                (Total: <span class="font-mono font-semibold text-slate-700">{{ number_format($order->quantity) }} lbr</span>)
            </div>
            <div class="text-blue-700 font-bold">
                Potensi Untung Warung: +Rp {{ number_format($order->total_reseller_profit, 0, ',', '.') }}
            </div>
        </div>
        @endif
    </div>

    <!-- TOTAL & TERBILANG SECTION -->
    <div class="grid grid-cols-12 gap-2.5 py-2 border-t border-b border-slate-200">
        <!-- Terbilang Box -->
        <div class="col-span-7 space-y-1">
            <div class="text-[9px] uppercase font-bold tracking-wider text-slate-400">Terbilang:</div>
            <div class="p-1.5 sm:p-2 bg-slate-50 border border-slate-200/70 rounded-lg text-[10px] font-semibold text-slate-700 italic leading-snug">
                "{{ $terbilang }}"
            </div>

            @if(!$isLunas)
            <!-- Instruksi Rekening -->
            <div class="p-1.5 bg-amber-50/70 border border-amber-200 rounded-lg text-[9px] text-amber-950 space-y-0.5">
                <div class="font-bold text-amber-900 flex items-center gap-1">
                    <svg class="w-3 h-3 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Instruksi Setoran Pembayaran:</span>
                </div>
                <div class="font-mono whitespace-pre-line pl-4 leading-tight">{{ $bankAccounts }}</div>
            </div>
            @else
            <div class="p-1.5 bg-emerald-50/70 border border-emerald-200 rounded-lg text-[9px] text-emerald-900 flex items-center gap-1.5">
                <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Kwitansi sah bukti pelunasan setoran voucher.</span>
            </div>
            @endif
        </div>

        <!-- Total Highlight -->
        <div class="col-span-5 flex flex-col justify-between bg-slate-50 p-2 sm:p-2.5 rounded-xl border border-slate-200/80">
            <div class="space-y-0.5 text-[10px]">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-mono font-bold text-slate-900">Rp {{ number_format($order->total_owner_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Biaya Admin:</span>
                    <span class="font-mono font-bold text-slate-900">Rp 0</span>
                </div>
            </div>

            <div class="pt-1 border-t border-slate-200">
                <div class="text-[9px] uppercase font-extrabold tracking-wider text-slate-500">
                    {{ $isLunas ? 'Total Diterima:' : 'Total Wajib Setor:' }}
                </div>
                <div class="text-lg sm:text-xl font-black font-mono {{ $isLunas ? 'text-emerald-700' : 'text-slate-900' }} leading-tight mt-0.5">
                    Rp {{ number_format($order->total_owner_amount, 0, ',', '.') }}
                </div>
                @if($isLunas)
                <div class="text-[9px] text-emerald-600 font-bold">
                    Sisa: Rp 0 (LUNAS)
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- STEMPEL DIGITAL & TANDA TANGAN -->
    <div class="pt-2.5 pb-1 flex items-center justify-between gap-4">
        <!-- Cap Stempel Resmi -->
        <div>
            @if($isLunas)
            <div class="stamp-lunas text-center py-1 px-3">
                <div class="text-[8px] tracking-widest font-mono">VOUCHERNET</div>
                <div class="text-xs sm:text-sm font-black my-0.2">★ LUNAS ★</div>
                <div class="text-[8px] font-mono tracking-normal normal-case">
                    {{ $order->confirmed_at ? $order->confirmed_at->format('d/m/Y') : now()->format('d/m/Y') }}
                </div>
                <div class="text-[8px] font-mono uppercase tracking-normal">
                    KASIR: {{ substr($cashierName, 0, 14) }}
                </div>
            </div>
            @else
            <div class="stamp-tempo text-center py-1 px-2.5">
                <div class="text-[8px] tracking-wider font-mono">KONSINYASI</div>
                <div class="text-[11px] sm:text-xs font-black my-0.2">MENUNGGU SETORAN</div>
                <div class="text-[8px] font-mono tracking-normal normal-case">
                    Voucher Diserahkan
                </div>
            </div>
            @endif
        </div>

        <!-- 2-Box Tanda Tangan -->
        <div class="flex items-center gap-7 sm:gap-10 text-center text-[10px]">
            <div>
                <p class="text-slate-400 font-medium text-[9px] mb-6 sm:mb-7">Penerima (Mitra)</p>
                <div class="border-t border-slate-300 pt-0.5 font-bold text-slate-800 text-[10px] truncate max-w-[120px]">
                    ( {{ $order->customer_display_name }} )
                </div>
            </div>
            <div>
                <p class="text-slate-400 font-medium text-[9px] mb-6 sm:mb-7">Kasir / Pengelola</p>
                <div class="border-t border-slate-300 pt-0.5 font-bold text-slate-800 text-[10px] truncate max-w-[120px]">
                    ( {{ $cashierName }} )
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER CATATAN -->
    <div class="pt-1 border-t border-slate-200 text-center text-[8px] text-slate-400 font-mono">
        Dokumen Komputerisasi {{ $appName }} • Dicetak: {{ now()->translatedFormat('d/m/Y H:i') }} WIB • ID: {{ $docNumber }}
    </div>

</div>
