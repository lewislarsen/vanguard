<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BackupTask;
use Illuminate\Console\Command;

class ExecuteScheduledBackupTasksCommand extends Command
{
    protected $signature = 'vanguard:execute-scheduled-backup-tasks';

    protected $description = 'Executes scheduled backup tasks.';

    public function handle(): void
    {
        $this->components->info('Running scheduled backup tasks...');

        $tasks = BackupTask::ready()->get();

        $tasks->each(function ($task): void {

            if (! $task->eligibleToRunNow()) {
                $this->components->info(sprintf('Task %s is not eligible to run now.', $task->label));

                return;
            }

            if ($task->isWeekly()) {
                $task->updateScheduledWeeklyRun();
            }

            $task->run();

            $this->components->info(sprintf('Dispatching job for task %s...', $task->label));
        });
    }
}
