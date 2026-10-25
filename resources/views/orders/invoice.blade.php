<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $docType }} - {{ $docNumber }} | {{ $appName }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
    
    <!-- Dynamic Print Page Size Styles -->
    <style id="dynamic-page-style">
        @page {
            size: auto;
            margin: 4mm 6mm;
        }
    </style>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Cap Stempel Lunas Digital */
        .stamp-lunas {
            border: 2.5px double #059669;
            color: #059669;
            border-radius: 8px;
            text-transform: uppercase;
            transform: rotate(-5deg);
            display: inline-block;
            font-weight: 800;
            letter-spacing: 1.5px;
            background-color: rgba(16, 185, 129, 0.04);
        }
        .stamp-tempo {
            border: 2px dashed #d97706;
            color: #b45309;
            border-radius: 8px;
            text-transform: uppercase;
            transform: rotate(-4deg);
            display: inline-block;
            font-weight: 800;
            letter-spacing: 1.2px;
            background-color: rgba(245, 158, 11, 0.04);
        }

        /* Print Media Rules */
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-shadow-none {
                box-shadow: none !important;
            }

            /* Saat Mode Kertas Setengah A4 */
            body.mode-a4 #container-thermal {
                display: none !important;
            }
            body.mode-a4 #container-a4 {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            body.mode-a4 .half-sheet {
                page-break-inside: avoid;
                break-inside: avoid;
                border: 1px solid #cbd5e1 !important;
            }

            /* Saat Mode Struk Thermal */
            body.mode-thermal #container-a4 {
                display: none !important;
            }
            body.mode-thermal #container-thermal {
                display: block !important;
                width: 100% !important;
                max-width: 80mm !important;
                margin: 0 auto !important;
                padding: 0 !important;
            }
            body.mode-thermal .thermal-receipt {
                border: none !important;
                box-shadow: none !important;
                padding: 1mm 0 !important;
            }
        }
    </style>
</head>
<body class="mode-a4 p-3 sm:p-5 lg:p-7 min-h-screen flex flex-col justify-between">

    <!-- Top Action Bar (Hidden on Print) -->
    <header class="max-w-4xl mx-auto w-full mb-5 no-print">
        <div class="bg-white p-3 sm:p-4 rounded-2xl shadow-sm border border-slate-200/90 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            
            <!-- Left: Back Navigation -->
            <div class="flex items-center gap-2.5">
                @if(auth()->user()->isTenant())
                <a href="{{ route('tenant.orders.index', ['tab' => 'pesanan']) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali</span>
                </a>
                @else
                <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali</span>
                </a>
                @endif
                <div class="hidden sm:block">
                    <span class="text-xs font-bold text-slate-800">{{ $docType }}</span>
                    <span class="text-[11px] text-slate-400 font-mono ml-1">• {{ $docNumber }}</span>
                </div>
            </div>

            <!-- Center: Layout Switcher Tabs (Kertas Setengah A4 vs Thermal POS) -->
            <div class="flex items-center justify-center bg-slate-100 p-1 rounded-xl gap-1 border border-slate-200/60">
                <button type="button" id="btn-mode-a4" onclick="switchPrintMode('a4')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-blue-700 bg-white shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>📄 Kertas Setengah A4</span>
                </button>
                <button type="button" id="btn-mode-thermal" onclick="switchPrintMode('thermal')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-slate-600 hover:text-slate-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>🧾 Struk Thermal (POS)</span>
                </button>
            </div>

            <!-- Right: Actions (WhatsApp, Print) -->
            <div class="flex items-center gap-2 justify-end">
                <!-- WhatsApp Share Button -->
                <a href="{{ $waShareUrl }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition-all active:scale-95" title="Kirim teks ringkasan ke WhatsApp">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    <span class="hidden sm:inline">Kirim WA</span>
                </a>

                <!-- Print Button -->
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-all active:scale-95">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak</span>
                </button>
            </div>
        </div>
    </header>

    <!-- ========================================== -->
    <!-- 1. CONTAINER MODE KERTAS SETENGAH A4 (A5)  -->
    <!-- ========================================== -->
    <main id="container-a4" class="max-w-4xl mx-auto w-full">
        @include('orders.partials.invoice_sheet')
    </main>

    <!-- ========================================== -->
    <!-- 2. CONTAINER MODE STRUK THERMAL (POS 58/80mm) -->
    <!-- ========================================== -->
    <section id="container-thermal" class="hidden max-w-sm mx-auto w-full py-2">
        @include('orders.partials.invoice_thermal')
    </section>

    <!-- Page Footer (Hidden on Print) -->
    <footer class="mt-8 text-center text-xs text-slate-400 no-print pb-4">
        &copy; {{ date('Y') }} {{ $appName }}. Seluruh hak cipta dilindungi.
    </footer>

    <!-- Interactive Script for Mode Switcher -->
    <script>
        function switchPrintMode(mode) {
            const btnA4 = document.getElementById('btn-mode-a4');
            const btnThermal = document.getElementById('btn-mode-thermal');
            const containerA4 = document.getElementById('container-a4');
            const containerThermal = document.getElementById('container-thermal');
            const dynamicPageStyle = document.getElementById('dynamic-page-style');

            if (mode === 'thermal') {
                document.body.classList.remove('mode-a4');
                document.body.classList.add('mode-thermal');

                btnThermal.className = "px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-blue-700 bg-white shadow-sm";
                btnA4.className = "px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-slate-600 hover:text-slate-900";

                containerA4.classList.add('hidden');
                containerThermal.classList.remove('hidden');

                // Margin dan ukuran kertas thermal 80mm roll
                dynamicPageStyle.innerHTML = `
                    @page {
                        size: 80mm auto;
                        margin: 2mm 3mm 4mm 3mm;
                    }
                `;
                localStorage.setItem('vouchernet_invoice_mode', 'thermal');
            } else {
                document.body.classList.remove('mode-thermal');
                document.body.classList.add('mode-a4');

                btnA4.className = "px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-blue-700 bg-white shadow-sm";
                btnThermal.className = "px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-slate-600 hover:text-slate-900";

                containerA4.classList.remove('hidden');
                containerThermal.classList.add('hidden');

                // Margin dan ukuran kertas untuk kertas setengah A4
                dynamicPageStyle.innerHTML = `
                    @page {
                        size: auto;
                        margin: 4mm 6mm;
                    }
                `;
                localStorage.setItem('vouchernet_invoice_mode', 'a4');
            }
        }

        // Restore saved preference on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedMode = localStorage.getItem('vouchernet_invoice_mode') || 'a4';
            switchPrintMode(savedMode);
        });
    </script>
</body>
</html>
