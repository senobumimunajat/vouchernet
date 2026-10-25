<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminApprovalController extends Controller
{
    public function index()
    {
        $pendingDeletes = Order::where('status', 'menunggu_approval_hapus')
            ->with(['tenant', 'package', 'items.package', 'deleteRequestedByUser', 'confirmedByUser'])
            ->latest('delete_requested_at')
            ->get();

        $historyDeletes = Order::where('status', 'dibatalkan')
            ->with(['tenant', 'package', 'items.package', 'deleteRequestedByUser', 'deleteApprovedByUser'])
            ->latest('delete_approved_at')
            ->paginate(15);

        return view('superadmin.approvals.index', compact('pendingDeletes', 'historyDeletes'));
    }

    public function approve(Request $request, Order $order)
    {
        if ($order->status !== 'menunggu_approval_hapus') {
            return back()->with('info', 'Pesanan ini tidak dalam status pengajuan hapus.');
        }

        $order->status = 'dibatalkan';
        $order->delete_approved_by = Auth::id();
        $order->delete_approved_at = now();
        $order->save();

        foreach ($order->effective_items as $item) {
            if ($item->package) {
                $item->package->restoreStock($item->quantity, Auth::id(), "Pengembalian stok dari pembatalan transaksi {$order->order_number}");
            }
        }

        return back()->with('success', "Pengajuan pembatalan untuk pesanan {$order->order_number} telah DISETUJUI. Transaksi dibatalkan dan {$order->quantity} lembar stok voucher telah dikembalikan.");
    }

    public function reject(Request $request, Order $order)
    {
        if ($order->status !== 'menunggu_approval_hapus') {
            return back()->with('info', 'Pesanan ini tidak dalam status pengajuan hapus.');
        }

        // Kembalikan ke status lunas
        $order->status = 'lunas';
        $order->delete_reason = 'DITOLAK SUPER ADMIN: ' . ($request->input('reject_reason') ?: 'Pengajuan tidak disetujui');
        $order->save();

        return back()->with('info', "Pengajuan pembatalan untuk pesanan {$order->order_number} telah DITOLAK. Transaksi tetap sah dan berstatus Lunas.");
    }
}
