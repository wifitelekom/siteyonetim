<?php

namespace App\Console\Commands;

use App\Services\TemplateService;
use Illuminate\Console\Command;

class GenerateMonthlyCharges extends Command
{
    protected $signature = 'charges:generate-monthly {--period= : Period in YYYY-MM format (defaults to current month)}';

    protected $description = 'Aktif aidat sablonlarindan aylik tahakkuk uretir';

    public function handle(TemplateService $templateService): int
    {
        $period = $this->option('period') ?? now()->format('Y-m');

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
            $this->error("Gecersiz donem formati: {$period}. YYYY-MM formatinda olmali.");
            return Command::FAILURE;
        }

        $this->info("Donem: {$period} icin aylik tahakkuklar uretiliyor...");

        try {
            $count = $templateService->generateMonthlyCharges($period);

            $this->info("Islem tamamlandi:");
            $this->info("  - Uretilen tahakkuk: {$count}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Hata olustu: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
