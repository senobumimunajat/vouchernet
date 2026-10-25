@extends('layouts.app')

@section('title', $isEdit ? 'Edit Paket Voucher' : 'Tambah Paket Voucher')

@section('content')
<div class="max-w-xl mx-auto py-2">
    <div class="bg-white p-6 sm:p-8 rounded-[28px] border border-slate-200/70 shadow-[0_8px_30px_rgb(0,0,0,0.04)] space-y-6">
        
        <!-- Header -->
        <div class="flex items-center gap-3.5 pb-5 border-b border-slate-100">
            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </div>
            <div>
                <h1 class="text-lg font-extrabold text-slate-900 tracking-tight">{{ $isEdit ? 'Edit Paket Voucher' : 'Tambah Paket Voucher Baru' }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">Atur parameter durasi/kuota dan skema bagi hasil reseller.</p>
            </div>
        </div>

        <form action="{{ $isEdit ? route('packages.update', $package) : route('packages.store') }}" method="POST" class="space-y-5">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Paket</label>
                <input type="text" name="name" id="name" value="{{ old('name', $package->name) }}" required
                    class="w-full px-4 py-3 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                    placeholder="Contoh: Paket 3 Jam / Paket 24 Jam">
            </div>

            <div>
                <label for="duration_info" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Keterangan Durasi / Kuota</label>
                <input type="text" name="duration_info" id="duration_info" value="{{ old('duration_info', $package->duration_info) }}" required
                    class="w-full px-4 py-3 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                    placeholder="Contoh: 3 Jam Nonstop / Unlimited 24 Jam">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="printed_price" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Harga di Voucher (Rp)</label>
                    <input type="text" inputmode="numeric" name="printed_price" id="printed_price"
                        value="{{ old('printed_price', $package->printed_price ? number_format($package->printed_price, 0, '', '.') : '') }}"
                        required oninput="formatRupiahInput(this); calcMargin();"
                        class="rupiah-input w-full px-4 py-3 font-mono text-base font-extrabold bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                        placeholder="Contoh: 3.000">
                    <p class="text-[11px] text-slate-400 mt-1">Harga jual eceran ke warga (otomatis titik ribuan)</p>
                </div>

                <div>
                    <label for="owner_price" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Harga Setor Owner (Rp)</label>
                    <input type="text" inputmode="numeric" name="owner_price" id="owner_price"
                        value="{{ old('owner_price', $package->owner_price ? number_format($package->owner_price, 0, '', '.') : '') }}"
                        required oninput="formatRupiahInput(this); calcMargin();"
                        class="rupiah-input w-full px-4 py-3 font-mono text-base font-extrabold bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                        placeholder="Contoh: 2.500">
                    <p class="text-[11px] text-slate-400 mt-1">Uang yang disetor reseller ke kita</p>
                </div>
            </div>

            <!-- Auto-Calculated Margin Preview Box -->
            <div class="p-4 bg-blue-50/80 border border-blue-200/80 rounded-2xl flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold text-blue-900 uppercase tracking-wider">Margin Keuntungan Warung:</div>
                    <div class="text-[11px] text-blue-700 mt-0.5">Selisih: Harga Voucher − Harga Setor Owner</div>
                </div>
                <div class="text-xl font-extrabold font-mono text-blue-800" id="marginDisplay">
                    +Rp 0
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Deskripsi Tambahan (Opsional)</label>
                <textarea name="description" id="description" rows="2"
                    class="w-full p-4 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                    placeholder="Contoh: Kecepatan up to 5 Mbps, cocok untuk streaming YouTube & game...">{{ old('description', $package->description) }}</textarea>
            </div>

            @if(! $isEdit)
            <div>
                <label for="stock" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Stok Awal Voucher Fisik (Opsional)</label>
                <input type="number" name="stock" id="stock" value="{{ old('stock', 0) }}" min="0"
                    class="w-full px-4 py-3 font-mono text-base font-extrabold bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                    placeholder="Contoh: 100">
                <p class="text-[11px] text-slate-400 mt-1">Jumlah lembar voucher fisik yang sudah siap/dicetak saat ini</p>
            </div>
            @endif

            <div class="p-3.5 bg-slate-50 border border-slate-200/70 rounded-2xl">
                <label class="flex items-center text-xs text-slate-800 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $package->is_active ?? true) ? 'checked' : '' }} class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500">
                    <span class="ml-2.5 font-bold">Aktifkan paket ini (bisa langsung dipesan oleh warung)</span>
                </label>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('packages.index') }}" class="px-5 py-2.5 rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-full bg-blue-600 text-white hover:bg-blue-700 text-xs font-bold shadow-md shadow-blue-500/20 active:scale-[0.98] transition-all">
                    {{ $isEdit ? 'Perbarui Paket' : 'Simpan Paket' }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function calcMargin() {
        const printedRaw = (document.getElementById('printed_price').value || '').replace(/\D/g, '');
        const ownerRaw = (document.getElementById('owner_price').value || '').replace(/\D/g, '');
        const printed = parseInt(printedRaw, 10) || 0;
        const owner = parseInt(ownerRaw, 10) || 0;
        const margin = printed - owner;
        document.getElementById('marginDisplay').innerText = '+Rp ' + margin.toLocaleString('id-ID');
    }
    document.addEventListener('DOMContentLoaded', calcMargin);
</script>
@endsection
