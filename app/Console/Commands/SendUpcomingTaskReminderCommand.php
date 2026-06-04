<?php

namespace App\Console\Commands;

use App\Jobs\SendTaskReminderJob;
use App\Models\Task;
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
            ->where('due_at', '>', now())
            ->where('due_at', '<=', now()->addMinutes(15))
            ->whereNull('reminder_sent_at')
            ->get();

        foreach ($tasks as $task) {

            SendTaskReminderJob::dispatch($task->user, $task);

            $task->reminder_sent_at = now();
            $task->save();
        }

        $this->info('Processed: '.$tasks->count());
    }
}
