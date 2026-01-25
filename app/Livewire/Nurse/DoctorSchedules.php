<?php

namespace App\Livewire\Nurse;

use App\Models\ConsultationType;
use App\Models\DoctorSchedule;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class DoctorSchedules extends Component
{
    // Filters
    public string $doctorFilter = '';

    public string $consultationTypeFilter = '';

    // View mode: 'weekly' or 'exceptions'
    public string $viewMode = 'weekly';

    // Add/Edit Regular Schedule Modal
    public bool $showScheduleModal = false;

    #[Locked]
    public ?int $editScheduleId = null;

    public string $scheduleDoctor = '';

    public string $scheduleConsultationType = '';

    /** @var array<int> */
    public array $scheduleDays = [];

    public string $scheduleStartTime = '';

    public string $scheduleEndTime = '';

    // Add/Edit Exception Modal
    public bool $showExceptionModal = false;

    #[Locked]
    public ?int $editExceptionId = null;

    public string $exceptionDoctor = '';

    public string $exceptionConsultationType = '';

    public string $exceptionDate = '';

    public bool $exceptionIsAvailable = false;

    public string $exceptionStartTime = '';

    public string $exceptionEndTime = '';

    public string $exceptionReason = '';

    // Delete Confirmation Modal
    public bool $showDeleteModal = false;

    #[Locked]
    public ?int $deleteScheduleId = null;

    public string $deleteType = '';

    /** @var array<string, string> */
    protected array $dayNames = [
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
        $this->scheduleStartTime = '08:00';
        $this->scheduleEndTime = '17:00';
        $this->exceptionStartTime = '08:00';
        $this->exceptionEndTime = '17:00';
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    // ==================== REGULAR SCHEDULE METHODS ====================

    public function openAddScheduleModal(): void
    {
        $this->resetScheduleForm();
        $this->showScheduleModal = true;
    }

    public function openEditScheduleModal(int $doctorId, int $consultationTypeId): void
    {
        $this->resetScheduleForm();

        // Load existing schedule for this doctor + consultation type
        $schedules = DoctorSchedule::query()
            ->where('user_id', $doctorId)
            ->where('consultation_type_id', $consultationTypeId)
            ->where('schedule_type', 'regular')
            ->get();

        if ($schedules->isNotEmpty()) {
            $this->scheduleDoctor = (string) $doctorId;
            $this->scheduleConsultationType = (string) $consultationTypeId;
            $this->scheduleDays = $schedules->pluck('day_of_week')->toArray();

            // Use the time from first schedule (all should be same)
            $first = $schedules->first();
            $this->scheduleStartTime = $first?->start_time ? Carbon::parse($first->start_time)->format('H:i') : '08:00';
            $this->scheduleEndTime = $first?->end_time ? Carbon::parse($first->end_time)->format('H:i') : '17:00';
            $this->editScheduleId = $doctorId; // Mark as edit mode
        }

        $this->showScheduleModal = true;
    }

    public function closeScheduleModal(): void
    {
        $this->showScheduleModal = false;
        $this->resetScheduleForm();
    }

    protected function resetScheduleForm(): void
    {
        $this->editScheduleId = null;
        $this->scheduleDoctor = '';
        $this->scheduleConsultationType = '';
        $this->scheduleDays = [];
        $this->scheduleStartTime = '08:00';
        $this->scheduleEndTime = '17:00';
    }

    public function saveSchedule(): void
    {
        $this->validate([
            'scheduleDoctor' => ['required', 'exists:users,id'],
            'scheduleConsultationType' => ['required', 'exists:consultation_types,id'],
            'scheduleDays' => ['required', 'array', 'min:1'],
            'scheduleDays.*' => ['integer', 'between:0,6'],
            'scheduleStartTime' => ['nullable', 'date_format:H:i'],
            'scheduleEndTime' => ['nullable', 'date_format:H:i', 'after:scheduleStartTime'],
        ], [
            'scheduleDays.required' => __('Please select at least one day.'),
            'scheduleDays.min' => __('Please select at least one day.'),
            'scheduleEndTime.after' => __('End time must be after start time.'),
        ]);

        $doctorId = (int) $this->scheduleDoctor;
        $consultationTypeId = (int) $this->scheduleConsultationType;

        // Delete existing regular schedules for this doctor + consultation type
        DoctorSchedule::query()
            ->where('user_id', $doctorId)
            ->where('consultation_type_id', $consultationTypeId)
            ->where('schedule_type', 'regular')
            ->delete();

        // Create new schedules for selected days
        foreach ($this->scheduleDays as $dayOfWeek) {
            DoctorSchedule::create([
                'user_id' => $doctorId,
                'consultation_type_id' => $consultationTypeId,
                'schedule_type' => 'regular',
                'day_of_week' => $dayOfWeek,
                'start_time' => $this->scheduleStartTime ?: null,
                'end_time' => $this->scheduleEndTime ?: null,
                'is_available' => true,
            ]);
        }

        $this->closeScheduleModal();
        Toaster::success(__('Schedule saved successfully.'));
    }

    public function confirmDeleteSchedule(int $doctorId, int $consultationTypeId): void
    {
        $this->deleteScheduleId = $doctorId;
        $this->deleteType = 'schedule:'.$consultationTypeId;
        $this->showDeleteModal = true;
    }

    // ==================== EXCEPTION METHODS ====================

    public function openAddExceptionModal(): void
    {
        $this->resetExceptionForm();
        $this->exceptionDate = now()->format('Y-m-d');
        $this->showExceptionModal = true;
    }

    public function openEditExceptionModal(int $exceptionId): void
    {
        $this->resetExceptionForm();

        $exception = DoctorSchedule::find($exceptionId);

        if ($exception && $exception->schedule_type === 'exception') {
            $this->editExceptionId = $exceptionId;
            $this->exceptionDoctor = (string) $exception->user_id;
            $this->exceptionConsultationType = (string) $exception->consultation_type_id;
            $this->exceptionDate = $exception->date?->format('Y-m-d') ?? '';
            $this->exceptionIsAvailable = $exception->is_available;
            $this->exceptionStartTime = $exception->start_time ? Carbon::parse($exception->start_time)->format('H:i') : '08:00';
            $this->exceptionEndTime = $exception->end_time ? Carbon::parse($exception->end_time)->format('H:i') : '17:00';
            $this->exceptionReason = $exception->reason ?? '';
        }

        $this->showExceptionModal = true;
    }

    public function closeExceptionModal(): void
    {
        $this->showExceptionModal = false;
        $this->resetExceptionForm();
    }

    protected function resetExceptionForm(): void
    {
        $this->editExceptionId = null;
        $this->exceptionDoctor = '';
        $this->exceptionConsultationType = '';
        $this->exceptionDate = '';
        $this->exceptionIsAvailable = false;
        $this->exceptionStartTime = '08:00';
        $this->exceptionEndTime = '17:00';
        $this->exceptionReason = '';
    }

    public function saveException(): void
    {
        $rules = [
            'exceptionDoctor' => ['required', 'exists:users,id'],
            'exceptionConsultationType' => ['required', 'exists:consultation_types,id'],
            'exceptionDate' => ['required', 'date'],
            'exceptionIsAvailable' => ['boolean'],
            'exceptionReason' => ['nullable', 'string', 'max:255'],
        ];

        // Only validate times if marking as available
        if ($this->exceptionIsAvailable) {
            $rules['exceptionStartTime'] = ['nullable', 'date_format:H:i'];
            $rules['exceptionEndTime'] = ['nullable', 'date_format:H:i', 'after:exceptionStartTime'];
        }

        $this->validate($rules, [
            'exceptionEndTime.after' => __('End time must be after start time.'),
        ]);

        $data = [
            'user_id' => (int) $this->exceptionDoctor,
            'consultation_type_id' => (int) $this->exceptionConsultationType,
            'schedule_type' => 'exception',
            'date' => $this->exceptionDate,
            'is_available' => $this->exceptionIsAvailable,
            'start_time' => $this->exceptionIsAvailable ? ($this->exceptionStartTime ?: null) : null,
            'end_time' => $this->exceptionIsAvailable ? ($this->exceptionEndTime ?: null) : null,
            'reason' => $this->exceptionReason ?: null,
        ];

        if ($this->editExceptionId) {
            DoctorSchedule::where('id', $this->editExceptionId)->update($data);
            $message = __('Exception updated successfully.');
        } else {
            // Check if exception already exists for this date
            $exists = DoctorSchedule::query()
                ->where('user_id', $data['user_id'])
                ->where('consultation_type_id', $data['consultation_type_id'])
                ->where('schedule_type', 'exception')
                ->where('date', $data['date'])
                ->exists();

            if ($exists) {
                $this->addError('exceptionDate', __('An exception already exists for this date.'));

                return;
            }

            DoctorSchedule::create($data);
            $message = __('Exception added successfully.');
        }

        $this->closeExceptionModal();
        Toaster::success($message);
    }

    public function confirmDeleteException(int $exceptionId): void
    {
        $this->deleteScheduleId = $exceptionId;
        $this->deleteType = 'exception';
        $this->showDeleteModal = true;
    }

    // ==================== DELETE METHODS ====================

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteScheduleId = null;
        $this->deleteType = '';
    }

    public function deleteConfirmed(): void
    {
        if (! $this->deleteScheduleId) {
            return;
        }

        if ($this->deleteType === 'exception') {
            DoctorSchedule::where('id', $this->deleteScheduleId)->delete();
            Toaster::success(__('Exception deleted.'));
        } elseif (str_starts_with($this->deleteType, 'schedule:')) {
            $consultationTypeId = (int) str_replace('schedule:', '', $this->deleteType);
            DoctorSchedule::query()
                ->where('user_id', $this->deleteScheduleId)
                ->where('consultation_type_id', $consultationTypeId)
                ->where('schedule_type', 'regular')
                ->delete();
            Toaster::success(__('Schedule deleted.'));
        }

        $this->closeDeleteModal();
    }

    // ==================== COMPUTED PROPERTIES ====================

    /** @return \Illuminate\Database\Eloquent\Collection<int, User> */
    public function getDoctorsProperty()
    {
        return User::role('doctor')
            ->with(['personalInformation', 'consultationTypes'])
            ->orderBy('first_name')
            ->get();
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, ConsultationType> */
    public function getConsultationTypesProperty()
    {
        return ConsultationType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get weekly schedule data grouped by doctor
     *
     * @return array<int, array{doctor: User, schedules: array<int, array{consultation_type: ConsultationType, days: array<int, DoctorSchedule>}>}>
     */
    public function getWeeklySchedulesProperty(): array
    {
        $query = DoctorSchedule::query()
            ->where('schedule_type', 'regular')
            ->with(['doctor.personalInformation', 'consultationType']);

        if ($this->doctorFilter) {
            $query->where('user_id', $this->doctorFilter);
        }

        if ($this->consultationTypeFilter) {
            $query->where('consultation_type_id', $this->consultationTypeFilter);
        }

        $schedules = $query->get();

        // Group by doctor, then by consultation type
        $grouped = [];
        foreach ($schedules as $schedule) {
            $doctorId = $schedule->user_id;
            $typeId = $schedule->consultation_type_id;

            if (! isset($grouped[$doctorId])) {
                $grouped[$doctorId] = [
                    'doctor' => $schedule->doctor,
                    'schedules' => [],
                ];
            }

            if (! isset($grouped[$doctorId]['schedules'][$typeId])) {
                $grouped[$doctorId]['schedules'][$typeId] = [
                    'consultation_type' => $schedule->consultationType,
                    'days' => [],
                ];
            }

            $grouped[$doctorId]['schedules'][$typeId]['days'][$schedule->day_of_week] = $schedule;
        }

        return $grouped;
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, DoctorSchedule> */
    public function getExceptionsProperty()
    {
        $query = DoctorSchedule::query()
            ->where('schedule_type', 'exception')
            ->where('date', '>=', now()->startOfDay())
            ->with(['doctor.personalInformation', 'consultationType'])
            ->orderBy('date');

        if ($this->doctorFilter) {
            $query->where('user_id', $this->doctorFilter);
        }

        if ($this->consultationTypeFilter) {
            $query->where('consultation_type_id', $this->consultationTypeFilter);
        }

        return $query->get();
    }

    /** @return array<string, int> */
    public function getCountsProperty(): array
    {
        $regularCount = DoctorSchedule::where('schedule_type', 'regular')->count();
        $exceptionCount = DoctorSchedule::where('schedule_type', 'exception')
            ->where('date', '>=', now()->startOfDay())
            ->count();

        return [
            'weekly' => $regularCount,
            'exceptions' => $exceptionCount,
        ];
    }

    /** @return array<int, string> */
    public function getDayNamesProperty(): array
    {
        return $this->dayNames;
    }

    public function render(): View
    {
        return view('livewire.nurse.doctor-schedules', [
            'doctors' => $this->doctors,
            'consultationTypes' => $this->consultationTypes,
            'weeklySchedules' => $this->weeklySchedules,
            'exceptions' => $this->exceptions,
            'counts' => $this->counts,
            'dayNames' => $this->dayNames,
        ])->layout('layouts.app');
    }
}
