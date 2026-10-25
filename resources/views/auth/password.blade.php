@extends('layouts.app')

@section('title', 'Ganti Password')

@section('content')
<div class="max-w-xl mx-auto py-2">
    <div class="bg-white p-6 sm:p-8 rounded-[28px] border border-slate-200/70 shadow-[0_8px_30px_rgb(0,0,0,0.04)] space-y-6">
        
        <!-- Header -->
        <div class="flex items-center gap-3.5 pb-5 border-b border-slate-100">
            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">Keamanan & Ganti Password</h2>
                <p class="text-xs text-slate-500 mt-0.5">Ubah kata sandi akun Anda secara mandiri untuk menjaga keamanan akses.</p>
            </div>
        </div>

        <form action="{{ route('password.update') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="current_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Password Saat Ini</label>
                <input type="password" id="current_password" name="current_password" required
                    class="block w-full px-4 py-3 bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                    placeholder="Masukkan password lama Anda">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Password Baru</label>
                <input type="password" id="password" name="password" required minlength="6"
                    class="block w-full px-4 py-3 bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                    placeholder="Minimal 6 karakter">
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Konfirmasi Password Baru</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6"
                    class="block w-full px-4 py-3 bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                    placeholder="Ketik ulang password baru">
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition-colors">
                    Kembali
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-full bg-blue-600 text-white hover:bg-blue-700 text-xs font-bold shadow-md shadow-blue-500/20 active:scale-[0.98] transition-all">
                    Simpan Password Baru
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
