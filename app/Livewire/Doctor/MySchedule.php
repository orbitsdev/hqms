<?php

namespace App\Livewire\Doctor;

use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class MySchedule extends Component
{
    public string $weekStart = '';

    public function mount(): void
    {
        $this->weekStart = now()->startOfWeek()->format('Y-m-d');
    }

    public function previousWeek(): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)->subWeek()->format('Y-m-d');
    }

    public function nextWeek(): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)->addWeek()->format('Y-m-d');
    }

    public function goToToday(): void
    {
        $this->weekStart = now()->startOfWeek()->format('Y-m-d');
    }

    #[Computed]
    public function weekDays(): array
    {
        $start = Carbon::parse($this->weekStart);
        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $days[] = $start->copy()->addDays($i);
        }

        return $days;
    }

    #[Computed]
    public function schedules(): Collection
    {
        $doctor = Auth::user();
        $start = Carbon::parse($this->weekStart);
        $end = $start->copy()->addDays(6);

        return DoctorSchedule::query()
            ->with('consultationType')
            ->where('doctor_id', $doctor->id)
            ->where(function ($q) use ($start, $end) {
                // Regular schedules
                $q->where(function ($sq) use ($start, $end) {
                    $sq->where('schedule_type', 'regular')
                        ->where(function ($ssq) use ($start, $end) {
                            $ssq->whereNull('effective_from')
                                ->orWhere('effective_from', '<=', $end);
                        })
                        ->where(function ($ssq) use ($start) {
                            $ssq->whereNull('effective_until')
                                ->orWhere('effective_until', '>=', $start);
                        });
                });
                // Or exceptions within the range
                $q->orWhere(function ($sq) use ($start, $end) {
                    $sq->where('schedule_type', 'exception')
                        ->whereBetween('exception_date', [$start, $end]);
                });
            })
            ->orderBy('start_time')
            ->get();
    }

    public function getScheduleForDay(Carbon $date): array
    {
        $dayOfWeek = strtolower($date->format('l'));
        $dateString = $date->format('Y-m-d');

        $regularSchedules = $this->schedules
            ->filter(fn ($s) => $s->schedule_type === 'regular' && $s->day_of_week === $dayOfWeek)
            ->values();

        $exceptions = $this->schedules
            ->filter(fn ($s) => $s->schedule_type === 'exception' && $s->exception_date?->format('Y-m-d') === $dateString)
            ->values();

        // Check if there's a full day off exception
        $dayOff = $exceptions->first(fn ($e) => $e->is_day_off);

        return [
            'regular' => $regularSchedules,
            'exceptions' => $exceptions,
            'isDayOff' => $dayOff !== null,
            'dayOffReason' => $dayOff?->exception_reason,
        ];
    }

    #[Computed]
    public function upcomingAppointments(): Collection
    {
        $doctor = Auth::user();

        return \App\Models\Appointment::query()
            ->with(['consultationType', 'queue'])
            ->where('doctor_id', $doctor->id)
            ->where('status', 'approved')
            ->where('appointment_date', '>=', today())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit(10)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.doctor.my-schedule', [
            'weekDays' => $this->weekDays,
        ])->layout('layouts.app');
    }
}
