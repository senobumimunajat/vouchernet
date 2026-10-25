<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Income;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Models\VoucherPackage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $rangeType = $request->input('range_type', 'month'); // 'month' or 'custom'
        $selectedYear = (int) $request->input('year', date('Y'));
        $selectedMonth = $request->input('month', date('m'));
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $selectedTenant = $request->input('tenant_id');
        $selectedPackage = $request->input('package_id');
        $selectedTenantUser = $selectedTenant ? User::find($selectedTenant) : null;

        // Query orders berstatus lunas
        $orderQuery = Order::where('status', 'lunas');
        // Query orders berstatus belum lunas (tempo / konsinyasi yang masih beredar)
        $pendingOrderQuery = Order::where('status', 'menunggu_pembayaran');
        // Query total tagihan konsinyasi akumulatif (all time) belum lunas
        $allTimePendingQuery = Order::where('status', 'menunggu_pembayaran');
        $incomeQuery = Income::query();
        $expenseQuery = Expense::query();

        // Apply Date / Month Filter
        if ($rangeType === 'custom' && $startDate && $endDate) {
            $orderQuery->whereBetween('confirmed_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            $pendingOrderQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            $incomeQuery->whereBetween('income_date', [$startDate, $endDate]);
            $expenseQuery->whereBetween('expense_date', [$startDate, $endDate]);
            $periodLabel = Carbon::parse($startDate)->translatedFormat('d M Y') . ' s/d ' . Carbon::parse($endDate)->translatedFormat('d M Y');
        } else {
            $rangeType = 'month';
            if ($selectedYear) {
                $orderQuery->whereYear('confirmed_at', $selectedYear);
                $pendingOrderQuery->whereYear('created_at', $selectedYear);
                $incomeQuery->whereYear('income_date', $selectedYear);
                $expenseQuery->whereYear('expense_date', $selectedYear);
            }
            if ($selectedMonth && $selectedMonth !== 'all') {
                $orderQuery->whereMonth('confirmed_at', $selectedMonth);
                $pendingOrderQuery->whereMonth('created_at', $selectedMonth);
                $incomeQuery->whereMonth('income_date', $selectedMonth);
                $expenseQuery->whereMonth('expense_date', $selectedMonth);
                $periodLabel = Carbon::create($selectedYear, (int)$selectedMonth)->translatedFormat('F Y');
            } else {
                $periodLabel = 'Semua Bulan ' . $selectedYear;
            }
        }

        if ($selectedTenant) {
            $orderQuery->where('tenant_id', $selectedTenant);
            $pendingOrderQuery->where('tenant_id', $selectedTenant);
            $allTimePendingQuery->where('tenant_id', $selectedTenant);
        }

        if ($selectedPackage) {
            $pkgFilter = function ($q) use ($selectedPackage) {
                $q->where('package_id', $selectedPackage)
                  ->orWhereHas('items', fn($iq) => $iq->where('package_id', $selectedPackage));
            };
            $orderQuery->where($pkgFilter);
            $pendingOrderQuery->where($pkgFilter);
            $allTimePendingQuery->where($pkgFilter);

            // Metrik lunas spesifik paket yang dipilih (order_items + legacy orders)
            $pkgLunasItems = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', 'lunas')
                ->where('order_items.package_id', $selectedPackage);
            if ($selectedTenant) $pkgLunasItems->where('orders.tenant_id', $selectedTenant);
            if ($rangeType === 'custom' && $startDate && $endDate) {
                $pkgLunasItems->whereBetween('orders.confirmed_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            } else {
                if ($selectedYear) $pkgLunasItems->whereYear('orders.confirmed_at', $selectedYear);
                if ($selectedMonth && $selectedMonth !== 'all') $pkgLunasItems->whereMonth('orders.confirmed_at', $selectedMonth);
            }

            $legacyLunas = DB::table('orders')
                ->where('orders.status', 'lunas')
                ->where('orders.package_id', $selectedPackage)
                ->whereNotExists(function ($sub) {
                    $sub->select(DB::raw(1))->from('order_items')->whereColumn('order_items.order_id', 'orders.id');
                });
            if ($selectedTenant) $legacyLunas->where('orders.tenant_id', $selectedTenant);
            if ($rangeType === 'custom' && $startDate && $endDate) {
                $legacyLunas->whereBetween('orders.confirmed_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            } else {
                if ($selectedYear) $legacyLunas->whereYear('orders.confirmed_at', $selectedYear);
                if ($selectedMonth && $selectedMonth !== 'all') $legacyLunas->whereMonth('orders.confirmed_at', $selectedMonth);
            }

            $totalVouchersSold = (int) ($pkgLunasItems->sum('order_items.quantity') + $legacyLunas->sum('orders.quantity'));
            $grossTurnover = (float) ($pkgLunasItems->sum('order_items.total_printed_amount') + $legacyLunas->sum('orders.total_printed_amount'));
            $netOwnerRevenue = (float) ($pkgLunasItems->sum('order_items.total_owner_amount') + $legacyLunas->sum('orders.total_owner_amount'));
            $resellerCommission = (float) ($pkgLunasItems->sum('order_items.total_reseller_profit') + $legacyLunas->sum('orders.total_reseller_profit'));

            // Pending untuk paket yang dipilih
            $pkgPendingItems = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', 'menunggu_pembayaran')
                ->where('order_items.package_id', $selectedPackage);
            if ($selectedTenant) $pkgPendingItems->where('orders.tenant_id', $selectedTenant);
            if ($rangeType === 'custom' && $startDate && $endDate) {
                $pkgPendingItems->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            } else {
                if ($selectedYear) $pkgPendingItems->whereYear('orders.created_at', $selectedYear);
                if ($selectedMonth && $selectedMonth !== 'all') $pkgPendingItems->whereMonth('orders.created_at', $selectedMonth);
            }

            $legacyPending = DB::table('orders')
                ->where('orders.status', 'menunggu_pembayaran')
                ->where('orders.package_id', $selectedPackage)
                ->whereNotExists(function ($sub) {
                    $sub->select(DB::raw(1))->from('order_items')->whereColumn('order_items.order_id', 'orders.id');
                });
            if ($selectedTenant) $legacyPending->where('orders.tenant_id', $selectedTenant);
            if ($rangeType === 'custom' && $startDate && $endDate) {
                $legacyPending->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            } else {
                if ($selectedYear) $legacyPending->whereYear('orders.created_at', $selectedYear);
                if ($selectedMonth && $selectedMonth !== 'all') $legacyPending->whereMonth('orders.created_at', $selectedMonth);
            }

            $pendingOrdersCount = (int) (clone $pendingOrderQuery)->count();
            $pendingVouchersCount = (int) ($pkgPendingItems->sum('order_items.quantity') + $legacyPending->sum('orders.quantity'));
            $pendingGrossTurnover = (float) ($pkgPendingItems->sum('order_items.total_printed_amount') + $legacyPending->sum('orders.total_printed_amount'));
            $pendingOwnerAmount = (float) ($pkgPendingItems->sum('order_items.total_owner_amount') + $legacyPending->sum('orders.total_owner_amount'));
            $pendingResellerProfit = (float) ($pkgPendingItems->sum('order_items.total_reseller_profit') + $legacyPending->sum('orders.total_reseller_profit'));
        } else {
            // Metrik Penjualan Voucher Lunas Global
            $totalVouchersSold = (int) $orderQuery->sum('quantity');
            $grossTurnover = (float) $orderQuery->sum('total_printed_amount');       // Nilai fisik tertera
            $netOwnerRevenue = (float) $orderQuery->sum('total_owner_amount');         // Kas masuk setor reseller
            $resellerCommission = (float) $orderQuery->sum('total_reseller_profit');   // Margin dinikmati mitra warung

            // Metrik Pesanan Belum Lunas (Tempo / Konsinyasi yang Masih Beredar)
            $pendingOrdersCount = (int) (clone $pendingOrderQuery)->count();
            $pendingVouchersCount = (int) (clone $pendingOrderQuery)->sum('quantity');
            $pendingGrossTurnover = (float) (clone $pendingOrderQuery)->sum('total_printed_amount');
            $pendingOwnerAmount = (float) (clone $pendingOrderQuery)->sum('total_owner_amount');
            $pendingResellerProfit = (float) (clone $pendingOrderQuery)->sum('total_reseller_profit');
        }

        // Total Tagihan Konsinyasi Akumulatif (Semua Waktu / All Time) yang Masih Belum Dibayar
        $allTimePendingOwnerAmount = (float) (clone $allTimePendingQuery)->sum('total_owner_amount');
        $allTimePendingCount = (int) (clone $allTimePendingQuery)->count();
        $allTimePendingVouchersCount = (int) (clone $allTimePendingQuery)->sum('quantity');

        // Proyeksi / Prediksi Finansial Jika Seluruh Tagihan Dibayar Lunas
        $projectedVoucherRevenue = $netOwnerRevenue + $pendingOwnerAmount;
        $projectedVouchersSold = $totalVouchersSold + $pendingVouchersCount;

        // Metrik Pemasukan Kas Lain
        $otherIncome = (float) $incomeQuery->sum('amount');

        // Metrik Beban Kas Pengeluaran
        $totalExpenses = (float) $expenseQuery->sum('amount');

        // Total Kas Masuk & Laba Bersih Realisasi
        $totalCashIn = $netOwnerRevenue + $otherIncome;
        $netProfit = $totalCashIn - $totalExpenses;

        // Proyeksi Total Kas Masuk & Proyeksi Laba Bersih
        $projectedTotalCashIn = $totalCashIn + $pendingOwnerAmount;
        $projectedNetProfit = $projectedTotalCashIn - $totalExpenses;

        // Breakdown per Paket Voucher (Difilter sesuai periode & tenant terpilih)
        $packageBreakdownQuery = VoucherPackage::query();
        if ($selectedPackage) {
            $packageBreakdownQuery->where('id', $selectedPackage);
        }
        $allPackages = $packageBreakdownQuery->get();

        // 1. Ambil data penjualan lunas dari tabel order_items (multi-item & new system)
        $itemSalesQuery = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'lunas');

        if ($selectedTenant) {
            $itemSalesQuery->where('orders.tenant_id', $selectedTenant);
        }
        if ($selectedPackage) {
            $itemSalesQuery->where('order_items.package_id', $selectedPackage);
        }
        if ($rangeType === 'custom' && $startDate && $endDate) {
            $itemSalesQuery->whereBetween('orders.confirmed_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } else {
            if ($selectedYear) $itemSalesQuery->whereYear('orders.confirmed_at', $selectedYear);
            if ($selectedMonth && $selectedMonth !== 'all') $itemSalesQuery->whereMonth('orders.confirmed_at', $selectedMonth);
        }

        $itemSales = $itemSalesQuery
            ->groupBy('order_items.package_id')
            ->select([
                'order_items.package_id',
                DB::raw('COUNT(DISTINCT order_items.order_id) as orders_count'),
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total_owner_amount) as total_owner_amount'),
                DB::raw('SUM(order_items.total_printed_amount) as total_printed_amount'),
                DB::raw('SUM(order_items.total_reseller_profit) as total_reseller_profit'),
            ])
            ->get()
            ->keyBy('package_id');

        // 2. Ambil data penjualan lunas dari legacy orders (yang tidak memiliki record di order_items)
        $legacySalesQuery = DB::table('orders')
            ->where('orders.status', 'lunas')
            ->whereNotNull('orders.package_id')
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('order_items')
                    ->whereColumn('order_items.order_id', 'orders.id');
            });

        if ($selectedTenant) {
            $legacySalesQuery->where('orders.tenant_id', $selectedTenant);
        }
        if ($selectedPackage) {
            $legacySalesQuery->where('orders.package_id', $selectedPackage);
        }
        if ($rangeType === 'custom' && $startDate && $endDate) {
            $legacySalesQuery->whereBetween('orders.confirmed_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } else {
            if ($selectedYear) $legacySalesQuery->whereYear('orders.confirmed_at', $selectedYear);
            if ($selectedMonth && $selectedMonth !== 'all') $legacySalesQuery->whereMonth('orders.confirmed_at', $selectedMonth);
        }

        $legacySales = $legacySalesQuery
            ->groupBy('orders.package_id')
            ->select([
                'orders.package_id',
                DB::raw('COUNT(orders.id) as orders_count'),
                DB::raw('SUM(orders.quantity) as total_quantity'),
                DB::raw('SUM(orders.total_owner_amount) as total_owner_amount'),
                DB::raw('SUM(orders.total_printed_amount) as total_printed_amount'),
                DB::raw('SUM(orders.total_reseller_profit) as total_reseller_profit'),
            ])
            ->get()
            ->keyBy('package_id');

        // 3. Ambil data pending (menunggu_pembayaran) dari order_items
        $itemPendingQuery = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'menunggu_pembayaran');

        if ($selectedTenant) {
            $itemPendingQuery->where('orders.tenant_id', $selectedTenant);
        }
        if ($selectedPackage) {
            $itemPendingQuery->where('order_items.package_id', $selectedPackage);
        }
        if ($rangeType === 'custom' && $startDate && $endDate) {
            $itemPendingQuery->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } else {
            if ($selectedYear) $itemPendingQuery->whereYear('orders.created_at', $selectedYear);
            if ($selectedMonth && $selectedMonth !== 'all') $itemPendingQuery->whereMonth('orders.created_at', $selectedMonth);
        }

        $itemPending = $itemPendingQuery
            ->groupBy('order_items.package_id')
            ->select([
                'order_items.package_id',
                DB::raw('SUM(order_items.quantity) as pending_quantity'),
            ])
            ->get()
            ->keyBy('package_id');

        // 4. Ambil data pending dari legacy orders (tanpa order_items)
        $legacyPendingQuery = DB::table('orders')
            ->where('orders.status', 'menunggu_pembayaran')
            ->whereNotNull('orders.package_id')
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('order_items')
                    ->whereColumn('order_items.order_id', 'orders.id');
            });

        if ($selectedTenant) {
            $legacyPendingQuery->where('orders.tenant_id', $selectedTenant);
        }
        if ($selectedPackage) {
            $legacyPendingQuery->where('orders.package_id', $selectedPackage);
        }
        if ($rangeType === 'custom' && $startDate && $endDate) {
            $legacyPendingQuery->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } else {
            if ($selectedYear) $legacyPendingQuery->whereYear('orders.created_at', $selectedYear);
            if ($selectedMonth && $selectedMonth !== 'all') $legacyPendingQuery->whereMonth('orders.created_at', $selectedMonth);
        }

        $legacyPending = $legacyPendingQuery
            ->groupBy('orders.package_id')
            ->select([
                'orders.package_id',
                DB::raw('SUM(orders.quantity) as pending_quantity'),
            ])
            ->get()
            ->keyBy('package_id');

        // 5. Gabungkan agregasi ke setiap model VoucherPackage
        foreach ($allPackages as $pkg) {
            $itemStat = $itemSales->get($pkg->id);
            $legacyStat = $legacySales->get($pkg->id);
            $pendingItem = $itemPending->get($pkg->id);
            $pendingLegacy = $legacyPending->get($pkg->id);

            $pkg->orders_count = (int) (($itemStat?->orders_count ?? 0) + ($legacyStat?->orders_count ?? 0));
            $pkg->total_quantity = (int) (($itemStat?->total_quantity ?? 0) + ($legacyStat?->total_quantity ?? 0));
            $pkg->total_owner_amount = (float) (($itemStat?->total_owner_amount ?? 0) + ($legacyStat?->total_owner_amount ?? 0));
            $pkg->total_printed_amount = (float) (($itemStat?->total_printed_amount ?? 0) + ($legacyStat?->total_printed_amount ?? 0));
            $pkg->total_reseller_profit = (float) (($itemStat?->total_reseller_profit ?? 0) + ($legacyStat?->total_reseller_profit ?? 0));
            $pkg->pending_quantity = (int) (($pendingItem?->pending_quantity ?? 0) + ($pendingLegacy?->pending_quantity ?? 0));
        }

        // Opsi A: Hanya tampilkan paket yang ada penjualannya (> 0 lembar) pada periode ini,
        // diurutkan dari yang paling laku (terbanyak terjual) di posisi paling atas.
        $packagesBreakdown = $allPackages->filter(fn($p) => ($p->total_quantity ?? 0) > 0)
            ->sortByDesc('total_quantity')
            ->values();

        // 1. Data Pemasukan Voucher (10 Transaksi Terbaru)
        $recentOrders = (clone $orderQuery)->with(['tenant', 'package', 'items.package'])->latest('confirmed_at')->limit(10)->get();

        // 2. Data Pemasukan Kas Lain (Terpisah dari voucher)
        $otherIncomes = (clone $incomeQuery)->with('creator')->latest('income_date')->limit(10)->get();
        $otherIncomesCount = (clone $incomeQuery)->count();

        // 3. Data Beban Pengeluaran Kas (10 Terakhir)
        $recentExpenses = (clone $expenseQuery)->with('creator')->latest('expense_date')->limit(10)->get();
        $recentExpensesCount = (clone $expenseQuery)->count();

        // 4. Analisis Peringkat: Paket Paling Laku & Warung Penjualan Terbanyak (Periode Terpilih)
        // A. Peringkat Paket Paling Laku
        $bestSellingPackage = $packagesBreakdown->first();
        $topPackagesList = $packagesBreakdown->take(3);

        // B. Peringkat Mitra Warung Penjualan Terbanyak (Berdasarkan jumlah lembar disetor lunas)
        $tenantsRankQuery = User::where('role', 'tenant')
            ->where('username', '!=', 'pelanggan_langsung')
            ->withCount(['orders as orders_count' => function ($q) use ($rangeType, $startDate, $endDate, $selectedYear, $selectedMonth, $selectedPackage) {
                $q->where('status', 'lunas');
                if ($selectedPackage) {
                    $q->where(function ($sub) use ($selectedPackage) {
                        $sub->where('package_id', $selectedPackage)
                            ->orWhereHas('items', fn($iq) => $iq->where('package_id', $selectedPackage));
                    });
                }
                if ($rangeType === 'custom' && $startDate && $endDate) {
                    $q->whereBetween('confirmed_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                } else {
                    if ($selectedYear) $q->whereYear('confirmed_at', $selectedYear);
                    if ($selectedMonth && $selectedMonth !== 'all') $q->whereMonth('confirmed_at', $selectedMonth);
                }
            }])
            ->withSum(['orders as total_vouchers' => function ($q) use ($rangeType, $startDate, $endDate, $selectedYear, $selectedMonth, $selectedPackage) {
                $q->where('status', 'lunas');
                if ($selectedPackage) {
                    $q->where(function ($sub) use ($selectedPackage) {
                        $sub->where('package_id', $selectedPackage)
                            ->orWhereHas('items', fn($iq) => $iq->where('package_id', $selectedPackage));
                    });
                }
                if ($rangeType === 'custom' && $startDate && $endDate) {
                    $q->whereBetween('confirmed_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                } else {
                    if ($selectedYear) $q->whereYear('confirmed_at', $selectedYear);
                    if ($selectedMonth && $selectedMonth !== 'all') $q->whereMonth('confirmed_at', $selectedMonth);
                }
            }], 'quantity')
            ->withSum(['orders as total_settled_amount' => function ($q) use ($rangeType, $startDate, $endDate, $selectedYear, $selectedMonth, $selectedPackage) {
                $q->where('status', 'lunas');
                if ($selectedPackage) {
                    $q->where(function ($sub) use ($selectedPackage) {
                        $sub->where('package_id', $selectedPackage)
                            ->orWhereHas('items', fn($iq) => $iq->where('package_id', $selectedPackage));
                    });
                }
                if ($rangeType === 'custom' && $startDate && $endDate) {
                    $q->whereBetween('confirmed_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                } else {
                    if ($selectedYear) $q->whereYear('confirmed_at', $selectedYear);
                    if ($selectedMonth && $selectedMonth !== 'all') $q->whereMonth('confirmed_at', $selectedMonth);
                }
            }], 'total_owner_amount')
            ->get();

        $sortedTenants = $tenantsRankQuery->filter(fn($t) => ($t->total_vouchers ?? 0) > 0)
            ->sortByDesc('total_vouchers')
            ->values();
        $topPerformingTenant = $sortedTenants->first();
        $topTenantsList = $sortedTenants->take(3);

        $selectedTenantRank = null;
        if ($selectedTenant) {
            $rankIndex = $sortedTenants->search(fn($t) => $t->id == $selectedTenant);
            $selectedTenantRank = $rankIndex !== false ? ($rankIndex + 1) : null;
        }

        // Data filter dropdown
        $tenants = User::where('role', 'tenant')->orderBy('name')->get();
        $packages = VoucherPackage::all();

        return view('superadmin.finance.index', compact(
            'rangeType',
            'startDate',
            'endDate',
            'periodLabel',
            'selectedYear',
            'selectedMonth',
            'selectedTenant',
            'selectedTenantUser',
            'selectedPackage',
            'totalVouchersSold',
            'grossTurnover',
            'netOwnerRevenue',
            'resellerCommission',
            'otherIncome',
            'totalCashIn',
            'totalExpenses',
            'netProfit',
            'packagesBreakdown',
            'recentOrders',
            'otherIncomes',
            'otherIncomesCount',
            'recentExpenses',
            'recentExpensesCount',
            'tenants',
            'packages',
            'pendingOrdersCount',
            'pendingVouchersCount',
            'pendingGrossTurnover',
            'pendingOwnerAmount',
            'pendingResellerProfit',
            'allTimePendingOwnerAmount',
            'allTimePendingCount',
            'allTimePendingVouchersCount',
            'projectedVoucherRevenue',
            'projectedTotalCashIn',
            'projectedNetProfit',
            'projectedVouchersSold',
            'bestSellingPackage',
            'topPackagesList',
            'topPerformingTenant',
            'topTenantsList',
            'sortedTenants',
            'selectedTenantRank'
        ));
    }

    public function printReport(Request $request)
    {
        $rangeType = $request->input('range_type', 'month');
        $selectedYear = (int) $request->input('year', date('Y'));
        $selectedMonth = $request->input('month', date('m'));
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $selectedTenant = $request->input('tenant_id');
        $selectedPackage = $request->input('package_id');

        $orderQuery = Order::where('status', 'lunas');
        $incomeQuery = Income::query();
        $expenseQuery = Expense::query();

        if ($rangeType === 'custom' && $startDate && $endDate) {
            $orderQuery->whereBetween('confirmed_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            $incomeQuery->whereBetween('income_date', [$startDate, $endDate]);
            $expenseQuery->whereBetween('expense_date', [$startDate, $endDate]);
            $periodLabel = Carbon::parse($startDate)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($endDate)->translatedFormat('d F Y');
        } else {
            $rangeType = 'month';
            if ($selectedYear) {
                $orderQuery->whereYear('confirmed_at', $selectedYear);
                $incomeQuery->whereYear('income_date', $selectedYear);
                $expenseQuery->whereYear('expense_date', $selectedYear);
            }
            if ($selectedMonth && $selectedMonth !== 'all') {
                $orderQuery->whereMonth('confirmed_at', $selectedMonth);
                $incomeQuery->whereMonth('income_date', $selectedMonth);
                $expenseQuery->whereMonth('expense_date', $selectedMonth);
                $periodLabel = Carbon::create($selectedYear, (int)$selectedMonth)->translatedFormat('F Y');
            } else {
                $periodLabel = 'Tahun ' . $selectedYear;
            }
        }

        if ($selectedTenant) {
            $orderQuery->where('tenant_id', $selectedTenant);
        }
        if ($selectedPackage) {
            $orderQuery->where(function ($q) use ($selectedPackage) {
                $q->where('package_id', $selectedPackage)
                  ->orWhereHas('items', fn($iq) => $iq->where('package_id', $selectedPackage));
            });
        }

        // Totals
        $totalVouchersSold = (int) $orderQuery->sum('quantity');
        $grossTurnover = (float) $orderQuery->sum('total_printed_amount');
        $netOwnerRevenue = (float) $orderQuery->sum('total_owner_amount');
        $resellerCommission = (float) $orderQuery->sum('total_reseller_profit');
        $otherIncome = (float) $incomeQuery->sum('amount');
        $totalExpenses = (float) $expenseQuery->sum('amount');
        $totalCashIn = $netOwnerRevenue + $otherIncome;
        $netProfit = $totalCashIn - $totalExpenses;

        // All Items for Full Report
        $orders = (clone $orderQuery)->with(['tenant', 'package', 'items.package'])->latest('confirmed_at')->get();
        $incomes = (clone $incomeQuery)->with('creator')->latest('income_date')->get();
        $expenses = (clone $expenseQuery)->with('creator')->latest('expense_date')->get();

        $appName = Setting::get('app_name', 'VoucherNet RT/RW');
        $adminPhone = Setting::get('admin_whatsapp_number', '0812-3456-7890');
        $selectedTenantUser = $selectedTenant ? User::find($selectedTenant) : null;

        return view('superadmin.finance.print', compact(
            'periodLabel',
            'rangeType',
            'startDate',
            'endDate',
            'selectedTenantUser',
            'totalVouchersSold',
            'grossTurnover',
            'netOwnerRevenue',
            'resellerCommission',
            'otherIncome',
            'totalCashIn',
            'totalExpenses',
            'netProfit',
            'orders',
            'incomes',
            'expenses',
            'appName',
            'adminPhone'
        ));
    }

    public function expenses(Request $request)
    {
        $query = Expense::with('creator');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('month')) {
            $query->whereMonth('expense_date', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('expense_date', $request->year);
        }

        $expenses = $query->latest('expense_date')->paginate(15);
        $totalAmount = (clone $query)->sum('amount');

        $defaultExpenseCategories = [
            'Operasional & Konsumsi',
            'Bensin & Transportasi',
            'Kertas Thermal & Tinta',
            'ISP Bandwidth',
            'Listrik & Token PLN',
            'Perangkat / Router / AP',
            'Kabel FO & Aksesoris',
            'Gaji & Upah',
            'Lain-lain',
        ];
        $existingCategories = Expense::distinct()->pluck('category')->filter()->toArray();
        $categories = array_values(array_unique(array_merge($defaultExpenseCategories, $existingCategories)));

        return view('superadmin.finance.expenses', compact('expenses', 'totalAmount', 'categories'));
    }

    public function storeExpense(Request $request)
    {
        if ($request->filled('amount')) {
            $request->merge([
                'amount' => str_replace(['.', ','], '', (string) $request->input('amount')),
            ]);
        }
        if ($request->filled('category')) {
            $request->merge([
                'category' => trim((string) $request->input('category')),
            ]);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:1'],
            'expense_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'receipt_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt_file')) {
            $receiptPath = $request->file('receipt_file')->store('receipts', 'public');
        }

        $expenseNumber = 'EXP-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        Expense::create([
            'expense_number' => $expenseNumber,
            'title' => $validated['title'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'notes' => $validated['notes'] ?? null,
            'receipt_file' => $receiptPath,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Pengeluaran kas berhasil dicatat!');
    }

    public function destroyExpense(Expense $expense)
    {
        $user = Auth::user();
        if ($user->isSuperAdmin() || $expense->created_by === $user->id) {
            $expense->delete();
            return back()->with('success', 'Catatan pengeluaran berhasil dihapus.');
        }

        return back()->with('error', 'Hanya pembuat catatan atau Super Admin yang dapat menghapus pengeluaran ini.');
    }

    public function incomes(Request $request)
    {
        $query = Income::with('creator');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('month')) {
            $query->whereMonth('income_date', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('income_date', $request->year);
        }

        $incomes = $query->latest('income_date')->paginate(15);
        $totalAmount = (clone $query)->sum('amount');

        $defaultIncomeCategories = [
            'Iuran Bulanan',
            'Pasang Baru',
            'Sewa Port / Tiang',
            'Penjualan Aksesoris',
            'Lain-lain',
        ];
        $existingCategories = Income::distinct()->pluck('category')->filter()->toArray();
        $categories = array_values(array_unique(array_merge($defaultIncomeCategories, $existingCategories)));

        return view('superadmin.finance.incomes', compact('incomes', 'totalAmount', 'categories'));
    }

    public function storeIncome(Request $request)
    {
        if ($request->filled('amount')) {
            $request->merge([
                'amount' => str_replace(['.', ','], '', (string) $request->input('amount')),
            ]);
        }
        if ($request->filled('category')) {
            $request->merge([
                'category' => trim((string) $request->input('category')),
            ]);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:1'],
            'income_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $incomeNumber = 'INC-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        Income::create([
            'income_number' => $incomeNumber,
            'title' => $validated['title'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'income_date' => $validated['income_date'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Pemasukan kas berhasil dicatat!');
    }

    public function destroyIncome(Income $income)
    {
        $income->delete();
        return back()->with('success', 'Catatan pemasukan berhasil dihapus.');
    }
}
