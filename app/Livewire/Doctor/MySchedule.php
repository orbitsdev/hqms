<?php

namespace App\Livewire\Doctor;

use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class MySchedule extends Component
{
    public string $weekStart = '';

    public string $viewMode = 'overview';

    // Schedule Modal
    public bool $showScheduleModal = false;

    public ?int $editScheduleTypeId = null;

    public string $scheduleConsultationType = '';

    public array $scheduleDays = [];

    public string $scheduleStartTime = '';

    public string $scheduleEndTime = '';

    // Exception Modal
    public bool $showExceptionModal = false;

    public ?int $editExceptionId = null;

    public string $exceptionConsultationType = '';

    public string $exceptionDate = '';

    public string $exceptionDateEnd = '';

    public bool $exceptionUseDateRange = false;

    public bool $exceptionIsAvailable = false;

    public string $exceptionStartTime = '';

    public string $exceptionEndTime = '';

    public string $exceptionReason = '';

    public string $exceptionPreset = '';

    // Delete Modal
    public bool $showDeleteModal = false;

    public string $deleteType = '';

    public array $dayNames = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    public function mount(): void
    {
        $this->weekStart = now()->startOfWeek()->format('Y-m-d');
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
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
            ->where('user_id', $doctor->id)
            ->where(function ($q) use ($start, $end) {
                $q->where('schedule_type', 'regular');
                $q->orWhere(function ($sq) use ($start, $end) {
                    $sq->where('schedule_type', 'exception')
                        ->whereBetween('date', [$start, $end]);
                });
            })
            ->orderBy('start_time')
            ->get();
    }

    #[Computed]
    public function weeklySchedules(): Collection
    {
        $doctor = Auth::user();

        return DoctorSchedule::query()
            ->with('consultationType')
            ->where('user_id', $doctor->id)
            ->where('schedule_type', 'regular')
            ->orderBy('consultation_type_id')
            ->orderBy('day_of_week')
            ->get()
            ->groupBy('consultation_type_id');
    }

    #[Computed]
    public function exceptions(): Collection
    {
        $doctor = Auth::user();

        return DoctorSchedule::query()
            ->with('consultationType')
            ->where('user_id', $doctor->id)
            ->where('schedule_type', 'exception')
            ->where('date', '>=', today())
            ->orderBy('date')
            ->get();
    }

    #[Computed]
    public function consultationTypes(): Collection
    {
        return Auth::user()->consultationTypes()->where('is_active', true)->get();
    }

    #[Computed]
    public function dateRangeDaysCount(): int
    {
        if (! $this->exceptionUseDateRange || ! $this->exceptionDate || ! $this->exceptionDateEnd) {
            return 0;
        }

        $start = Carbon::parse($this->exceptionDate);
        $end = Carbon::parse($this->exceptionDateEnd);

        if ($end->lt($start)) {
            return 0;
        }

        return $start->diffInDays($end) + 1;
    }

    public function getScheduleForDay(Carbon|CarbonImmutable $date): array
    {
        $dayOfWeek = $date->dayOfWeek;
        $dateString = $date->format('Y-m-d');

        $regularSchedules = $this->schedules
            ->filter(fn ($s) => $s->schedule_type === 'regular' && (int) $s->day_of_week === $dayOfWeek)
            ->values();

        $exceptions = $this->schedules
            ->filter(fn ($s) => $s->schedule_type === 'exception' && $s->date?->format('Y-m-d') === $dateString)
            ->values();

        $dayOff = $exceptions->first(fn ($e) => ! $e->is_available);

        return [
            'regular' => $regularSchedules,
            'exceptions' => $exceptions,
            'isDayOff' => $dayOff !== null,
            'dayOffReason' => $dayOff?->reason,
        ];
    }

    #[Computed]
    public function upcomingAppointments(): Collection
    {
        $doctor = Auth::user();

        return Appointment::query()
            ->with(['consultationType', 'queue'])
            ->where('doctor_id', $doctor->id)
            ->where('status', 'approved')
            ->where('appointment_date', '>=', today())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit(10)
            ->get();
    }

    // Schedule Modal Methods
    public function openAddScheduleModal(): void
    {
        $this->resetScheduleForm();
        $this->showScheduleModal = true;
    }

    public function openEditScheduleModal(int $consultationTypeId): void
    {
        $this->resetScheduleForm();
        $doctor = Auth::user();

        $schedules = DoctorSchedule::query()
            ->where('user_id', $doctor->id)
            ->where('consultation_type_id', $consultationTypeId)
            ->where('schedule_type', 'regular')
            ->get();

        if ($schedules->isEmpty()) {
            return;
        }

        $this->editScheduleTypeId = $consultationTypeId;
        $this->scheduleConsultationType = (string) $consultationTypeId;
        $this->scheduleDays = $schedules->pluck('day_of_week')->map(fn ($d) => (int) $d)->toArray();

        $first = $schedules->first();
        $this->scheduleStartTime = $first->start_time ? Carbon::parse($first->start_time)->format('H:i') : '';
        $this->scheduleEndTime = $first->end_time ? Carbon::parse($first->end_time)->format('H:i') : '';

        $this->showScheduleModal = true;
    }

    public function closeScheduleModal(): void
    {
        $this->showScheduleModal = false;
        $this->resetScheduleForm();
    }

    public function resetScheduleForm(): void
    {
        $this->editScheduleTypeId = null;
        $this->scheduleConsultationType = '';
        $this->scheduleDays = [];
        $this->scheduleStartTime = '';
        $this->scheduleEndTime = '';
        $this->resetValidation();
    }

    public function saveSchedule(): void
    {
        $this->validate([
            'scheduleConsultationType' => 'required|exists:consultation_types,id',
            'scheduleDays' => 'required|array|min:1',
            'scheduleDays.*' => 'integer|min:0|max:6',
            'scheduleStartTime' => 'nullable|date_format:H:i',
            'scheduleEndTime' => 'nullable|date_format:H:i|after:scheduleStartTime',
        ]);

        $doctor = Auth::user();
        $consultationTypeId = (int) $this->scheduleConsultationType;

        // Verify doctor has this consultation type
        if (! $doctor->consultationTypes()->where('consultation_types.id', $consultationTypeId)->exists()) {
            Toaster::error(__('Invalid consultation type.'));

            return;
        }

        DB::transaction(function () use ($doctor, $consultationTypeId): void {
            // Delete existing schedules for this type
            DoctorSchedule::query()
                ->where('user_id', $doctor->id)
                ->where('consultation_type_id', $consultationTypeId)
                ->where('schedule_type', 'regular')
                ->delete();

            // Create new schedules
            foreach ($this->scheduleDays as $day) {
                DoctorSchedule::create([
                    'user_id' => $doctor->id,
                    'consultation_type_id' => $consultationTypeId,
                    'schedule_type' => 'regular',
                    'day_of_week' => (int) $day,
                    'is_available' => true,
                    'start_time' => $this->scheduleStartTime ?: null,
                    'end_time' => $this->scheduleEndTime ?: null,
                ]);
            }
        });

        $this->closeScheduleModal();
        Toaster::success($this->editScheduleTypeId ? __('Schedule updated.') : __('Schedule created.'));
    }

    public function confirmDeleteSchedule(int $consultationTypeId): void
    {
        $this->deleteType = "schedule:{$consultationTypeId}";
        $this->showDeleteModal = true;
    }

    // Exception Modal Methods
    public function openAddExceptionModal(?string $preset = null): void
    {
        $this->resetExceptionForm();

        if ($preset) {
            $this->applyExceptionPreset($preset);
        }

        $this->showExceptionModal = true;
    }

    public function openEditExceptionModal(int $id): void
    {
        $this->resetExceptionForm();
        $doctor = Auth::user();

        $exception = DoctorSchedule::query()
            ->where('id', $id)
            ->where('user_id', $doctor->id)
            ->where('schedule_type', 'exception')
            ->first();

        if (! $exception) {
            return;
        }

        $this->editExceptionId = $id;
        $this->exceptionConsultationType = (string) $exception->consultation_type_id;
        $this->exceptionDate = $exception->date->format('Y-m-d');
        $this->exceptionIsAvailable = $exception->is_available;
        $this->exceptionStartTime = $exception->start_time ? Carbon::parse($exception->start_time)->format('H:i') : '';
        $this->exceptionEndTime = $exception->end_time ? Carbon::parse($exception->end_time)->format('H:i') : '';
        $this->exceptionReason = $exception->reason ?? '';

        $this->showExceptionModal = true;
    }

    public function closeExceptionModal(): void
    {
        $this->showExceptionModal = false;
        $this->resetExceptionForm();
    }

    public function resetExceptionForm(): void
    {
        $this->editExceptionId = null;
        $this->exceptionConsultationType = '';
        $this->exceptionDate = '';
        $this->exceptionDateEnd = '';
        $this->exceptionUseDateRange = false;
        $this->exceptionIsAvailable = false;
        $this->exceptionStartTime = '';
        $this->exceptionEndTime = '';
        $this->exceptionReason = '';
        $this->exceptionPreset = '';
        $this->resetValidation();
    }

    public function updatedExceptionPreset(string $value): void
    {
        if ($value) {
            $this->applyExceptionPreset($value);
        }
    }

    protected function applyExceptionPreset(string $preset): void
    {
        $this->exceptionPreset = $preset;

        match ($preset) {
            'annual_leave' => $this->setExceptionPresetValues(false, __('Annual Leave')),
            'sick_leave' => $this->setExceptionPresetValues(false, __('Sick Leave')),
            'holiday' => $this->setExceptionPresetValues(false, __('Holiday')),
            'training' => $this->setExceptionPresetValues(false, __('Training/Seminar')),
            'emergency_leave' => $this->setExceptionPresetValues(false, __('Emergency Leave')),
            'half_day_am' => $this->setExceptionPresetValues(true, __('Half Day - Afternoon Only'), '13:00', '17:00'),
            'half_day_pm' => $this->setExceptionPresetValues(true, __('Half Day - Morning Only'), '08:00', '12:00'),
            'extra_clinic' => $this->setExceptionPresetValues(true, __('Extra Clinic Day')),
            default => null,
        };
    }

    protected function setExceptionPresetValues(bool $isAvailable, string $reason, ?string $start = null, ?string $end = null): void
    {
        $this->exceptionIsAvailable = $isAvailable;
        $this->exceptionReason = $reason;
        $this->exceptionStartTime = $start ?? '';
        $this->exceptionEndTime = $end ?? '';
    }

    public function saveException(): void
    {
        $rules = [
            'exceptionConsultationType' => 'required|exists:consultation_types,id',
            'exceptionDate' => 'required|date|after_or_equal:today',
            'exceptionReason' => 'nullable|string|max:255',
        ];

        if ($this->exceptionUseDateRange && ! $this->editExceptionId) {
            $rules['exceptionDateEnd'] = 'required|date|after_or_equal:exceptionDate';
        }

        if ($this->exceptionIsAvailable) {
            $rules['exceptionStartTime'] = 'nullable|date_format:H:i';
            $rules['exceptionEndTime'] = 'nullable|date_format:H:i|after:exceptionStartTime';
        }

        $this->validate($rules);

        $doctor = Auth::user();
        $consultationTypeId = (int) $this->exceptionConsultationType;

        // Verify doctor has this consultation type
        if (! $doctor->consultationTypes()->where('consultation_types.id', $consultationTypeId)->exists()) {
            Toaster::error(__('Invalid consultation type.'));

            return;
        }

        if ($this->editExceptionId) {
            // Update existing
            DoctorSchedule::where('id', $this->editExceptionId)->update([
                'consultation_type_id' => $consultationTypeId,
                'date' => $this->exceptionDate,
                'is_available' => $this->exceptionIsAvailable,
                'start_time' => $this->exceptionIsAvailable && $this->exceptionStartTime ? $this->exceptionStartTime : null,
                'end_time' => $this->exceptionIsAvailable && $this->exceptionEndTime ? $this->exceptionEndTime : null,
                'reason' => $this->exceptionReason ?: null,
            ]);

            Toaster::success(__('Exception updated.'));
        } else {
            // Create new
            $dates = [$this->exceptionDate];

            if ($this->exceptionUseDateRange && $this->exceptionDateEnd) {
                $dates = [];
                $current = Carbon::parse($this->exceptionDate);
                $end = Carbon::parse($this->exceptionDateEnd);

                while ($current->lte($end)) {
                    $dates[] = $current->format('Y-m-d');
                    $current->addDay();
                }
            }

            $created = 0;
            foreach ($dates as $date) {
                // Check for existing exception on this date
                $exists = DoctorSchedule::query()
                    ->where('user_id', $doctor->id)
                    ->where('consultation_type_id', $consultationTypeId)
                    ->where('schedule_type', 'exception')
                    ->where('date', $date)
                    ->exists();

                if (! $exists) {
                    DoctorSchedule::create([
                        'user_id' => $doctor->id,
                        'consultation_type_id' => $consultationTypeId,
                        'schedule_type' => 'exception',
                        'date' => $date,
                        'is_available' => $this->exceptionIsAvailable,
                        'start_time' => $this->exceptionIsAvailable && $this->exceptionStartTime ? $this->exceptionStartTime : null,
                        'end_time' => $this->exceptionIsAvailable && $this->exceptionEndTime ? $this->exceptionEndTime : null,
                        'reason' => $this->exceptionReason ?: null,
                    ]);
                    $created++;
                }
            }

            if ($created > 0) {
                Toaster::success(trans_choice('{1} Exception created.|[2,*] :count exceptions created.', $created, ['count' => $created]));
            } else {
                Toaster::warning(__('No exceptions created. Dates may already have exceptions.'));
            }
        }

        $this->closeExceptionModal();
    }

    public function confirmDeleteException(int $id): void
    {
        $this->deleteType = "exception:{$id}";
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteType = '';
    }

    public function deleteConfirmed(): void
    {
        $doctor = Auth::user();

        if (str_starts_with($this->deleteType, 'schedule:')) {
            $consultationTypeId = (int) str_replace('schedule:', '', $this->deleteType);

            DoctorSchedule::query()
                ->where('user_id', $doctor->id)
                ->where('consultation_type_id', $consultationTypeId)
                ->where('schedule_type', 'regular')
                ->delete();

            Toaster::success(__('Schedule deleted.'));
        } elseif (str_starts_with($this->deleteType, 'exception:')) {
            $exceptionId = (int) str_replace('exception:', '', $this->deleteType);

            DoctorSchedule::query()
                ->where('id', $exceptionId)
                ->where('user_id', $doctor->id)
                ->delete();

            Toaster::success(__('Exception deleted.'));
        }

        $this->closeDeleteModal();
    }

    public function render(): View
    {
        return view('livewire.doctor.my-schedule', [
            'weekDays' => $this->weekDays,
        ])->layout('layouts.app');
    }
}
