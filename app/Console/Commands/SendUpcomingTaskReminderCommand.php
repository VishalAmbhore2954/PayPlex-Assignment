<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:send-upcoming-task-reminder-command')]
#[Description('Command description')]
class SendUpcomingTaskReminderCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $tasks = Task::with('user')
            ->whereBetween(
                'due_at',
                [
                    now()->addMinutes(15)->startOfMinute(),
                    now()->addMinutes(15)->endOfMinute()
                ]
            )
            ->get();

        foreach ($tasks as $task) {

            SendTaskReminderJob::dispatch(
                $task->user,
                $task
            );
        }
    }
}
