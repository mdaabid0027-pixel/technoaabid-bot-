<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TelegramUser;
use App\Models\Key;
use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Display executive analytics dashboard with date filtering and claim tracking.
     */
    public function index(Request $request)
    {
        $period = $request->input('period', 'today');
        $customStart = $request->input('start_date');
        $customEnd = $request->input('end_date');

        $now = Carbon::now();
        $startDate = Carbon::today()->startOfDay();
        $endDate = Carbon::today()->endOfDay();
        $periodLabel = "Today's Overview";
        $prevStartDate = Carbon::yesterday()->startOfDay();
        $prevEndDate = Carbon::yesterday()->endOfDay();

        switch ($period) {
            case 'yesterday':
                $startDate = Carbon::yesterday()->startOfDay();
                $endDate = Carbon::yesterday()->endOfDay();
                $prevStartDate = Carbon::yesterday()->subDay()->startOfDay();
                $prevEndDate = Carbon::yesterday()->subDay()->endOfDay();
                $periodLabel = "Yesterday's Performance";
                break;

            case '7days':
                $startDate = Carbon::now()->subDays(6)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $prevStartDate = Carbon::now()->subDays(13)->startOfDay();
                $prevEndDate = Carbon::now()->subDays(7)->endOfDay();
                $periodLabel = "Last 7 Days";
                break;

            case '30days':
                $startDate = Carbon::now()->subDays(29)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $prevStartDate = Carbon::now()->subDays(59)->startOfDay();
                $prevEndDate = Carbon::now()->subDays(30)->endOfDay();
                $periodLabel = "Last 30 Days";
                break;

            case 'this_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfDay();
                $prevStartDate = Carbon::now()->subMonth()->startOfMonth();
                $prevEndDate = Carbon::now()->subMonth()->endOfMonth();
                $periodLabel = "This Month (" . $now->format('F Y') . ")";
                break;

            case 'all':
                $startDate = Carbon::createFromTimestamp(0);
                $endDate = Carbon::now()->endOfDay();
                $periodLabel = "All-Time Lifetime";
                break;

            case 'custom':
                if ($customStart && $customEnd) {
                    $startDate = Carbon::parse($customStart)->startOfDay();
                    $endDate = Carbon::parse($customEnd)->endOfDay();
                    $daysDiff = $startDate->diffInDays($endDate) + 1;
                    $prevStartDate = (clone $startDate)->subDays($daysDiff);
                    $prevEndDate = (clone $startDate)->subSecond();
                    $periodLabel = $startDate->format('d M Y') . " - " . $endDate->format('d M Y');
                }
                break;

            default:
                $period = 'today';
                break;
        }

        // 1. Period Gross Sales (Debit Transactions = Purchases)
        $periodSales = (float) Transaction::where('type', 'debit')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        $prevPeriodSales = (float) Transaction::where('type', 'debit')
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->sum('amount');

        // Sales Growth %
        $salesGrowth = null;
        if ($period !== 'all') {
            if ($prevPeriodSales > 0) {
                $salesGrowth = round((($periodSales - $prevPeriodSales) / $prevPeriodSales) * 100, 1);
            } elseif ($periodSales > 0) {
                $salesGrowth = 100.0;
            } else {
                $salesGrowth = 0.0;
            }
        }

        // Lifetime Gross Sales
        $lifetimeSales = (float) Transaction::where('type', 'debit')->sum('amount');

        // 2. Keys Sold in Period & Cost Calculations
        $soldKeysQuery = Key::where('is_sold', true)
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('sold_at', [$startDate, $endDate])
                  ->orWhere(function($sub) use ($startDate, $endDate) {
                      $sub->whereNull('sold_at')
                          ->whereBetween('updated_at', [$startDate, $endDate]);
                  });
            })
            ->with('product');

        $periodKeysSold = $soldKeysQuery->count();
        if ($periodKeysSold === 0 && $periodSales > 0) {
            $periodKeysSold = Transaction::where('type', 'debit')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
        }

        $soldKeys = $soldKeysQuery->get();
        $periodCost = (float) $soldKeys->sum(fn($k) => $k->product ? $k->product->getCostForDuration($k->duration) : 0);
        $periodNetProfit = $periodSales > $periodCost ? ($periodSales - $periodCost) : $periodSales;
        $profitMarginPercent = ($periodSales > 0) ? round(($periodNetProfit / $periodSales) * 100, 1) : 100.0;

        // All-Time Cost & Net Profit
        $allSoldKeys = Key::where('is_sold', true)->with('product')->get();
        $lifetimeCost = (float) $allSoldKeys->sum(fn($k) => $k->product ? $k->product->getCostForDuration($k->duration) : 0);
        $lifetimeNetProfit = $lifetimeSales > $lifetimeCost ? ($lifetimeSales - $lifetimeCost) : $lifetimeSales;
        $lifetimeKeysSold = Key::where('is_sold', true)->count();

        // 3. User Claims & Free Rewards (Spin Prizes + Referral Cashbacks Paid)
        $spinClaimsQuery = Transaction::where('type', 'credit')
            ->where(function($q) {
                $q->where('description', 'LIKE', '%Spin%')
                  ->orWhere('description', 'LIKE', '%Wheel%');
            });

        $periodSpinClaimsAmount = (float) (clone $spinClaimsQuery)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');
        $periodSpinClaimsCount = (clone $spinClaimsQuery)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $referralClaimsQuery = Transaction::where('type', 'credit')
            ->where('description', 'LIKE', '%Referral%');

        $periodReferralClaimsAmount = (float) (clone $referralClaimsQuery)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');
        $periodReferralClaimsCount = (clone $referralClaimsQuery)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $periodTotalClaimsAmount = $periodSpinClaimsAmount + $periodReferralClaimsAmount;
        $periodTotalClaimsCount = $periodSpinClaimsCount + $periodReferralClaimsCount;

        // Lifetime Claims Paid Out
        $lifetimeSpinClaims = (float) (clone $spinClaimsQuery)->sum('amount');
        $lifetimeReferralClaims = (float) (clone $referralClaimsQuery)->sum('amount');
        $lifetimeTotalClaims = $lifetimeSpinClaims + $lifetimeReferralClaims;

        // 4. Stock & Customer Counts
        $totalKeysInStock = Key::where('is_sold', false)->count();
        $totalUsers = TelegramUser::count();
        $totalResellers = TelegramUser::where('is_reseller', true)->orWhereHas('customPrices')->count();
        $activeUsers = TelegramUser::where('balance', '>', 0)->count();

        // 5. Interactive Sales Chart Generation
        $chartLabels = [];
        $chartRevenue = [];
        $chartClaims = [];
        $chartKeysSold = [];

        $daysCount = $startDate->diffInDays($endDate);
        if ($daysCount <= 31 && $period !== 'all') {
            // Daily Chart
            $currentDay = clone $startDate;
            while ($currentDay <= $endDate) {
                $dayStr = $currentDay->format('Y-m-d');
                $chartLabels[] = $currentDay->format('d M');

                $dayRev = (float) Transaction::where('type', 'debit')
                    ->whereDate('created_at', $dayStr)
                    ->sum('amount');
                $chartRevenue[] = $dayRev;

                $dayClaim = (float) Transaction::where('type', 'credit')
                    ->where(function($q) {
                        $q->where('description', 'LIKE', '%Spin%')
                          ->orWhere('description', 'LIKE', '%Wheel%')
                          ->orWhere('description', 'LIKE', '%Referral%');
                    })
                    ->whereDate('created_at', $dayStr)
                    ->sum('amount');
                $chartClaims[] = $dayClaim;

                $dayKeys = Transaction::where('type', 'debit')
                    ->whereDate('created_at', $dayStr)
                    ->count();
                $chartKeysSold[] = $dayKeys;

                $currentDay->addDay();
            }
        } else {
            // Last 7 days fallback for general chart display
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $dateStr = $date->format('Y-m-d');
                $chartLabels[] = $date->format('d M');

                $chartRevenue[] = (float) Transaction::where('type', 'debit')
                    ->whereDate('created_at', $dateStr)
                    ->sum('amount');

                $chartClaims[] = (float) Transaction::where('type', 'credit')
                    ->where(function($q) {
                        $q->where('description', 'LIKE', '%Spin%')
                          ->orWhere('description', 'LIKE', '%Wheel%')
                          ->orWhere('description', 'LIKE', '%Referral%');
                    })
                    ->whereDate('created_at', $dateStr)
                    ->sum('amount');

                $chartKeysSold[] = Transaction::where('type', 'debit')
                    ->whereDate('created_at', $dateStr)
                    ->count();
            }
        }

        // 6. Game-by-Game Live Stock
        $products = Product::withCount(['keys as available_keys_count' => function ($query) {
            $query->where('is_sold', false);
        }])->get();

        // 7. Recent Transactions Feed
        $recentTransactions = Transaction::with('telegramUser')
            ->latest()
            ->take(10)
            ->get();

        // 8. Top Claimed / Rewarded Users (Anti-Fraud Monitoring)
        $topClaimers = TelegramUser::query()
            ->withSum(['transactions as spin_claims_total' => function($q) {
                $q->where('type', 'credit')->where(function($sub) {
                    $sub->where('description', 'LIKE', '%Spin%')->orWhere('description', 'LIKE', '%Wheel%');
                });
            }], 'amount')
            ->withSum(['transactions as referral_claims_total' => function($q) {
                $q->where('type', 'credit')->where('description', 'LIKE', '%Referral%');
            }], 'amount')
            ->withMax(['transactions as last_claim_date' => function($q) {
                $q->where('type', 'credit')->where(function($sub) {
                    $sub->where('description', 'LIKE', '%Spin%')
                        ->orWhere('description', 'LIKE', '%Wheel%')
                        ->orWhere('description', 'LIKE', '%Referral%');
                });
            }], 'created_at')
            ->having('spin_claims_total', '>', 0)
            ->orHaving('referral_claims_total', '>', 0)
            ->orderByDesc(DB::raw('COALESCE(spin_claims_total, 0) + COALESCE(referral_claims_total, 0)'))
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'period',
            'periodLabel',
            'customStart',
            'customEnd',
            'startDate',
            'endDate',
            'periodSales',
            'prevPeriodSales',
            'salesGrowth',
            'lifetimeSales',
            'periodCost',
            'periodNetProfit',
            'profitMarginPercent',
            'lifetimeCost',
            'lifetimeNetProfit',
            'periodKeysSold',
            'lifetimeKeysSold',
            'periodSpinClaimsAmount',
            'periodSpinClaimsCount',
            'periodReferralClaimsAmount',
            'periodReferralClaimsCount',
            'periodTotalClaimsAmount',
            'periodTotalClaimsCount',
            'lifetimeSpinClaims',
            'lifetimeReferralClaims',
            'lifetimeTotalClaims',
            'totalKeysInStock',
            'totalUsers',
            'totalResellers',
            'activeUsers',
            'chartLabels',
            'chartRevenue',
            'chartClaims',
            'chartKeysSold',
            'products',
            'recentTransactions',
            'topClaimers'
        ));
    }
}
