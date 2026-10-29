@extends('layouts.app')

@section('title', 'Pemasukan Kas Manual')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-900">Pemasukan Kas Manual (Di Luar Voucher)</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">Catat penerimaan kas dari iuran bulanan pelanggan kabel rumahan, jasa registrasi pasang baru, sewa tiang, dll.</p>
        </div>
        @if(auth()->user()->isSuperAdmin())
        <a href="{{ route('finance.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold transition-colors">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Buku Kas
        </a>
        @else
        <a href="{{ route('finance.expenses') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold transition-colors">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Ke Pengeluaran Kas
        </a>
        @endif
    </div>

    <!-- Form Tambah Pemasukan -->
    <div class="bg-white p-6 sm:p-7 rounded-[28px] border border-slate-200/70 shadow-[0_4px_20px_rgb(0,0,0,0.03)] space-y-4">
        <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
            Catat Pemasukan Baru
        </h2>

        <form action="{{ route('finance.incomes.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keterangan Pemasukan</label>
                <input type="text" name="title" required placeholder="Contoh: Iuran Bulanan Bpk. Joko"
                    class="w-full px-4 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <!-- Kategori Seamless (Pilih List ATAU Tambah Kategori Baru) -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Kategori</label>
                    <button type="button" onclick="toggleIncomeNewCategory()" class="text-[11px] font-bold text-blue-600 hover:text-blue-800 transition-colors flex items-center gap-1">
                        <span id="incomeToggleIcon">+</span> <span id="incomeToggleText">Kategori Baru</span>
                    </button>
                </div>

                <!-- Dropdown Kategori Eksisting -->
                <div id="incomeSelectBox">
                    <select name="category" id="incomeCategorySelect" onchange="checkIncomeOption(this)" class="w-full px-4 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                        <option value="__NEW__" class="font-bold text-blue-600">+ Ketik Kategori Baru...</option>
                    </select>
                </div>

                <!-- Input Kategori Baru (Muncul Seamless di Tempat yang Sama) -->
                <div id="incomeInputBox" class="hidden">
                    <div class="relative flex items-center">
                        <input type="text" id="incomeNewCategoryInput" placeholder="Ketik kategori baru (mis: Iuran RT)..."
                            class="w-full pl-3.5 pr-8 py-2.5 text-xs bg-blue-50/60 border border-blue-300 rounded-2xl text-slate-900 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        <button type="button" onclick="cancelIncomeNewCategory()" title="Batal, pilih dari list" class="absolute right-2.5 text-slate-400 hover:text-slate-600 p-1 text-xs font-bold">✕</button>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nominal (Rp)</label>
                <input type="text" inputmode="numeric" name="amount" required placeholder="Contoh: 150.000" oninput="formatRupiahInput(this)"
                    class="rupiah-input w-full px-4 py-2.5 text-xs font-mono font-extrabold bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Masuk</label>
                <input type="date" name="income_date" required value="{{ date('Y-m-d') }}"
                    class="w-full px-4 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div class="sm:col-span-3">
                <input type="text" name="notes" placeholder="Catatan tambahan opsional..."
                    class="w-full px-4 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div>
                <button type="submit" class="w-full h-full py-2.5 px-5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 active:scale-[0.98] transition-all">
                    + Simpan Pemasukan
                </button>
            </div>
        </form>
    </div>

    <!-- Filter Riwayat Pemasukan -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-[0_2px_12px_rgb(0,0,0,0.02)]">
        <form method="GET" action="{{ route('finance.incomes') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Filter Kategori</label>
                <select name="category" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Bulan</label>
                <select name="month" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                    <option value="">Semua Bulan</option>
                    @for($m = 1; $m <= 12; $m++)
                        @php $val = sprintf('%02d', $m); @endphp
                        <option value="{{ $val }}" {{ request('month') == $val ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tahun</label>
                <select name="year" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                    <option value="">Semua Tahun</option>
                    @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <a href="{{ route('finance.incomes') }}" class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 border border-slate-200/60 touch-target">
                    Reset Filter
                </a>
            </div>
        </form>
    </div>

    <!-- Riwayat Tabel Pemasukan -->
    <div class="bg-white rounded-[28px] border border-slate-200/70 overflow-hidden shadow-[0_4px_20px_rgb(0,0,0,0.03)]">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-extrabold text-slate-900">Riwayat Pemasukan Kas Manual</h2>
            <span class="text-xs font-extrabold font-mono text-blue-700 bg-blue-50 px-3 py-1 rounded-full border border-blue-200/60">
                Total: Rp {{ number_format($totalAmount, 0, ',', '.') }}
            </span>
        </div>

        <!-- Desktop View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/70 text-slate-600 font-bold text-[11px] uppercase tracking-wider">
                        <th class="py-3.5 px-5">No. Pemasukan</th>
                        <th class="py-3.5 px-5">Tanggal</th>
                        <th class="py-3.5 px-5">Keterangan</th>
                        <th class="py-3.5 px-5">Kategori</th>
                        <th class="py-3.5 px-5">Nominal</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($incomes as $inc)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="py-3.5 px-5 font-mono font-bold text-slate-900">{{ $inc->income_number }}</td>
                        <td class="py-3.5 px-5 text-slate-500 font-medium">{{ $inc->income_date->translatedFormat('d M Y') }}</td>
                        <td class="py-3.5 px-5 font-semibold text-slate-900">{{ $inc->title }}</td>
                        <td class="py-3.5 px-5">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200/60">
                                {{ $inc->category }}
                            </span>
                        </td>
                        <td class="py-3.5 px-5 font-mono font-extrabold text-slate-900">Rp {{ number_format($inc->amount, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-5 text-right">
                            @if(auth()->user()->isSuperAdmin() || $inc->created_by === auth()->id())
                            <form action="{{ route('finance.incomes.destroy', $inc) }}" method="POST" onsubmit="return confirm('Hapus catatan pemasukan ini?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold transition-colors">Hapus</button>
                            </form>
                            @else
                            <span class="text-slate-300 text-xs">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400 font-medium">Belum ada catatan pemasukan manual yang sesuai filter.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Adaptive Cards View -->
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($incomes as $inc)
            <div class="p-4 space-y-2.5 hover:bg-slate-50/50 transition-colors">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="font-bold text-slate-900 text-xs block">{{ $inc->title }}</span>
                        <span class="text-[10px] text-slate-400 font-mono">{{ $inc->income_number }} • {{ $inc->income_date->translatedFormat('d M Y') }}</span>
                    </div>
                    <span class="font-mono font-extrabold text-blue-700 text-sm block">
                        +Rp {{ number_format($inc->amount, 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-xs pt-1">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200/60">
                        {{ $inc->category }}
                    </span>
                    @if(auth()->user()->isSuperAdmin() || $inc->created_by === auth()->id())
                    <form action="{{ route('finance.incomes.destroy', $inc) }}" method="POST" onsubmit="return confirm('Hapus catatan pemasukan ini?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold text-xs">Hapus</button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="p-6 text-center text-slate-400 text-xs font-medium">Belum ada catatan pemasukan manual yang sesuai filter.</div>
            @endforelse
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $incomes->links() }}
        </div>
    </div>

</div>

<script>
    function checkIncomeOption(select) {
        if (select.value === '__NEW__') {
            showIncomeNewCategory();
        }
    }

    function showIncomeNewCategory() {
        document.getElementById('incomeSelectBox').classList.add('hidden');
        document.getElementById('incomeInputBox').classList.remove('hidden');
        document.getElementById('incomeCategorySelect').removeAttribute('name');
        const input = document.getElementById('incomeNewCategoryInput');
        input.setAttribute('name', 'category');
        input.required = true;
        input.focus();
        document.getElementById('incomeToggleText').innerText = 'Pilih List';
        document.getElementById('incomeToggleIcon').innerText = '←';
    }

    function cancelIncomeNewCategory() {
        document.getElementById('incomeInputBox').classList.add('hidden');
        document.getElementById('incomeSelectBox').classList.remove('hidden');
        const input = document.getElementById('incomeNewCategoryInput');
        input.removeAttribute('name');
        input.required = false;
        input.value = '';
        const select = document.getElementById('incomeCategorySelect');
        select.setAttribute('name', 'category');
        if (select.value === '__NEW__') {
            select.selectedIndex = 0;
        }
        document.getElementById('incomeToggleText').innerText = 'Kategori Baru';
        document.getElementById('incomeToggleIcon').innerText = '+';
    }

    function toggleIncomeNewCategory() {
        const box = document.getElementById('incomeInputBox');
        if (box.classList.contains('hidden')) {
            showIncomeNewCategory();
        } else {
            cancelIncomeNewCategory();
        }
    }
</script>
@endsection
