@extends('layouts.app')

@section('title', 'Pencatatan Pengeluaran Kas')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-900">Beban & Pengeluaran Operasional</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">Catat pengeluaran harian seperti bensin/transport, kertas thermal/tinta, ISP, token listrik tiang, perbaikan router, dll.</p>
        </div>
        @if(auth()->user()->isSuperAdmin())
        <a href="{{ route('finance.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold transition-colors">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Buku Kas
        </a>
        @else
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold transition-colors">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Pesanan
        </a>
        @endif
    </div>

    <!-- Form Tambah Pengeluaran -->
    <div class="bg-white p-6 sm:p-7 rounded-[28px] border border-slate-200/70 shadow-[0_4px_20px_rgb(0,0,0,0.03)] space-y-4">
        <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
            Catat Pengeluaran Baru
        </h2>

        <form action="{{ route('finance.expenses.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama / Keterangan</label>
                <input type="text" name="title" required placeholder="Contoh: Tagihan ISP Biznet / Gaji Teknisi"
                    class="w-full px-4 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <!-- Kategori Seamless (Pilih List ATAU Tambah Kategori Baru) -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Kategori</label>
                    <button type="button" onclick="toggleExpenseNewCategory()" class="text-[11px] font-bold text-blue-600 hover:text-blue-800 transition-colors flex items-center gap-1">
                        <span id="expenseToggleIcon">+</span> <span id="expenseToggleText">Kategori Baru</span>
                    </button>
                </div>

                <!-- Dropdown Kategori Eksisting -->
                <div id="expenseSelectBox">
                    <select name="category" id="expenseCategorySelect" onchange="checkExpenseOption(this)" class="w-full px-4 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                        <option value="__NEW__" class="font-bold text-blue-600">+ Ketik Kategori Baru...</option>
                    </select>
                </div>

                <!-- Input Kategori Baru (Muncul Seamless di Tempat yang Sama) -->
                <div id="expenseInputBox" class="hidden">
                    <div class="relative flex items-center">
                        <input type="text" id="expenseNewCategoryInput" placeholder="Ketik kategori baru (mis: Gaji)..."
                            class="w-full pl-3.5 pr-8 py-2.5 text-xs bg-blue-50/60 border border-blue-300 rounded-2xl text-slate-900 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        <button type="button" onclick="cancelExpenseNewCategory()" title="Batal, pilih dari list" class="absolute right-2 text-slate-400 hover:text-slate-600 p-1 text-xs font-bold">✕</button>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nominal Biaya (Rp)</label>
                <input type="text" inputmode="numeric" name="amount" required placeholder="Contoh: 750.000" oninput="formatRupiahInput(this)"
                    class="rupiah-input w-full px-4 py-2.5 text-xs font-mono font-extrabold bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Bayar</label>
                <input type="date" name="expense_date" required value="{{ date('Y-m-d') }}"
                    class="w-full px-4 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div class="sm:col-span-3">
                <input type="text" name="notes" placeholder="Catatan tambahan opsional (nomor resi / nama toko / nomor tiang)..."
                    class="w-full px-4 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div>
                <button type="submit" class="w-full h-full py-2.5 px-5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 active:scale-[0.98] transition-all">
                    + Simpan Pengeluaran
                </button>
            </div>
        </form>
    </div>

    <!-- Filter Riwayat Pengeluaran -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-[0_2px_12px_rgb(0,0,0,0.02)]">
        <form method="GET" action="{{ route('finance.expenses') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
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
                <a href="{{ route('finance.expenses') }}" class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 border border-slate-200/60 touch-target">
                    Reset Filter
                </a>
            </div>
        </form>
    </div>

    <!-- Riwayat Tabel Pengeluaran -->
    <div class="bg-white rounded-[28px] border border-slate-200/70 overflow-hidden shadow-[0_4px_20px_rgb(0,0,0,0.03)]">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-extrabold text-slate-900">Riwayat Pengeluaran Kas</h2>
            <span class="text-xs font-extrabold font-mono text-rose-600 bg-rose-50 px-3 py-1 rounded-full border border-rose-200/60">
                Total: Rp {{ number_format($totalAmount, 0, ',', '.') }}
            </span>
        </div>

        <!-- Desktop View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/70 text-slate-600 font-bold text-[11px] uppercase tracking-wider">
                        <th class="py-3.5 px-5">No. Pengeluaran</th>
                        <th class="py-3.5 px-5">Tanggal</th>
                        <th class="py-3.5 px-5">Keterangan</th>
                        <th class="py-3.5 px-5">Kategori</th>
                        <th class="py-3.5 px-5">Dicatat Oleh</th>
                        <th class="py-3.5 px-5">Nominal</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($expenses as $exp)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="py-3.5 px-5 font-mono font-bold text-slate-900">{{ $exp->expense_number }}</td>
                        <td class="py-3.5 px-5 text-slate-500 font-medium">{{ $exp->expense_date->translatedFormat('d M Y') }}</td>
                        <td class="py-3.5 px-5">
                            <span class="font-semibold text-slate-900 block">{{ $exp->title }}</span>
                            @if($exp->notes)
                            <span class="text-[11px] text-slate-400 mt-0.5 block italic">{{ $exp->notes }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-5">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200/60">
                                {{ $exp->category }}
                            </span>
                        </td>
                        <td class="py-3.5 px-5">
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-600 bg-slate-100/80 px-2.5 py-1 rounded-full border border-slate-200/60">
                                <span class="w-1.5 h-1.5 rounded-full {{ ($exp->creator->role ?? '') === 'super_admin' ? 'bg-purple-500' : 'bg-sky-500' }}"></span>
                                {{ $exp->creator->name ?? 'Admin' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-5 font-mono font-extrabold text-rose-600">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-5 text-right">
                            @if(auth()->user()->isSuperAdmin() || $exp->created_by === auth()->id())
                            <form action="{{ route('finance.expenses.destroy', $exp) }}" method="POST" onsubmit="return confirm('Hapus catatan pengeluaran ini?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold transition-colors">Hapus</button>
                            </form>
                            @else
                            <span class="text-slate-400 text-[11px] italic" title="Hanya pembuat atau Super Admin yang bisa menghapus">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400 font-medium">Belum ada catatan pengeluaran yang sesuai filter.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Adaptive Cards View -->
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($expenses as $exp)
            <div class="p-4 space-y-2.5 hover:bg-slate-50/50 transition-colors">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="font-bold text-slate-900 text-xs block">{{ $exp->title }}</span>
                        <span class="text-[10px] text-slate-400 font-mono">{{ $exp->expense_number }} • {{ $exp->expense_date->translatedFormat('d M Y') }}</span>
                        @if($exp->notes)
                        <span class="text-[10px] text-slate-500 italic block mt-0.5">{{ $exp->notes }}</span>
                        @endif
                    </div>
                    <span class="font-mono font-extrabold text-rose-600 text-sm block">
                        -Rp {{ number_format($exp->amount, 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-xs pt-1">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200/60">
                            {{ $exp->category }}
                        </span>
                        <span class="inline-flex items-center gap-1 text-[10px] font-medium text-slate-500 bg-slate-50 px-2 py-0.5 rounded-full border border-slate-200/50">
                            <span class="w-1.5 h-1.5 rounded-full {{ ($exp->creator->role ?? '') === 'super_admin' ? 'bg-purple-500' : 'bg-sky-500' }}"></span>
                            {{ $exp->creator->name ?? 'Admin' }}
                        </span>
                    </div>
                    @if(auth()->user()->isSuperAdmin() || $exp->created_by === auth()->id())
                    <form action="{{ route('finance.expenses.destroy', $exp) }}" method="POST" onsubmit="return confirm('Hapus catatan pengeluaran ini?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold text-xs">Hapus</button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="p-6 text-center text-slate-400 text-xs font-medium">Belum ada catatan pengeluaran yang sesuai filter.</div>
            @endforelse
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $expenses->links() }}
        </div>
    </div>

</div>

<script>
    function checkExpenseOption(select) {
        if (select.value === '__NEW__') {
            showExpenseNewCategory();
        }
    }

    function showExpenseNewCategory() {
        document.getElementById('expenseSelectBox').classList.add('hidden');
        document.getElementById('expenseInputBox').classList.remove('hidden');
        document.getElementById('expenseCategorySelect').removeAttribute('name');
        const input = document.getElementById('expenseNewCategoryInput');
        input.setAttribute('name', 'category');
        input.required = true;
        input.focus();
        document.getElementById('expenseToggleText').innerText = 'Pilih List';
        document.getElementById('expenseToggleIcon').innerText = '←';
    }

    function cancelExpenseNewCategory() {
        document.getElementById('expenseInputBox').classList.add('hidden');
        document.getElementById('expenseSelectBox').classList.remove('hidden');
        const input = document.getElementById('expenseNewCategoryInput');
        input.removeAttribute('name');
        input.required = false;
        input.value = '';
        const select = document.getElementById('expenseCategorySelect');
        select.setAttribute('name', 'category');
        if (select.value === '__NEW__') {
            select.selectedIndex = 0;
        }
        document.getElementById('expenseToggleText').innerText = 'Kategori Baru';
        document.getElementById('expenseToggleIcon').innerText = '+';
    }

    function toggleExpenseNewCategory() {
        const box = document.getElementById('expenseInputBox');
        if (box.classList.contains('hidden')) {
            showExpenseNewCategory();
        } else {
            cancelExpenseNewCategory();
        }
    }
</script>
@endsection
