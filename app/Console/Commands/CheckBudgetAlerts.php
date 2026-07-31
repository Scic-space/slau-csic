<?php

namespace App\Console\Commands;

use App\Services\BudgetAlertService;
use Illuminate\Console\Command;

class CheckBudgetAlerts extends Command
{
    protected $signature = 'budget:check-alerts';

    protected $description = 'Check budget thresholds and send alerts to treasurers and presidents';

    public function handle(): int
    {
        $this->info('Checking budget alerts...');

        BudgetAlertService::checkBudgetAlerts();

        $this->info('Budget alerts checked successfully.');

        return Command::SUCCESS;
    }
}
