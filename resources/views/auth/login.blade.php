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
                        placeholder="Masukkan username Anda">
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Password</label>
                    <div class="relative mt-1.5">
                        <input type="password" id="password" name="password" required
                            class="block w-full pl-4 pr-11 py-3 bg-slate-50/80 border border-slate-200 rounded-2xl text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                            placeholder="Masukkan password Anda">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none transition-colors" title="Lihat / Sembunyikan Password">
                            <!-- Eye Open -->
                            <svg id="eyeOpenIcon" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <!-- Eye Closed -->
                            <svg id="eyeClosedIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
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

<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const eyeOpen = document.getElementById('eyeOpenIcon');
        const eyeClosed = document.getElementById('eyeClosedIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeOpen.classList.add('hidden');
            eyeClosed.classList.remove('hidden');
        } else {
            passwordInput.type = 'password';
            eyeOpen.classList.remove('hidden');
            eyeClosed.classList.add('hidden');
        }
    }
</script>
@endsection
