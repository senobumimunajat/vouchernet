@extends('layouts.app')

@section('title', 'Input Pesanan Offline / Walk-in')

@section('content')
<div class="max-w-2xl mx-auto py-2">
    <div class="bg-white p-6 sm:p-8 rounded-[28px] border border-slate-200/70 shadow-[0_8px_30px_rgb(0,0,0,0.04)] space-y-6">
        
        <!-- Header -->
        <div class="flex items-center gap-3.5 pb-5 border-b border-slate-100">
            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/></svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-lg font-extrabold text-slate-900 tracking-tight">Input Pesanan Offline</h1>
                    <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/70 rounded-full">Walk-In</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Catat transaksi voucher untuk warung reseller atau pembeli eceran langsung.</p>
            </div>
        </div>

        <form action="{{ route('admin.orders.store_offline') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- 1. Pilihan Tipe Pembeli (Segmented Switcher) -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Tipe Pembeli:</label>
                <div class="grid grid-cols-2 gap-2.5 p-1.5 bg-slate-100/80 rounded-2xl border border-slate-200/60">
                    <button type="button" onclick="setBuyerType('warung')" id="tabWarung"
                        class="py-2.5 px-3 rounded-xl text-xs font-extrabold flex items-center justify-center gap-2 transition-all bg-white text-blue-700 shadow-sm border border-slate-200/80">
                        <span>🏪 Mitra Warung / Reseller</span>
                        <span class="text-[10px] bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded-full border border-blue-200/60">Grosir</span>
                    </button>
                    <button type="button" onclick="setBuyerType('direct')" id="tabDirect"
                        class="py-2.5 px-3 rounded-xl text-xs font-extrabold flex items-center justify-center gap-2 transition-all text-slate-600 hover:text-slate-900">
                        <span>👤 Pembeli Langsung / Eceran</span>
                        <span class="text-[10px] bg-emerald-50 text-emerald-700 px-1.5 py-0.5 rounded-full border border-emerald-200/60">Perorangan</span>
                    </button>
                </div>
                <input type="hidden" name="buyer_type" id="buyer_type" value="{{ old('buyer_type', 'warung') }}">
            </div>

            <!-- 2A. Dropdown Warung (Muncul jika Tipe Pembeli = Warung) -->
            <div id="tenantSelectContainer" class="space-y-2">
                <label for="tenant_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Pilih Mitra Warung / Reseller</label>
                <div class="relative">
                    <select name="tenant_id" id="tenant_id" class="w-full px-4 py-3 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all appearance-none">
                        <option value="">-- Pilih Warung Pemesan --</option>
                        @foreach($tenants as $t)
                        <option value="{{ $t->id }}" {{ old('tenant_id') == $t->id ? 'selected' : '' }}>
                            {{ $t->shop_name ?: $t->name }} (Pemilik: {{ $t->name }} - {{ $t->phone }})
                        </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <div class="text-[11px] text-slate-400 flex items-center gap-1">
                    <span>Belum terdaftar?</span>
                    <a href="{{ route('users.index') }}" class="text-blue-600 hover:text-blue-700 font-semibold underline">Tambah warung baru di menu Pengguna</a>
                </div>
            </div>

            <!-- 2B. Input Pembeli Langsung (Muncul jika Tipe Pembeli = Direct Eceran) -->
            <div id="directCustomerContainer" class="hidden space-y-2 p-4 bg-emerald-50/50 border border-emerald-200/70 rounded-2xl">
                <div class="flex items-center justify-between">
                    <label for="customer_name" class="block text-xs font-bold text-emerald-950 uppercase tracking-wider">Nama / Catatan Pembeli Eceran (Opsional)</label>
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded-full">Walk-In Basecamp</span>
                </div>
                <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name') }}"
                    placeholder="Contoh: Mas Budi / Tetangga / Tamu (Boleh dikosongkan)"
                    class="w-full px-4 py-3 text-xs bg-white border border-emerald-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                <p class="text-[11px] text-emerald-800">
                    💡 Pembeli perorangan tidak memerlukan akun warung. Pembayaran langsung 100% masuk ke buku kas owner tanpa potongan komisi reseller.
                </p>
            </div>

            <!-- 3. Pilih Paket Voucher & Jumlah Lembar (Multi Paket) -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Pilih Paket & Jumlah Lembar</label>
                        <p class="text-[11px] text-slate-500">Tentukan jumlah lembar untuk satu atau beberapa paket voucher di bawah.</p>
                    </div>
                    <span class="text-[11px] font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full border border-blue-200/80">
                        Bisa Multi Paket
                    </span>
                </div>

                <div class="space-y-3">
                    @foreach($packages as $pkg)
                    <div class="p-3.5 sm:p-4 rounded-2xl border-2 transition-all bg-white hover:border-slate-300 {{ old('items.' . $pkg->id, 0) > 0 ? 'border-blue-500 bg-blue-50/20' : 'border-slate-200/80' }} item-package-card" id="pkg_card_{{ $pkg->id }}">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-extrabold text-slate-900">{{ $pkg->name }}</h4>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">
                                        {{ $pkg->duration_info }}
                                    </span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $pkg->stock > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-rose-50 text-rose-700 border border-rose-200/60' }}">
                                        Stok: {{ number_format($pkg->stock) }} lbr
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 flex items-center gap-2 flex-wrap">
                                    <span class="price-display-warung">
                                        Jual: <strong class="text-slate-800">Rp {{ number_format($pkg->printed_price, 0, ',', '.') }}</strong> | 
                                        Setor: <strong class="text-blue-900 font-bold">Rp {{ number_format($pkg->owner_price, 0, ',', '.') }}</strong> 
                                        <span class="text-emerald-700 font-semibold">(Untung: Rp {{ number_format($pkg->reseller_profit, 0, ',', '.') }})</span>
                                    </span>
                                    <span class="price-display-direct hidden">
                                        Harga: <strong class="text-emerald-800 font-bold">Rp {{ number_format($pkg->printed_price, 0, ',', '.') }}/lbr</strong> (100% Kas Owner)
                                    </span>
                                </div>
                            </div>

                            <!-- Stepper Input & Quick Chips -->
                            <div class="flex flex-col sm:items-end gap-1.5 shrink-0">
                                @if($pkg->stock > 0)
                                <div class="inline-flex items-center bg-slate-100 rounded-full p-1 border border-slate-200/80 shadow-inner self-start sm:self-end">
                                    <button type="button" onclick="adjustItemQty({{ $pkg->id }}, -5)" class="w-8 h-8 rounded-full bg-white text-slate-800 hover:bg-slate-200 shadow-xs flex items-center justify-center font-bold text-base active:scale-90 transition-transform">
                                        −
                                    </button>
                                    <input type="number" name="items[{{ $pkg->id }}]" id="item_qty_{{ $pkg->id }}" 
                                        value="{{ old('items.' . $pkg->id, 0) }}" min="0" max="{{ $pkg->stock }}"
                                        oninput="onItemQtyChange({{ $pkg->id }})"
                                        class="w-14 text-center font-mono font-extrabold text-sm bg-transparent border-0 focus:outline-none focus:ring-0 text-slate-900 item-qty-input"
                                        data-id="{{ $pkg->id }}"
                                        data-name="{{ $pkg->name }}"
                                        data-stock="{{ $pkg->stock }}"
                                        data-printed="{{ $pkg->printed_price }}"
                                        data-owner="{{ $pkg->owner_price }}">
                                    <button type="button" onclick="adjustItemQty({{ $pkg->id }}, 5)" class="w-8 h-8 rounded-full bg-blue-600 text-white hover:bg-blue-700 shadow-xs flex items-center justify-center font-bold text-base active:scale-90 transition-transform">
                                        +
                                    </button>
                                </div>

                                <!-- Preset Chips per Row -->
                                <div class="flex items-center gap-1">
                                    <span class="text-[10px] text-slate-400 font-semibold mr-0.5">Template:</span>
                                    <!-- Warung Chips -->
                                    <div class="chips-warung flex items-center gap-1">
                                        <button type="button" onclick="setItemPreset({{ $pkg->id }}, 10)" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 active:scale-95 transition-colors">10</button>
                                        <button type="button" onclick="setItemPreset({{ $pkg->id }}, 30)" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 active:scale-95 transition-colors">30</button>
                                        <button type="button" onclick="setItemPreset({{ $pkg->id }}, 50)" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 active:scale-95 transition-colors">50</button>
                                        <button type="button" onclick="setItemPreset({{ $pkg->id }}, 100)" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 active:scale-95 transition-colors">100</button>
                                        <button type="button" onclick="setItemPreset({{ $pkg->id }}, 0)" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 hover:bg-rose-100 text-slate-500 hover:text-rose-700 active:scale-95 transition-colors">0</button>
                                    </div>
                                    <!-- Direct Eceran Chips -->
                                    <div class="chips-direct hidden flex items-center gap-1">
                                        <button type="button" onclick="setItemPreset({{ $pkg->id }}, 1)" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 hover:bg-emerald-200 text-emerald-800 active:scale-95 transition-colors">1</button>
                                        <button type="button" onclick="setItemPreset({{ $pkg->id }}, 2)" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 hover:bg-emerald-200 text-emerald-800 active:scale-95 transition-colors">2</button>
                                        <button type="button" onclick="setItemPreset({{ $pkg->id }}, 5)" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 hover:bg-emerald-200 text-emerald-800 active:scale-95 transition-colors">5</button>
                                        <button type="button" onclick="setItemPreset({{ $pkg->id }}, 10)" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 hover:bg-emerald-200 text-emerald-800 active:scale-95 transition-colors">10</button>
                                        <button type="button" onclick="setItemPreset({{ $pkg->id }}, 0)" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 hover:bg-rose-100 text-slate-500 hover:text-rose-700 active:scale-95 transition-colors">0</button>
                                    </div>
                                </div>
                                @else
                                <span class="text-[11px] font-bold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-full border border-rose-200 self-start sm:self-end">
                                    Stok Habis
                                </span>
                                @endif
                            </div>
                        </div>

                        <!-- Per-card Subtotal Info -->
                        <div id="item_subtotal_row_{{ $pkg->id }}" class="hidden mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="text-slate-500 text-[11px]">Subtotal:</span>
                            <span class="font-mono font-bold text-slate-800" id="item_subtotal_val_{{ $pkg->id }}">Rp 0</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- 4. Ringkasan Total Pesanan (Live Calculation Card) -->
            <div id="calcCard" class="p-4 bg-gradient-to-br from-slate-50 to-blue-50/40 border-2 border-blue-200/80 rounded-2xl space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Ringkasan Total Pesanan</span>
                    <span id="calcTotalSummaryBadge" class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-white text-blue-800 border border-blue-200 font-mono">
                        0 lembar
                    </span>
                </div>

                <!-- Selected Items List -->
                <div id="selectedItemsList" class="hidden space-y-1.5 p-2.5 bg-white rounded-xl border border-blue-100 text-xs">
                    <!-- Populated dynamically via JS -->
                </div>

                <div class="space-y-1 pt-1 border-t border-slate-200/60">
                    <div class="flex items-center justify-between text-xs">
                        <span id="calcOwnerLabel" class="text-slate-600 font-medium">Total Wajib Setor Owner:</span>
                        <span class="text-base font-extrabold font-mono text-slate-900" id="calcOwnerAmount">Rp 0</span>
                    </div>
                    <div class="flex items-center justify-between text-[11px]">
                        <span id="calcProfitLabel" class="text-slate-500">Total Margin Warung (Reseller):</span>
                        <span class="font-bold font-mono text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md" id="calcProfitAmount">+Rp 0</span>
                    </div>
                </div>
            </div>

            <!-- 5. Status Pengambilan & Pembayaran (Hanya Ditampilkan untuk Warung) -->
            <div id="paymentStatusWarungContainer" class="space-y-3">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Status Pembayaran Saat Penyerahan Voucher</label>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <!-- Opsi 1: Ambil Dulu (Konsinyasi / Tempo) - Default -->
                    <label class="relative flex items-start p-4 rounded-2xl border-2 cursor-pointer transition-all has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50/40 border-slate-200 hover:bg-slate-50/70">
                        <input type="radio" name="payment_status" id="statusTempo" value="tempo" checked onchange="togglePaymentStatus('tempo')" class="mt-0.5 text-amber-600 focus:ring-amber-500">
                        <div class="ml-3 space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-extrabold text-slate-900">📦 Ambil Dulu (Konsinyasi / Tempo)</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200/80">Bayar Nanti</span>
                            </div>
                            <p class="text-[11px] text-slate-500 leading-relaxed">
                                Voucher fisik dibawa warung sekarang, uang disetor nanti setelah voucher laku terjual.
                            </p>
                        </div>
                    </label>

                    <!-- Opsi 2: Langsung Lunas -->
                    <label class="relative flex items-start p-4 rounded-2xl border-2 cursor-pointer transition-all has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/40 border-slate-200 hover:bg-slate-50/70">
                        <input type="radio" name="payment_status" id="statusLunas" value="lunas" onchange="togglePaymentStatus('lunas')" class="mt-0.5 text-blue-600 focus:ring-blue-500">
                        <div class="ml-3 space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-extrabold text-slate-900">💵 Langsung Lunas (Bayar di Tempat)</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 border border-blue-200/80">Lunas</span>
                            </div>
                            <p class="text-[11px] text-slate-500 leading-relaxed">
                                Mitra langsung menyetor uang tunai atau transfer lunas saat mengambil voucher fisik.
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- 6. Detail Metode Bayar (Muncul Jika "Langsung Lunas" ATAU "Tipe Pembeli = Direct") -->
            <div id="paymentMethodSection" class="hidden space-y-3 p-4 bg-blue-50/40 border border-blue-200/60 rounded-2xl transition-all">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Pilih Cara Pembayaran:</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="relative flex items-center p-3.5 rounded-xl border border-slate-200 bg-white cursor-pointer hover:bg-slate-50 has-[:checked]:border-blue-600 has-[:checked]:ring-1 has-[:checked]:ring-blue-500 transition-all">
                        <input type="radio" name="payment_method" value="cash" checked onchange="toggleProof(false)" class="text-blue-600">
                        <div class="ml-2.5">
                            <span class="block text-xs font-bold text-slate-900">💵 Tunai / Cash di Tempat</span>
                            <span class="block text-[10px] text-slate-400">Uang fisik langsung diterima</span>
                        </div>
                    </label>
                    <label class="relative flex items-center p-3.5 rounded-xl border border-slate-200 bg-white cursor-pointer hover:bg-slate-50 has-[:checked]:border-blue-600 has-[:checked]:ring-1 has-[:checked]:ring-blue-500 transition-all">
                        <input type="radio" name="payment_method" value="transfer" onchange="toggleProof(true)" class="text-blue-600">
                        <div class="ml-2.5">
                            <span class="block text-xs font-bold text-slate-900">🏦 Transfer Bank / QRIS</span>
                            <span class="block text-[10px] text-slate-400">Bukti mutasi rekening</span>
                        </div>
                    </label>
                </div>

                <!-- Upload Bukti Transfer (Conditional) -->
                <div id="proofBox" class="hidden pt-2 border-t border-blue-200/60 space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Foto Bukti Transfer (Opsional)</label>
                    <input type="file" name="payment_proof_file" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 transition-colors">
                </div>
            </div>

            <!-- 7. Penjelasan Alur Lapangan -->
            <div id="statusNoticeBox" class="p-3 bg-amber-50/80 border border-amber-200/70 rounded-2xl flex items-start gap-2.5 text-xs text-amber-900">
                <span class="text-base">💡</span>
                <p id="statusNoticeText" class="leading-relaxed">
                    <strong>Mode Konsinyasi:</strong> Stok voucher fisik akan langsung berkurang dari gudang. Pesanan akan tercatat berstatus <strong class="text-amber-950 font-bold">"Menunggu Pembayaran"</strong> dan dapat dikonfirmasi lunas nanti saat warung menyetor uangnya.
                </p>
            </div>

            <!-- 8. Buttons -->
            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('admin.orders.index') }}" class="px-5 py-2.5 rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition-colors">
                    Batal
                </a>
                <button type="submit" id="submitBtn" class="px-6 py-2.5 rounded-full bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-md shadow-slate-900/20 active:scale-[0.98] transition-all">
                    Simpan Pengambilan Voucher (Ambil Dulu)
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentBuyerType = 'warung';

    function adjustItemQty(pkgId, delta) {
        const input = document.getElementById('item_qty_' + pkgId);
        if (!input) return;
        const maxStock = parseInt(input.dataset.stock) || 0;
        let current = parseInt(input.value) || 0;
        let next = Math.max(0, Math.min(maxStock, current + delta));
        input.value = next;
        updateCalc();
    }

    function setItemPreset(pkgId, targetVal) {
        const input = document.getElementById('item_qty_' + pkgId);
        if (!input) return;
        const maxStock = parseInt(input.dataset.stock) || 0;
        let next = Math.max(0, Math.min(maxStock, targetVal));
        input.value = next;
        updateCalc();
    }

    function addItemQty(pkgId, amount) {
        setItemPreset(pkgId, amount);
    }

    function resetItemQty(pkgId) {
        setItemPreset(pkgId, 0);
    }

    function onItemQtyChange(pkgId) {
        const input = document.getElementById('item_qty_' + pkgId);
        if (!input) return;
        const maxStock = parseInt(input.dataset.stock) || 0;
        let val = parseInt(input.value) || 0;
        if (val < 0) val = 0;
        if (val > maxStock) val = maxStock;
        input.value = val;
        updateCalc();
    }

    function setBuyerType(type) {
        currentBuyerType = type;
        document.getElementById('buyer_type').value = type;

        const tabWarung = document.getElementById('tabWarung');
        const tabDirect = document.getElementById('tabDirect');
        const tenantContainer = document.getElementById('tenantSelectContainer');
        const directContainer = document.getElementById('directCustomerContainer');
        const tenantSelect = document.getElementById('tenant_id');
        const paymentWarungContainer = document.getElementById('paymentStatusWarungContainer');
        const paymentSection = document.getElementById('paymentMethodSection');
        const noticeBox = document.getElementById('statusNoticeBox');
        const noticeText = document.getElementById('statusNoticeText');
        const submitBtn = document.getElementById('submitBtn');

        // Toggle price displays & chips
        document.querySelectorAll('.price-display-warung').forEach(el => el.classList.toggle('hidden', type === 'direct'));
        document.querySelectorAll('.price-display-direct').forEach(el => el.classList.toggle('hidden', type !== 'direct'));
        document.querySelectorAll('.chips-warung').forEach(el => el.classList.toggle('hidden', type === 'direct'));
        document.querySelectorAll('.chips-direct').forEach(el => el.classList.toggle('hidden', type !== 'direct'));

        if (type === 'direct') {
            tabDirect.className = 'py-2.5 px-3 rounded-xl text-xs font-extrabold flex items-center justify-center gap-2 transition-all bg-white text-emerald-800 shadow-sm border border-emerald-200';
            tabWarung.className = 'py-2.5 px-3 rounded-xl text-xs font-extrabold flex items-center justify-center gap-2 transition-all text-slate-600 hover:text-slate-900';

            tenantContainer.classList.add('hidden');
            tenantSelect.removeAttribute('required');
            directContainer.classList.remove('hidden');

            paymentWarungContainer.classList.add('hidden');
            paymentSection.classList.remove('hidden');

            noticeBox.className = 'p-3 bg-emerald-50/80 border border-emerald-200/70 rounded-2xl flex items-start gap-2.5 text-xs text-emerald-900';
            noticeText.innerHTML = '<strong>Penjualan Eceran Langsung:</strong> Pembeli perorangan membayar harga penuh di tempat. <strong class="text-emerald-950 font-bold">100% uang langsung masuk kas owner</strong> (tanpa potongan reseller), dan stok voucher terpotong otomatis dari sistem.';

            submitBtn.innerText = 'Selesaikan Penjualan Eceran (Lunas)';
            submitBtn.className = 'px-6 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-500/20 active:scale-[0.98] transition-all';
        } else {
            tabWarung.className = 'py-2.5 px-3 rounded-xl text-xs font-extrabold flex items-center justify-center gap-2 transition-all bg-white text-blue-700 shadow-sm border border-slate-200/80';
            tabDirect.className = 'py-2.5 px-3 rounded-xl text-xs font-extrabold flex items-center justify-center gap-2 transition-all text-slate-600 hover:text-slate-900';

            tenantContainer.classList.remove('hidden');
            tenantSelect.setAttribute('required', 'required');
            directContainer.classList.add('hidden');

            paymentWarungContainer.classList.remove('hidden');
            const isLunas = document.getElementById('statusLunas').checked;
            togglePaymentStatus(isLunas ? 'lunas' : 'tempo');
        }

        updateCalc();
    }

    function toggleProof(show) {
        document.getElementById('proofBox').classList.toggle('hidden', !show);
    }

    function togglePaymentStatus(status) {
        if (currentBuyerType === 'direct') return;

        const paymentSection = document.getElementById('paymentMethodSection');
        const noticeBox = document.getElementById('statusNoticeBox');
        const noticeText = document.getElementById('statusNoticeText');
        const submitBtn = document.getElementById('submitBtn');

        if (status === 'lunas') {
            paymentSection.classList.remove('hidden');
            noticeBox.className = 'p-3 bg-blue-50/80 border border-blue-200/70 rounded-2xl flex items-start gap-2.5 text-xs text-blue-900';
            noticeText.innerHTML = '<strong>Mode Langsung Lunas:</strong> Uang setoran warung diterima lunas sekarang. Transaksi langsung berstatus <strong class="text-blue-950 font-bold">"Lunas"</strong> dan kas masuk langsung tercatat di Buku Kas.';
            submitBtn.innerText = 'Simpan & Konfirmasi Lunas Sekarang';
            submitBtn.className = 'px-6 py-2.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 active:scale-[0.98] transition-all';
        } else {
            paymentSection.classList.add('hidden');
            noticeBox.className = 'p-3 bg-amber-50/80 border border-amber-200/70 rounded-2xl flex items-start gap-2.5 text-xs text-amber-900';
            noticeText.innerHTML = '<strong>Mode Konsinyasi:</strong> Stok voucher fisik diserahkan & berkurang dari gudang. Pesanan akan berstatus <strong class="text-amber-950 font-bold">"Menunggu Pembayaran"</strong> untuk ditagih nanti saat warung setor.';
            submitBtn.innerText = 'Simpan Pengambilan Voucher (Ambil Dulu)';
            submitBtn.className = 'px-6 py-2.5 rounded-full bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-md shadow-slate-900/20 active:scale-[0.98] transition-all';
        }
    }

    function updateCalc() {
        const inputs = document.querySelectorAll('.item-qty-input');
        const ownerLabel = document.getElementById('calcOwnerLabel');
        const profitLabel = document.getElementById('calcProfitLabel');
        const ownerAmount = document.getElementById('calcOwnerAmount');
        const profitAmount = document.getElementById('calcProfitAmount');
        const summaryBadge = document.getElementById('calcTotalSummaryBadge');
        const selectedList = document.getElementById('selectedItemsList');

        let totalSheets = 0;
        let totalOwner = 0;
        let totalPrinted = 0;
        let selectedItemsHtml = '';
        let selectedCount = 0;

        inputs.forEach(input => {
            const qty = parseInt(input.value) || 0;
            const pkgId = input.dataset.id;
            const pkgName = input.dataset.name;
            const ownerPrice = parseFloat(input.dataset.owner) || 0;
            const printedPrice = parseFloat(input.dataset.printed) || 0;
            const card = document.getElementById('pkg_card_' + pkgId);
            const subtotalRow = document.getElementById('item_subtotal_row_' + pkgId);
            const subtotalVal = document.getElementById('item_subtotal_val_' + pkgId);

            if (qty > 0) {
                totalSheets += qty;
                totalOwner += (ownerPrice * qty);
                totalPrinted += (printedPrice * qty);
                selectedCount++;

                if (card) {
                    card.classList.remove('border-slate-200/80');
                    card.classList.add('border-blue-500', 'bg-blue-50/20');
                }
                if (subtotalRow && subtotalVal) {
                    subtotalRow.classList.remove('hidden');
                    const subtotalAmount = currentBuyerType === 'direct' ? (printedPrice * qty) : (ownerPrice * qty);
                    subtotalVal.innerText = qty + ' lbr = Rp ' + subtotalAmount.toLocaleString('id-ID');
                }

                const itemSub = currentBuyerType === 'direct' ? (printedPrice * qty) : (ownerPrice * qty);
                selectedItemsHtml += `
                    <div class="flex items-center justify-between py-1 border-b border-slate-100 last:border-b-0">
                        <span class="text-slate-700 font-medium">${pkgName} <strong class="text-slate-900 font-mono">(${qty} lbr)</strong></span>
                        <span class="font-mono font-bold text-slate-900">Rp ${itemSub.toLocaleString('id-ID')}</span>
                    </div>
                `;
            } else {
                if (card) {
                    card.classList.remove('border-blue-500', 'bg-blue-50/20');
                    card.classList.add('border-slate-200/80');
                }
                if (subtotalRow) {
                    subtotalRow.classList.add('hidden');
                }
            }
        });

        // Update selected items list display
        if (selectedCount > 0) {
            selectedList.innerHTML = selectedItemsHtml;
            selectedList.classList.remove('hidden');
        } else {
            selectedList.innerHTML = '';
            selectedList.classList.add('hidden');
        }

        summaryBadge.innerText = totalSheets + ' lembar (' + selectedCount + ' paket)';

        if (currentBuyerType === 'direct') {
            ownerLabel.innerText = 'Total Kas Masuk (100% Owner):';
            ownerAmount.innerText = 'Rp ' + totalPrinted.toLocaleString('id-ID');
            profitLabel.innerText = 'Margin Reseller:';
            profitAmount.className = 'font-bold font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md';
            profitAmount.innerText = 'Rp 0 (Tanpa Perantara)';
        } else {
            const totalProfit = totalPrinted - totalOwner;
            ownerLabel.innerText = 'Total Wajib Setor Owner:';
            ownerAmount.innerText = 'Rp ' + totalOwner.toLocaleString('id-ID');
            profitLabel.innerText = 'Total Margin Warung (Reseller):';
            profitAmount.className = 'font-bold font-mono text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md';
            profitAmount.innerText = '+Rp ' + totalProfit.toLocaleString('id-ID');
        }
    }

    // Client-side form submission validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const inputs = document.querySelectorAll('.item-qty-input');
        let total = 0;
        inputs.forEach(inp => total += (parseInt(inp.value) || 0));
        if (total <= 0) {
            e.preventDefault();
            alert('Mohon tentukan jumlah lembar minimal 1 lembar pada paket yang ingin dipesan.');
            return false;
        }
    });

    // Inisialisasi sesuai kondisi awal
    @if(old('buyer_type') === 'direct')
        setBuyerType('direct');
    @else
        setBuyerType('warung');
    @endif
</script>
@endsection
