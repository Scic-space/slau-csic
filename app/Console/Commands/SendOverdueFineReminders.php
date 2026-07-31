<?php

namespace App\Console\Commands;

use App\Models\Fine;
use App\Notifications\FineOverdueNotification;
use Illuminate\Console\Command;

class SendOverdueFineReminders extends Command
{
    protected $signature = 'fines:send-reminders {--type=overdue : Type of reminder (overdue|due-soon)}';

    protected $description = 'Send reminders for overdue fines or fines due soon';

    public function handle(): int
    {
        $type = $this->option('type');

        if ($type === 'overdue') {
            $fines = Fine::overdue()->with('user')->get();

            if ($fines->isEmpty()) {
                $this->info('No overdue fines to remind about.');

                return self::SUCCESS;
            }

            $sent = 0;
            foreach ($fines as $fine) {
                if ($fine->user) {
                    $fine->user->notify(new FineOverdueNotification($fine));
                    $sent++;
                }
            }

            $this->info("Sent {$sent} overdue fine reminder(s).");

            return self::SUCCESS;
        }

        if ($type === 'due-soon') {
            $fines = Fine::dueSoon()->with('user')->get();

            if ($fines->isEmpty()) {
                $this->info('No fines due soon to remind about.');

                return self::SUCCESS;
            }

            $sent = 0;
            foreach ($fines as $fine) {
                if ($fine->user) {
                    $fine->user->notify(new FineOverdueNotification($fine));
                    $sent++;
                }
            }

            $this->info("Sent {$sent} due-soon fine reminder(s).");

            return self::SUCCESS;
        }

        $this->error("Unknown reminder type: {$type}. Use 'overdue' or 'due-soon'.");

        return self::FAILURE;
    }
}
