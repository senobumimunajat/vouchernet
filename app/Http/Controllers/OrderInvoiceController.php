<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderInvoiceController extends Controller
{
    /**
     * Tampilkan halaman Faktur Tagihan (Invoice) atau Kwitansi Pelunasan (Receipt)
     */
    public function show(Order $order)
    {
        $user = Auth::user();

        // Keamanan: Mitra warung hanya boleh melihat faktur/kwitansi miliknya sendiri
        if ($user->isTenant() && $order->tenant_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat dokumen pesanan ini.');
        }

        $order->load(['tenant', 'package', 'confirmedByUser', 'creator', 'items.package']);

        $appName = Setting::get('app_name', 'VoucherNet');
        $adminPhone = Setting::get('admin_whatsapp_number', '6281234567890');
        $bankAccounts = Setting::get('bank_accounts', "BCA: 123456789 a.n RT RW Net\nBRI: 987654321 a.n RT RW Net\nDANA: 081234567890");

        $totalAmount = (int) $order->total_owner_amount;
        $terbilang = $this->terbilang($totalAmount) . ' Rupiah';

        $isLunas = $order->status === 'lunas';
        $customerName = $order->customer_display_name;

        // Nama kasir / petugas penandatangan faktur & kwitansi:
        // Permanen mengikuti siapa petugas/kasir yang memproses transaksi asli:
        // - Jika LUNAS: nama petugas yang mengonfirmasi/menerima pembayaran (confirmedByUser), atau pembuat transaksi (creator)
        // - Jika TEMPO (Belum Lunas): nama petugas yang membuat dan mengeluarkan faktur (creator)
        // - Nama TIDAK PERNAH berubah mengikuti siapa yang sedang membuka atau mencetak dokumen saat ini.
        $cashierName = null;
        if ($isLunas && $order->confirmedByUser) {
            $cashierName = $order->confirmedByUser->name;
        } elseif ($order->creator && ($order->creator->isAdmin() || $order->creator->isSuperAdmin())) {
            $cashierName = $order->creator->name;
        } elseif ($order->confirmedByUser) {
            $cashierName = $order->confirmedByUser->name;
        } else {
            // Fallback untuk riwayat lama jika creator/confirmed_by kosong
            $cashierName = ($user->isAdmin() || $user->isSuperAdmin())
                ? $user->name
                : (\App\Models\User::whereIn('role', ['admin', 'super_admin'])->value('name') ?? 'Admin Kasir');
        }

        $packageSummary = $order->package_names_summary;

        // Persiapkan teks dan tautan bagikan WhatsApp
        if ($isLunas) {
            $docType = 'Kwitansi Pelunasan';
            $docNumber = 'KWT-' . $order->order_number;
            $waShareText = "*BUKTI PEMBAYARAN LUNAS (KWITANSI)*\n" .
                "_{$appName}_\n\n" .
                "• *No. Kwitansi:* {$docNumber}\n" .
                "• *Mitra:* {$customerName}\n" .
                "• *Rincian:* {$packageSummary}\n" .
                "• *Total Lembar:* {$order->quantity} Lembar\n" .
                "• *Total Diterima:* *Rp " . number_format($totalAmount, 0, ',', '.') . "*\n" .
                "• *Metode:* " . strtoupper($order->payment_method ?? 'TUNAI') . "\n" .
                "• *Tgl Lunas:* " . ($order->confirmed_at ? $order->confirmed_at->format('d/m/Y H:i') : '-') . "\n" .
                "• *Penerima:* " . $cashierName . "\n" .
                "• *Status:* *LUNAS (SAH)*\n\n" .
                "Terima kasih atas pelunasan setoran voucher Anda.";
        } else {
            $docType = 'Faktur Tagihan';
            $docNumber = 'INV-' . $order->order_number;
            $waShareText = "*FAKTUR TAGIHAN KONSINYASI*\n" .
                "_{$appName}_\n\n" .
                "• *No. Faktur:* {$docNumber}\n" .
                "• *Mitra:* {$customerName}\n" .
                "• *Rincian:* {$packageSummary}\n" .
                "• *Total Lembar:* {$order->quantity} Lembar\n" .
                "• *Petugas:* {$cashierName}\n" .
                "• *Wajib Setor:* *Rp " . number_format($totalAmount, 0, ',', '.') . "*\n" .
                "• *Status:* *MENUNGGU PEMBAYARAN (TEMPO)*\n\n" .
                "Silakan lakukan setoran tunai ke kasir atau transfer ke rekening terdaftar saat voucher telah laku terjual. Terima kasih!";
        }

        // Tentukan target nomor WA untuk berbagi:
        // Jika admin/super_admin -> kirim ke nomor HP warung/tenant
        // Jika warung/tenant -> kirim ke nomor WA admin
        $targetPhone = $user->isTenant() ? $adminPhone : ($order->tenant?->phone ?? $adminPhone);
        $targetPhoneClean = preg_replace('/[^0-9]/', '', $targetPhone);
        if (str_starts_with($targetPhoneClean, '0')) {
            $targetPhoneClean = '62' . substr($targetPhoneClean, 1);
        }

        $waShareUrl = 'https://api.whatsapp.com/send?phone=' . $targetPhoneClean . '&text=' . rawurlencode($waShareText);

        return view('orders.invoice', compact(
            'order',
            'docType',
            'docNumber',
            'appName',
            'adminPhone',
            'bankAccounts',
            'terbilang',
            'isLunas',
            'cashierName',
            'waShareText',
            'waShareUrl'
        ));
    }

    /**
     * Konversi bilangan integer menjadi kata terbilang dalam Bahasa Indonesia
     */
    private function terbilang(int|float $angka): string
    {
        $angka = abs((int) $angka);
        $baca = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

        if ($angka < 12) {
            return $baca[$angka];
        } elseif ($angka < 20) {
            return $this->terbilang($angka - 10) . ' Belas';
        } elseif ($angka < 100) {
            $sisa = $angka % 10;
            return trim($this->terbilang(intdiv($angka, 10)) . ' Puluh ' . ($sisa ? $this->terbilang($sisa) : ''));
        } elseif ($angka < 200) {
            $sisa = $angka - 100;
            return trim('Seratus ' . ($sisa ? $this->terbilang($sisa) : ''));
        } elseif ($angka < 1000) {
            $sisa = $angka % 100;
            return trim($this->terbilang(intdiv($angka, 100)) . ' Ratus ' . ($sisa ? $this->terbilang($sisa) : ''));
        } elseif ($angka < 2000) {
            $sisa = $angka - 1000;
            return trim('Seribu ' . ($sisa ? $this->terbilang($sisa) : ''));
        } elseif ($angka < 1000000) {
            $sisa = $angka % 1000;
            return trim($this->terbilang(intdiv($angka, 1000)) . ' Ribu ' . ($sisa ? $this->terbilang($sisa) : ''));
        } elseif ($angka < 1000000000) {
            $sisa = $angka % 1000000;
            return trim($this->terbilang(intdiv($angka, 1000000)) . ' Juta ' . ($sisa ? $this->terbilang($sisa) : ''));
        } elseif ($angka < 1000000000000) {
            $sisa = $angka % 1000000000;
            return trim($this->terbilang(intdiv($angka, 1000000000)) . ' Miliar ' . ($sisa ? $this->terbilang($sisa) : ''));
        }

        return (string) $angka;
    }
}
