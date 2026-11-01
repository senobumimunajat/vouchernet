<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $query = User::query()->where('username', '!=', 'pelanggan_langsung');

        // Admin can only see and manage tenants
        if ($currentUser->isAdmin()) {
            $query->where('role', 'tenant');
        } else {
            // Super Admin can filter by role
            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('shop_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->withCount(['orders', 'deposits'])->orderBy('role')->orderBy('name')->paginate(20)->withQueryString();

        return view('users.index', compact('users', 'currentUser'));
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();

        // Allowed roles to create
        $allowedRoles = $currentUser->isSuperAdmin() ? ['admin', 'tenant'] : ['tenant'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:100', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:25'],
            'role' => ['required', Rule::in($allowedRoles)],
            'shop_name' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
        ], [
            'username.unique' => 'Username ini sudah terdaftar.',
            'username.alpha_dash' => 'Username hanya boleh huruf, angka, tanda strip, atau garis bawah.',
        ]);

        $defaultPassword = 'password';

        $user = User::create([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'shop_name' => $validated['role'] === 'tenant' ? ($validated['shop_name'] ?? null) : null,
            'address' => $validated['address'] ?? null,
            'password' => Hash::make($defaultPassword),
            'is_active' => true,
        ]);

        $roleLabel = $validated['role'] === 'tenant' ? 'Mitra Warung' : 'Admin Operator';
        return back()->with('success', "Akun {$roleLabel} ({$user->name}) berhasil didaftarkan! Username: {$user->username} | Password awal: {$defaultPassword}");
    }

    public function update(Request $request, User $user)
    {
        $currentUser = Auth::user();

        // Admin cannot edit admin or super admin
        if ($currentUser->isAdmin() && $user->role !== 'tenant') {
            abort(403, 'Anda tidak memiliki hak untuk mengubah akun ini.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:25'],
            'shop_name' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        if ($user->role !== 'tenant') {
            $validated['shop_name'] = null;
        }

        $user->update($validated);

        return back()->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $currentUser = Auth::user();

        // Super Admin can reset anyone. Admin can only reset warung (tenant).
        if ($currentUser->isAdmin() && $user->role !== 'tenant') {
            abort(403, 'Admin hanya berhak me-reset password untuk akun Mitra Warung.');
        }

        if (! $currentUser->isSuperAdmin() && ! $currentUser->isAdmin()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mereset password.');
        }

        $request->validate([
            'new_password' => ['required', 'string', Password::min(6)],
            'new_password_confirmation' => ['nullable', 'string', 'same:new_password'],
        ], [
            'new_password.required' => 'Wajib memasukkan password baru.',
            'new_password.min' => 'Password baru minimal 6 karakter.',
            'new_password_confirmation.same' => 'Konfirmasi password baru (retype) tidak cocok.',
        ]);

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', "Password untuk akun {$user->name} ({$user->username}) berhasil di-reset!");
    }

    /**
     * Hapus akun pengguna.
     * Aturan:
     * - Super Admin dapat menghapus Admin dan Mitra (Tenant).
     * - Admin HANYA dapat menghapus Mitra (Tenant).
     * - Akun Super Admin tidak boleh dihapus.
     * - Pengguna tidak dapat menghapus akunnya sendiri yang sedang aktif.
     */
    public function destroy(User $user)
    {
        $currentUser = Auth::user();

        // 1. Akun yang sedang login tidak boleh menghapus dirinya sendiri
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');
        }

        // 2. Akun super admin tidak boleh dihapus sama sekali
        if ($user->isSuperAdmin()) {
            abort(403, 'Akun Super Admin tidak dapat dihapus.');
        }

        // 3. Akun sistem khusus tidak boleh dihapus
        if ($user->username === 'pelanggan_langsung') {
            abort(403, 'Akun sistem tidak dapat dihapus.');
        }

        // 4. Hak Akses berdasarkan peran:
        // - Super Admin: bisa menghapus 'admin' dan 'tenant'
        // - Admin: HANYA bisa menghapus 'tenant'
        if ($currentUser->isAdmin()) {
            if ($user->role !== 'tenant') {
                abort(403, 'Admin hanya memiliki izin untuk menghapus akun Mitra Warung.');
            }
        } elseif (! $currentUser->isSuperAdmin()) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus pengguna.');
        }

        // 5. Validasi Transaksi untuk Mitra (Tenant) - OPSI 1:
        if ($user->role === 'tenant') {
            $targetLabel = $user->shop_name ?: $user->name;

            // A. Cek apakah mitra masih memiliki tagihan tempo aktif atau deposit yang belum selesai
            $hasUnpaidOrders = $user->orders()->where('status', 'menunggu_pembayaran')->exists();
            $hasActiveDeposits = $user->deposits()->whereIn('status', ['tempo', 'menunggu_konfirmasi'])->exists();

            if ($hasUnpaidOrders || $hasActiveDeposits) {
                return back()->with('error', "Mitra '{$targetLabel}' tidak dapat dihapus karena masih memiliki tagihan tempo aktif yang belum lunas atau deposit menunggu konfirmasi. Selesaikan transaksi terlebih dahulu.");
            }

            // B. Opsi 1: Cek apakah mitra sudah pernah memiliki riwayat transaksi (lunas/selesai)
            $hasOrderHistory = $user->orders()->exists();
            $hasDepositHistory = $user->deposits()->exists();

            if ($hasOrderHistory || $hasDepositHistory) {
                return back()->with('error', "Mitra '{$targetLabel}' tidak dapat dihapus permanen karena sudah memiliki riwayat transaksi di buku kas. Untuk mengamankan pembukuan kas dan menghentikan akses mitra, silakan ubah status akun menjadi Nonaktif.");
            }
        }

        // 6. Jika Admin yang dihapus oleh Super Admin:
        // Alihkan catatan pengeluaran, pemasukan, mutasi inventaris, dan deposit ke Super Admin agar pembukuan tidak terhapus
        if ($user->role === 'admin') {
            \App\Models\Expense::where('created_by', $user->id)->update(['created_by' => $currentUser->id]);
            \App\Models\Income::where('created_by', $user->id)->update(['created_by' => $currentUser->id]);
            \App\Models\InventoryMovement::where('created_by', $user->id)->update(['created_by' => $currentUser->id]);
            \App\Models\MerchantDeposit::where('created_by', $user->id)->update(['created_by' => $currentUser->id]);
        }

        $userName = $user->shop_name ?: $user->name;
        $roleName = $user->role === 'tenant' ? 'Mitra Warung' : 'Admin Operator';

        $user->delete();

        return back()->with('success', "Akun {$roleName} '{$userName}' ({$user->username}) berhasil dihapus permanen.");
    }

    /**
     * Ubah status aktif / nonaktif akun pengguna.
     */
    public function toggleStatus(User $user)
    {
        $currentUser = Auth::user();

        // 1. Dilarang mengubah status akun sendiri
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Anda tidak dapat mengubah status akun Anda sendiri.');
        }

        // 2. Dilarang mengubah status akun super admin
        if ($user->isSuperAdmin()) {
            abort(403, 'Status akun Super Admin tidak dapat diubah.');
        }

        // 3. Admin hanya memiliki izin untuk mengubah status Mitra Warung
        if ($currentUser->isAdmin() && $user->role !== 'tenant') {
            abort(403, 'Admin hanya memiliki hak untuk mengubah status akun Mitra Warung.');
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        $statusLabel = $user->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';
        $targetLabel = $user->shop_name ?: $user->name;

        return back()->with('success', "Akun '{$targetLabel}' ({$user->username}) berhasil {$statusLabel}.");
    }
}
