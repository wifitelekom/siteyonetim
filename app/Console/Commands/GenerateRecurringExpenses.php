<?php

namespace App\Console\Commands;

use App\Services\TemplateService;
use Illuminate\Console\Command;

class GenerateRecurringExpenses extends Command
{
    protected $signature = 'expenses:generate-recurring';

    protected $description = 'Aktif gider sablonlarindan periyodik gider uretir';

    public function handle(TemplateService $templateService): int
    {
        $this->info("Tekrarlanan giderler uretiliyor...");

        try {
            $count = $templateService->generateRecurringExpenses();

            $this->info("Islem tamamlandi:");
            $this->info("  - Uretilen gider: {$count}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Hata olustu: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
