@extends('layouts.app')

@section('title', 'Beli Saldo Kuota Voucher')

@section('content')
<div class="space-y-5 pb-36 max-w-full overflow-x-hidden">

    <!-- WhatsApp Redirect Popup (After deposit request placed) -->
    @if(session('whatsapp_url'))
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-md animate-fade-in">
        <div class="bg-white rounded-[32px] max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-100 text-center space-y-4">
            <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-600 mx-auto flex items-center justify-center shadow-inner">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
            </div>
            <div>
                <h3 class="text-xl font-bold text-slate-900">Permintaan Saldo Terkirim!</h3>
                <p class="text-xs text-slate-500 mt-1">Konfirmasikan ke admin via WhatsApp agar saldo segera diisikan ke akun Anda.</p>
            </div>

            <div class="pt-2 flex flex-col gap-2">
                <a href="{{ session('whatsapp_url') }}" target="_blank" class="w-full touch-target flex justify-center items-center py-3.5 px-4 rounded-full font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-600/20 transition-all text-sm gap-2 active:scale-95">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    Buka WhatsApp Sekarang
                </a>
                <button type="button" onclick="this.closest('.fixed').remove()" class="text-xs text-slate-400 hover:text-slate-700 py-1 font-medium">
                    Tutup Popup
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Modern Partner Greeting Bar -->
    <div class="bg-gradient-to-br from-indigo-700 via-blue-700 to-sky-700 text-white rounded-2xl sm:rounded-3xl p-4 sm:p-5 shadow-soft relative overflow-hidden">
        <div class="absolute -right-8 -top-8 w-36 h-36 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 relative z-10">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-white/15 border border-white/20 backdrop-blur-md flex items-center justify-center text-white shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h1 class="text-base sm:text-xl font-extrabold tracking-tight truncate text-white">{{ $user->shop_name ?: $user->name }}</h1>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white border border-white/25 shrink-0">Saldo Kuota</span>
                    </div>
                    <p class="text-xs text-blue-100 truncate mt-0.5">
                        Isi saldo kuota untuk cetak voucher mandiri
                    </p>
                </div>
            </div>

            <!-- 3 Metric Pills -->
            <div class="grid grid-cols-3 gap-1.5 sm:gap-2 bg-black/15 border border-white/15 backdrop-blur-md p-2 sm:p-2.5 rounded-xl sm:rounded-2xl text-center">
                <div class="px-1 sm:px-2">
                    <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-blue-200">Total Diisi</div>
                    <div class="text-xs sm:text-sm font-extrabold font-mono text-white mt-0.5 truncate">
                        Rp {{ number_format($totalDepositTaken, 0, ',', '.') }}
                    </div>
                </div>
                <div class="px-1 sm:px-2 border-x border-white/15">
                    <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-amber-200">Tempo Saldo</div>
                    <div class="text-xs sm:text-sm font-extrabold font-mono text-amber-300 mt-0.5 truncate">
                        Rp {{ number_format($pendingDepositBill, 0, ',', '.') }}
                    </div>
                </div>
                <div class="px-1 sm:px-2">
                    <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-blue-200">Untung Anda</div>
                    <div class="text-xs sm:text-sm font-extrabold font-mono text-emerald-300 mt-0.5 truncate">
                        Rp {{ number_format($totalProfitEarned, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tab Back to Voucher Portal -->
    <div class="flex items-center justify-between">
        <a href="{{ route('tenant.orders.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-blue-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Kembali ke Katalog Voucher Fisik
        </a>
    </div>

    <!-- Main Grid: Form Pengajuan Saldo (Kiri) & Riwayat Transaksi (Kanan) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Form Beli Saldo Baru -->
        <div class="lg:col-span-5 bg-white p-6 rounded-[28px] border border-slate-200/80 shadow-[0_4px_24px_rgb(0,0,0,0.03)] space-y-5">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    Ajukan Pembelian Saldo
                </h2>
                <p class="text-xs text-slate-500 mt-1">Saldo kuota akan diisikan admin ke akun cetak mandiri Anda.</p>
            </div>

            <form action="{{ route('tenant.deposits.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- Nominal Saldo -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nominal Saldo (Rp)</label>
                    <input type="text" inputmode="numeric" name="amount" id="depositAmountInput" required placeholder="Contoh: 500.000" oninput="formatRupiahInput(this)"
                        class="rupiah-input w-full px-4 py-3 text-base font-mono font-extrabold bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">

                    <!-- Quick Preset Chips -->
                    <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                        <button type="button" onclick="setDepositAmount('100.000')" class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-600 text-slate-600 transition-colors">100rb</button>
                        <button type="button" onclick="setDepositAmount('200.000')" class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-600 text-slate-600 transition-colors">200rb</button>
                        <button type="button" onclick="setDepositAmount('300.000')" class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-600 text-slate-600 transition-colors">300rb</button>
                        <button type="button" onclick="setDepositAmount('500.000')" class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-600 text-slate-600 transition-colors">500rb</button>
                        <button type="button" onclick="setDepositAmount('1.000.000')" class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-600 text-slate-600 transition-colors">1 Juta</button>
                    </div>
                </div>

                <!-- Tipe Pembayaran (Lunas vs Tempo/Konsinyasi) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Sistem Pembayaran</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="relative flex flex-col p-3 rounded-2xl border-2 cursor-pointer transition-all border-blue-500 bg-blue-50/50" id="labelPayLunas">
                            <input type="radio" name="payment_type" value="lunas" checked onchange="togglePayType('lunas')" class="sr-only">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-extrabold text-blue-900">Lunas Langsung</span>
                                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            </div>
                            <span class="text-[11px] text-blue-700/80 mt-1">Bayar transfer / cash sekarang</span>
                        </label>

                        <label class="relative flex flex-col p-3 rounded-2xl border-2 border-slate-200 cursor-pointer transition-all hover:bg-slate-50" id="labelPayTempo">
                            <input type="radio" name="payment_type" value="tempo" onchange="togglePayType('tempo')" class="sr-only">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-extrabold text-slate-800">Konsinyasi (Tempo)</span>
                                <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                            </div>
                            <span class="text-[11px] text-slate-500 mt-1">Isi dulu, setor setelah voucher laku</span>
                        </label>
                    </div>
                </div>

                <!-- Metode Bayar & Upload Bukti (Khusus jika Lunas) -->
                <div id="sectionPaymentMethod" class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Metode Bayar</label>
                        <select name="payment_method" id="selectPaymentMethod" onchange="toggleProofField(this.value)" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="transfer">Transfer Bank / E-Wallet</option>
                            <option value="cash">Tunai (Cash ke Admin)</option>
                        </select>
                    </div>

                    <!-- Rekening Tujuan Info -->
                    <div id="boxBankInfo" class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl space-y-1">
                        <div class="text-[11px] font-bold text-slate-700">Rekening Tujuan Transfer:</div>
                        <div class="text-[11px] font-mono text-slate-600 whitespace-pre-line leading-relaxed">{{ $bankAccounts }}</div>
                    </div>

                    <!-- Upload Bukti Transfer -->
                    <div id="boxProofUpload">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Bukti Transfer (Foto/Struk)</label>
                        <input type="file" name="payment_proof_file" accept="image/*"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                    </div>
                </div>

                <!-- Catatan / ID Akun Tujuan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Catatan / Akun Tujuan (Opsional)</label>
                    <input type="text" name="notes" placeholder="Contoh: Akun warung_siti / No. HP"
                        class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                </div>

                <button type="submit" class="w-full py-3.5 px-5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-sm shadow-md shadow-blue-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Kirim Permintaan Saldo
                </button>
            </form>
        </div>

        <!-- Riwayat Permintaan & Pembelian Saldo -->
        <div class="lg:col-span-7 bg-white rounded-[28px] border border-slate-200/80 shadow-[0_4px_24px_rgb(0,0,0,0.03)] overflow-hidden space-y-0">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">Riwayat Pembelian Saldo</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Pantau status pengisian dan pelunasan konsinyasi.</p>
                </div>
            </div>

            <!-- Table View Desktop -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 border-b border-slate-100 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="py-3 px-4">Tanggal / No.</th>
                            <th class="py-3 px-4">Saldo Diisi</th>
                            <th class="py-3 px-4">Sistem</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Uang Disetor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($deposits as $dep)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3 px-4">
                                <span class="font-mono font-bold text-slate-900 block">{{ $dep->deposit_number }}</span>
                                <span class="text-[10px] text-slate-400">{{ $dep->deposit_date->translatedFormat('d M Y') }}</span>
                                @if($dep->status === 'tempo')
                                <div class="mt-1">
                                    <a href="{{ route('deposits.invoice', $dep) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200/80 px-2 py-0.5 rounded-md transition-all shadow-2xs">
                                        📄 Faktur Tagihan
                                    </a>
                                </div>
                                @elseif($dep->status === 'lunas')
                                <div class="mt-1">
                                    <a href="{{ route('deposits.invoice', $dep) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200/80 px-2 py-0.5 rounded-md transition-all shadow-2xs">
                                        🧾 Kwitansi Lunas
                                    </a>
                                </div>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-mono font-extrabold text-blue-700">
                                Rp {{ number_format($dep->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $dep->status === 'tempo' ? 'bg-amber-50 text-amber-700 border border-amber-200/60' : 'bg-slate-100 text-slate-700' }}">
                                    @if($dep->status === 'lunas')
                                        Lunas {{ strtoupper($dep->payment_method) }}
                                    @else
                                        {{ $dep->payment_type === 'tempo' ? 'Konsinyasi' : 'Lunas ' . strtoupper($dep->payment_method) }}
                                    @endif
                                </span>
                                @if($dep->payment_proof_file)
                                <a href="{{ Storage::url($dep->payment_proof_file) }}" target="_blank" class="block text-[10px] font-bold text-blue-600 hover:underline mt-1">
                                    📎 Bukti Transfer
                                </a>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($dep->status === 'lunas')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Lunas
                                </span>
                                @elseif($dep->status === 'tempo')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Tempo Aktif
                                </span>
                                @elseif($dep->status === 'menunggu_konfirmasi')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span> Menunggu Diisi
                                </span>
                                @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">Dibatalkan</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-extrabold">
                                @if($dep->actual_paid_amount !== null)
                                <span class="text-slate-900">Rp {{ number_format($dep->actual_paid_amount, 0, ',', '.') }}</span>
                                @if($dep->merchant_profit > 0)
                                <span class="block text-[10px] font-sans font-bold text-emerald-600">+Untung Rp {{ number_format($dep->merchant_profit, 0, ',', '.') }}</span>
                                @endif
                                @else
                                <span class="text-amber-600 text-xs">Belum Setor</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400 font-medium">Belum ada riwayat pembelian saldo.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards View -->
            <div class="sm:hidden divide-y divide-slate-100">
                @forelse($deposits as $dep)
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="font-mono font-bold text-slate-900 text-xs block">{{ $dep->deposit_number }}</span>
                            <span class="text-[10px] text-slate-400">{{ $dep->deposit_date->translatedFormat('d M Y') }}</span>
                        </div>
                        <span class="font-mono font-extrabold text-blue-700 text-sm">
                            Rp {{ number_format($dep->amount, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <div>
                            @if($dep->status === 'lunas')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Lunas {{ strtoupper($dep->payment_method) }}
                            </span>
                            @elseif($dep->status === 'tempo')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Tempo Aktif</span>
                            @elseif($dep->status === 'menunggu_konfirmasi')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">Menunggu Diisi</span>
                            @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">Dibatalkan</span>
                            @endif

                            @if($dep->payment_proof_file)
                            <a href="{{ Storage::url($dep->payment_proof_file) }}" target="_blank" class="block text-[10px] font-bold text-blue-600 hover:underline mt-1">
                                📎 Bukti Transfer
                            </a>
                            @endif
                        </div>

                        <div class="text-right font-mono">
                            @if($dep->actual_paid_amount !== null)
                            <span class="text-xs font-bold text-slate-800">Setor: Rp {{ number_format($dep->actual_paid_amount, 0, ',', '.') }}</span>
                            @if($dep->merchant_profit > 0)
                            <span class="block text-[10px] text-emerald-600 font-bold font-sans">Untung: Rp {{ number_format($dep->merchant_profit, 0, ',', '.') }}</span>
                            @endif
                            @else
                            <span class="text-xs font-bold text-amber-600">Belum Disetor</span>
                            @endif
                        </div>
                    </div>

                    @if($dep->status === 'tempo')
                    <div class="pt-1.5 border-t border-slate-50 flex justify-end">
                        <a href="{{ route('deposits.invoice', $dep) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 font-bold text-xs">
                            📄 Faktur Tagihan
                        </a>
                    </div>
                    @elseif($dep->status === 'lunas')
                    <div class="pt-1.5 border-t border-slate-50 flex justify-end">
                        <a href="{{ route('deposits.invoice', $dep) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold text-xs">
                            🧾 Kwitansi Lunas
                        </a>
                    </div>
                    @endif
                </div>
                @empty
                <div class="p-6 text-center text-slate-400 text-xs font-medium">Belum ada riwayat pembelian saldo.</div>
                @endforelse
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $deposits->links() }}
            </div>
        </div>

    </div>

</div>

<script>
function formatRupiahInput(input) {
    let value = input.value.replace(/[^0-9]/g, '');
    if (value) {
        input.value = new Intl.NumberFormat('id-ID').format(value);
    } else {
        input.value = '';
    }
}

function setDepositAmount(amountStr) {
    document.getElementById('depositAmountInput').value = amountStr;
}

function togglePayType(type) {
    const labelLunas = document.getElementById('labelPayLunas');
    const labelTempo = document.getElementById('labelPayTempo');
    const sectionPaymentMethod = document.getElementById('sectionPaymentMethod');

    if (type === 'lunas') {
        labelLunas.className = "relative flex flex-col p-3 rounded-2xl border-2 cursor-pointer transition-all border-blue-500 bg-blue-50/50";
        labelTempo.className = "relative flex flex-col p-3 rounded-2xl border-2 border-slate-200 cursor-pointer transition-all hover:bg-slate-50";
        sectionPaymentMethod.classList.remove('hidden');
    } else {
        labelTempo.className = "relative flex flex-col p-3 rounded-2xl border-2 cursor-pointer transition-all border-amber-500 bg-amber-50/50";
        labelLunas.className = "relative flex flex-col p-3 rounded-2xl border-2 border-slate-200 cursor-pointer transition-all hover:bg-slate-50";
        sectionPaymentMethod.classList.add('hidden');
    }
}

function toggleProofField(method) {
    const boxBankInfo = document.getElementById('boxBankInfo');
    const boxProofUpload = document.getElementById('boxProofUpload');
    if (method === 'cash') {
        boxBankInfo.classList.add('hidden');
        boxProofUpload.classList.add('hidden');
    } else {
        boxBankInfo.classList.remove('hidden');
        boxProofUpload.classList.remove('hidden');
    }
}
</script>
@endsection
