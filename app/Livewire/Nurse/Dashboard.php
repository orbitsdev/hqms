<?php

namespace App\Livewire\Nurse;

use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\DoctorSchedule;
use App\Models\MedicalRecord;
use App\Models\Queue;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Dashboard extends Component
{
    /**
     * Refresh dashboard when queue updates come in via Echo.
     */
    #[On('echo-private:queue.staff,queue.updated')]
    public function refreshOnQueueUpdate(): void
    {
        // Component will automatically re-render
    }

    /** @return array<string, int> */
    public function getStatsProperty(): array
    {
        return [
            'pending_appointments' => Appointment::query()
                ->where('status', 'pending')
                ->count(),
            'today_appointments' => Appointment::query()
                ->whereDate('appointment_date', today())
                ->count(),
            'waiting_checkin' => Appointment::query()
                ->where('status', 'approved')
                ->whereDate('appointment_date', today())
                ->count(),
            'queue_waiting' => Queue::query()
                ->today()
                ->where('status', 'waiting')
                ->count(),
            'queue_serving' => Queue::query()
                ->today()
                ->where('status', 'serving')
                ->count(),
            'queue_completed' => Queue::query()
                ->today()
                ->where('status', 'completed')
                ->count(),
        ];
    }

    /**
     * Get doctors available today based on schedules.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function getDoctorsAvailableProperty(): \Illuminate\Support\Collection
    {
        $today = now();
        $dayOfWeek = $today->dayOfWeek;

        // Get doctors with regular schedules for today (day_of_week matches and is_available)
        $doctorIds = DoctorSchedule::query()
            ->where('schedule_type', 'regular')
            ->where('day_of_week', $dayOfWeek)
            ->where('is_available', true)
            ->pluck('user_id')
            ->unique();

        // Check for exceptions (leaves) today - where is_available is false
        $onLeaveIds = DoctorSchedule::query()
            ->where('schedule_type', 'exception')
            ->whereDate('date', $today)
            ->where('is_available', false)
            ->pluck('user_id');

        // Remove doctors on leave
        $availableDoctorIds = $doctorIds->diff($onLeaveIds);

        return User::query()
            ->role('doctor')
            ->whereIn('id', $availableDoctorIds)
            ->with(['personalInformation', 'consultationTypes'])
            ->get()
            ->map(fn (User $doctor) => [
                'id' => $doctor->id,
                'name' => $doctor->personalInformation?->full_name ?? $doctor->email,
                'specialties' => $doctor->consultationTypes->pluck('short_name')->join(', '),
                'is_serving' => Queue::query()
                    ->today()
                    ->where('doctor_id', $doctor->id)
                    ->where('status', 'serving')
                    ->exists(),
            ]);
    }

    /**
     * Get estimated wait times per consultation type.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function getWaitTimesProperty(): \Illuminate\Support\Collection
    {
        $types = ConsultationType::query()
            ->where('is_active', true)
            ->get();

        return $types->map(function (ConsultationType $type) {
            $waiting = Queue::query()
                ->today()
                ->where('consultation_type_id', $type->id)
                ->where('status', 'waiting')
                ->count();

            $serving = Queue::query()
                ->today()
                ->where('consultation_type_id', $type->id)
                ->where('status', 'serving')
                ->count();

            // Estimate based on average 10 minutes per patient
            $estimatedMinutes = $waiting * 10;

            return [
                'id' => $type->id,
                'name' => $type->name,
                'short_name' => $type->short_name,
                'waiting' => $waiting,
                'serving' => $serving,
                'estimated_minutes' => $estimatedMinutes,
            ];
        })->filter(fn ($item) => $item['waiting'] > 0 || $item['serving'] > 0);
    }

    /**
     * Get alerts requiring attention.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getAlertsProperty(): array
    {
        $alerts = [];

        // Pending appointments needing approval (older than 1 hour)
        $oldPending = Appointment::query()
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subHour())
            ->count();

        if ($oldPending > 0) {
            $alerts['pending'][] = [
                'type' => 'warning',
                'message' => __(':count pending appointments older than 1 hour', ['count' => $oldPending]),
                'action' => route('nurse.appointments', ['status' => 'pending']),
            ];
        }

        // Patients waiting too long (more than 30 minutes)
        $longWait = Queue::query()
            ->today()
            ->where('status', 'waiting')
            ->where('created_at', '<', now()->subMinutes(30))
            ->count();

        if ($longWait > 0) {
            $alerts['queue'][] = [
                'type' => 'warning',
                'message' => __(':count patients waiting more than 30 minutes', ['count' => $longWait]),
                'action' => route('nurse.queue'),
            ];
        }

        // Patients being served for too long (more than 20 minutes without vitals)
        $longServing = Queue::query()
            ->today()
            ->where('status', 'serving')
            ->where('serving_started_at', '<', now()->subMinutes(20))
            ->whereDoesntHave('medicalRecord', function ($q) {
                $q->whereNotNull('vital_signs_recorded_at');
            })
            ->count();

        if ($longServing > 0) {
            $alerts['serving'][] = [
                'type' => 'info',
                'message' => __(':count patients served 20+ min without vitals', ['count' => $longServing]),
                'action' => route('nurse.queue', ['status' => 'serving']),
            ];
        }

        // Records with potentially abnormal vital signs today
        $abnormalVitals = MedicalRecord::query()
            ->whereDate('visit_date', today())
            ->where(function ($q) {
                // High temperature (fever)
                $q->where('temperature', '>=', 38.0)
                    // Low temperature (hypothermia)
                    ->orWhere('temperature', '<=', 35.0)
                    // High heart rate
                    ->orWhere('cardiac_rate', '>=', 120)
                    // Low heart rate
                    ->orWhere('cardiac_rate', '<=', 50)
                    // High respiratory rate
                    ->orWhere('respiratory_rate', '>=', 25);
            })
            ->count();

        if ($abnormalVitals > 0) {
            $alerts['vitals'][] = [
                'type' => 'danger',
                'message' => __(':count patients with abnormal vital signs today', ['count' => $abnormalVitals]),
                'action' => route('nurse.medical-records'),
            ];
        }

        return $alerts;
    }

    public function render(): View
    {
        $currentServing = Queue::query()
            ->with(['consultationType', 'appointment', 'servedBy'])
            ->today()
            ->where('status', 'serving')
            ->orderBy('serving_started_at')
            ->get();

        $recentQueue = Queue::query()
            ->with(['consultationType', 'appointment'])
            ->today()
            ->whereIn('status', ['waiting', 'called'])
            ->orderByRaw("CASE priority WHEN 'emergency' THEN 1 WHEN 'urgent' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END")
            ->orderBy('queue_number')
            ->limit(5)
            ->get();

        return view('livewire.nurse.dashboard', [
            'stats' => $this->stats,
            'currentServing' => $currentServing,
            'recentQueue' => $recentQueue,
            'doctorsAvailable' => $this->doctorsAvailable,
            'waitTimes' => $this->waitTimes,
            'alerts' => $this->alerts,
        ])->layout('layouts.app');
    }
}
