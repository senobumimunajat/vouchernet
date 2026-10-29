<?php

namespace App\Http\Controllers;

use App\Models\MerchantDeposit;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MerchantDepositController extends Controller
{
    /**
     * Tampilan Riwayat & Pemesanan Saldo Mandiri oleh Mitra Warung
     */
    public function tenantIndex(Request $request)
    {
        $user = Auth::user();

        $deposits = MerchantDeposit::where('tenant_id', $user->id)
            ->latest('deposit_date')
            ->latest('id')
            ->paginate(15);

        // Metrik Saldo Mitra
        $totalDepositTaken = MerchantDeposit::where('tenant_id', $user->id)
            ->whereIn('status', ['lunas', 'tempo'])
            ->sum('amount');

        $pendingDepositBill = MerchantDeposit::where('tenant_id', $user->id)
            ->where('status', 'tempo')
            ->sum('amount');

        $totalProfitEarned = MerchantDeposit::where('tenant_id', $user->id)
            ->where('status', 'lunas')
            ->sum('merchant_profit');

        $bankAccounts = Setting::get('bank_accounts', "BCA: 5410-988-123\nBRI: 021-456-789-01\nDANA: 0812-3456-7890");
        $adminWhatsapp = Setting::get('admin_whatsapp_number', '6281234567890');

        return view('tenant.deposits', compact(
            'user',
            'deposits',
            'totalDepositTaken',
            'pendingDepositBill',
            'totalProfitEarned',
            'bankAccounts',
            'adminWhatsapp'
        ));
    }

    /**
     * Pengajuan Permintaan Pembelian Saldo oleh Mitra Warung
     */
    public function tenantStore(Request $request)
    {
        if ($request->filled('amount')) {
            $request->merge([
                'amount' => str_replace(['.', ','], '', (string) $request->input('amount')),
            ]);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10000'],
            'payment_type' => ['required', 'in:lunas,tempo'],
            'payment_method' => ['required', 'in:cash,transfer'],
            'payment_proof_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $proofPath = null;
        if ($request->hasFile('payment_proof_file')) {
            $proofPath = $request->file('payment_proof_file')->store('proofs', 'public');
        }

        $depositNumber = 'DEP-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        $deposit = MerchantDeposit::create([
            'deposit_number' => $depositNumber,
            'tenant_id' => Auth::id(),
            'amount' => $validated['amount'],
            'payment_type' => $validated['payment_type'],
            'payment_method' => $validated['payment_method'],
            'status' => 'menunggu_konfirmasi',
            'actual_paid_amount' => null,
            'merchant_profit' => 0,
            'payment_proof_file' => $proofPath,
            'notes' => $validated['notes'] ?? null,
            'deposit_date' => now()->format('Y-m-d'),
            'created_by' => Auth::id(),
        ]);

        // Buat URL pesan WhatsApp otomatis ke Admin
        $user = Auth::user();
        $adminPhone = Setting::get('admin_whatsapp_number', '6281234567890');
        $cleanPhone = preg_replace('/[^0-9]/', '', $adminPhone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }

        $shopName = $user->shop_name ?: $user->name;
        $payLabel = $validated['payment_type'] === 'tempo' 
            ? 'Konsinyasi (Tempo)' 
            : 'Lunas (' . strtoupper($validated['payment_method']) . ')';

        $waText = "Halo Admin, saya *{$shopName}* mengajukan Top Up Saldo Kuota Voucher:\n\n" .
                  "• No. Permintaan: *{$depositNumber}*\n" .
                  "• Nominal Saldo: *Rp " . number_format($validated['amount'], 0, ',', '.') . "*\n" .
                  "• Pembayaran: *{$payLabel}*\n";

        if (!empty($validated['notes'])) {
            $waText .= "• Catatan: {$validated['notes']}\n";
        }

        $waText .= "\nMohon dicek dan diisikan ke akun saya ya. Terima kasih!";
        $whatsappUrl = "https://api.whatsapp.com/send?phone={$cleanPhone}&text=" . urlencode($waText);

        return redirect()->route('tenant.deposits.index')
            ->with('success', 'Permintaan saldo berhasil dibuat! Klik tombol WhatsApp untuk mengonfirmasi ke admin.')
            ->with('whatsapp_url', $whatsappUrl)
            ->with('whatsapp_text', $waText);
    }

    /**
     * Dashboard Manajemen Saldo Mitra untuk Admin & Super Admin
     */
    public function adminIndex(Request $request)
    {
        $query = MerchantDeposit::with(['tenant', 'creator', 'confirmer', 'settler']);

        // Search & Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('deposit_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('tenant', function ($tq) use ($search) {
                      $tq->where('name', 'like', "%{$search}%")
                         ->orWhere('shop_name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('month')) {
            $query->whereMonth('deposit_date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('deposit_date', $request->year);
        }

        $deposits = $query->latest('deposit_date')->latest('id')->paginate(15);

        // Metrik Ringkasan Operasional
        $pendingCount = MerchantDeposit::where('status', 'menunggu_konfirmasi')->count();
        $activeTempoCount = MerchantDeposit::where('status', 'tempo')->count();
        $activeTempoAmount = MerchantDeposit::where('status', 'tempo')->sum('amount');
        
        $currentMonthPaid = MerchantDeposit::where('status', 'lunas')
            ->whereMonth('deposit_date', now()->month)
            ->whereYear('deposit_date', now()->year)
            ->sum('actual_paid_amount');

        $tenants = User::where('role', 'tenant')->orderBy('name')->get();

        return view('admin.deposits.index', compact(
            'deposits',
            'pendingCount',
            'activeTempoCount',
            'activeTempoAmount',
            'currentMonthPaid',
            'tenants'
        ));
    }

    /**
     * Input Pembelian Saldo Offline / Walk-in langsung oleh Admin
     */
    public function adminStore(Request $request)
    {
        if ($request->filled('amount')) {
            $request->merge([
                'amount' => str_replace(['.', ','], '', (string) $request->input('amount')),
            ]);
        }
        if ($request->filled('actual_paid_amount')) {
            $request->merge([
                'actual_paid_amount' => str_replace(['.', ','], '', (string) $request->input('actual_paid_amount')),
            ]);
        }

        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:10000'],
            'payment_type' => ['required', 'in:lunas,tempo'],
            'payment_method' => ['required', 'in:cash,transfer'],
            'actual_paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_proof_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'deposit_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $proofPath = null;
        if ($request->hasFile('payment_proof_file')) {
            $proofPath = $request->file('payment_proof_file')->store('proofs', 'public');
        }

        $depositNumber = 'DEP-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        $isLunas = $validated['payment_type'] === 'lunas';

        $actualPaid = null;
        $profit = 0;

        if ($isLunas) {
            $actualPaid = $validated['actual_paid_amount'] ?? $validated['amount'];
            $profit = max(0, $validated['amount'] - $actualPaid);
        }

        MerchantDeposit::create([
            'deposit_number' => $depositNumber,
            'tenant_id' => $validated['tenant_id'],
            'amount' => $validated['amount'],
            'payment_type' => $validated['payment_type'],
            'payment_method' => $validated['payment_method'],
            'status' => $isLunas ? 'lunas' : 'tempo',
            'actual_paid_amount' => $actualPaid,
            'merchant_profit' => $profit,
            'payment_proof_file' => $proofPath,
            'notes' => $validated['notes'] ?? null,
            'deposit_date' => $validated['deposit_date'],
            'confirmed_by' => Auth::id(),
            'confirmed_at' => now(),
            'settled_by' => $isLunas ? Auth::id() : null,
            'settled_at' => $isLunas ? now() : null,
            'created_by' => Auth::id(),
        ]);

        $statusMsg = $isLunas ? 'Lunas (Kas Masuk Bertambah)' : 'Konsinyasi (Tempo Aktif)';
        return back()->with('success', "Transaksi saldo {$depositNumber} berhasil dicatat sebagai {$statusMsg}!");
    }

    /**
     * Konfirmasi Saldo Diisi ke Akun Cetak Mandiri
     */
    public function confirm(MerchantDeposit $deposit)
    {
        if ($deposit->status !== 'menunggu_konfirmasi') {
            return back()->with('error', 'Pesanan saldo ini sudah tidak berstatus menunggu konfirmasi.');
        }

        $isLunas = $deposit->payment_type === 'lunas';

        $deposit->update([
            'status' => $isLunas ? 'lunas' : 'tempo',
            'actual_paid_amount' => $isLunas ? $deposit->amount : null,
            'merchant_profit' => 0,
            'confirmed_by' => Auth::id(),
            'confirmed_at' => now(),
            'settled_by' => $isLunas ? Auth::id() : null,
            'settled_at' => $isLunas ? now() : null,
        ]);

        $msg = $isLunas 
            ? "Saldo berhasil dikonfirmasi dan langsung Lunas. Kas masuk owner bertambah Rp " . number_format($deposit->amount, 0, ',', '.')
            : "Saldo berhasil dikonfirmasi dan masuk ke Konsinyasi (Tempo Aktif).";

        return back()->with('success', $msg);
    }

    /**
     * Pelunasan Saldo Konsinyasi (Tempo) saat Mitra Menyetor Uang
     */
    public function settle(Request $request, MerchantDeposit $deposit)
    {
        if ($deposit->status !== 'tempo') {
            return back()->with('error', 'Hanya transaksi saldo berstatus tempo/konsinyasi yang dapat dilunasi.');
        }

        if ($request->filled('actual_paid_amount')) {
            $request->merge([
                'actual_paid_amount' => str_replace(['.', ','], '', (string) $request->input('actual_paid_amount')),
            ]);
        }

        $validated = $request->validate([
            'actual_paid_amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,transfer'],
            'settled_date' => ['required', 'date'],
            'payment_proof_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'settlement_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $proofPath = $deposit->payment_proof_file;
        if ($request->hasFile('payment_proof_file')) {
            $proofPath = $request->file('payment_proof_file')->store('proofs', 'public');
        }

        $paid = (float) $validated['actual_paid_amount'];
        $profit = max(0, $deposit->amount - $paid);

        $notes = $deposit->notes;
        if (!empty($validated['settlement_notes'])) {
            $notes = ($notes ? $notes . "\n" : "") . "[Pelunasan]: " . $validated['settlement_notes'];
        }

        $deposit->update([
            'status' => 'lunas',
            'actual_paid_amount' => $paid,
            'merchant_profit' => $profit,
            'payment_method' => $validated['payment_method'],
            'payment_proof_file' => $proofPath,
            'notes' => $notes,
            'settled_by' => Auth::id(),
            'settled_at' => Carbon::parse($validated['settled_date'])->setTime(now()->hour, now()->minute),
        ]);

        return back()->with('success', "Pelunasan berhasil! Kas masuk Owner tercatat Rp " . number_format($paid, 0, ',', '.') . " dan Keuntungan Mitra Rp " . number_format($profit, 0, ',', '.'));
    }

    /**
     * Batalkan Permintaan Saldo
     */
    public function cancel(MerchantDeposit $deposit)
    {
        if ($deposit->status === 'lunas') {
            return back()->with('error', 'Transaksi saldo yang sudah lunas tidak dapat dibatalkan.');
        }

        $deposit->update([
            'status' => 'dibatalkan',
        ]);

        return back()->with('success', "Transaksi saldo {$deposit->deposit_number} berhasil dibatalkan.");
    }

    /**
     * Hapus Catatan Saldo (Khusus Super Admin)
     */
    public function destroy(MerchantDeposit $deposit)
    {
        $deposit->delete();
        return back()->with('success', 'Catatan transaksi saldo berhasil dihapus.');
    }

    /**
     * Tampilkan Faktur Tagihan (Invoice) atau Kwitansi Pelunasan Resmi (Receipt) Saldo Mitra
     */
    public function invoice(MerchantDeposit $deposit)
    {
        $user = Auth::user();

        // Keamanan: Mitra warung hanya boleh melihat faktur/kwitansi miliknya sendiri
        if ($user->isTenant() && $deposit->tenant_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat dokumen deposit ini.');
        }

        // Saldo harus dikonfirmasi diisi terlebih dahulu baru faktur atau kwitansi dapat terbit
        if ($deposit->status === 'menunggu_konfirmasi') {
            return back()->with('error', 'Faktur tagihan baru dapat diterbitkan setelah saldo berhasil dikonfirmasi dan diisikan oleh admin.');
        }

        if ($deposit->status === 'dibatalkan') {
            return back()->with('error', 'Transaksi saldo ini telah dibatalkan.');
        }

        $deposit->load(['tenant', 'creator', 'confirmer', 'settler']);

        $appName = Setting::get('app_name', 'VoucherNet');
        $adminPhone = Setting::get('admin_whatsapp_number', '6281234567890');
        $bankAccounts = Setting::get('bank_accounts', "BCA: 123456789 a.n RT RW Net\nBRI: 987654321 a.n RT RW Net\nDANA: 081234567890");

        $isLunas = $deposit->status === 'lunas';
        $customerName = $deposit->tenant?->shop_name ?: ($deposit->tenant?->name ?? 'Mitra Warung');

        $nominalCash = $isLunas ? (int) $deposit->actual_paid_amount : (int) $deposit->amount;
        $terbilang = $this->terbilang($nominalCash) . ' Rupiah';

        // Petugas kasir / penandatangan
        if ($isLunas) {
            $docType = 'Kwitansi Pelunasan Saldo';
            $docNumber = 'KWT-' . $deposit->deposit_number;
            $cashierName = $deposit->settler?->name ?? ($deposit->confirmer?->name ?? 'Pengelola / Kasir');
            $cashierSigner = $cashierName;

            $profitText = $deposit->merchant_profit > 0 ? "\n• *Untung Mitra:* Rp " . number_format($deposit->merchant_profit, 0, ',', '.') : "";
            $waShareText = "*BUKTI PEMBAYARAN LUNAS (KWITANSI SALDO)*\n" .
                "_{$appName}_\n\n" .
                "• *No. Kwitansi:* {$docNumber}\n" .
                "• *Mitra:* {$customerName}\n" .
                "• *Keterangan:* Pembelian Saldo Cetak Mandiri\n" .
                "• *Nominal Saldo:* Rp " . number_format($deposit->amount, 0, ',', '.') . "\n" .
                "• *Kas Masuk Disetor:* *Rp " . number_format($deposit->actual_paid_amount, 0, ',', '.') . "*{$profitText}\n" .
                "• *Metode:* " . strtoupper($deposit->payment_method ?? 'TUNAI') . "\n" .
                "• *Tgl Lunas:* " . ($deposit->settled_at ? $deposit->settled_at->format('d/m/Y H:i') : ($deposit->confirmed_at ? $deposit->confirmed_at->format('d/m/Y H:i') : date('d/m/Y'))) . "\n" .
                "• *Penerima:* {$cashierName}\n" .
                "• *Status:* *LUNAS (SAH)*\n\n" .
                "Terima kasih atas pelunasan setoran saldo voucher Anda.";
        } else {
            $docType = 'Faktur Tagihan Saldo';
            $docNumber = 'INV-' . $deposit->deposit_number;
            $cashierName = $deposit->confirmer?->name ?? ($deposit->creator?->name ?? 'Pengelola Hotspot');
            $cashierSigner = $deposit->confirmed_at ? $cashierName : null;

            $statusLabel = $deposit->status === 'menunggu_konfirmasi' ? 'MENUNGGU KONFIRMASI' : 'TEMPO (KONSINYASI)';
            $waShareText = "*FAKTUR TAGIHAN SALDO KUOTA*\n" .
                "_{$appName}_\n\n" .
                "• *No. Faktur:* {$docNumber}\n" .
                "• *Mitra:* {$customerName}\n" .
                "• *Keterangan:* Pengambilan Saldo Kuota Voucher Mandiri\n" .
                "• *Nominal Saldo:* Rp " . number_format($deposit->amount, 0, ',', '.') . "\n" .
                "• *Petugas:* {$cashierName}\n" .
                "• *Wajib Setor:* *Rp " . number_format($deposit->amount, 0, ',', '.') . "*\n" .
                "• *Status:* *{$statusLabel}*\n\n" .
                "Silakan lakukan setoran saat saldo voucher telah laku terjual. Terima kasih!";
        }

        $targetPhone = preg_replace('/[^0-9]/', '', (string) ($deposit->tenant?->phone ?: $adminPhone));
        if (str_starts_with($targetPhone, '0')) {
            $targetPhone = '62' . substr($targetPhone, 1);
        }
        $waShareUrl = "https://api.whatsapp.com/send?phone={$targetPhone}&text=" . urlencode($waShareText);

        return view('deposits.invoice', compact(
            'deposit',
            'docType',
            'docNumber',
            'isLunas',
            'customerName',
            'appName',
            'adminPhone',
            'bankAccounts',
            'nominalCash',
            'terbilang',
            'cashierName',
            'cashierSigner',
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
