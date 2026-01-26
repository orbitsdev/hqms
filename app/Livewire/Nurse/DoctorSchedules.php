<?php

namespace App\Livewire\Nurse;

use App\Models\ConsultationType;
use App\Models\DoctorSchedule;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class DoctorSchedules extends Component
{
    // ==================== VIEW STATE ====================
    public string $viewMode = 'overview'; // 'overview', 'weekly', 'exceptions'

    // ==================== FILTERS ====================
    public string $doctorFilter = '';

    public string $consultationTypeFilter = '';

    public string $searchQuery = '';

    // ==================== REGULAR SCHEDULE MODAL ====================
    public bool $showScheduleModal = false;

    #[Locked]
    public ?int $editScheduleId = null;

    public string $scheduleDoctor = '';

    public string $scheduleConsultationType = '';

    /** @var array<int> */
    public array $scheduleDays = [];

    public string $scheduleStartTime = '';

    public string $scheduleEndTime = '';

    // ==================== EXCEPTION MODAL ====================
    public bool $showExceptionModal = false;

    #[Locked]
    public ?int $editExceptionId = null;

    public string $exceptionDoctor = '';

    public string $exceptionConsultationType = '';

    public string $exceptionDate = '';

    public string $exceptionDateEnd = '';

    public bool $exceptionUseDateRange = false;

    public bool $exceptionIsAvailable = false;

    public string $exceptionStartTime = '';

    public string $exceptionEndTime = '';

    public string $exceptionReason = '';

    public string $exceptionPreset = '';

    // ==================== COPY SCHEDULE MODAL ====================
    public bool $showCopyModal = false;

    public string $copyFromDoctor = '';

    public string $copyToDoctor = '';

    public string $copyConsultationType = '';

    // ==================== DELETE MODAL ====================
    public bool $showDeleteModal = false;

    #[Locked]
    public ?int $deleteScheduleId = null;

    public string $deleteType = '';

    // ==================== QUICK ADD EXCEPTION ====================
    public bool $showQuickExceptionModal = false;

    public string $quickExceptionDoctor = '';

    public string $quickExceptionType = '';

    public string $quickExceptionDate = '';

    // ==================== CONSTANTS ====================
    /** @var array<int, string> */
    protected array $dayNames = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    /** @var array<string, array{label: string, available: bool, reason: string}> */
    protected array $exceptionPresets = [
        'annual_leave' => ['label' => 'Annual Leave', 'available' => false, 'reason' => 'Annual Leave'],
        'sick_leave' => ['label' => 'Sick Leave', 'available' => false, 'reason' => 'Sick Leave'],
        'holiday' => ['label' => 'Holiday', 'available' => false, 'reason' => 'Holiday'],
        'training' => ['label' => 'Training/Seminar', 'available' => false, 'reason' => 'Training/Seminar'],
        'emergency_leave' => ['label' => 'Emergency Leave', 'available' => false, 'reason' => 'Emergency Leave'],
        'half_day_am' => ['label' => 'Half Day (Morning Off)', 'available' => true, 'reason' => 'Half Day - Morning Off'],
        'half_day_pm' => ['label' => 'Half Day (Afternoon Off)', 'available' => true, 'reason' => 'Half Day - Afternoon Off'],
        'extra_clinic' => ['label' => 'Extra Clinic Day', 'available' => true, 'reason' => 'Extra Clinic Day'],
    ];

    public function mount(): void
    {
        $this->scheduleStartTime = '08:00';
        $this->scheduleEndTime = '17:00';
        $this->exceptionStartTime = '08:00';
        $this->exceptionEndTime = '17:00';
    }

    // ==================== VIEW MODE METHODS ====================

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    // ==================== REGULAR SCHEDULE METHODS ====================

    public function openAddScheduleModal(?int $doctorId = null): void
    {
        $this->resetScheduleForm();

        if ($doctorId) {
            $this->scheduleDoctor = (string) $doctorId;
        }

        $this->showScheduleModal = true;
    }

    public function openEditScheduleModal(int $doctorId, int $consultationTypeId): void
    {
        $this->resetScheduleForm();

        $schedules = DoctorSchedule::query()
            ->where('user_id', $doctorId)
            ->where('consultation_type_id', $consultationTypeId)
            ->where('schedule_type', 'regular')
            ->get();

        if ($schedules->isNotEmpty()) {
            $this->scheduleDoctor = (string) $doctorId;
            $this->scheduleConsultationType = (string) $consultationTypeId;
            $this->scheduleDays = $schedules->pluck('day_of_week')->toArray();

            $first = $schedules->first();
            $this->scheduleStartTime = $first?->start_time ? Carbon::parse($first->start_time)->format('H:i') : '08:00';
            $this->scheduleEndTime = $first?->end_time ? Carbon::parse($first->end_time)->format('H:i') : '17:00';
            $this->editScheduleId = $doctorId;
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
        $this->resetErrorBag();
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
            'scheduleDoctor.required' => __('Please select a doctor.'),
            'scheduleConsultationType.required' => __('Please select a consultation type.'),
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

        // Sort days to ensure consistent order (0=Sun, 1=Mon, ... 6=Sat)
        $sortedDays = $this->scheduleDays;
        sort($sortedDays);

        // Create new schedules for selected days in order
        foreach ($sortedDays as $dayOfWeek) {
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

    public function openAddExceptionModal(?string $preset = null, ?int $doctorId = null): void
    {
        $this->resetExceptionForm();
        $this->exceptionDate = now()->addDay()->format('Y-m-d');

        if ($doctorId) {
            $this->exceptionDoctor = (string) $doctorId;
        }

        if ($preset && isset($this->exceptionPresets[$preset])) {
            $this->exceptionPreset = $preset;
            $this->applyExceptionPreset();
        }

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
            $this->exceptionPreset = 'custom';
        }

        $this->showExceptionModal = true;
    }

    public function updatedExceptionPreset(): void
    {
        $this->applyExceptionPreset();
    }

    protected function applyExceptionPreset(): void
    {
        if (! $this->exceptionPreset) {
            return;
        }

        $preset = $this->exceptionPresets[$this->exceptionPreset] ?? null;
        if (! $preset) {
            return;
        }

        $this->exceptionIsAvailable = $preset['available'];
        $this->exceptionReason = $preset['reason'];

        // Set times for half-day presets
        if ($this->exceptionPreset === 'half_day_am') {
            $this->exceptionStartTime = '13:00';
            $this->exceptionEndTime = '17:00';
        } elseif ($this->exceptionPreset === 'half_day_pm') {
            $this->exceptionStartTime = '08:00';
            $this->exceptionEndTime = '12:00';
        } elseif ($this->exceptionIsAvailable) {
            $this->exceptionStartTime = '08:00';
            $this->exceptionEndTime = '17:00';
        }
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
        $this->exceptionDateEnd = '';
        $this->exceptionUseDateRange = false;
        $this->exceptionIsAvailable = false;
        $this->exceptionStartTime = '08:00';
        $this->exceptionEndTime = '17:00';
        $this->exceptionReason = '';
        $this->exceptionPreset = '';
        $this->resetErrorBag();
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

        // Add date range validation if using date range
        if ($this->exceptionUseDateRange && ! $this->editExceptionId) {
            $rules['exceptionDateEnd'] = ['required', 'date', 'after_or_equal:exceptionDate'];
        }

        if ($this->exceptionIsAvailable) {
            $rules['exceptionStartTime'] = ['nullable', 'date_format:H:i'];
            $rules['exceptionEndTime'] = ['nullable', 'date_format:H:i', 'after:exceptionStartTime'];
        }

        $this->validate($rules, [
            'exceptionDoctor.required' => __('Please select a doctor.'),
            'exceptionConsultationType.required' => __('Please select a consultation type.'),
            'exceptionDate.required' => __('Please select a start date.'),
            'exceptionDateEnd.required' => __('Please select an end date.'),
            'exceptionDateEnd.after_or_equal' => __('End date must be on or after start date.'),
            'exceptionEndTime.after' => __('End time must be after start time.'),
        ]);

        $userId = (int) $this->exceptionDoctor;
        $consultationTypeId = (int) $this->exceptionConsultationType;

        $baseData = [
            'user_id' => $userId,
            'consultation_type_id' => $consultationTypeId,
            'schedule_type' => 'exception',
            'is_available' => $this->exceptionIsAvailable,
            'start_time' => $this->exceptionIsAvailable ? ($this->exceptionStartTime ?: null) : null,
            'end_time' => $this->exceptionIsAvailable ? ($this->exceptionEndTime ?: null) : null,
            'reason' => $this->exceptionReason ?: null,
        ];

        if ($this->editExceptionId) {
            // Update single exception
            $baseData['date'] = $this->exceptionDate;
            DoctorSchedule::where('id', $this->editExceptionId)->update($baseData);
            $message = __('Exception updated successfully.');
        } else {
            // Create new exception(s)
            if ($this->exceptionUseDateRange) {
                // Date range mode - create multiple exceptions
                $startDate = Carbon::parse($this->exceptionDate);
                $endDate = Carbon::parse($this->exceptionDateEnd);
                $daysCount = $startDate->diffInDays($endDate) + 1;

                // Limit to prevent accidental huge ranges (max 60 days / ~2 months)
                if ($daysCount > 60) {
                    $this->addError('exceptionDateEnd', __('Date range cannot exceed 60 days.'));

                    return;
                }

                $createdCount = 0;
                $skippedCount = 0;

                for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                    // Check if exception already exists for this date
                    $exists = DoctorSchedule::query()
                        ->where('user_id', $userId)
                        ->where('consultation_type_id', $consultationTypeId)
                        ->where('schedule_type', 'exception')
                        ->where('date', $date->format('Y-m-d'))
                        ->exists();

                    if ($exists) {
                        $skippedCount++;

                        continue;
                    }

                    DoctorSchedule::create(array_merge($baseData, [
                        'date' => $date->format('Y-m-d'),
                    ]));
                    $createdCount++;
                }

                if ($createdCount === 0) {
                    $this->addError('exceptionDate', __('All dates in this range already have exceptions.'));

                    return;
                }

                $message = trans_choice(
                    '{1} :count exception created successfully.|[2,*] :count exceptions created successfully.',
                    $createdCount,
                    ['count' => $createdCount]
                );

                if ($skippedCount > 0) {
                    $message .= ' '.trans_choice(
                        '{1} :count date skipped (already exists).|[2,*] :count dates skipped (already exist).',
                        $skippedCount,
                        ['count' => $skippedCount]
                    );
                }
            } else {
                // Single date mode
                $exists = DoctorSchedule::query()
                    ->where('user_id', $userId)
                    ->where('consultation_type_id', $consultationTypeId)
                    ->where('schedule_type', 'exception')
                    ->where('date', $this->exceptionDate)
                    ->exists();

                if ($exists) {
                    $this->addError('exceptionDate', __('An exception already exists for this doctor and date.'));

                    return;
                }

                DoctorSchedule::create(array_merge($baseData, [
                    'date' => $this->exceptionDate,
                ]));
                $message = __('Exception added successfully.');
            }
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

    // ==================== COPY SCHEDULE METHODS ====================

    public function openCopyModal(): void
    {
        $this->copyFromDoctor = '';
        $this->copyToDoctor = '';
        $this->copyConsultationType = '';
        $this->showCopyModal = true;
    }

    public function closeCopyModal(): void
    {
        $this->showCopyModal = false;
        $this->resetErrorBag();
    }

    public function copySchedule(): void
    {
        $this->validate([
            'copyFromDoctor' => ['required', 'exists:users,id'],
            'copyToDoctor' => ['required', 'exists:users,id', 'different:copyFromDoctor'],
            'copyConsultationType' => ['required', 'exists:consultation_types,id'],
        ], [
            'copyFromDoctor.required' => __('Please select a source doctor.'),
            'copyToDoctor.required' => __('Please select a target doctor.'),
            'copyToDoctor.different' => __('Target doctor must be different from source.'),
            'copyConsultationType.required' => __('Please select a consultation type.'),
        ]);

        $sourceSchedules = DoctorSchedule::query()
            ->where('user_id', $this->copyFromDoctor)
            ->where('consultation_type_id', $this->copyConsultationType)
            ->where('schedule_type', 'regular')
            ->get();

        if ($sourceSchedules->isEmpty()) {
            $this->addError('copyFromDoctor', __('No schedule found for the source doctor.'));

            return;
        }

        // Delete existing schedules for target doctor
        DoctorSchedule::query()
            ->where('user_id', $this->copyToDoctor)
            ->where('consultation_type_id', $this->copyConsultationType)
            ->where('schedule_type', 'regular')
            ->delete();

        // Copy schedules
        foreach ($sourceSchedules as $schedule) {
            DoctorSchedule::create([
                'user_id' => $this->copyToDoctor,
                'consultation_type_id' => $this->copyConsultationType,
                'schedule_type' => 'regular',
                'day_of_week' => $schedule->day_of_week,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'is_available' => true,
            ]);
        }

        $this->closeCopyModal();
        Toaster::success(__('Schedule copied successfully.'));
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
            Toaster::success(__('Exception deleted successfully.'));
        } elseif (str_starts_with($this->deleteType, 'schedule:')) {
            $consultationTypeId = (int) str_replace('schedule:', '', $this->deleteType);
            DoctorSchedule::query()
                ->where('user_id', $this->deleteScheduleId)
                ->where('consultation_type_id', $consultationTypeId)
                ->where('schedule_type', 'regular')
                ->delete();
            Toaster::success(__('Schedule deleted successfully.'));
        }

        $this->closeDeleteModal();
    }

    // ==================== COMPUTED PROPERTIES ====================

    /** @return \Illuminate\Database\Eloquent\Collection<int, User> */
    #[Computed]
    public function doctors()
    {
        return User::role('doctor')
            ->with(['personalInformation', 'consultationTypes'])
            ->orderBy('first_name')
            ->get();
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, ConsultationType> */
    #[Computed]
    public function consultationTypes()
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
    #[Computed]
    public function weeklySchedules(): array
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

        // Sort by doctor name for consistent display order
        uasort($grouped, fn ($a, $b) => strcasecmp($a['doctor']->name, $b['doctor']->name));

        // Sort each doctor's consultation types by name
        foreach ($grouped as $doctorId => $data) {
            uasort($grouped[$doctorId]['schedules'], fn ($a, $b) => strcasecmp($a['consultation_type']->name, $b['consultation_type']->name));
        }

        return $grouped;
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, DoctorSchedule> */
    #[Computed]
    public function exceptions()
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

    /** @return \Illuminate\Database\Eloquent\Collection<int, DoctorSchedule> */
    #[Computed]
    public function upcomingExceptions()
    {
        return DoctorSchedule::query()
            ->where('schedule_type', 'exception')
            ->whereBetween('date', [now()->startOfDay(), now()->addDays(14)])
            ->with(['doctor.personalInformation', 'consultationType'])
            ->orderBy('date')
            ->limit(5)
            ->get();
    }

    /** @return array<string, int> */
    #[Computed]
    public function stats(): array
    {
        $totalDoctors = User::role('doctor')->count();
        $doctorsWithSchedule = DoctorSchedule::where('schedule_type', 'regular')
            ->distinct('user_id')
            ->count('user_id');

        $upcomingLeaves = DoctorSchedule::where('schedule_type', 'exception')
            ->where('is_available', false)
            ->whereBetween('date', [now()->startOfDay(), now()->addDays(30)])
            ->count();

        $todayExceptions = DoctorSchedule::where('schedule_type', 'exception')
            ->whereDate('date', now())
            ->count();

        return [
            'total_doctors' => $totalDoctors,
            'doctors_with_schedule' => $doctorsWithSchedule,
            'doctors_without_schedule' => $totalDoctors - $doctorsWithSchedule,
            'upcoming_leaves' => $upcomingLeaves,
            'today_exceptions' => $todayExceptions,
            'weekly_schedules' => DoctorSchedule::where('schedule_type', 'regular')->count(),
            'total_exceptions' => DoctorSchedule::where('schedule_type', 'exception')
                ->where('date', '>=', now()->startOfDay())
                ->count(),
        ];
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, User> */
    #[Computed]
    public function doctorsWithoutSchedule()
    {
        $doctorIdsWithSchedule = DoctorSchedule::where('schedule_type', 'regular')
            ->distinct()
            ->pluck('user_id');

        return User::role('doctor')
            ->whereNotIn('id', $doctorIdsWithSchedule)
            ->with('personalInformation')
            ->orderBy('first_name')
            ->get();
    }

    /**
     * Get week calendar data for the overview
     *
     * @return array<int, array{date: Carbon, dayName: string, dayShort: string, isToday: bool, doctors: array}>
     */
    #[Computed]
    public function weekCalendar(): array
    {
        $startOfWeek = now()->startOfWeek(Carbon::MONDAY);
        $calendar = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dayOfWeek = $date->dayOfWeek;

            // Get regular schedules for this day
            $schedules = DoctorSchedule::query()
                ->where('schedule_type', 'regular')
                ->where('day_of_week', $dayOfWeek)
                ->with(['doctor.personalInformation', 'consultationType'])
                ->get()
                ->groupBy('user_id');

            // Get exceptions for this specific date
            $exceptions = DoctorSchedule::query()
                ->where('schedule_type', 'exception')
                ->whereDate('date', $date)
                ->with(['doctor.personalInformation', 'consultationType'])
                ->get()
                ->keyBy(fn ($e) => $e->user_id.'_'.$e->consultation_type_id);

            $doctors = [];
            foreach ($schedules as $doctorId => $doctorSchedules) {
                $doctor = $doctorSchedules->first()->doctor;
                $types = [];

                foreach ($doctorSchedules as $schedule) {
                    $exceptionKey = $doctorId.'_'.$schedule->consultation_type_id;
                    $exception = $exceptions->get($exceptionKey);

                    $types[] = [
                        'type' => $schedule->consultationType,
                        'start_time' => $schedule->start_time,
                        'end_time' => $schedule->end_time,
                        'has_exception' => $exception !== null,
                        'exception' => $exception,
                        'is_available' => $exception ? $exception->is_available : true,
                    ];
                }

                $doctors[] = [
                    'doctor' => $doctor,
                    'types' => $types,
                ];
            }

            // Add doctors who only have exceptions (extra clinic days)
            foreach ($exceptions as $exception) {
                if ($exception->is_available && ! $schedules->has($exception->user_id)) {
                    $doctors[] = [
                        'doctor' => $exception->doctor,
                        'types' => [
                            [
                                'type' => $exception->consultationType,
                                'start_time' => $exception->start_time,
                                'end_time' => $exception->end_time,
                                'has_exception' => true,
                                'exception' => $exception,
                                'is_available' => true,
                                'is_extra' => true,
                            ],
                        ],
                    ];
                }
            }

            $calendar[] = [
                'date' => $date,
                'dayName' => $date->format('l'),
                'dayShort' => $date->format('D'),
                'dateFormatted' => $date->format('M d'),
                'isToday' => $date->isToday(),
                'isPast' => $date->isPast() && ! $date->isToday(),
                'doctors' => $doctors,
            ];
        }

        return $calendar;
    }

    /** @return array<int, string> */
    public function getDayNamesProperty(): array
    {
        return $this->dayNames;
    }

    /**
     * Calculate the number of days in the selected date range
     */
    #[Computed]
    public function dateRangeDaysCount(): int
    {
        if (! $this->exceptionUseDateRange || ! $this->exceptionDate || ! $this->exceptionDateEnd) {
            return 0;
        }

        try {
            $start = Carbon::parse($this->exceptionDate);
            $end = Carbon::parse($this->exceptionDateEnd);

            if ($end->lt($start)) {
                return 0;
            }

            return $start->diffInDays($end) + 1;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /** @return array<string, array{label: string, available: bool, reason: string}> */
    public function getExceptionPresetsProperty(): array
    {
        return $this->exceptionPresets;
    }

    public function render(): View
    {
        return view('livewire.nurse.doctor-schedules', [
            'doctors' => $this->doctors,
            'consultationTypes' => $this->consultationTypes,
            'weeklySchedules' => $this->weeklySchedules,
            'exceptions' => $this->exceptions,
            'upcomingExceptions' => $this->upcomingExceptions,
            'stats' => $this->stats,
            'weekCalendar' => $this->weekCalendar,
            'dayNames' => $this->dayNames,
            'exceptionPresets' => $this->exceptionPresets,
            'dateRangeDaysCount' => $this->dateRangeDaysCount,
            'doctorsWithoutSchedule' => $this->doctorsWithoutSchedule,
        ])->layout('layouts.app');
    }
}
