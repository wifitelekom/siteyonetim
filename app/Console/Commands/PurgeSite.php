<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Services\SitePurger;
use Illuminate\Console\Command;

class PurgeSite extends Command
{
    protected $signature = 'sites:purge {site : Site id or exact name} {--force : Skip confirmation}';

    protected $description = 'Permanently delete a site and all related data.';

    public function handle(SitePurger $purger): int
    {
        $input = $this->argument('site');

        $site = Site::withTrashed()
            ->where('id', $input)
            ->orWhere('name', $input)
            ->first();

        if (!$site) {
            $this->error('Site not found.');
            return self::FAILURE;
        }

        $label = sprintf('%s (#%d)', $site->name, $site->id);

        if (!$this->option('force')) {
            if (!$this->confirm("Permanently delete {$label} and all related data?")) {
                $this->info('Aborted.');
                return self::SUCCESS;
            }
        }

        $purger->purge($site);

        $this->info("Site deleted: {$label}");
        return self::SUCCESS;
    }
}
