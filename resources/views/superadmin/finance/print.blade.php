<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan & Buku Kas - {{ $periodLabel }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                font-size: 11pt;
            }
            .page-break {
                page-break-before: always;
            }
            tr {
                page-break-inside: avoid;
            }
            .print-border {
                border: 1px solid #cbd5e1 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased min-h-screen py-6 px-4 sm:px-6">

    <!-- Top Floating Toolbar (Hidden during Print) -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex items-center justify-between gap-4 bg-white p-4 rounded-2xl shadow-md border border-slate-200">
        <a href="{{ route('finance.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-slate-900 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Kembali ke Buku Kas
        </a>
        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-500 hidden sm:inline">Tips: Pilih "Save as PDF" pada dialog cetak.</span>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Printable Paper Sheet -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-3xl shadow-lg border border-slate-200 space-y-8">
        
        <!-- Kop Surat / Header Laporan Resmi -->
        <div class="border-b-2 border-slate-900 pb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    @if(!empty($globalAppLogo))
                    <div class="h-11 w-auto flex items-center justify-center shrink-0">
                        <img src="{{ $globalAppLogo }}" alt="{{ $appName }}" class="h-10 w-auto max-w-[140px] object-contain">
                    </div>
                    @else
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-extrabold text-lg">
                        VN
                    </div>
                    @endif
                    <div>
                        <h1 class="text-xl font-extrabold text-slate-900 uppercase tracking-tight">{{ $appName }}</h1>
                        <p class="text-xs text-slate-500 font-medium">{{ $globalAppSubtitle ?? 'Pengelolaan Jaringan Hotspot Komunitas & Buku Kas RT/RW' }}</p>
                    </div>
                </div>
            </div>
            <div class="text-left sm:text-right text-xs text-slate-600 space-y-0.5">
                <div class="font-bold text-slate-900">LAPORAN ARUS KAS & LABA BERSIH</div>
                <div>Periode: <strong class="text-slate-900">{{ $periodLabel }}</strong></div>
                @if($selectedTenantUser)
                <div>Mitra: <strong class="text-blue-900">{{ $selectedTenantUser->shop_name ?: $selectedTenantUser->name }}</strong></div>
                @endif
                <div class="text-[11px] text-slate-400">Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB</div>
            </div>
        </div>

        <!-- Ringkasan Eksekutif Kas -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ringkasan Arus Kas Periode Ini</h2>
                <div class="text-xs font-extrabold text-slate-900 bg-slate-100 px-3 py-1 rounded-full border border-slate-200">
                    Total Kas Masuk (Voucher + Kas Lain): <span class="font-mono text-emerald-800">Rp {{ number_format($totalCashIn, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Setoran Voucher</span>
                    <span class="text-base font-extrabold font-mono text-slate-900 block mt-1">Rp {{ number_format($netOwnerRevenue, 0, ',', '.') }}</span>
                    <span class="text-[10px] text-slate-400">{{ number_format($totalVouchersSold) }} lembar voucher</span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Pemasukan Lain</span>
                    <span class="text-base font-extrabold font-mono text-slate-900 block mt-1">Rp {{ number_format($otherIncome, 0, ',', '.') }}</span>
                    <span class="text-[10px] text-slate-400">{{ count($incomes) }} transaksi lain</span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Beban Operasional</span>
                    <span class="text-base font-extrabold font-mono text-slate-900 block mt-1">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</span>
                    <span class="text-[10px] text-slate-400">{{ count($expenses) }} pos pengeluaran</span>
                </div>
                <div class="p-4 rounded-2xl {{ $netProfit >= 0 ? 'bg-blue-50/70 border-blue-200' : 'bg-rose-50/70 border-rose-200' }} border">
                    <span class="text-[10px] font-bold {{ $netProfit >= 0 ? 'text-blue-800' : 'text-rose-800' }} uppercase tracking-wider block">Laba Kas Bersih</span>
                    <span class="text-base font-extrabold font-mono {{ $netProfit >= 0 ? 'text-blue-900' : 'text-rose-900' }} block mt-1">Rp {{ number_format($netProfit, 0, ',', '.') }}</span>
                    <span class="text-[10px] {{ $netProfit >= 0 ? 'text-blue-600' : 'text-rose-600' }} font-bold">{{ $netProfit >= 0 ? 'Surplus Kas' : 'Defisit Kas' }}</span>
                </div>
            </div>
        </div>

        <!-- Tabel 1: Pemasukan Kas dari Penjualan Voucher (Terpisah) -->
        <div class="space-y-3">
            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-md bg-blue-600 text-white flex items-center justify-center text-xs">1</span>
                    Pemasukan dari Penjualan Voucher Hotspot
                </h3>
                <span class="text-xs font-mono font-bold text-slate-900">Subtotal: Rp {{ number_format($netOwnerRevenue, 0, ',', '.') }}</span>
            </div>

            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 font-bold border-y border-slate-200">
                        <th class="py-2.5 px-3">Waktu</th>
                        <th class="py-2.5 px-3">No. Order</th>
                        <th class="py-2.5 px-3">Mitra Warung / Tenant</th>
                        <th class="py-2.5 px-3">Paket Voucher</th>
                        <th class="py-2.5 px-3 text-center">Lembar</th>
                        <th class="py-2.5 px-3 text-right">Setoran Kas Owner</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($orders as $ord)
                    <tr>
                        <td class="py-2 px-3 text-slate-500 whitespace-nowrap">{{ $ord->confirmed_at?->translatedFormat('d/m/Y H:i') }}</td>
                        <td class="py-2 px-3 font-mono font-bold text-slate-900">{{ $ord->order_number }}</td>
                        <td class="py-2 px-3">
                            @if($ord->items->isNotEmpty())
                                @foreach($ord->items as $it)
                                    <div>{{ $it->package?->name ?? 'Paket' }} <span class="text-slate-500 font-mono text-[10px]">({{ $it->quantity }} lbr)</span></div>
                                @endforeach
                            @else
                                {{ $ord->package?->name ?? 'Voucher' }}
                            @endif
                        </td>
                        <td class="py-2 px-3 text-center font-mono">{{ $ord->quantity }}</td>
                        <td class="py-2 px-3 text-right font-mono font-bold text-slate-900">Rp {{ number_format($ord->total_owner_amount, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-4 text-center text-slate-400 italic">Tidak ada transaksi voucher pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($orders) > 0)
                <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-300">
                    <tr>
                        <td colspan="4" class="py-2 px-3 uppercase">Total Setoran Voucher</td>
                        <td class="py-2 px-3 text-center font-mono">{{ number_format($totalVouchersSold) }}</td>
                        <td class="py-2 px-3 text-right font-mono font-bold text-slate-900">Rp {{ number_format($netOwnerRevenue, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <!-- Tabel 2: Pemasukan Kas Lain-Lain (Terpisah) -->
        <div class="space-y-3">
            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-md bg-sky-600 text-white flex items-center justify-center text-xs">2</span>
                    Pemasukan Kas Lain-Lain (Iuran, Pasang Baru, Sewa Tiang, dll.)
                </h3>
                <span class="text-xs font-mono font-bold text-slate-900">Subtotal: Rp {{ number_format($otherIncome, 0, ',', '.') }}</span>
            </div>

            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 font-bold border-y border-slate-200">
                        <th class="py-2.5 px-3">Tanggal</th>
                        <th class="py-2.5 px-3">No. Bukti</th>
                        <th class="py-2.5 px-3">Kategori</th>
                        <th class="py-2.5 px-3">Keterangan / Sumber Pemasukan</th>
                        <th class="py-2.5 px-3">Penerima Kas</th>
                        <th class="py-2.5 px-3 text-right">Nominal Kas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($incomes as $inc)
                    <tr>
                        <td class="py-2 px-3 text-slate-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($inc->income_date)->translatedFormat('d/m/Y') }}</td>
                        <td class="py-2 px-3 font-mono font-bold text-slate-900">{{ $inc->income_number }}</td>
                        <td class="py-2 px-3 font-medium">{{ ucfirst($inc->category) }}</td>
                        <td class="py-2 px-3">{{ $inc->title }} {{ $inc->notes ? '(' . $inc->notes . ')' : '' }}</td>
                        <td class="py-2 px-3 text-slate-500">{{ $inc->creator?->name ?? 'Admin' }}</td>
                        <td class="py-2 px-3 text-right font-mono font-bold text-slate-900">Rp {{ number_format($inc->amount, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-4 text-center text-slate-400 italic">Tidak ada catatan pemasukan lain pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($incomes) > 0)
                <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-300">
                    <tr>
                        <td colspan="5" class="py-2 px-3 uppercase">Total Pemasukan Kas Lain</td>
                        <td class="py-2 px-3 text-right font-mono font-bold text-slate-900">Rp {{ number_format($otherIncome, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <!-- Tabel 3: Beban Pengeluaran Kas Operasional -->
        <div class="space-y-3">
            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-md bg-amber-600 text-white flex items-center justify-center text-xs">3</span>
                    Beban Pengeluaran Kas Operasional
                </h3>
                <span class="text-xs font-mono font-bold text-slate-900">Subtotal: Rp {{ number_format($totalExpenses, 0, ',', '.') }}</span>
            </div>

            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 font-bold border-y border-slate-200">
                        <th class="py-2.5 px-3">Tanggal</th>
                        <th class="py-2.5 px-3">No. Beban</th>
                        <th class="py-2.5 px-3">Kategori</th>
                        <th class="py-2.5 px-3">Keperluan / Keterangan</th>
                        <th class="py-2.5 px-3">Penanggung Jawab</th>
                        <th class="py-2.5 px-3 text-right">Nominal Pengeluaran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($expenses as $exp)
                    <tr>
                        <td class="py-2 px-3 text-slate-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($exp->expense_date)->translatedFormat('d/m/Y') }}</td>
                        <td class="py-2 px-3 font-mono font-bold text-slate-900">{{ $exp->expense_number }}</td>
                        <td class="py-2 px-3 font-medium">{{ ucfirst($exp->category) }}</td>
                        <td class="py-2 px-3">{{ $exp->title }} {{ $exp->notes ? '(' . $exp->notes . ')' : '' }}</td>
                        <td class="py-2 px-3 text-slate-500">{{ $exp->creator?->name ?? 'Admin' }}</td>
                        <td class="py-2 px-3 text-right font-mono font-bold text-slate-900">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-4 text-center text-slate-400 italic">Tidak ada catatan pengeluaran beban pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($expenses) > 0)
                <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-300">
                    <tr>
                        <td colspan="5" class="py-2 px-3 uppercase">Total Beban Pengeluaran</td>
                        <td class="py-2 px-3 text-right font-mono font-bold text-slate-900">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <!-- Kolom Tanda Tangan Formal -->
        <div class="pt-8 border-t-2 border-slate-300 grid grid-cols-2 gap-8 text-center text-xs">
            <div class="space-y-16">
                <div>
                    <p class="text-slate-500">Mengetahui,</p>
                    <p class="font-bold text-slate-900">Ketua / Pengelola RT-RW Net</p>
                </div>
                <div>
                    <p class="font-bold text-slate-900 border-b border-slate-400 pb-1 inline-block min-w-[160px]">( .................................................. )</p>
                </div>
            </div>

            <div class="space-y-16">
                <div>
                    <p class="text-slate-500">Dibuat Oleh,</p>
                    <p class="font-bold text-slate-900">Bendahara / Bagian Keuangan</p>
                </div>
                <div>
                    <p class="font-bold text-slate-900 border-b border-slate-400 pb-1 inline-block min-w-[160px]">{{ auth()->user()->name }}</p>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
