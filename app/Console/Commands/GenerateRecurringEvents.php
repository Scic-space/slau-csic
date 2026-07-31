<?php

namespace App\Console\Commands;

use App\Services\RecurrenceGenerator;
use Illuminate\Console\Command;

class GenerateRecurringEvents extends Command
{
    protected $signature = 'events:generate-recurring';

    protected $description = 'Generate future instances for recurring events';

    public function handle(RecurrenceGenerator $generator): int
    {
        $this->info('Generating recurring event instances...');

        $count = $generator->regenerateUpcoming();

        $this->info("Generated {$count} new event instances.");

        return Command::SUCCESS;
    }
}
