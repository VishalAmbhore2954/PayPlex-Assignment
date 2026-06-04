<?php

use App\Mail\TaskMailReminder;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('daily task reminder command sends emails for tasks due tomorrow', function () {
    Mail::fake();

    // Create users
    $user = User::factory()->create();

    // Task due tomorrow (e.g. 24 hours from now)
    $taskTomorrow = Task::factory()->create([
        'user_id' => $user->id,
        'due_at' => now()->addDay(),
        'status' => 'pending',
    ]);

    // Task due today
    $taskToday = Task::factory()->create([
        'user_id' => $user->id,
        'due_at' => now(),
        'status' => 'pending',
    ]);

    // Task due in 2 days
    $taskInTwoDays = Task::factory()->create([
        'user_id' => $user->id,
        'due_at' => now()->addDays(2),
        'status' => 'pending',
    ]);

    // Run the daily reminder command
    $this->artisan('app:send-task-reminders')->assertExitCode(0);

    // Assert that the email was sent for tomorrow's task
    Mail::assertSent(TaskMailReminder::class, function ($mail) use ($taskTomorrow) {
        return $mail->task->id === $taskTomorrow->id;
    });

    // Assert that the email was not sent for today's or 2 days later task
    Mail::assertNotSent(TaskMailReminder::class, function ($mail) use ($taskToday, $taskInTwoDays) {
        return $mail->task->id === $taskToday->id || $mail->task->id === $taskInTwoDays->id;
    });
});

test('upcoming task reminder command sends emails for tasks due in the next 15 minutes', function () {
    Mail::fake();

    $user = User::factory()->create();

    // Task due in 10 minutes (upcoming)
    $taskUpcoming = Task::factory()->create([
        'user_id' => $user->id,
        'due_at' => now()->addMinutes(10),
        'status' => 'pending',
        'reminder_sent_at' => null,
    ]);

    // Task due in 20 minutes (not within 15 mins)
    $taskFar = Task::factory()->create([
        'user_id' => $user->id,
        'due_at' => now()->addMinutes(20),
        'status' => 'pending',
        'reminder_sent_at' => null,
    ]);

    // Run upcoming reminder command
    $this->artisan('app:send-upcoming-task-reminder-command')->assertExitCode(0);

    // Assert email sent for upcoming task
    Mail::assertSent(TaskMailReminder::class, function ($mail) use ($taskUpcoming) {
        return $mail->task->id === $taskUpcoming->id;
    });

    // Assert email not sent for far task
    Mail::assertNotSent(TaskMailReminder::class, function ($mail) use ($taskFar) {
        return $mail->task->id === $taskFar->id;
    });

    // Assert that reminder_sent_at was updated on the upcoming task
    $taskUpcoming->refresh();
    expect($taskUpcoming->reminder_sent_at)->not->toBeNull();
});

test('upcoming task reminder command does not send duplicate emails', function () {
    Mail::fake();

    $user = User::factory()->create();

    // Task due in 10 minutes, but reminder already sent
    $taskUpcomingAlreadySent = Task::factory()->create([
        'user_id' => $user->id,
        'due_at' => now()->addMinutes(10),
        'status' => 'pending',
        'reminder_sent_at' => now()->subMinutes(5),
    ]);

    // Run upcoming reminder command
    $this->artisan('app:send-upcoming-task-reminder-command')->assertExitCode(0);

    // Assert no email was sent
    Mail::assertNothingSent();
});
