<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Task;
use App\Jobs\SendTaskReminderJob;

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
            ->get();

        foreach ($tasks as $task) {

            SendTaskReminderJob::dispatch($task->user, $task);

            $task->update([
                'reminder_sent_at' => now()
            ]);
        }

        $this->info("Processed: " . $tasks->count());
    }
}
