@extends('layouts.app')

@section('title', 'Persetujuan Hapus Bayar')

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900">Persetujuan Hapus Bayar (Approval)</h1>
        <p class="text-xs text-slate-500 mt-0.5">Tinjau permohonan pembatalan transaksi yang diajukan oleh Admin operasional.</p>
    </div>

    <!-- Active Pending Delete Approvals -->
    <div class="bg-white rounded-[28px] border border-slate-200/80 overflow-hidden shadow-soft">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
                <h2 class="text-sm font-bold text-slate-900">Permohonan Pembatalan Menunggu Persetujuan</h2>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200/80">
                {{ count($pendingDeletes) }} Pengajuan
            </span>
        </div>

        @if($pendingDeletes->isEmpty())
        <div class="p-10 text-center text-slate-400 text-sm">
            Tidak ada permohonan hapus bayar saat ini. Semua transaksi berjalan normal.
        </div>
        @else
        <div class="divide-y divide-slate-100">
            @foreach($pendingDeletes as $order)
            <div class="p-5 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-5 hover:bg-slate-50/50 transition-colors">
                <div class="space-y-2">
                    <div class="flex items-center gap-2.5">
                        <span class="font-mono font-bold text-slate-900 text-sm">{{ $order->order_number }}</span>
                        <span class="text-xs px-3 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 font-semibold">
                            {{ $order->package_names_summary }}
                        </span>
                        <span class="text-xs font-mono font-extrabold text-slate-900">
                            Rp {{ number_format($order->total_owner_amount, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="text-xs text-slate-600">
                        Warung: <strong class="text-slate-800">{{ $order->tenant->shop_name ?: $order->tenant->name }}</strong> • 
                        Dikonfirmasi oleh Admin: <span class="font-semibold">{{ $order->confirmedByUser?->name ?? 'Admin' }}</span>
                    </div>

                    <!-- Kotak Alasan Admin -->
                    <div class="p-3.5 bg-rose-50/60 border border-rose-200 rounded-2xl text-xs space-y-1">
                        <div class="font-bold text-rose-800 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                            Alasan Diajukan oleh {{ $order->deleteRequestedByUser?->name ?? 'Admin' }} ({{ $order->delete_requested_at?->translatedFormat('d M Y H:i') }}):
                        </div>
                        <div class="text-rose-900 italic font-sans pl-5">"{{ $order->delete_reason }}"</div>
                    </div>
                </div>

                <!-- Tombol Aksi Super Admin -->
                <div class="flex items-center gap-2.5 self-end md:self-center shrink-0">
                    <!-- Reject Form -->
                    <form action="{{ route('approvals.reject', $order) }}" method="POST" onsubmit="return confirm('Tolak permohonan ini? Transaksi akan tetap berstatus Lunas.');">
                        @csrf
                        <button type="submit" class="touch-target px-4 py-2 rounded-full text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                            Tolak (Biarkan Lunas)
                        </button>
                    </form>

                    <!-- Approve Delete Form -->
                    <form action="{{ route('approvals.approve', $order) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin menyetujui pembatalan pesanan {{ $order->order_number }}? Transaksi akan berstatus Dibatalkan.');">
                        @csrf
                        <button type="submit" class="touch-target px-5 py-2.5 rounded-full text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 shadow-sm transition-all active:scale-95">
                            Setujui Pembatalan
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Riwayat Transaksi Dibatalkan -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-100">
            <h2 class="text-sm font-bold text-slate-900">Riwayat Transaksi yang Telah Dibatalkan</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold text-[11px] uppercase">
                        <th class="py-3 px-4">No. Order</th>
                        <th class="py-3 px-4">Warung / Tenant</th>
                        <th class="py-3 px-4">Paket & Nominal</th>
                        <th class="py-3 px-4">Alasan Pembatalan</th>
                        <th class="py-3 px-4">Disetujui Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($historyDeletes as $hist)
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $hist->order_number }}</td>
                        <td class="py-3 px-4">{{ $hist->tenant->shop_name ?: $hist->tenant->name }}</td>
                        <td class="py-3 px-4 font-mono">
                            {{ $hist->quantity }} lembar • Rp {{ number_format($hist->total_owner_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-slate-500 italic max-w-xs truncate">{{ $hist->delete_reason }}</td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $hist->deleteApprovedByUser?->name ?? 'Super Admin' }}
                            <div class="text-[10px] text-slate-400">{{ $hist->delete_approved_at?->translatedFormat('d M Y H:i') }}</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-slate-400">Belum ada riwayat pembatalan transaksi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-slate-100">
            {{ $historyDeletes->links() }}
        </div>
    </div>

</div>
@endsection
