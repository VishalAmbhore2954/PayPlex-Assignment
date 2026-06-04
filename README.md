# Task Reminder System

## Setup

### 1. Clone the Repository

```bash
git clone https://github.com/VishalAmbhore2954/PayPlex-Assignment.git
cd <project-folder>
```

```bash
cd PayPlex-Assignment
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

Copy the example environment file:

```bash
cp .env.example .env
```

Update the database credentials and SMTP configuration in `.env`.

### 4. Configure Timezone

In `.env`:

```env
APP_TIMEZONE=Asia/Kolkata
```

In `config/app.php`:

```php
'timezone' => env('APP_TIMEZONE', 'UTC'),
```

Clear configuration cache:

```bash
php artisan optimize:clear
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Run Migrations

```bash
php artisan migrate
```

### 7. Start the Application

```bash
php artisan serve
```

### 8. Start Queue Worker

```bash
php artisan queue:work
```

### 9. Start Scheduler

```bash
php artisan schedule:work
```

---

## Mail Configuration

Configure SMTP credentials in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=<smtp_host>
MAIL_PORT=<smtp_port>
MAIL_USERNAME=<smtp_username>
MAIL_PASSWORD=<smtp_password>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=<from_email>
MAIL_FROM_NAME="${APP_NAME}"
```

For local testing, Mailtrap SMTP can be used.

---

## Testing

### Daily Reminder

Create a task due tomorrow and run:

```bash
php artisan app:send-task-reminders
```

This command queues reminder emails for all tasks due tomorrow.

---

### Upcoming Reminder (15 Minutes Before Due Time)

Create a task with `due_at` set to 15 minutes from the current time and run:

```bash
php artisan app:send-upcoming-task-reminder-command
```

This command queues reminder emails for tasks that are due within the next 15 minutes.

---

## Automated Scheduling

The application includes Laravel Scheduler configuration for:

* Daily task reminders
* 15-minute task reminders

To enable automatic execution, keep both processes running:

```bash
php artisan schedule:work
```

```bash
php artisan queue:work
```

---

## Scheduler Configuration

### Daily Reminder

Runs every day at 12:00 AM.

```php
Schedule::command('tasks:daily-reminder')
    ->dailyAt('00:00');
```

### Upcoming Reminder

Runs every minute and sends reminders for tasks due in the next 15 minutes.

```php
Schedule::command('tasks:upcoming-reminder')
    ->everyMinute();
```
