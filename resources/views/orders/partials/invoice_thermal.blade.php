{{-- Partial Dokumen Format Struk Thermal (POS 58mm / 80mm) --}}
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
            <span class="text-slate-500">Ref Order:</span>
            <span class="font-bold">{{ $order->order_number }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Tanggal:</span>
            <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Mitra/Warung:</span>
            <span class="font-bold text-right truncate max-w-[140px]">{{ $order->customer_display_name }}</span>
        </div>
        @if($order->tenant?->phone)
        <div class="flex justify-between">
            <span class="text-slate-500">No. HP:</span>
            <span>{{ $order->tenant->phone }}</span>
        </div>
        @endif
        <div class="flex justify-between">
            <span class="text-slate-500">Kasir/Petugas:</span>
            <span class="truncate max-w-[140px] text-right">{{ $cashierName }}</span>
        </div>
    </div>

    <div class="border-b border-dashed border-slate-400 my-1.5"></div>

    <!-- Items Listing -->
    <div class="py-1 space-y-1.5">
        @foreach($order->effective_items as $item)
        <div>
            <div class="font-bold text-[11px]">{{ $item->package?->name ?? 'Paket Voucher' }}</div>
            <div class="flex justify-between text-[10px] text-slate-700 mt-0.5">
                <span>{{ $item->quantity }} lbr x Rp {{ number_format($item->owner_price_each, 0, ',', '.') }}</span>
                <span class="font-extrabold text-slate-900">Rp {{ number_format($item->total_owner_amount, 0, ',', '.') }}</span>
            </div>
            @if($item->package?->duration_info)
            <div class="text-[9px] text-slate-500">Durasi: {{ $item->package->duration_info }}</div>
            @endif
        </div>
        @endforeach
    </div>

    <div class="border-b border-dashed border-slate-400 my-1.5"></div>

    <!-- Totals -->
    <div class="space-y-1 text-[10px] py-1">
        <div class="flex justify-between text-slate-600">
            <span>Subtotal:</span>
            <span>Rp {{ number_format($order->total_owner_amount, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between text-slate-600">
            <span>Biaya Admin:</span>
            <span>Rp 0</span>
        </div>
        <div class="flex justify-between font-black text-xs pt-1 border-t border-dotted border-slate-300">
            <span>{{ $isLunas ? 'TOTAL DITERIMA:' : 'TOTAL WAJIB SETOR:' }}</span>
            <span>Rp {{ number_format($order->total_owner_amount, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="border-b border-dashed border-slate-400 my-1.5"></div>

    <!-- Status & Payment Detail -->
    <div class="py-1 text-center">
        @if($isLunas)
        <div class="text-xs font-black text-emerald-800 bg-emerald-50 py-1 px-2.5 rounded border border-emerald-300 inline-block">
            ★ LUNAS ★ ({{ strtoupper($order->payment_method ?? 'TUNAI') }})
        </div>
        <div class="text-[9px] text-slate-500 mt-1">
            Tgl Lunas: {{ $order->confirmed_at ? $order->confirmed_at->format('d/m/Y H:i') : '-' }} WIB
        </div>
        @else
        <div class="text-xs font-black text-amber-900 bg-amber-50 py-1 px-2.5 rounded border border-amber-300 inline-block">
            ⚠ MENUNGGU SETORAN (TEMPO) ⚠
        </div>
        <div class="text-[9px] text-slate-600 mt-1">
            Status: Konsinyasi (Ambil Dulu)
        </div>
        @endif
    </div>

    <!-- Terbilang -->
    <div class="py-1 text-[9px] text-slate-600 italic text-center">
        "{{ $terbilang }}"
    </div>

    @if(!$isLunas)
    <!-- Rekening Bank (jika belum lunas) -->
    <div class="border-t border-dashed border-slate-300 pt-1.5 mt-1 text-[9px]">
        <div class="font-bold text-slate-700 text-center">Rekening Pembayaran:</div>
        <div class="whitespace-pre-line text-slate-600 mt-0.5 text-center leading-normal">{{ $bankAccounts }}</div>
    </div>
    @endif

    @if(!$order->isDirectCustomer())
    <!-- Info Warung -->
    <div class="border-t border-dashed border-slate-300 pt-1.5 mt-1 text-[9px] text-slate-500">
        <div>* Jual ke pembeli: Rp {{ number_format($order->printed_price_each, 0, ',', '.') }}/lbr</div>
        <div>* Total fisik: Rp {{ number_format($order->total_printed_amount, 0, ',', '.') }}</div>
        <div class="font-bold text-slate-700">* Untung warung: +Rp {{ number_format($order->total_reseller_profit, 0, ',', '.') }}</div>
    </div>
    @endif

    <div class="border-b border-dashed border-slate-400 my-2"></div>

    <!-- Tanda Tangan Singkat Kasir & Mitra -->
    <div class="grid grid-cols-2 gap-2 text-center text-[9px] pt-1 pb-1">
        <div>
            <div class="text-slate-500">Mitra/Penerima</div>
            <div class="h-7"></div>
            <div class="border-t border-dotted border-slate-400 pt-0.5 font-bold truncate">
                {{ $order->customer_display_name }}
            </div>
        </div>
        <div>
            <div class="text-slate-500">Kasir/Petugas</div>
            <div class="h-7"></div>
            <div class="border-t border-dotted border-slate-400 pt-0.5 font-bold truncate">
                {{ $cashierName }}
            </div>
        </div>
    </div>

    <div class="border-b border-dashed border-slate-400 my-1.5"></div>

    <!-- Thermal Footer -->
    <div class="text-center text-[9px] text-slate-500 space-y-0.5 pt-1 pb-2">
        <div>Terima Kasih Atas Kerjasamanya!</div>
        <div>Simpan struk ini sebagai bukti transaksi sah.</div>
        <div class="text-[8px] text-slate-400 pt-1">
            Dicetak: {{ now()->translatedFormat('d/m/Y H:i:s') }} WIB
        </div>
    </div>

    <!-- Tear-off buffer space for thermal paper cutter -->
    <div class="h-6"></div>
</div>
