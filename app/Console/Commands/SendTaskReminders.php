<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Task;
use App\Jobs\SendTaskReminderJob;

#[Signature('app:send-task-reminders')]
#[Description('Command description')]
class SendTaskReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tasks = Task::whereDate('due_at',now()->addDay()->toDateString())->get();

        foreach($tasks as $task){
                SendTaskReminderJob::dispatch(
                $task->user,
                $task
            );
        }
    }
}
