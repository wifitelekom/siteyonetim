<?php

namespace App\Services;

use App\Models\Apartment;
use App\Models\CashAccount;
use App\Models\Charge;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\TemplateAidat;
use App\Models\TemplateExpense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    private const CACHE_TTL_MINUTES = 5;

    public function getSummary(?User $user = null): array
    {
        $user ??= auth()->user();

        if (!$user instanceof User) {
            throw new \RuntimeException('Authenticated user is required.');
        }

        $version = (int) Cache::get(self::siteVersionKey((int) $user->site_id), 1);
        $cacheKey = "dashboard_summary_{$user->site_id}_{$user->id}_v{$version}";

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($user) {
            return $this->buildSummary($user);
        });
    }

    /**
     * Invalidate dashboard cache for a given site.
     */
    public static function clearCache(int $siteId): void
    {
        if ($siteId <= 0) {
            return;
        }

        $versionKey = self::siteVersionKey($siteId);
        $currentVersion = (int) Cache::get($versionKey, 1);
        Cache::forever($versionKey, $currentVersion + 1);
    }

    private static function siteVersionKey(int $siteId): string
    {
        return "dashboard_summary_version_{$siteId}";
    }

    private function buildSummary(User $user): array
    {
        $isAdmin = $user->hasAnyRole(['admin', 'super-admin']);
        $apartmentIds = (!$isAdmin && $user->hasAnyRole(['owner', 'tenant']))
            ? $user->apartment_ids
            : null;
        $vendorId = (!$isAdmin && $user->hasRole('vendor') && $user->vendor)
            ? $user->vendor->id
            : null;

        $today = Carbon::today()->toDateString();
        $monthStart = Carbon::today()->startOfMonth()->toDateString();
        $monthEnd = Carbon::today()->endOfMonth()->toDateString();

        // Helper: scope charge queries for residents
        $scopeCharge = function ($query) use ($apartmentIds) {
            return $apartmentIds !== null ? $query->whereIn('apartment_id', $apartmentIds) : $query;
        };

        // Helper: scope expense queries for vendors
        $scopeExpense = function ($query) use ($vendorId) {
            return $vendorId !== null ? $query->where('vendor_id', $vendorId) : $query;
        };

        // Receivables: admin or resident
        $receivables = null;
        if ($isAdmin || $apartmentIds !== null) {
            $receivables = [
                'not_due' => (float) $scopeCharge(Charge::unpaid()->where('due_date', '>', $today))->sum(\DB::raw('amount - paid_amount')),
                'due_today' => (float) $scopeCharge(Charge::dueToday())->sum(\DB::raw('amount - paid_amount')),
                'overdue' => (float) $scopeCharge(Charge::overdue())->sum(\DB::raw('amount - paid_amount')),
            ];
            $receivables['total'] = $receivables['not_due'] + $receivables['due_today'] + $receivables['overdue'];
            $receivables['count'] = $scopeCharge(Charge::unpaid())->count();
        }

        // Payables: admin or vendor
        $payables = null;
        if ($isAdmin || $vendorId !== null) {
            $payables = [
                'not_due' => (float) $scopeExpense(Expense::whereColumn('paid_amount', '<', 'amount')->where('due_date', '>', $today))->sum(\DB::raw('amount - paid_amount')),
                'due_today' => (float) $scopeExpense(Expense::dueToday())->sum(\DB::raw('amount - paid_amount')),
                'overdue' => (float) $scopeExpense(Expense::overdue())->sum(\DB::raw('amount - paid_amount')),
            ];
            $payables['total'] = $payables['not_due'] + $payables['due_today'] + $payables['overdue'];
            $payables['vendor_count'] = $scopeExpense(Expense::whereColumn('paid_amount', '<', 'amount'))->distinct('vendor_id')->count('vendor_id');
        }

        // Cash accounts: admin only
        $cashAccounts = null;
        $totalCash = null;
        if ($isAdmin) {
            $cashAccountQuery = CashAccount::query()
                ->where('is_active', true)
                ->withComputedBalance();

            $cashAccounts = $cashAccountQuery->get()->map(function ($account) {
                return [
                    'id' => $account->id,
                    'name' => $account->name,
                    'type' => $account->type,
                    'balance' => $account->balance,
                ];
            });
            $totalCash = $cashAccounts->sum('balance');
        }

        // Recent transactions
        $recentReceipts = collect();
        $recentPayments = collect();

        if ($isAdmin || $apartmentIds !== null) {
            $receiptQuery = Receipt::with('apartment')->orderByDesc('paid_at')->limit(10);
            if ($apartmentIds !== null) {
                $receiptQuery->whereIn('apartment_id', $apartmentIds);
            }
            $recentReceipts = $receiptQuery->get()->map(function ($r) {
                return [
                    'id' => $r->id,
                    'date' => $r->paid_at,
                    'type' => 'receipt',
                    'description' => 'Tahsilat' . ($r->apartment ? ' - ' . $r->apartment->full_label : ''),
                    'amount' => (float) $r->total_amount,
                    'receipt_no' => $r->receipt_no,
                ];
            });
        }

        if ($isAdmin || $vendorId !== null) {
            $paymentQuery = Payment::with('vendor')->orderByDesc('paid_at')->limit(10);
            if ($vendorId !== null) {
                $paymentQuery->where('vendor_id', $vendorId);
            }
            $recentPayments = $paymentQuery->get()->map(function ($p) {
                return [
                    'id' => $p->id,
                    'date' => $p->paid_at,
                    'type' => 'payment',
                    'description' => 'Ödeme' . ($p->vendor ? ' - ' . $p->vendor->name : ''),
                    'amount' => (float) $p->total_amount,
                    'receipt_no' => null,
                ];
            });
        }

        $recentTransactions = $recentReceipts->concat($recentPayments)
            ->sortByDesc('date')
            ->take(10)
            ->values();

        $monthlyReceiptCount = null;
        if ($isAdmin || $apartmentIds !== null) {
            $receiptCountQuery = Receipt::whereBetween('paid_at', [$monthStart, $monthEnd]);
            if ($apartmentIds !== null) {
                $receiptCountQuery->whereIn('apartment_id', $apartmentIds);
            }
            $monthlyReceiptCount = $receiptCountQuery->count();
        }

        // Templates, monthly trend, collection rate: admin only
        $aidatTemplates = null;
        $aidatTemplatesTotal = null;
        $expenseTemplates = null;
        $expenseTemplatesTotal = null;
        $monthlyTrend = null;
        $collectionRate = null;
        $timeline = null;

        if ($isAdmin) {
            // Template counts: 4 queries → 2 queries using conditional count
            $aidatCounts = TemplateAidat::selectRaw('COUNT(*) as total, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')->first();
            $aidatTemplates = (int) $aidatCounts->active;
            $aidatTemplatesTotal = (int) $aidatCounts->total;

            $expenseCounts = TemplateExpense::selectRaw('COUNT(*) as total, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')->first();
            $expenseTemplates = (int) $expenseCounts->active;
            $expenseTemplatesTotal = (int) $expenseCounts->total;

            // Monthly trend (last 6 months): 12 queries → 2 queries
            $sixMonthsAgo = Carbon::today()->subMonths(5)->startOfMonth()->toDateString();
            $trendEnd = Carbon::today()->endOfMonth()->toDateString();

            $incomeByMonth = Receipt::selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as month, SUM(total_amount) as total")
                ->whereBetween('paid_at', [$sixMonthsAgo, $trendEnd])
                ->groupByRaw("DATE_FORMAT(paid_at, '%Y-%m')")
                ->pluck('total', 'month');

            $expenseByMonth = Payment::selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as month, SUM(total_amount) as total")
                ->whereBetween('paid_at', [$sixMonthsAgo, $trendEnd])
                ->groupByRaw("DATE_FORMAT(paid_at, '%Y-%m')")
                ->pluck('total', 'month');

            $monthlyTrend = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::today()->subMonths($i);
                $key = $month->format('Y-m');
                $monthlyTrend[] = [
                    'month' => $month->translatedFormat('M Y'),
                    'income' => (float) ($incomeByMonth[$key] ?? 0),
                    'expense' => (float) ($expenseByMonth[$key] ?? 0),
                ];
            }

            // Collection rate for current month (reuse income data from trend)
            $currentMonthKey = Carbon::today()->format('Y-m');
            $monthlyChargeTotal = (float) Charge::whereBetween('due_date', [$monthStart, $monthEnd])->sum('amount');
            $monthlyCollected = (float) ($incomeByMonth[$currentMonthKey] ?? 0);
            $collectionRate = $monthlyChargeTotal > 0 ? round(($monthlyCollected / $monthlyChargeTotal) * 100) : 0;
        }

        // Timeline: upcoming 30 days
        if ($isAdmin || $apartmentIds !== null || $vendorId !== null) {
            $upcomingEnd = Carbon::today()->addDays(30)->toDateString();
            $timeline = collect();

            // Upcoming charges (admin + residents)
            if ($isAdmin || $apartmentIds !== null) {
                $chargeQuery = $scopeCharge(Charge::unpaid()->whereBetween('due_date', [$today, $upcomingEnd]))
                    ->with('apartment')
                    ->orderBy('due_date')
                    ->limit(15);

                $timeline = $timeline->concat($chargeQuery->get()->map(function ($c) {
                    return [
                        'uid' => 'charge-' . $c->id,
                        'date' => $c->due_date->format('Y-m-d'),
                        'date_display' => $c->due_date->format('d.m.Y'),
                        'type' => 'receivable',
                        'title' => 'Tahsilat',
                        'subtitle' => $c->apartment?->full_label ?? $c->description,
                        'amount' => number_format($c->remaining, 2, ',', '.') . ' ₺',
                        'dot_class' => 'dot-success',
                    ];
                }));
            }

            // Upcoming expenses (admin + vendors)
            if ($isAdmin || $vendorId !== null) {
                $expenseQuery = $scopeExpense(Expense::whereColumn('paid_amount', '<', 'amount')->whereBetween('due_date', [$today, $upcomingEnd]))
                    ->with('vendor')
                    ->orderBy('due_date')
                    ->limit(15);

                $timeline = $timeline->concat($expenseQuery->get()->map(function ($e) {
                    return [
                        'uid' => 'expense-' . $e->id,
                        'date' => $e->due_date->format('Y-m-d'),
                        'date_display' => $e->due_date->format('d.m.Y'),
                        'type' => 'payable',
                        'title' => 'Ödeme',
                        'subtitle' => $e->vendor?->name ?? $e->description,
                        'amount' => number_format($e->remaining, 2, ',', '.') . ' ₺',
                        'dot_class' => 'dot-danger',
                    ];
                }));
            }

            // Past receipts for timeline (admin + residents)
            if ($isAdmin || $apartmentIds !== null) {
                $pastReceiptQuery = Receipt::with('apartment')->orderByDesc('paid_at')->limit(5);
                if ($apartmentIds !== null) {
                    $pastReceiptQuery->whereIn('apartment_id', $apartmentIds);
                }

                $timeline = $timeline->concat($pastReceiptQuery->get()->map(function ($r) {
                    return [
                        'uid' => 'receipt-' . $r->id,
                        'date' => Carbon::parse($r->paid_at)->format('Y-m-d'),
                        'date_display' => Carbon::parse($r->paid_at)->format('d.m.Y'),
                        'type' => 'past_receipt',
                        'title' => 'Tahsilatlar',
                        'subtitle' => $r->apartment?->full_label ?? 'Tahsilat',
                        'amount' => number_format($r->total_amount, 2, ',', '.') . ' ₺',
                        'dot_class' => 'dot-info',
                    ];
                }));
            }

            $timeline = $timeline->sortBy('date')->values();
        }

        // Admin-only: apartment/resident stats, daily cash flow
        $apartmentStats = null;
        $dailyCashFlow = null;

        if ($isAdmin) {
            $apartmentStats = [
                'total' => Apartment::count(),
                'active' => Apartment::where('is_active', true)->count(),
                'total_residents' => \DB::table('apartment_user')->count(),
            ];

            // 30-day daily cash flow
            $thirtyDaysAgo = Carbon::today()->subDays(29)->toDateString();
            $dailyIncome = Receipt::selectRaw("DATE(paid_at) as day, SUM(total_amount) as total")
                ->whereBetween('paid_at', [$thirtyDaysAgo, $today])
                ->groupByRaw("DATE(paid_at)")
                ->pluck('total', 'day');

            $dailyExpense = Payment::selectRaw("DATE(paid_at) as day, SUM(total_amount) as total")
                ->whereBetween('paid_at', [$thirtyDaysAgo, $today])
                ->groupByRaw("DATE(paid_at)")
                ->pluck('total', 'day');

            $dailyCashFlow = [];
            for ($i = 29; $i >= 0; $i--) {
                $day = Carbon::today()->subDays($i);
                $dayKey = $day->toDateString();
                $dailyCashFlow[] = [
                    'date' => $day->format('d.m'),
                    'income' => (float) ($dailyIncome[$dayKey] ?? 0),
                    'expense' => (float) ($dailyExpense[$dayKey] ?? 0),
                ];
            }
        }

        return [
            'receivables' => $receivables,
            'payables' => $payables,
            'cashAccounts' => $cashAccounts,
            'totalCash' => $totalCash,
            'recentTransactions' => $recentTransactions,
            'monthlyReceiptCount' => $monthlyReceiptCount,
            'aidatTemplates' => $aidatTemplates,
            'aidatTemplatesTotal' => $aidatTemplatesTotal,
            'expenseTemplates' => $expenseTemplates,
            'expenseTemplatesTotal' => $expenseTemplatesTotal,
            'timeline' => $timeline,
            'monthlyTrend' => $monthlyTrend,
            'collectionRate' => $collectionRate,
            'apartmentStats' => $apartmentStats,
            'dailyCashFlow' => $dailyCashFlow,
        ];
    }
}
