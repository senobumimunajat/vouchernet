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

        $users = $query->orderBy('role')->orderBy('name')->paginate(20)->withQueryString();

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
}
