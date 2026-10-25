<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $adminWhatsapp = Setting::get('admin_whatsapp_number', '6281234567890');
        $bankAccounts = Setting::get('bank_accounts', "BCA: 123456789 a.n RT RW Net\nBRI: 987654321 a.n RT RW Net\nDANA: 081234567890");
        $appName = Setting::get('app_name', 'VoucherNet RT RW');

        return view('settings.index', compact('adminWhatsapp', 'bankAccounts', 'appName'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'admin_whatsapp_number' => ['required', 'string', 'max:25'],
            'bank_accounts' => ['required', 'string', 'max:1000'],
            'app_name' => ['required', 'string', 'max:100'],
        ]);

        Setting::set('admin_whatsapp_number', $request->admin_whatsapp_number);
        Setting::set('bank_accounts', $request->bank_accounts);
        Setting::set('app_name', $request->app_name);

        return back()->with('success', 'Pengaturan nomor WhatsApp dan rekening berhasil disimpan!');
    }

    public function downloadBackup()
    {
        $dbPath = database_path('database.sqlite');
        if (!file_exists($dbPath)) {
            return back()->with('error', 'File database tidak ditemukan.');
        }

        $filename = 'vouchernet-backup-' . date('Y-m-d_His') . '.sqlite';
        return response()->download($dbPath, $filename, [
            'Content-Type' => 'application/x-sqlite3',
        ]);
    }
}
