<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $adminWhatsapp = Setting::get('admin_whatsapp_number', '6281234567890');
        $bankAccounts = Setting::get('bank_accounts', "BCA: 123456789 a.n RT RW Net\nBRI: 987654321 a.n RT RW Net\nDANA: 081234567890");
        $appName = Setting::get('app_name', 'VoucherNet RT/RW');
        $appSubtitle = Setting::get('app_subtitle', 'Sistem Voucher & Buku Kas');
        $ownerName = Setting::get('owner_name', '');
        $ownerTitle = Setting::get('owner_title', 'Owner / Pengelola');
        $financeOfficerName = Setting::get('finance_officer_name', '');
        $logoPath = Setting::get('app_logo_path');
        $faviconPath = Setting::get('app_favicon_path');

        $appLogoUrl = $logoPath && Storage::disk('public')->exists($logoPath)
            ? asset('storage/' . $logoPath)
            : null;

        $appFaviconUrl = $faviconPath && Storage::disk('public')->exists($faviconPath)
            ? asset('storage/' . $faviconPath)
            : null;

        return view('settings.index', compact(
            'adminWhatsapp',
            'bankAccounts',
            'appName',
            'appSubtitle',
            'ownerName',
            'ownerTitle',
            'financeOfficerName',
            'appLogoUrl',
            'appFaviconUrl'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name' => ['required', 'string', 'max:100'],
            'app_subtitle' => ['nullable', 'string', 'max:150'],
            'owner_name' => ['nullable', 'string', 'max:100'],
            'owner_title' => ['nullable', 'string', 'max:100'],
            'finance_officer_name' => ['nullable', 'string', 'max:100'],
            'admin_whatsapp_number' => ['required', 'string', 'max:25'],
            'bank_accounts' => ['required', 'string', 'max:1000'],
            'app_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'app_favicon' => ['nullable', 'file', 'mimes:ico,png,svg', 'max:1024'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_favicon' => ['nullable', 'boolean'],
        ], [
            'app_name.required' => 'Nama Brand / Aplikasi wajib diisi.',
            'app_logo.image' => 'File logo harus berupa gambar (PNG, JPG, SVG, atau WebP).',
            'app_logo.max' => 'Ukuran file logo maksimal 2 MB.',
            'app_favicon.max' => 'Ukuran file favicon maksimal 1 MB.',
        ]);

        Setting::set('app_name', trim($request->input('app_name')));
        Setting::set('app_subtitle', trim($request->input('app_subtitle', 'Sistem Voucher & Buku Kas')));
        Setting::set('owner_name', trim($request->input('owner_name', '')));
        Setting::set('owner_title', trim($request->input('owner_title', 'Owner / Pengelola')));
        Setting::set('finance_officer_name', trim($request->input('finance_officer_name', '')));
        Setting::set('admin_whatsapp_number', trim($request->input('admin_whatsapp_number')));
        Setting::set('bank_accounts', trim($request->input('bank_accounts')));

        // If owner_name is supplied and current user is Super Admin with placeholder name, sync user name
        if ($request->filled('owner_name') && auth()->user()->isSuperAdmin()) {
            if (auth()->user()->name === 'Owner RT/RW Net (Super Admin)') {
                auth()->user()->update(['name' => trim($request->input('owner_name'))]);
            }
        }

        // Handle Logo Upload / Removal
        if ($request->hasFile('app_logo')) {
            $oldLogo = Setting::get('app_logo_path');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            $logoPath = $request->file('app_logo')->store('branding', 'public');
            Setting::set('app_logo_path', $logoPath);
        } elseif ($request->boolean('remove_logo')) {
            $oldLogo = Setting::get('app_logo_path');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            Setting::set('app_logo_path', null);
        }

        // Handle Favicon Upload / Removal
        if ($request->hasFile('app_favicon')) {
            $oldFavicon = Setting::get('app_favicon_path');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }
            $faviconPath = $request->file('app_favicon')->store('branding', 'public');
            Setting::set('app_favicon_path', $faviconPath);
        } elseif ($request->boolean('remove_favicon')) {
            $oldFavicon = Setting::get('app_favicon_path');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }
            Setting::set('app_favicon_path', null);
        }

        return back()->with('success', 'Identitas brand, logo, dan pengaturan sistem berhasil diperbarui!');
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
