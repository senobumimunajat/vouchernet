<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f8fafd] overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'VoucherNet') }} - @yield('title', 'Sistem Rekap Voucher')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <!-- Modern Geometric Typography: Plus Jakarta Sans (Friendly, Clean, Perfectly Aligned) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb', // Clean Royal Blue
                            700: '#1d4ed8',
                        },
                    },
                    boxShadow: {
                        'soft': '0 4px 20px -2px rgba(0, 0, 0, 0.04), 0 2px 6px -1px rgba(0, 0, 0, 0.02)',
                        'floating': '0 12px 36px -4px rgba(15, 23, 42, 0.08), 0 4px 12px -2px rgba(15, 23, 42, 0.03)',
                        'card': '0 2px 12px 0 rgba(0,0,0,0.03)',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', -apple-system, sans-serif; letter-spacing: -0.01em; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .touch-target { min-height: 44px; min-width: 44px; }
        /* Smooth Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="h-full flex flex-col text-slate-800 antialiased selection:bg-blue-100 selection:text-blue-900 bg-[#f8fafd] overflow-x-hidden">

    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200/80 shadow-xs transition-all">
        <div class="max-w-7xl mx-auto px-2.5 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 gap-1.5 sm:gap-3 w-full">
                <!-- Custom Bespoke Brand Identity -->
                <div class="flex items-center min-w-0 shrink">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 group min-w-0">
                        <!-- Bespoke Geometric Mark: V-Signal (Voucher Foundation + 3 WiFi Bars) -->
                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl sm:rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-600/20 group-hover:scale-105 transition-transform duration-200 shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" viewBox="0 0 256 256" fill="currentColor">
                                <rect x="66" y="44" width="34" height="168" rx="17" transform="rotate(-15 83 128)"/>
                                <circle cx="138" cy="188" r="15"/>
                                <rect x="146" y="112" width="28" height="54" rx="14" transform="rotate(15 160 139)"/>
                                <rect x="172" y="46" width="28" height="96" rx="14" transform="rotate(15 186 94)"/>
                            </svg>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <div class="flex items-center gap-1 sm:gap-1.5 leading-none">
                                <span class="text-xs sm:text-base font-extrabold text-slate-900 tracking-tight truncate">
                                    Voucher<span class="text-blue-600">Net</span>
                                </span>
                                <span class="px-1 sm:px-1.5 py-0.5 text-[8px] sm:text-[10px] font-bold bg-blue-50 text-blue-700 rounded-md border border-blue-200/60 hidden xs:inline-block">
                                    RT/RW
                                </span>
                            </div>
                            <span class="text-[10px] sm:text-[11px] font-medium text-slate-400 tracking-normal mt-0.5 hidden md:inline truncate">
                                Sistem Voucher & Buku Kas
                            </span>
                        </div>
                    </a>
                </div>

                <!-- User Profile & Action -->
                @auth
                <div class="flex items-center gap-1 sm:gap-2 shrink-0 ml-auto">
                    <!-- Mobile Menu Hamburger Button for Admin/Super Admin -->
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                    <button type="button" onclick="toggleAdminMobileNav()" class="md:hidden p-1.5 sm:p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition-colors shrink-0" title="Buka Menu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    @endif

                    <!-- User Identity Chip (Nama Petugas + Role) -->
                    @php
                        $u = auth()->user();
                        $roleConfig = match($u->role) {
                            'super_admin' => [
                                'label' => 'Super Admin',
                                'dotClass' => 'bg-purple-500',
                                'avatarBg' => 'bg-purple-100 text-purple-700',
                            ],
                            'admin' => [
                                'label' => 'Admin Operator',
                                'dotClass' => 'bg-sky-500',
                                'avatarBg' => 'bg-sky-100 text-sky-700',
                            ],
                            default => [
                                'label' => 'Mitra Warung',
                                'dotClass' => 'bg-blue-500',
                                'avatarBg' => 'bg-blue-100 text-blue-700',
                            ]
                        };
                        $initial = strtoupper(substr($u->name ?: 'U', 0, 1));
                        $primaryName = $u->role === 'tenant' && $u->shop_name ? $u->shop_name : $u->name;
                        $subLabel = $u->role === 'tenant' && $u->shop_name ? $u->name : $roleConfig['label'];
                    @endphp

                    <div class="flex items-center gap-1.5 sm:gap-2.5 px-1.5 sm:px-3 py-1 sm:py-1.5 rounded-xl sm:rounded-2xl bg-slate-50/90 border border-slate-200/80 shadow-xs max-w-[85px] xs:max-w-[120px] sm:max-w-[200px] shrink min-w-0" title="{{ $primaryName }} ({{ $subLabel }})">
                        <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg sm:rounded-xl flex items-center justify-center font-extrabold text-[11px] sm:text-xs {{ $roleConfig['avatarBg'] }} shrink-0">
                            {{ $initial }}
                        </div>
                        <div class="text-left flex flex-col justify-center min-w-0">
                            <div class="text-[11px] sm:text-xs font-extrabold text-slate-900 leading-tight truncate">
                                {{ $primaryName }}
                            </div>
                            <div class="text-[9px] sm:text-[10px] font-semibold text-slate-500 leading-tight items-center gap-1 mt-0.5 truncate hidden sm:flex">
                                <span class="w-1.5 h-1.5 rounded-full {{ $roleConfig['dotClass'] }} shrink-0"></span>
                                <span class="truncate">{{ $subLabel }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons (Ganti Password & Keluar) - Pinned & Always 100% Visible shrink-0 -->
                    <div class="flex items-center space-x-1 pl-1 sm:pl-2 border-l border-slate-200 shrink-0">
                        <a href="{{ route('password.edit') }}" title="Ganti Password" class="p-1.5 text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition-colors shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                            </svg>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="flex items-center shrink-0 m-0">
                            @csrf
                            <button type="submit" title="Keluar dari Sistem" class="flex items-center gap-1 px-2 py-1.5 sm:px-2.5 sm:py-1.5 rounded-xl font-bold text-xs bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200/80 transition-all shadow-xs active:scale-95 shrink-0">
                                <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                </svg>
                                <span class="text-[11px] sm:text-xs">Keluar</span>
                            </button>
                        </form>
                    </div>
                </div>
                @endauth
            </div>
        </div>

        <!-- Mobile Drawer Nav for Admin / Super Admin -->
        @auth
        @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
        <div id="adminMobileNav" class="hidden md:hidden border-t border-slate-100 bg-white/95 backdrop-blur-md px-4 py-3 space-y-1">
            <div class="p-2.5 bg-slate-50 border border-slate-200/80 rounded-2xl mb-2 flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center font-extrabold text-xs {{ $roleConfig['avatarBg'] }} shrink-0">
                    {{ $initial }}
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-extrabold text-slate-900 truncate">{{ $primaryName }}</div>
                    <div class="text-[10px] text-slate-500 font-semibold truncate flex items-center gap-1 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full {{ $roleConfig['dotClass'] }}"></span>
                        <span>{{ $subLabel }}</span>
                    </div>
                </div>
            </div>

            @if(auth()->user()->isSuperAdmin())
            <div class="px-3 pt-1 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Owner & Keuangan</div>
            <a href="{{ route('finance.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('finance.index') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/70' : 'text-slate-700 hover:bg-slate-100' }}">
                Buku Kas & Laba
            </a>
            <a href="{{ route('approvals.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('approvals.*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/70' : 'text-slate-700 hover:bg-slate-100' }}">
                <span>Approve Hapus</span>
                @php $mDelCount = \App\Models\Order::where('status', 'menunggu_approval_hapus')->count(); @endphp
                @if($mDelCount > 0)
                <span class="px-2 py-0.5 text-[10px] font-bold bg-rose-500 text-white rounded-full">{{ $mDelCount }}</span>
                @endif
            </a>
            <a href="{{ route('finance.expenses') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('finance.expenses*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/70' : 'text-slate-700 hover:bg-slate-100' }}">
                Beban Pengeluaran
            </a>
            <a href="{{ route('finance.incomes') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('finance.incomes*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/70' : 'text-slate-700 hover:bg-slate-100' }}">
                Pemasukan Lain
            </a>
            @endif

            <div class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Operasional</div>
            <a href="{{ route('packages.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('packages.*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/70' : 'text-slate-700 hover:bg-slate-100' }}">
                Stok & Paket Voucher
            </a>
            <a href="{{ route('admin.orders.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.orders.index') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/70' : 'text-slate-700 hover:bg-slate-100' }}">
                <span>Pesanan Masuk</span>
                @php $mPending = \App\Models\Order::where('status', 'menunggu_pembayaran')->count(); @endphp
                @if($mPending > 0)
                <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-500 text-white rounded-full">{{ $mPending }}</span>
                @endif
            </a>
            <a href="{{ route('admin.orders.create_offline') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.orders.create_offline') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/70' : 'text-slate-700 hover:bg-slate-100' }}">
                Input Order Offline
            </a>
            @if(!auth()->user()->isSuperAdmin())
            <a href="{{ route('finance.expenses') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('finance.expenses*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/70' : 'text-slate-700 hover:bg-slate-100' }}">
                Beban Pengeluaran
            </a>
            @endif
            <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('users.*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/70' : 'text-slate-700 hover:bg-slate-100' }}">
                Data Warung & User
            </a>
            <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('settings.*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/70' : 'text-slate-700 hover:bg-slate-100' }}">
                Nomor WA & Bank
            </a>
            <div class="pt-2 border-t border-slate-100">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors">
                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                        <span>Keluar dari Akun</span>
                    </button>
                </form>
            </div>
        </div>
        @endif
        @endauth

    </header>


    <!-- Main Container Layout -->
    <div class="flex-1 flex flex-col md:flex-row max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 gap-6">

        @auth
        <!-- Desktop Sidebar Navigation for Admin & Super Admin -->
        @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
        <aside class="hidden md:block w-64 shrink-0">
            <nav class="sticky top-24 space-y-1 bg-white p-3.5 rounded-3xl border border-slate-200/70 shadow-soft text-sm">
                
                @if(auth()->user()->isSuperAdmin())
                <div class="px-3 pt-2 pb-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Owner & Keuangan</div>
                <a href="{{ route('finance.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-medium transition-all {{ request()->routeIs('finance.index') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/70 shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('finance.index') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    Buku Kas & Laba
                </a>
                <a href="{{ route('approvals.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-2xl font-medium transition-all {{ request()->routeIs('approvals.*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/70 shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <span class="flex items-center gap-3">
                        <svg class="w-4 h-4 {{ request()->routeIs('approvals.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                        Approve Hapus
                    </span>
                    @php $pendingDelCount = \App\Models\Order::where('status', 'menunggu_approval_hapus')->count(); @endphp
                    @if($pendingDelCount > 0)
                    <span class="px-2 py-0.5 text-xs font-bold bg-rose-500 text-white rounded-full">{{ $pendingDelCount }}</span>
                    @endif
                </a>
                <a href="{{ route('finance.expenses') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-medium transition-all {{ request()->routeIs('finance.expenses*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/70 shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('finance.expenses*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6H1.5m8.25-1.5h1.5m-1.5 0v.75A.75.75 0 0012 6h1.5m6-1.5h.75a.75.75 0 01.75.75V6m0 0v1.5m0-1.5h-1.5"/></svg>
                    Beban Pengeluaran
                </a>
                <a href="{{ route('finance.incomes') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-medium transition-all {{ request()->routeIs('finance.incomes*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/70 shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('finance.incomes*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Pemasukan Lain
                </a>
                @endif

                <div class="px-3 pt-3 pb-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Operasional Voucher</div>
                <a href="{{ route('packages.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-medium transition-all {{ request()->routeIs('packages.*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/70 shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('packages.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/></svg>
                    Stok & Paket Voucher
                </a>
                <a href="{{ route('admin.orders.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-2xl font-medium transition-all {{ request()->routeIs('admin.orders.index') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/70 shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <span class="flex items-center gap-3">
                        <svg class="w-4 h-4 {{ request()->routeIs('admin.orders.index') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
                        Pesanan Masuk
                    </span>
                    @php $pendingCount = \App\Models\Order::where('status', 'menunggu_pembayaran')->count(); @endphp
                    @if($pendingCount > 0)
                    <span class="px-2 py-0.5 text-xs font-bold bg-amber-500 text-white rounded-full">{{ $pendingCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.orders.create_offline') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-medium transition-all {{ request()->routeIs('admin.orders.create_offline') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/70 shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('admin.orders.create_offline') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Input Order Offline
                </a>
                @if(!auth()->user()->isSuperAdmin())
                <a href="{{ route('finance.expenses') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-medium transition-all {{ request()->routeIs('finance.expenses*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/70 shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('finance.expenses*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6H1.5m8.25-1.5h1.5m-1.5 0v.75A.75.75 0 0012 6h1.5m6-1.5h.75a.75.75 0 01.75.75V6m0 0v1.5m0-1.5h-1.5"/></svg>
                    Beban Pengeluaran
                </a>
                @endif
                <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-medium transition-all {{ request()->routeIs('users.*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/70 shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('users.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                    Data Warung & User
                </a>
                <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-medium transition-all {{ request()->routeIs('settings.*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/70 shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('settings.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Nomor WA & Bank
                </a>
            </nav>
        </aside>
        @endif
        @endauth

        <!-- Main Content Body -->
        <main class="flex-1 min-w-0">
            <!-- Flash Message Alerts -->
            @if(session('success'))
            <div class="mb-5 p-4 rounded-2xl bg-blue-50/90 border border-blue-200/80 text-blue-900 flex items-start gap-3 shadow-xs">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="text-sm font-semibold">{{ session('success') }}</div>
            </div>
            @endif

            @if(session('info'))
            <div class="mb-5 p-4 rounded-2xl bg-blue-50/90 border border-blue-200/80 text-blue-900 flex items-start gap-3 shadow-xs">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="text-sm font-semibold">{{ session('info') }}</div>
            </div>
            @endif

            @if($errors->any())
            <div class="mb-5 p-4 rounded-2xl bg-rose-50/90 border border-rose-200/80 text-rose-900 shadow-xs">
                <div class="flex items-center gap-2 font-bold text-sm mb-1.5 text-rose-800">
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    Periksa kembali data yang dimasukkan:
                </div>
                <ul class="list-disc list-inside text-xs space-y-1 text-rose-700 pl-2">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function toggleAdminMobileNav() {
            const nav = document.getElementById('adminMobileNav');
            if (nav) nav.classList.toggle('hidden');
        }

        // Helper format rupiah otomatis dengan tanda titik (20000 -> 20.000, tanpa ,00)
        function formatRupiahInput(input) {
            let raw = input.value.replace(/\D/g, '');
            if (!raw) {
                input.value = '';
                return;
            }
            input.value = parseInt(raw, 10).toLocaleString('id-ID');
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.rupiah-input').forEach(function (el) {
                if (el.value) {
                    formatRupiahInput(el);
                }
                el.addEventListener('input', function () {
                    formatRupiahInput(this);
                });

                const parentForm = el.closest('form');
                if (parentForm) {
                    parentForm.addEventListener('submit', function () {
                        el.value = el.value.replace(/\D/g, '');
                    });
                }
            });
        });
    </script>
    @yield('scripts')
</body>
</html>
