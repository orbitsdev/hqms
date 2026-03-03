<?php

use App\Models\Appointment;
use App\Models\Queue;
use App\Notifications\GenericNotification;
use App\Services\QueueNumberService;
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
        // Auto-queue generation: create queue entries for today's confirmed appointments (runs daily at midnight)
        $schedule->call(function (): void {
            $queueService = app(QueueNumberService::class);

            $appointments = Appointment::query()
                ->where('status', 'confirmed')
                ->whereDate('appointment_date', today())
                ->with(['consultationType', 'user'])
                ->get();

            $queued = 0;

            foreach ($appointments as $appointment) {
                try {
                    $queue = $queueService->createQueueForAppointment($appointment);

                    // Notify patient with queue number
                    if ($appointment->user) {
                        $appointment->user->notify(new GenericNotification([
                            'type' => 'queue.assigned',
                            'title' => __('Queue Number Assigned'),
                            'message' => __('Your queue number for :type today is :queue.', [
                                'type' => $appointment->consultationType->name ?? 'consultation',
                                'queue' => $queue->formatted_number,
                            ]),
                            'appointment_id' => $appointment->id,
                            'queue_id' => $queue->id,
                            'queue_number' => $queue->formatted_number,
                            'sender_role' => 'system',
                        ]));
                    }

                    $queued++;
                } catch (\Exception $e) {
                    Log::error("Auto-queue failed for appointment #{$appointment->id}: {$e->getMessage()}");
                }
            }

            if ($queued > 0) {
                Log::info("Scheduled: Auto-queued {$queued} appointments for today");
            }
        })->daily()->at('00:00')->name('auto-queue-generation')->before(function (): void {
            Log::info('Scheduled: Starting auto-queue generation');
        });

        // No-show marking: mark yesterday's unserved appointments as no_show (runs daily at 06:00)
        $schedule->call(function (): void {
            $yesterday = today()->subDay();

            // Mark appointments that were approved but never served
            $noShowAppointments = Appointment::query()
                ->where('status', 'approved')
                ->whereDate('appointment_date', $yesterday)
                ->get();

            $marked = 0;

            foreach ($noShowAppointments as $appointment) {
                $appointment->update(['status' => 'no_show']);

                // Also mark queue entries as no_show
                Queue::query()
                    ->where('appointment_id', $appointment->id)
                    ->whereIn('status', ['waiting', 'called'])
                    ->update(['status' => 'no_show']);

                $marked++;
            }

            if ($marked > 0) {
                Log::info("Scheduled: Marked {$marked} appointments as no-show from {$yesterday->toDateString()}");
            }
        })->daily()->at('06:00')->name('mark-no-shows');

        // Morning reminders for today's queued patients (runs daily at 07:00)
        $schedule->call(function (): void {
            $appointments = Appointment::query()
                ->where('status', 'approved')
                ->whereDate('appointment_date', today())
                ->with(['consultationType', 'user', 'queue'])
                ->get();

            $reminded = 0;

            foreach ($appointments as $appointment) {
                if ($appointment->user && $appointment->queue) {
                    $appointment->user->notify(new GenericNotification([
                        'type' => 'queue.reminder',
                        'title' => __('Appointment Reminder'),
                        'message' => __('Reminder: Your queue number is :queue for :type today.', [
                            'queue' => $appointment->queue->formatted_number,
                            'type' => $appointment->consultationType->name ?? 'consultation',
                        ]),
                        'appointment_id' => $appointment->id,
                        'queue_id' => $appointment->queue->id,
                        'sender_role' => 'system',
                    ]));

                    $reminded++;
                }
            }

            if ($reminded > 0) {
                Log::info("Scheduled: Sent {$reminded} morning reminders");
            }
        })->daily()->at('07:00')->name('morning-reminders');

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
