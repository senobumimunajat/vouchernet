@extends('layouts.app')

@section('title', 'Data Warung & Pengguna')

@section('content')
<div class="space-y-6">

    <!-- Header & Toggle Tambah -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold tracking-tight text-slate-900">Data Mitra Warung & Pengguna</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                {{ $currentUser->isSuperAdmin() ? 'Kelola akun Super Admin, Admin Operator, dan Mitra Warung (Reseller).' : 'Kelola akun mitra warung (reseller).' }}
            </p>
        </div>
        <button type="button" onclick="toggleAddUserForm()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 active:scale-[0.98] transition-all">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/></svg>
            Tambah {{ $currentUser->isSuperAdmin() ? 'Pengguna / Warung' : 'Warung Baru' }}
        </button>
    </div>

    <!-- Form Tambah User / Warung (Collapsible) -->
    <div id="addUserBox" class="{{ $errors->any() ? '' : 'hidden' }} bg-white p-6 sm:p-8 rounded-[28px] border border-slate-200/70 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                <h2 class="text-sm font-extrabold text-slate-900">Pendaftaran Akun Baru</h2>
            </div>
            <button type="button" onclick="toggleAddUserForm()" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-100 text-xs transition-colors">✕</button>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @csrf

            @if($currentUser->isSuperAdmin())
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Peran Akun (Role)</label>
                <select name="role" id="roleSelect" onchange="toggleShopField(this.value)" required class="w-full px-4 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                    <option value="tenant" {{ old('role') === 'tenant' ? 'selected' : '' }}>Mitra Warung / Reseller</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin Operator</option>
                </select>
            </div>
            @else
            <input type="hidden" name="role" value="tenant">
            @endif

            <div id="shopNameBox">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Warung / Toko</label>
                <input type="text" name="shop_name" value="{{ old('shop_name') }}" placeholder="Contoh: Warung Bu Siti / Kios Pulsa"
                    class="w-full px-4 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Pemilik / Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Siti Aminah"
                    class="w-full px-4 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nomor WhatsApp</label>
                <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="Contoh: 081234567890"
                    class="w-full px-4 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all font-mono">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Username Login</label>
                <input type="text" name="username" value="{{ old('username') }}" required placeholder="Contoh: warung_siti (huruf kecil)"
                    class="w-full px-4 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all font-mono">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Alamat / Lokasi Warung (Opsional)</label>
                <input type="text" name="address" value="{{ old('address') }}" placeholder="Contoh: RT 04 RW 02 Depan Lapangan"
                    class="w-full px-4 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div class="sm:col-span-2 lg:col-span-3 flex items-center gap-3.5 p-3.5 rounded-2xl bg-blue-50/80 border border-blue-200/70 text-blue-900 text-xs shadow-2xs">
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center text-base shrink-0 shadow-xs">
                    🔑
                </div>
                <div class="flex-1">
                    <div class="font-bold text-slate-900 flex items-center gap-2">
                        <span>Password Awal Otomatis:</span>
                        <code class="px-2.5 py-0.5 bg-white text-blue-700 rounded-md font-mono font-extrabold text-xs border border-blue-200 shadow-2xs">password</code>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-0.5">Mitra warung dapat langsung login dengan password default di atas dan menggantinya secara mandiri di menu profil setelah masuk.</p>
                </div>
            </div>

            <div class="sm:col-span-2 lg:col-span-3 flex justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="toggleAddUserForm()" class="px-5 py-2.5 rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition-colors">Batal</button>
                <button type="submit" class="px-6 py-2.5 rounded-full bg-blue-600 text-white hover:bg-blue-700 text-xs font-bold shadow-md shadow-blue-500/20 active:scale-[0.98] transition-all">
                    Daftarkan Akun
                </button>
            </div>
        </form>
    </div>

    <!-- Table Users (Desktop) & Adaptive Cards (Mobile) -->
    <div class="bg-white rounded-[28px] border border-slate-200/70 overflow-hidden shadow-[0_4px_20px_rgb(0,0,0,0.03)]">
        
        <!-- Desktop Table (md:block) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/70 text-slate-600 font-bold text-[11px] uppercase tracking-wider">
                        <th class="py-3.5 px-5">Nama / Warung</th>
                        <th class="py-3.5 px-5">Username & HP</th>
                        <th class="py-3.5 px-5">Peran (Role)</th>
                        <th class="py-3.5 px-5">Alamat</th>
                        <th class="py-3.5 px-5">Status</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($users as $u)
                    @php
                        $canEdit = $currentUser->isSuperAdmin() || ($currentUser->isAdmin() && $u->role === 'tenant');
                        $canReset = $currentUser->isSuperAdmin() || ($currentUser->isAdmin() && $u->role === 'tenant');
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="py-3.5 px-5">
                            <span class="font-bold text-slate-900 block">{{ $u->shop_name ?: $u->name }}</span>
                            @if($u->shop_name)
                            <span class="text-[11px] text-slate-400 block mt-0.5">Pemilik: {{ $u->name }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-5">
                            <span class="font-mono text-slate-900 font-bold block">{{ $u->username }}</span>
                            <span class="text-[11px] text-slate-500 block mt-0.5">{{ $u->phone }}</span>
                        </td>
                        <td class="py-3.5 px-5">
                            @php
                                $rBadge = match($u->role) {
                                    'super_admin' => ['label' => 'Super Admin', 'class' => 'bg-purple-50 text-purple-700 border-purple-200/80'],
                                    'admin' => ['label' => 'Admin Operator', 'class' => 'bg-sky-50 text-sky-700 border-sky-200/80'],
                                    default => ['label' => 'Mitra Warung', 'class' => 'bg-blue-50 text-blue-700 border-blue-200/80']
                                };
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold border {{ $rBadge['class'] }}">
                                {{ $rBadge['label'] }}
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-slate-500 text-[11px] max-w-xs truncate font-medium">{{ $u->address ?: '-' }}</td>
                        <td class="py-3.5 px-5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $u->is_active ? 'bg-blue-50 text-blue-700 border border-blue-200/60' : 'bg-slate-100 text-slate-500' }}">
                                {{ $u->is_active ? '● Aktif' : '○ Nonaktif' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-right space-x-1.5 whitespace-nowrap">
                            @if($canEdit)
                            <button type="button" onclick="openEditUserModal({{ $u->toJson() }})"
                                class="px-3 py-1.5 rounded-full bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-[11px] inline-flex items-center gap-1 transition-colors">
                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Edit</span>
                            </button>
                            @endif
                            @if($canReset)
                            <button type="button" onclick="openResetPasswordModal({{ $u->id }}, '{{ addslashes($u->shop_name ?: $u->name) }}', '{{ addslashes($u->username) }}')"
                                class="px-3 py-1.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] inline-flex items-center gap-1 transition-colors">
                                <span>🔑</span>
                                <span>Reset Password</span>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400 font-medium">Belum ada data pengguna.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Adaptive Cards (md:hidden - Tidak Terpotong di Layar HP) -->
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($users as $u)
            @php
                $rBadge = match($u->role) {
                    'super_admin' => ['label' => 'Super Admin', 'class' => 'bg-purple-50 text-purple-700 border-purple-200/80'],
                    'admin' => ['label' => 'Admin Operator', 'class' => 'bg-sky-50 text-sky-700 border-sky-200/80'],
                    default => ['label' => 'Mitra Warung', 'class' => 'bg-blue-50 text-blue-700 border-blue-200/80']
                };
                $canReset = $currentUser->isSuperAdmin() || ($currentUser->isAdmin() && $u->role === 'tenant');
            @endphp
            <div class="p-4 space-y-3 hover:bg-slate-50/50 transition-colors">
                <!-- Header Card: Nama Toko & Badge -->
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="font-extrabold text-slate-900 text-sm block">{{ $u->shop_name ?: $u->name }}</span>
                        @if($u->shop_name)
                        <span class="text-xs text-slate-500 font-medium">Pemilik: {{ $u->name }}</span>
                        @endif
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $rBadge['class'] }}">
                            {{ $rBadge['label'] }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $u->is_active ? 'bg-blue-50 text-blue-700 border border-blue-200/60' : 'bg-slate-100 text-slate-500' }}">
                            {{ $u->is_active ? '● Aktif' : '○ Nonaktif' }}
                        </span>
                    </div>
                </div>

                <!-- Info Box Rincian -->
                <div class="p-3 bg-slate-50/80 rounded-2xl space-y-2 text-xs border border-slate-100">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500">Username Login:</span>
                        <span class="font-mono font-bold text-slate-900 bg-white px-2 py-0.5 rounded-lg border border-slate-200/70">{{ $u->username }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500">No. WhatsApp:</span>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $u->phone) }}" target="_blank" class="font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                            <span>💬</span>
                            <span>{{ $u->phone }}</span>
                        </a>
                    </div>
                    @if($u->address)
                    <div class="flex justify-between items-start gap-2 pt-1 border-t border-slate-200/60 text-[11px]">
                        <span class="text-slate-500 shrink-0">Alamat:</span>
                        <span class="text-slate-700 font-medium text-right">{{ $u->address }}</span>
                    </div>
                    @endif
                </div>

                <!-- Action Buttons (Edit Data & Reset Password) -->
                <div class="pt-1 flex items-center gap-2">
                    @if($canEdit)
                    <button type="button" onclick="openEditUserModal({{ $u->toJson() }})"
                        class="flex-1 py-2 px-3 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center gap-1.5 active:scale-95 transition-all">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>Edit Data</span>
                    </button>
                    @endif
                    @if($canReset)
                    <button type="button" onclick="openResetPasswordModal({{ $u->id }}, '{{ addslashes($u->shop_name ?: $u->name) }}', '{{ addslashes($u->username) }}')"
                        class="{{ $canEdit ? 'flex-1' : 'w-full' }} py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs flex items-center justify-center gap-1.5 active:scale-95 transition-all">
                        <span>🔑</span>
                        <span>Reset Password</span>
                    </button>
                    @endif
                </div>
            </div>
            @empty
            <div class="p-6 text-center text-slate-400 text-xs font-medium">Belum ada data pengguna.</div>
            @endforelse
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
    </div>

</div>

<!-- Modal Edit Pengguna (Super Admin kelola nama admin/kasir & mitra warung) -->
<div id="editUserModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm transition-all">
    <div class="bg-white rounded-[28px] max-w-lg w-full p-6 sm:p-7 shadow-floating border border-slate-200/80 space-y-4 animate-in fade-in zoom-in duration-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
            <div class="flex items-center gap-2.5 text-slate-900">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900" id="editModalTitle">Edit Data Pengguna</h3>
                    <p class="text-[11px] text-slate-500">Perbarui identitas profil dan akses akun</p>
                </div>
            </div>
            <button type="button" onclick="closeEditUserModal()" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-100 text-xs transition-colors">✕</button>
        </div>

        <form id="editUserForm" action="" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Tip Khusus untuk Akun Admin / Kasir -->
            <div id="editAdminTip" class="hidden p-3 bg-sky-50 text-sky-900 border border-sky-200 rounded-2xl text-xs flex items-start gap-2.5">
                <span class="text-base shrink-0 mt-0.5">ℹ️</span>
                <p class="leading-relaxed">
                    <strong>Nama Admin / Kasir</strong> ini akan otomatis tercetak di <strong>Faktur & Kwitansi Pelunasan</strong> sebagai bukti pertanggungjawaban kasir yang melayani transaksi.
                </p>
            </div>

            <div id="editShopNameBox">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Warung / Toko</label>
                <input type="text" name="shop_name" id="editShopName" placeholder="Contoh: Warung Bu Siti"
                    class="w-full px-4 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" id="editNameLabel">Nama Lengkap / Nama Kasir</label>
                <input type="text" name="name" id="editName" required placeholder="Contoh: Budi Santoso (Admin Kasir Pagi)"
                    class="w-full px-4 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Username Login</label>
                    <input type="text" name="username" id="editUsername" required
                        class="w-full px-4 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">No. WhatsApp</label>
                    <input type="text" name="phone" id="editPhone" required placeholder="081234567890"
                        class="w-full px-4 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all font-mono">
                </div>
            </div>

            <div id="editAddressBox">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alamat (Opsional)</label>
                <input type="text" name="address" id="editAddress" placeholder="Lokasi mitra warung"
                    class="w-full px-4 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Status Akun</label>
                <select name="is_active" id="editIsActive" class="w-full px-4 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                    <option value="1">● Aktif (Dapat Login & Beroperasi)</option>
                    <option value="0">○ Nonaktif (Akses Masuk Dinonaktifkan)</option>
                </select>
            </div>

            <div class="pt-3 flex justify-end gap-2.5 border-t border-slate-100">
                <button type="button" onclick="closeEditUserModal()" class="px-4 py-2.5 rounded-full text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">Batal</button>
                <button type="submit" class="px-6 py-2.5 rounded-full bg-blue-600 text-white hover:bg-blue-700 font-bold text-xs shadow-md shadow-blue-500/20 active:scale-[0.98] transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Reset Password (Tersedia untuk Super Admin & Admin) -->
@if($currentUser->isSuperAdmin() || $currentUser->isAdmin())
<div id="resetPasswordModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm transition-all">
    <div class="bg-white rounded-[28px] max-w-sm w-full p-6 shadow-floating border border-slate-200/80 space-y-4 animate-in fade-in zoom-in duration-200">
        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
            <div class="flex items-center gap-2 text-slate-900">
                <span class="w-7 h-7 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs">🔑</span>
                <h3 class="text-sm font-extrabold text-slate-900">Reset Password Warung</h3>
            </div>
            <button type="button" onclick="closeResetPasswordModal()" class="p-1 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-100 text-xs transition-colors">✕</button>
        </div>

        <form id="resetPasswordForm" action="" method="POST" class="space-y-4">
            @csrf
            <p class="text-xs text-slate-600 leading-relaxed">
                Atur kata sandi baru untuk akun <strong id="resetTargetName" class="text-slate-900"></strong> (<span id="resetTargetUsername" class="font-mono font-bold text-blue-700"></span>):
            </p>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Password Baru</label>
                <div class="relative">
                    <input type="password" name="new_password" id="resetNewPassword" required minlength="6"
                        class="w-full pl-4 pr-10 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                        placeholder="Minimal 6 karakter" oninput="validateResetPasswordMatch()">
                    <button type="button" onclick="togglePasswordVisibility('resetNewPassword', this)" tabindex="-1" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none p-1" title="Lihat password">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Ulangi Password Baru</label>
                    <span id="resetMatchBadge" class="hidden text-[10px] font-bold px-2 py-0.5 rounded-full"></span>
                </div>
                <div class="relative">
                    <input type="password" name="new_password_confirmation" id="resetNewPasswordConfirmation" required minlength="6"
                        class="w-full pl-4 pr-10 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                        placeholder="Ketik ulang password baru" oninput="validateResetPasswordMatch()">
                    <button type="button" onclick="togglePasswordVisibility('resetNewPasswordConfirmation', this)" tabindex="-1" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none p-1" title="Lihat password">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                <p id="resetMatchHint" class="text-[11px] text-slate-400 mt-1 hidden"></p>
            </div>

            <div class="pt-2 flex justify-end gap-2.5">
                <button type="button" onclick="closeResetPasswordModal()" class="px-4 py-2 rounded-full text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-full bg-blue-600 text-white hover:bg-blue-700 font-bold text-xs shadow-md shadow-blue-500/20 active:scale-[0.98] transition-all">
                    Simpan Password Baru
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    function toggleAddUserForm() {
        const box = document.getElementById('addUserBox');
        box.classList.toggle('hidden');
    }
    function toggleShopField(role) {
        const box = document.getElementById('shopNameBox');
        if (role === 'admin') box.classList.add('hidden');
        else box.classList.remove('hidden');
    }

    function openEditUserModal(user) {
        document.getElementById('editUserForm').action = "/users/" + user.id;
        document.getElementById('editName').value = user.name || '';
        document.getElementById('editUsername').value = user.username || '';
        document.getElementById('editPhone').value = user.phone || '';
        document.getElementById('editIsActive').value = user.is_active ? '1' : '0';

        const isTenant = user.role === 'tenant';
        const isAdmin = user.role === 'admin';
        
        const editShopNameBox = document.getElementById('editShopNameBox');
        const editShopName = document.getElementById('editShopName');
        const editAddressBox = document.getElementById('editAddressBox');
        const editAddress = document.getElementById('editAddress');
        const editAdminTip = document.getElementById('editAdminTip');
        const editModalTitle = document.getElementById('editModalTitle');
        const editNameLabel = document.getElementById('editNameLabel');

        if (isTenant) {
            editModalTitle.innerText = 'Edit Akun Mitra Warung';
            editNameLabel.innerText = 'Nama Pemilik Warung';
            editShopNameBox.classList.remove('hidden');
            editAddressBox.classList.remove('hidden');
            editShopName.value = user.shop_name || '';
            editAddress.value = user.address || '';
            editAdminTip.classList.add('hidden');
        } else {
            editModalTitle.innerText = isAdmin ? 'Edit Akun Admin / Kasir' : 'Edit Akun Super Admin';
            editNameLabel.innerText = 'Nama Admin / Kasir di Kwitansi';
            editShopNameBox.classList.add('hidden');
            editAddressBox.classList.add('hidden');
            editShopName.value = '';
            editAddress.value = '';
            if (isAdmin) {
                editAdminTip.classList.remove('hidden');
            } else {
                editAdminTip.classList.add('hidden');
            }
        }

        document.getElementById('editUserModal').classList.remove('hidden');
    }

    function closeEditUserModal() {
        document.getElementById('editUserModal').classList.add('hidden');
    }

    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        
        if (isPassword) {
            btn.innerHTML = `<svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>`;
            btn.title = "Sembunyikan password";
        } else {
            btn.innerHTML = `<svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>`;
            btn.title = "Lihat password";
        }
    }

    function validateResetPasswordMatch() {
        const pwd = document.getElementById('resetNewPassword').value;
        const confirm = document.getElementById('resetNewPasswordConfirmation').value;
        const badge = document.getElementById('resetMatchBadge');
        const hint = document.getElementById('resetMatchHint');
        const inputConfirm = document.getElementById('resetNewPasswordConfirmation');

        if (!confirm) {
            badge.classList.add('hidden');
            hint.classList.add('hidden');
            inputConfirm.classList.remove('border-rose-400', 'focus:ring-rose-400', 'border-emerald-400', 'focus:ring-emerald-400');
            inputConfirm.setCustomValidity('');
            return;
        }

        if (pwd === confirm) {
            badge.classList.remove('hidden', 'bg-rose-100', 'text-rose-700');
            badge.classList.add('bg-emerald-100', 'text-emerald-700');
            badge.textContent = '✓ Cocok';
            hint.classList.add('hidden');
            inputConfirm.classList.remove('border-rose-400', 'focus:ring-rose-400');
            inputConfirm.classList.add('border-emerald-400', 'focus:ring-emerald-400');
            inputConfirm.setCustomValidity('');
        } else {
            badge.classList.remove('hidden', 'bg-emerald-100', 'text-emerald-700');
            badge.classList.add('bg-rose-100', 'text-rose-700');
            badge.textContent = '✕ Belum Cocok';
            hint.classList.remove('hidden');
            hint.className = 'text-[11px] text-rose-500 mt-1 font-medium';
            hint.textContent = 'Konfirmasi password baru tidak cocok.';
            inputConfirm.classList.remove('border-emerald-400', 'focus:ring-emerald-400');
            inputConfirm.classList.add('border-rose-400', 'focus:ring-rose-400');
            inputConfirm.setCustomValidity('Konfirmasi password baru tidak cocok.');
        }
    }

    function openResetPasswordModal(id, name, username) {
        document.getElementById('resetTargetName').innerText = name;
        document.getElementById('resetTargetUsername').innerText = username;
        document.getElementById('resetPasswordForm').action = "/users/" + id + "/reset-password";
        const input1 = document.getElementById('resetNewPassword');
        const input2 = document.getElementById('resetNewPasswordConfirmation');
        if (input1) input1.value = '';
        if (input2) {
            input2.value = '';
            input2.setCustomValidity('');
            input2.classList.remove('border-rose-400', 'focus:ring-rose-400', 'border-emerald-400', 'focus:ring-emerald-400');
        }
        const badge = document.getElementById('resetMatchBadge');
        if (badge) badge.classList.add('hidden');
        const hint = document.getElementById('resetMatchHint');
        if (hint) hint.classList.add('hidden');
        document.getElementById('resetPasswordModal').classList.remove('hidden');
    }
    function closeResetPasswordModal() {
        document.getElementById('resetPasswordModal').classList.add('hidden');
    }
</script>
@endsection
