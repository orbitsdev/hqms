<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
         $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'personal-info-complete' => \App\Http\Middleware\EnsurePersonalInformationIsComplete::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Clean up old queue data (runs daily at midnight)
        $schedule->call(function (): void {
            $deleted = DB::table('queues')
                ->where('queue_date', '<', now()->subDays(90))
                ->delete();

            if ($deleted > 0) {
                Log::info("Scheduled: Deleted {$deleted} old queue records");
            }
        })->daily()->at('00:00')->name('cleanup-old-queues');

        // Prune old notifications (runs daily at 1 AM)
        $schedule->call(function (): void {
            $deleted = DB::table('notifications')
                ->whereNotNull('read_at')
                ->where('read_at', '<', now()->subDays(30))
                ->delete();

            if ($deleted > 0) {
                Log::info("Scheduled: Pruned {$deleted} old notifications");
            }
        })->daily()->at('01:00')->name('prune-notifications');

        // Clean up old SMS logs (runs weekly on Sunday at 2 AM)
        $schedule->call(function (): void {
            $deleted = DB::table('sms_logs')
                ->where('status', 'sent')
                ->where('created_at', '<', now()->subDays(60))
                ->delete();

            if ($deleted > 0) {
                Log::info("Scheduled: Cleaned {$deleted} old SMS logs");
            }
        })->weekly()->sundays()->at('02:00')->name('cleanup-sms-logs');

        // Clear expired cache (runs daily at 3 AM)
        $schedule->command('cache:prune-stale-tags')->daily()->at('03:00');

        // Optimize application (runs daily at 4 AM) - production only
        $schedule->command('optimize')->daily()->at('04:00')->environments(['production']);

        // Health check log (runs every hour)
        $schedule->call(function (): void {
            Log::channel('single')->info('Scheduler health check: OK');
        })->hourly()->name('health-check');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
