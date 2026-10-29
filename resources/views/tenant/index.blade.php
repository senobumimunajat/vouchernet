@extends('layouts.app')

@section('title', 'Portal Mitra Warung')

@section('content')
<div class="space-y-5 pb-36 max-w-full overflow-x-hidden">

    <!-- WhatsApp Redirect Popup (After order placed) -->
    @if(session('whatsapp_url'))
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-md animate-fade-in">
        <div class="bg-white rounded-[32px] max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-100 text-center space-y-4">
            <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-600 mx-auto flex items-center justify-center shadow-inner">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
            </div>
            <div>
                <h3 class="text-xl font-bold text-slate-900">Pesanan Siap Dikirim!</h3>
                <p class="text-xs text-slate-500 mt-1">Klik tombol WhatsApp di bawah untuk mengirim detail pesanan ke admin agar segera dicetak.</p>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl text-left text-xs space-y-1.5 border border-slate-200/70">
                <div class="font-bold text-slate-700">Rekening Pembayaran:</div>
                <div class="font-mono text-slate-600 whitespace-pre-line leading-relaxed">{{ $bankAccounts }}</div>
            </div>

            <div class="pt-2 flex flex-col gap-2">
                <a href="{{ session('whatsapp_url') }}" target="_blank" class="w-full touch-target flex justify-center items-center py-3.5 px-4 rounded-full font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-600/20 transition-all text-sm gap-2 active:scale-95">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    Buka WhatsApp Sekarang
                </a>
                @if(session('whatsapp_text'))
                <button type="button" onclick="copyWaText()" id="btnCopyWa" class="w-full touch-target flex justify-center items-center py-2.5 px-4 rounded-full font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-all text-xs gap-1.5 active:scale-95">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <span id="copyWaLabel">Salin Teks Pesan</span>
                </button>
                <textarea id="hiddenWaText" class="sr-only">{{ session('whatsapp_text') }}</textarea>
                @endif
                <button type="button" onclick="this.closest('.fixed').remove()" class="text-xs text-slate-400 hover:text-slate-700 py-1 font-medium">
                    Tutup Popup
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Modern Sleek Partner Greeting Bar (Compact on Mobile, Elegant on Desktop) -->
    <div class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white rounded-2xl sm:rounded-3xl p-4 sm:p-5 shadow-soft relative overflow-hidden">
        <div class="absolute -right-8 -top-8 w-36 h-36 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 relative z-10">
            <!-- Warung Profile Info -->
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-white/15 border border-white/20 backdrop-blur-md flex items-center justify-center text-white shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h1 class="text-base sm:text-xl font-extrabold tracking-tight truncate text-white">{{ $user->shop_name ?: $user->name }}</h1>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white border border-white/25 shrink-0">Mitra Warung</span>
                    </div>
                    <p class="text-xs text-blue-100 truncate mt-0.5">
                        {{ $user->name }} • <span class="font-mono text-white/90">{{ $user->phone }}</span>
                    </p>
                </div>
            </div>

            <!-- Sleek 3-Pill Metric Summary (Compact single-line grid on mobile) -->
            <div class="grid grid-cols-3 gap-1.5 sm:gap-2 bg-black/15 border border-white/15 backdrop-blur-md p-2 sm:p-2.5 rounded-xl sm:rounded-2xl text-center">
                <div class="px-1 sm:px-2">
                    <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-blue-200">Diambil</div>
                    <div class="text-xs sm:text-base font-extrabold font-mono text-white mt-0.5 truncate">
                        {{ number_format($totalVouchersTaken) }} <span class="text-[10px] font-sans text-blue-200 font-normal">lbr</span>
                    </div>
                </div>
                <div class="px-1 sm:px-2 border-x border-white/15">
                    <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-amber-200">Wajib Setor</div>
                    <div class="text-xs sm:text-base font-extrabold font-mono text-amber-300 mt-0.5 truncate">
                        Rp {{ number_format($pendingBillAmount, 0, ',', '.') }}
                    </div>
                </div>
                <div class="px-1 sm:px-2">
                    <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-blue-200">Untung Anda</div>
                    <div class="text-xs sm:text-base font-extrabold font-mono text-white mt-0.5 truncate">
                        Rp {{ number_format($totalProfitEarned, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Segmented Tab Switcher (Katalog vs Riwayat Pesanan) -->
    <div class="bg-slate-200/80 p-1.5 rounded-2xl flex items-center gap-1.5 shadow-inner">
        <button type="button" onclick="switchTenantTab('katalog')" id="tabBtnKatalog"
            class="flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition-all {{ $activeTab === 'katalog' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
            <svg class="w-4 h-4 {{ $activeTab === 'katalog' ? 'text-blue-600' : 'text-slate-500' }}" id="tabIconKatalog" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
            <span>Katalog Voucher</span>
        </button>
        <button type="button" onclick="switchTenantTab('pesanan')" id="tabBtnPesanan"
            class="flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition-all {{ $activeTab === 'pesanan' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }} relative">
            <svg class="w-4 h-4 {{ $activeTab === 'pesanan' ? 'text-blue-600' : 'text-slate-500' }}" id="tabIconPesanan" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
            <span>Pesanan & Setoran</span>
            @if($pendingOrdersCount > 0)
            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500 text-white shadow-xs">
                {{ $pendingOrdersCount }}
            </span>
            @endif
        </button>
        <a href="{{ route('tenant.deposits.index') }}" id="tabBtnSaldo"
            class="flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition-all text-slate-600 hover:text-slate-900 hover:bg-white/60">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Beli Saldo Kuota</span>
        </a>
    </div>

    <!-- ========================================== -->
    <!-- TAB PANE 1: KATALOG & PEMESANAN VOUCHER    -->
    <!-- ========================================== -->
    <div id="paneKatalog" class="{{ $activeTab === 'katalog' ? '' : 'hidden' }} space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base sm:text-xl font-bold tracking-tight text-slate-900">Pilih Paket Voucher</h2>
                <p class="text-xs text-slate-500">Pilih paket voucher yang ingin di-restock ke warung Anda.</p>
            </div>
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200/80">
                Stok Tersedia
            </span>
        </div>

        <form id="orderForm" action="{{ route('tenant.orders.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($packages as $pkg)
                <div class="package-card bg-white rounded-[24px] p-5 border-2 border-slate-200/80 transition-all duration-200 relative overflow-hidden flex flex-col justify-between shadow-card hover:border-slate-300"
                    id="card_{{ $pkg->id }}"
                    data-id="{{ $pkg->id }}">

                    <!-- Card Header -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[11px] font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-800">
                                {{ $pkg->duration_info }}
                            </span>
                            <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full {{ $pkg->stock > 0 ? 'bg-blue-50 text-blue-700 border border-blue-200/80' : 'bg-rose-50 text-rose-700 border border-rose-200/80' }}">
                                {{ $pkg->stock > 0 ? 'Stok: ' . number_format($pkg->stock) . ' lbr' : 'Stok Habis' }}
                            </span>
                        </div>

                        <h3 class="text-base font-extrabold text-slate-900 tracking-tight">{{ $pkg->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">{{ $pkg->description }}</p>
                    </div>

                    <!-- Perforated Divider Ticket Style -->
                    <div class="relative my-4">
                        <div class="border-t-2 border-dashed border-slate-200"></div>
                        <div class="absolute -left-7 -top-2 w-4 h-4 bg-[#f8fafd] rounded-full"></div>
                        <div class="absolute -right-7 -top-2 w-4 h-4 bg-[#f8fafd] rounded-full"></div>
                    </div>

                    <!-- Pricing Info -->
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between items-center text-slate-500">
                            <span>Harga Tertera (Warga):</span>
                            <span class="font-bold text-slate-700 font-mono text-xs">Rp {{ number_format($pkg->printed_price, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-800 pt-1 border-t border-slate-100">
                            <span class="font-semibold">Harga Setor Anda:</span>
                            <span class="font-extrabold text-slate-900 font-mono text-sm">Rp {{ number_format($pkg->owner_price, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Stepper Multi-Item per Paket -->
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-slate-700">Pesan:</span>
                            @if($pkg->stock > 0)
                            <div class="inline-flex items-center bg-slate-100 rounded-full p-1 border border-slate-200/80 shadow-inner">
                                <button type="button" onclick="adjustItemQty({{ $pkg->id }}, -5)" class="w-8 h-8 rounded-full bg-white text-slate-800 hover:bg-slate-200 shadow-xs flex items-center justify-center font-bold text-base active:scale-90 transition-transform">
                                    −
                                </button>
                                <input type="number" name="items[{{ $pkg->id }}]" id="item_qty_{{ $pkg->id }}" value="0" min="0" max="{{ $pkg->stock }}"
                                    onchange="onItemQtyChange({{ $pkg->id }})"
                                    class="w-14 text-center font-mono font-extrabold text-sm bg-transparent border-0 focus:outline-none focus:ring-0 text-slate-900 item-qty-input"
                                    data-id="{{ $pkg->id }}"
                                    data-owner="{{ $pkg->owner_price }}"
                                    data-printed="{{ $pkg->printed_price }}"
                                    data-stock="{{ $pkg->stock }}"
                                    data-name="{{ $pkg->name }}">
                                <button type="button" onclick="adjustItemQty({{ $pkg->id }}, 5)" class="w-8 h-8 rounded-full bg-blue-600 text-white hover:bg-blue-700 shadow-xs flex items-center justify-center font-bold text-base active:scale-90 transition-transform">
                                    +
                                </button>
                            </div>
                            @else
                            <span class="text-[11px] font-bold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-full border border-rose-200">
                                Stok Habis
                            </span>
                            @endif
                        </div>

                        @if($pkg->stock > 0)
                        <div class="flex items-center gap-1 mt-2 justify-end flex-wrap">
                            <span class="text-[10px] text-slate-400 font-semibold mr-auto">Template:</span>
                            <button type="button" onclick="setItemPreset({{ $pkg->id }}, 10)" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 active:scale-95 transition-colors">10</button>
                            <button type="button" onclick="setItemPreset({{ $pkg->id }}, 30)" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 active:scale-95 transition-colors">30</button>
                            <button type="button" onclick="setItemPreset({{ $pkg->id }}, 50)" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 active:scale-95 transition-colors">50</button>
                            <button type="button" onclick="setItemPreset({{ $pkg->id }}, 100)" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 active:scale-95 transition-colors">100</button>
                            <button type="button" onclick="setItemPreset({{ $pkg->id }}, 0)" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 hover:bg-rose-100 text-slate-500 hover:text-rose-700 active:scale-95 transition-colors">0</button>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Fixed Bottom Checkout Bar (Always Fixed at Bottom on Mobile & Desktop) -->
            <div id="checkoutFixedBar" class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200/90 shadow-[0_-8px_24px_rgba(0,0,0,0.08)] py-3 px-4 sm:px-6">
                <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center justify-between w-full sm:w-auto gap-4 px-1">
                        <div>
                            <div class="text-[10px] uppercase font-bold tracking-wider text-slate-400 flex items-center gap-1.5">
                                <span>Total Wajib Setor</span>
                                <span id="displayItemSummary" class="text-blue-700 font-bold bg-blue-50 px-1.5 py-0.5 rounded-md border border-blue-200/60 text-[10px] hidden">0 Paket (0 lbr)</span>
                            </div>
                            <div class="text-xl sm:text-2xl font-black font-mono text-slate-900 leading-tight" id="displayTotalOwner">Rp 0</div>
                        </div>
                        <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>
                        <div class="text-right sm:text-left">
                            <div class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Potensi Untung Warung</div>
                            <div class="text-xs sm:text-base font-extrabold font-mono text-emerald-700 leading-tight" id="displayTotalProfit">+Rp 0</div>
                        </div>
                    </div>

                    <button type="submit" form="orderForm" id="btnSubmitOrder" disabled
                        class="w-full sm:w-auto touch-target flex justify-center items-center py-3.5 px-6 sm:px-8 rounded-full font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/25 transition-all text-sm gap-2 active:scale-95 shrink-0 opacity-50 cursor-not-allowed">
                        <svg class="w-5 h-5 text-white shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        <span id="btnSubmitOrderText" class="truncate">Pilih Lembar Voucher Dahulu</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- ========================================== -->
    <!-- TAB PANE 2: RIWAYAT PESANAN & REKAP SETORAN-->
    <!-- ========================================== -->
    <div id="panePesanan" class="{{ $activeTab === 'pesanan' ? '' : 'hidden' }} space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base sm:text-xl font-bold tracking-tight text-slate-900">Riwayat Pesanan & Rekap Setoran</h2>
                <p class="text-xs text-slate-500">Pantau jumlah voucher yang telah diambil dan total tagihan setoran ke admin.</p>
            </div>
            
            @if($pendingBillAmount > 0)
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold shadow-xs self-start sm:self-auto">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                Ada {{ number_format($pendingVouchersCount) }} voucher belum disetor
            </div>
            @endif
        </div>

        <!-- 3 Kartu Ringkasan Rekap Konsinyasi & Wajib Setor -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
            <!-- 1. Total Voucher Diambil -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-soft flex items-center justify-between relative overflow-hidden">
                <div class="space-y-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Voucher Diambil</span>
                    <div class="text-2xl sm:text-3xl font-extrabold font-mono text-slate-900">
                        {{ number_format($totalVouchersTaken) }} <span class="text-xs font-sans text-slate-500 font-semibold">lembar</span>
                    </div>
                    <div class="text-xs text-slate-500 pt-0.5">
                        <span class="text-amber-600 font-semibold">{{ number_format($pendingVouchersCount) }} belum setor</span> • 
                        <span class="text-blue-700 font-semibold">{{ number_format($paidVouchersCount) }} lunas</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
            </div>

            <!-- 2. Harus Disetorkan ke Admin (Tempo) - Highlight Card -->
            <div class="bg-gradient-to-br {{ $pendingBillAmount > 0 ? 'from-amber-50/90 via-white to-amber-50/50 border-amber-300 ring-4 ring-amber-400/10' : 'from-slate-50 to-white border-slate-200/80' }} rounded-2xl p-4 sm:p-5 border shadow-soft flex items-center justify-between relative overflow-hidden">
                <div class="space-y-1">
                    <div class="flex items-center gap-1.5">
                        <span class="text-[11px] font-bold uppercase tracking-wider {{ $pendingBillAmount > 0 ? 'text-amber-800' : 'text-slate-500' }}">Harus Disetorkan (Tempo)</span>
                        @if($pendingBillAmount > 0)
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        @endif
                    </div>
                    <div class="text-2xl sm:text-3xl font-extrabold font-mono {{ $pendingBillAmount > 0 ? 'text-amber-900' : 'text-slate-800' }}">
                        Rp {{ number_format($pendingBillAmount, 0, ',', '.') }}
                    </div>
                    <div class="text-xs {{ $pendingBillAmount > 0 ? 'text-amber-700 font-medium' : 'text-slate-400' }} pt-0.5">
                        @if($pendingBillAmount > 0)
                            {{ number_format($pendingVouchersCount) }} lembar menunggu setoran Anda
                        @else
                            Semua tagihan sudah disetor lunas 👍
                        @endif
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl {{ $pendingBillAmount > 0 ? 'bg-amber-100 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <!-- 3. Sudah Disetor Lunas -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-soft flex items-center justify-between relative overflow-hidden">
                <div class="space-y-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Sudah Disetor Lunas</span>
                    <div class="text-2xl sm:text-3xl font-extrabold font-mono text-emerald-600">
                        Rp {{ number_format($paidBillAmount, 0, ',', '.') }}
                    </div>
                    <div class="text-xs text-slate-500 pt-0.5">
                        {{ number_format($paidVouchersCount) }} lembar • Untung: <span class="text-emerald-700 font-bold font-mono">+Rp {{ number_format($totalProfitEarned, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        @if($pendingBillAmount > 0)
        <!-- Banner Edukasi & Pengingat Setoran Mitra -->
        <div class="bg-amber-50/70 border border-amber-200 rounded-2xl p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-amber-950">Pengingat Setoran Voucher Konsinyasi (Ambil Dulu)</h4>
                    <p class="text-xs text-amber-900/80 mt-0.5 leading-relaxed">
                        Warung Anda saat ini membawa <span class="font-bold text-amber-950">{{ number_format($pendingVouchersCount) }} lembar voucher</span> dengan total kewajiban setoran <span class="font-bold font-mono text-amber-950">Rp {{ number_format($pendingBillAmount, 0, ',', '.') }}</span>. Setelah voucher laku terjual ke pembeli, Anda dapat menyetorkan secara tunai ke admin atau via transfer bank.
                    </p>
                </div>
            </div>
            @php
                $waTextSetoran = "*Halo Admin " . ($globalAppName ?? 'VoucherNet') . ",*\nSaya dari *" . ($user->shop_name ?: $user->name) . "* ingin konfirmasi setoran voucher:\n- Total Voucher: " . number_format($pendingVouchersCount) . " lembar\n- Total Tagihan Setor: Rp " . number_format($pendingBillAmount, 0, ',', '.') . "\n\nMohon infokan nomor rekening / jika admin ada di tempat. Terima kasih!";
                $waSetoranUrl = 'https://api.whatsapp.com/send?phone=' . $adminPhoneClean . '&text=' . rawurlencode($waTextSetoran);
            @endphp
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ $waSetoranUrl }}" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-full text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white shadow-xs transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    Konfirmasi Setor via WA
                </a>
            </div>
        </div>
        @endif

        <!-- Filter Tab Buttons (Semua, Belum Disetor / Tempo, Sudah Lunas) -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1">
            <a href="{{ route('tenant.orders.index', ['tab' => 'pesanan', 'status' => '']) }}" 
                class="px-4 py-2 rounded-full text-xs font-bold transition-all whitespace-nowrap {{ empty($statusFilter) ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
                Semua Pesanan ({{ $totalOrdersCount }})
            </a>
            <a href="{{ route('tenant.orders.index', ['tab' => 'pesanan', 'status' => 'menunggu_pembayaran']) }}" 
                class="px-4 py-2 rounded-full text-xs font-bold transition-all whitespace-nowrap {{ $statusFilter === 'menunggu_pembayaran' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
                Belum Disetor / Tempo ({{ $pendingOrdersCount }})
            </a>
            <a href="{{ route('tenant.orders.index', ['tab' => 'pesanan', 'status' => 'lunas']) }}" 
                class="px-4 py-2 rounded-full text-xs font-bold transition-all whitespace-nowrap {{ $statusFilter === 'lunas' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
                Sudah Lunas ({{ $paidOrdersCount }})
            </a>
        </div>

        <div class="bg-white rounded-[24px] border border-slate-200/80 overflow-hidden shadow-soft">
            @if($orders->isEmpty())
            <div class="p-10 text-center text-slate-400 text-sm">
                @if($statusFilter === 'menunggu_pembayaran')
                    Tidak ada pesanan yang berstatus belum disetor (tempo). Semua voucher Anda telah lunas!
                @elseif($statusFilter === 'lunas')
                    Belum ada riwayat pesanan yang sudah lunas.
                @else
                    Belum ada riwayat pesanan. Pilih tab Katalog untuk melakukan pemesanan pertama!
                @endif
            </div>
            @else
            <div class="divide-y divide-slate-100">
                @foreach($orders as $ord)
                <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/60 transition-colors">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono font-extrabold text-slate-900 text-sm tracking-tight">{{ $ord->order_number }}</span>
                            <span class="text-xs text-slate-400 font-sans">• {{ $ord->created_at->translatedFormat('d M Y, H:i') }}</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $ord->status_badge['bg'] }}">
                                {{ $ord->status_badge['label'] }}
                            </span>
                            @if($ord->status === 'menunggu_pembayaran')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                📦 Ambil Dulu (Tempo)
                            </span>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-1.5">
                            @foreach($ord->effective_items as $item)
                            <div class="inline-flex items-center gap-1.5 bg-slate-100/90 px-2.5 py-1 rounded-lg border border-slate-200/80 text-xs">
                                <span class="font-semibold text-slate-800">{{ $item->package?->name ?? 'Paket' }}</span>
                                <span class="font-mono font-extrabold text-blue-700 text-[11px] bg-white px-1.5 py-0.5 rounded border border-slate-200/50">
                                    {{ number_format($item->quantity) }} lbr
                                </span>
                            </div>
                            @endforeach
                            @if($ord->effective_items->count() > 1)
                            <span class="text-[11px] text-slate-400 font-medium ml-1">({{ number_format($ord->quantity) }} lembar total)</span>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs pt-0.5">
                            <div class="text-slate-600">
                                Wajib Setor: <span class="font-mono font-extrabold text-slate-900 text-sm">Rp {{ number_format($ord->total_owner_amount, 0, ',', '.') }}</span>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Potensi Untung: +Rp {{ number_format($ord->total_reseller_profit, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-start sm:self-auto flex-wrap">
                        @if($ord->status === 'lunas')
                        <a href="{{ route('orders.invoice', $ord) }}" target="_blank" class="text-xs text-emerald-800 bg-emerald-100/90 hover:bg-emerald-200 px-3.5 py-1.5 rounded-full border border-emerald-300 font-bold inline-flex items-center gap-1.5 transition-colors shadow-xs">
                            <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Kwitansi Lunas
                        </a>
                        @elseif($ord->status === 'menunggu_pembayaran')
                        <a href="{{ route('orders.invoice', $ord) }}" target="_blank" class="text-xs text-slate-700 bg-white hover:bg-slate-100 px-3.5 py-1.5 rounded-full border border-slate-300 font-bold inline-flex items-center gap-1.5 transition-colors shadow-xs">
                            <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Faktur Tagihan
                        </a>
                        @php
                            $waSingleText = "*Halo Admin " . ($globalAppName ?? 'VoucherNet') . ",*\nSaya ingin setorkan pesanan voucher:\n• *Order ID:* {$ord->order_number}\n• *Paket:* {$ord->package_names_summary}\n• *Jumlah:* {$ord->quantity} Lembar\n• *Total Setor:* *Rp " . number_format($ord->total_owner_amount, 0, ',', '.') . "*\n\nMohon konfirmasinya ya min!";
                            $waSingleUrl = 'https://api.whatsapp.com/send?phone=' . $adminPhoneClean . '&text=' . rawurlencode($waSingleText);
                        @endphp
                        <a href="{{ $waSingleUrl }}" target="_blank" class="text-xs text-amber-800 bg-amber-100/80 hover:bg-amber-200/80 px-3.5 py-1.5 rounded-full border border-amber-300 font-bold inline-flex items-center gap-1.5 transition-colors">
                            <svg class="w-3.5 h-3.5 text-amber-700" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            Konfirmasi Setor
                        </a>
                        @elseif($ord->status === 'menunggu_approval_hapus')
                        <span class="text-xs text-rose-700 bg-rose-50 px-3 py-1.5 rounded-full border border-rose-200 font-semibold">
                            Pengajuan Batal
                        </span>
                        @elseif($ord->status === 'dibatalkan')
                        <span class="text-xs text-slate-500 bg-slate-100 px-3 py-1.5 rounded-full border border-slate-200 font-medium">
                            Dibatalkan
                        </span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            <div class="p-4 border-t border-slate-100">
                {{ $orders->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<script>
    function adjustItemQty(pkgId, delta) {
        const input = document.getElementById('item_qty_' + pkgId);
        if (!input) return;
        let val = parseInt(input.value) || 0;
        let max = parseInt(input.dataset.stock) || 5000;
        val += delta;
        if (val < 0) val = 0;
        if (val > max) val = max;
        input.value = val;
        onItemQtyChange(pkgId);
    }

    function setItemPreset(pkgId, targetVal) {
        const input = document.getElementById('item_qty_' + pkgId);
        if (!input) return;
        let max = parseInt(input.dataset.stock) || 5000;
        let val = Math.max(0, Math.min(max, targetVal));
        input.value = val;
        onItemQtyChange(pkgId);
    }

    function setItemQty(pkgId, amount) {
        setItemPreset(pkgId, amount);
    }

    function onItemQtyChange(pkgId) {
        const input = document.getElementById('item_qty_' + pkgId);
        if (input) {
            let val = parseInt(input.value) || 0;
            let max = parseInt(input.dataset.stock) || 5000;
            if (val < 0) val = 0;
            if (val > max) val = max;
            input.value = val;

            const card = document.getElementById('card_' + pkgId);
            if (card) {
                if (val > 0) {
                    card.classList.add('border-blue-600', 'shadow-soft', 'ring-4', 'ring-blue-500/15', 'bg-blue-50/20');
                    card.classList.remove('border-slate-200/80');
                } else {
                    card.classList.remove('border-blue-600', 'shadow-soft', 'ring-4', 'ring-blue-500/15', 'bg-blue-50/20');
                    card.classList.add('border-slate-200/80');
                }
            }
        }
        recalcAllTotals();
    }

    function recalcAllTotals() {
        let totalQty = 0;
        let totalOwner = 0;
        let totalProfit = 0;
        let packagesCount = 0;

        document.querySelectorAll('.item-qty-input').forEach(input => {
            const qty = parseInt(input.value) || 0;
            if (qty > 0) {
                const ownerPrice = parseFloat(input.dataset.owner) || 0;
                const printedPrice = parseFloat(input.dataset.printed) || 0;
                totalQty += qty;
                totalOwner += (ownerPrice * qty);
                totalProfit += ((printedPrice - ownerPrice) * qty);
                packagesCount++;
            }
        });

        const displayOwner = document.getElementById('displayTotalOwner');
        const displayProfit = document.getElementById('displayTotalProfit');
        const displaySummary = document.getElementById('displayItemSummary');
        const btnSubmit = document.getElementById('btnSubmitOrder');
        const btnText = document.getElementById('btnSubmitOrderText');

        if (displayOwner) displayOwner.innerText = 'Rp ' + totalOwner.toLocaleString('id-ID');
        if (displayProfit) displayProfit.innerText = '+Rp ' + totalProfit.toLocaleString('id-ID');

        if (displaySummary) {
            if (totalQty > 0) {
                displaySummary.innerText = packagesCount + ' Paket (' + totalQty.toLocaleString('id-ID') + ' lbr)';
                displaySummary.classList.remove('hidden');
            } else {
                displaySummary.classList.add('hidden');
            }
        }

        if (btnSubmit && btnText) {
            if (totalQty > 0) {
                btnSubmit.removeAttribute('disabled');
                btnSubmit.classList.remove('opacity-50', 'cursor-not-allowed');
                btnText.innerText = 'Kirim Pesanan (' + totalQty.toLocaleString('id-ID') + ' Lembar) ke WA Admin';
            } else {
                btnSubmit.setAttribute('disabled', 'disabled');
                btnSubmit.classList.add('opacity-50', 'cursor-not-allowed');
                btnText.innerText = 'Pilih Lembar Voucher Dahulu';
            }
        }
    }

    // Tab switcher between Katalog and Pesanan
    function switchTenantTab(tab) {
        const paneKatalog = document.getElementById('paneKatalog');
        const panePesanan = document.getElementById('panePesanan');
        const btnKatalog = document.getElementById('tabBtnKatalog');
        const btnPesanan = document.getElementById('tabBtnPesanan');
        const iconKatalog = document.getElementById('tabIconKatalog');
        const iconPesanan = document.getElementById('tabIconPesanan');

        const bottomNavKatalog = document.getElementById('bottomNavKatalog');
        const bottomNavPesanan = document.getElementById('bottomNavPesanan');

        const checkoutBar = document.getElementById('checkoutFixedBar');

        if (tab === 'katalog') {
            paneKatalog?.classList.remove('hidden');
            panePesanan?.classList.add('hidden');
            checkoutBar?.classList.remove('hidden');

            btnKatalog?.classList.add('bg-white', 'text-slate-900', 'shadow-sm');
            btnKatalog?.classList.remove('text-slate-600');
            iconKatalog?.classList.add('text-blue-600');
            iconKatalog?.classList.remove('text-slate-500');

            btnPesanan?.classList.remove('bg-white', 'text-slate-900', 'shadow-sm');
            btnPesanan?.classList.add('text-slate-600');
            iconPesanan?.classList.remove('text-blue-600');
            iconPesanan?.classList.add('text-slate-500');

            if (history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.set('tab', 'katalog');
                url.hash = '';
                history.replaceState(null, '', url);
            }
        } else {
            panePesanan?.classList.remove('hidden');
            paneKatalog?.classList.add('hidden');
            checkoutBar?.classList.add('hidden');

            btnPesanan?.classList.add('bg-white', 'text-slate-900', 'shadow-sm');
            btnPesanan?.classList.remove('text-slate-600');
            iconPesanan?.classList.add('text-blue-600');
            iconPesanan?.classList.remove('text-slate-500');

            btnKatalog?.classList.remove('bg-white', 'text-slate-900', 'shadow-sm');
            btnKatalog?.classList.add('text-slate-600');
            iconKatalog?.classList.remove('text-blue-600');
            iconKatalog?.classList.add('text-slate-500');

            if (history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.set('tab', 'pesanan');
                history.replaceState(null, '', url);
            }
        }
    }
    function copyWaText() {
        const textEl = document.getElementById('hiddenWaText');
        if (!textEl) return;
        navigator.clipboard.writeText(textEl.value).then(() => {
            const label = document.getElementById('copyWaLabel');
            if (label) {
                const old = label.innerText;
                label.innerText = '✓ Berhasil Disalin!';
                setTimeout(() => { label.innerText = old; }, 2500);
            }
        }).catch(() => {
            textEl.select();
            document.execCommand('copy');
        });
    }
    window.copyWaText = copyWaText;

    document.addEventListener('DOMContentLoaded', function () {
        recalcAllTotals();

        const hash = window.location.hash;
        const urlParams = new URLSearchParams(window.location.search);
        const serverTab = '{{ $activeTab }}';

        if (hash === '#riwayat' || hash === '#pesanan' || urlParams.get('tab') === 'pesanan' || urlParams.has('status') || urlParams.has('page') || serverTab === 'pesanan') {
            switchTenantTab('pesanan');
        } else {
            switchTenantTab('katalog');
        }
    });
</script>
@endsection
