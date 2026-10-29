@extends('layouts.app')

@section('title', 'Masuk ke Sistem')

@section('content')
<div class="min-h-[80vh] flex flex-col justify-center items-center py-6 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">
        <!-- Brand Header Identity (Clean Royal Blue Logo Mark) -->
        <div class="text-center mb-8">
            @if(!empty($globalAppLogo))
            <div class="inline-flex items-center justify-center mb-4 group hover:scale-105 transition-transform duration-200">
                <img src="{{ $globalAppLogo }}" alt="{{ $globalAppName }}" class="h-16 w-auto max-w-[220px] object-contain drop-shadow-md rounded-2xl">
            </div>
            @else
            <div class="inline-flex w-16 h-16 rounded-[24px] bg-blue-600 text-white items-center justify-center shadow-lg shadow-blue-500/20 mb-4 group hover:scale-105 transition-transform duration-200">
                <svg class="w-9 h-9 text-white" viewBox="0 0 256 256" fill="currentColor">
                    <rect x="66" y="44" width="34" height="168" rx="17" transform="rotate(-15 83 128)"/>
                    <circle cx="138" cy="188" r="15"/>
                    <rect x="146" y="112" width="28" height="54" rx="14" transform="rotate(15 160 139)"/>
                    <rect x="172" y="46" width="28" height="96" rx="14" transform="rotate(15 186 94)"/>
                </svg>
            </div>
            @endif
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                {{ $globalAppName }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">{{ $globalAppSubtitle ?? 'Sistem Manajemen Voucher & Buku Kas RT/RW Net' }}</p>
        </div>

        <!-- Login Form Card -->
        <div class="bg-white py-8 px-6 sm:px-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-[32px] border border-slate-200/80">
            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Username</label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
                        class="mt-1.5 block w-full px-4 py-3 bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                        placeholder="superadmin / admin / warung_siti">
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Password</label>
                    <input type="password" id="password" name="password" required
                        class="mt-1.5 block w-full px-4 py-3 bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center text-xs font-medium text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500">
                        <span class="ml-2 font-semibold">Ingat sesi saya</span>
                    </label>
                </div>

                <button type="submit" class="w-full touch-target flex justify-center items-center py-3.5 px-4 border border-transparent rounded-full text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-md shadow-blue-500/20 transition-all active:scale-95">
                    Masuk ke Akun
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
