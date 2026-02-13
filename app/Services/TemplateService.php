<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\Expense;
use App\Models\Site;
use App\Models\TemplateAidat;
use App\Models\TemplateExpense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TemplateService
{
    public function __construct(
        private ChargeService $chargeService,
        private ExpenseService $expenseService
    ) {}

    /**
     * Generate monthly charges from all active aidat templates.
     * Runs without auth context (scheduler), so bypasses global scopes.
     */
    public function generateMonthlyCharges(?string $period = null): int
    {
        $period = $period ?? now()->format('Y-m');
        $count = 0;

        $sites = Site::where('is_active', true)->get();

        foreach ($sites as $site) {
            $templates = TemplateAidat::withoutGlobalScope('site')
                ->where('site_id', $site->id)
                ->where('is_active', true)
                ->get();

            foreach ($templates as $template) {
                $apartments = $template->getTargetApartments();
                $dueDay = str_pad($template->due_day, 2, '0', STR_PAD_LEFT);

                // Handle months with fewer days
                $dueDate = $this->calculateDueDate($period, $template->due_day);

                foreach ($apartments as $apartment) {
                    try {
                        DB::transaction(function () use ($site, $template, $apartment, $period, $dueDate, &$count) {
                            $exists = Charge::withoutGlobalScope('site')
                                ->where('site_id', $site->id)
                                ->where('apartment_id', $apartment->id)
                                ->where('period', $period)
                                ->where('account_id', $template->account_id)
                                ->lockForUpdate()
                                ->exists();

                            if ($exists) {
                                return;
                            }

                            Charge::withoutGlobalScope('site')->create([
                                'site_id' => $site->id,
                                'apartment_id' => $apartment->id,
                                'account_id' => $template->account_id,
                                'charge_type' => 'aidat',
                                'period' => $period,
                                'due_date' => $dueDate,
                                'amount' => $template->amount,
                                'description' => $template->name . ' - ' . $period,
                            ]);
                            $count++;
                        });
                    } catch (\Illuminate\Database\QueryException $e) {
                        // Duplicate entry (1062) — skip silently
                        if ($e->errorInfo[1] !== 1062) {
                            throw $e;
                        }
                    }
                }
            }
        }

        Log::info("Aylık tahakkuk oluşturma tamamlandı: {$count} adet, dönem: {$period}");
        return $count;
    }

    /**
     * Generate recurring expenses from active templates.
     */
    public function generateRecurringExpenses(): int
    {
        $count = 0;
        $today = Carbon::today();

        $sites = Site::where('is_active', true)->get();

        foreach ($sites as $site) {
            $templates = TemplateExpense::withoutGlobalScope('site')
                ->where('site_id', $site->id)
                ->where('is_active', true)
                ->get();

            foreach ($templates as $template) {
                if (!$template->shouldGenerate()) {
                    continue;
                }

                $dueDate = $this->calculateDueDate($today->format('Y-m'), $template->due_day);

                DB::transaction(function () use ($site, $template, $dueDate, $today, &$count) {
                    Expense::withoutGlobalScope('site')->create([
                        'site_id' => $site->id,
                        'vendor_id' => $template->vendor_id,
                        'account_id' => $template->account_id,
                        'expense_date' => $today,
                        'due_date' => $dueDate,
                        'amount' => $template->amount,
                        'description' => $template->name . ' - ' . $today->format('Y-m'),
                    ]);

                    $template->update(['last_generated_at' => $today]);
                    $count++;
                });
            }
        }

        Log::info("Tekrarlanan gider oluşturma tamamlandı: {$count} adet");
        return $count;
    }

    private function calculateDueDate(string $period, int $dueDay): string
    {
        $year = (int) substr($period, 0, 4);
        $month = (int) substr($period, 5, 2);
        $maxDay = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $day = min($dueDay, $maxDay);

        return Carbon::createFromDate($year, $month, $day)->toDateString();
    }
}
