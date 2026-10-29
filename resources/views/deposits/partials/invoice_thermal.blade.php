{{-- Partial Dokumen Saldo Mitra Format Struk Thermal (POS 58mm / 80mm) --}}
<div class="thermal-receipt bg-white text-slate-900 font-mono text-[11px] leading-tight p-3 sm:p-4 mx-auto border border-dashed border-slate-300 rounded-lg shadow-sm print:shadow-none print:border-none print:p-1" style="max-width: 78mm; width: 100%;">
    
    <!-- Store Header -->
    <div class="text-center pb-1">
        <div class="text-sm font-black uppercase tracking-wider">{{ $appName }}</div>
        <div class="text-[10px] text-slate-600">{{ $globalAppSubtitle ?? 'Layanan Internet & Voucher Jaringan' }}</div>
        <div class="text-[10px] text-slate-600">CS / WA: {{ $adminPhone }}</div>
    </div>

    <div class="border-b border-dashed border-slate-400 my-1.5"></div>

    <!-- Title & Doc Ref -->
    <div class="text-center font-bold text-xs py-0.5 tracking-wider">
        *** {{ strtoupper($docType) }} ***
    </div>

    <div class="space-y-0.5 text-[10px] py-1">
        <div class="flex justify-between">
            <span class="text-slate-500">No. Dok:</span>
            <span class="font-bold">{{ $docNumber }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Ref Saldo:</span>
            <span class="font-bold">{{ $deposit->deposit_number }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Tanggal:</span>
            <span>{{ $deposit->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Mitra/Warung:</span>
            <span class="font-bold text-right truncate max-w-[140px]">{{ $customerName }}</span>
        </div>
        @if($deposit->tenant?->phone)
        <div class="flex justify-between">
            <span class="text-slate-500">No. HP:</span>
            <span>{{ $deposit->tenant->phone }}</span>
        </div>
        @endif
        <div class="flex justify-between">
            <span class="text-slate-500">Petugas:</span>
            <span class="truncate max-w-[140px] text-right">{{ $cashierName }}</span>
        </div>
    </div>

    <div class="border-b border-dashed border-slate-400 my-1.5"></div>

    <!-- Items Listing -->
    <div class="py-1 space-y-1">
        <div>
            <div class="font-bold text-[11px]">Top Up Saldo Kuota Voucher Mandiri</div>
            <div class="text-[9px] text-slate-500">Cetak voucher mandiri</div>
            <div class="flex justify-between text-[10px] text-slate-700 mt-1">
                <span>Nominal Saldo:</span>
                <span class="font-extrabold text-slate-900">Rp {{ number_format($deposit->amount, 0, ',', '.') }}</span>
            </div>
            @if($isLunas && $deposit->merchant_profit > 0)
            <div class="flex justify-between text-[10px] text-blue-700">
                <span>Untung Mitra:</span>
                <span class="font-bold">+Rp {{ number_format($deposit->merchant_profit, 0, ',', '.') }}</span>
            </div>
            @endif
        </div>
    </div>

    <div class="border-b border-dashed border-slate-400 my-1.5"></div>

    <!-- Totals -->
    <div class="space-y-1 text-[10px] py-1">
        <div class="flex justify-between font-black text-xs pt-1 border-t border-dotted border-slate-300">
            <span>{{ $isLunas ? 'TOTAL DITERIMA:' : 'TOTAL ESTIMASI SETOR:' }}</span>
            <span>Rp {{ number_format($nominalCash, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between text-[10px] pt-0.5">
            <span class="text-slate-500">Status Pembayaran:</span>
            <span class="font-black {{ $isLunas ? 'text-emerald-700' : 'text-amber-700' }}">
                {{ $isLunas ? 'LUNAS (SAH)' : 'TEMPO (KONSINYASI)' }}
            </span>
        </div>
        @if($isLunas)
        <div class="flex justify-between text-[10px]">
            <span class="text-slate-500">Metode Bayar:</span>
            <span class="font-bold uppercase">{{ $deposit->payment_method === 'transfer' ? 'TRANSFER BANK' : 'TUNAI' }}</span>
        </div>
        <div class="flex justify-between text-[10px]">
            <span class="text-slate-500">Tgl Pelunasan:</span>
            <span>{{ $deposit->settled_at ? $deposit->settled_at->format('d/m/Y H:i') : ($deposit->confirmed_at ? $deposit->confirmed_at->format('d/m/Y H:i') : '-') }}</span>
        </div>
        @endif
    </div>

    <div class="border-b border-dashed border-slate-400 my-1.5"></div>

    <!-- Footer Note -->
    <div class="text-center text-[9px] text-slate-500 pt-1 space-y-0.5">
        @if($isLunas)
        <p class="font-bold text-slate-700">*** PEMBAYARAN SALDO LUNAS & SAH ***</p>
        <p>Terima kasih atas pelunasan setoran Anda.</p>
        @else
        <p class="font-bold text-amber-700">*** TAGIHAN SALDO KONSINYASI ***</p>
        <p>Harap disetor saat voucher telah terjual.</p>
        @endif
        <p class="text-[8px] text-slate-400 pt-1">Simpan struk ini sebagai bukti transaksi resmi.</p>
    </div>

</div>
