<?php

namespace App\Livewire\Patient;

use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\SystemSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Masmerise\Toaster\Toaster;
use Livewire\Component;

class BookAppointment extends Component
{
    public int $currentStep = 1;

    public int $maxStep = 1;

    public ?int $consultationTypeId = null;

    public ?string $appointmentDate = null;

    public string $patientType = 'self';

    public string $patientRelationship = 'child';

    public string $patientFirstName = '';

    public ?string $patientMiddleName = null;

    public string $patientLastName = '';

    public ?string $patientDateOfBirth = null;

    public ?string $patientGender = null;

    public string $chiefComplaints = '';

    /** @var array<int, array<string, mixed>> */
    public array $availableDates = [];

    public bool $showAvailabilityModal = false;

public function openAvailabilityModal(): void
{
    $this->showAvailabilityModal = true;
}

public function closeAvailabilityModal(): void
{
    $this->showAvailabilityModal = false;
}
    public function mount(): void
    {
        $this->fillSelfPatientDetails();
    }

    public function selectConsultationType(int $consultationTypeId): void
    {
        $this->consultationTypeId = $consultationTypeId;
        $this->appointmentDate = null;

        $this->availableDates = $this->buildAvailableDates();

        // $this->currentStep = 2;
        // $this->maxStep = 2;
    }

    public function updatedPatientType(string $value): void
    {
        if ($value === 'self') {
            $this->fillSelfPatientDetails();

            return;
        }

        $this->patientFirstName = '';
        $this->patientMiddleName = null;
        $this->patientLastName = '';
        $this->patientDateOfBirth = null;
        $this->patientGender = null;
        $this->patientRelationship = 'child';
    }

   public function selectDate(string $date): void
{
    $this->appointmentDate = $date;
    // rebuild so selected state updates
    $this->availableDates = $this->buildAvailableDates();
}


    public function goToStep(int $step): void
    {
        if ($step <= $this->maxStep && $step >= 1) {
            $this->currentStep = $step;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function nextStep(): void
    {
        if ($this->currentStep === 1) {
            $this->validate([
                'consultationTypeId' => ['required', 'exists:consultation_types,id'],
            ]);

            $this->currentStep = 2;
            $this->maxStep = max($this->maxStep, 2);

            return;
        }

        if ($this->currentStep === 2) {
            $this->validate($this->patientRules());

            $this->currentStep = 3;
            $this->maxStep = max($this->maxStep, 3);

            return;
        }

       if ($this->currentStep === 3) {
    $this->validate([
        'appointmentDate' => [
            'required',
            'date',
            'after_or_equal:today',
            function ($attribute, $value, $fail) {
                if (! $this->isDateAvailable((string) $value)) {
                    $fail(__('Selected date is not available. Please choose another date.'));
                }
            },
        ],
    ]);

    $this->currentStep = 4;
    $this->maxStep = max($this->maxStep, 4);
}

    }

    public function submitAppointment(): void
    {
        $this->validate(array_merge([
            'consultationTypeId' => ['required', 'exists:consultation_types,id'],
            'appointmentDate' => ['required', 'date', 'after_or_equal:today', function ($attribute, $value, $fail) {
            if (! $this->isDateAvailable((string) $value)) {
                $fail(__('Selected date is not available.'));
            }
        },],
            'chiefComplaints' => ['required', 'string', 'min:10', 'max:2000'],
        ], $this->patientRules()));

        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $patient = $this->resolvePatientDetails();

        Appointment::create([
            'user_id' => $user->id,
            'consultation_type_id' => $this->consultationTypeId,
            'doctor_id' => null,
            'patient_first_name' => $patient['first_name'],
            'patient_middle_name' => $patient['middle_name'],
            'patient_last_name' => $patient['last_name'],
            'patient_date_of_birth' => $patient['date_of_birth'],
            'patient_gender' => $patient['gender'],
            'patient_phone' => $patient['phone'],
            'patient_province' => $patient['province'],
            'patient_municipality' => $patient['municipality'],
            'patient_barangay' => $patient['barangay'],
            'patient_street' => $patient['street'],
            'relationship_to_account' => $patient['relationship'],
            'appointment_date' => $this->appointmentDate,
            'appointment_time' => null,
            'chief_complaints' => $this->chiefComplaints,
            'status' => 'pending',
            'source' => 'online',
        ]);

        Toaster::success(__('Appointment request submitted. We will confirm your schedule soon.'));

        $this->redirect(route('patient.appointments'), navigate: true);
    }

    /** @return array<string, array<int, string>> */
    protected function patientRules(): array
    {
        $rules = [
            'patientType' => ['required', Rule::in(['self', 'dependent'])],
        ];

        if ($this->patientType === 'dependent') {
            $rules = array_merge($rules, [
                'patientFirstName' => ['required', 'string', 'max:255'],
                'patientMiddleName' => ['nullable', 'string', 'max:255'],
                'patientLastName' => ['required', 'string', 'max:255'],
                'patientDateOfBirth' => ['required', 'date', 'before_or_equal:today'],
                'patientGender' => ['required', Rule::in(['male', 'female'])],
                'patientRelationship' => ['required', Rule::in(['child', 'spouse', 'parent', 'sibling', 'other'])],
            ]);
        }

        return $rules;
    }

    /** @return array<string, mixed> */
    protected function resolvePatientDetails(): array
    {
        $user = Auth::user();
        $info = $user?->personalInformation;

        if ($this->patientType === 'self') {
            return [
                'first_name' => $info?->first_name ?? '',
                'middle_name' => $info?->middle_name,
                'last_name' => $info?->last_name ?? '',
                'date_of_birth' => $info?->date_of_birth?->format('Y-m-d'),
                'gender' => $info?->gender,
                'phone' => $info?->phone,
                'province' => $info?->province,
                'municipality' => $info?->municipality,
                'barangay' => $info?->barangay,
                'street' => $info?->street,
                'relationship' => 'self',
            ];
        }

        return [
            'first_name' => $this->patientFirstName,
            'middle_name' => $this->patientMiddleName,
            'last_name' => $this->patientLastName,
            'date_of_birth' => $this->patientDateOfBirth,
            'gender' => $this->patientGender,
            'phone' => $info?->phone,
            'province' => $info?->province,
            'municipality' => $info?->municipality,
            'barangay' => $info?->barangay,
            'street' => $info?->street,
            'relationship' => $this->patientRelationship,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    protected function buildAvailableDates(): array
{
    if (! $this->consultationTypeId) return [];

    $type = ConsultationType::find($this->consultationTypeId);
    if (! $type) return [];

    $maxAdvanceDays = (int) SystemSetting::get('max_advance_booking_days', 30);
    $allowSameDay = (bool) SystemSetting::get('allow_same_day_booking', true);
    $daysToShow = min($maxAdvanceDays, 14);

    // IMPORTANT: force timezone consistency
    $tz = config('app.timezone', 'Asia/Manila');

    $today = Carbon::now($tz)->startOfDay();
    $startDate = $allowSameDay ? $today->copy() : $today->copy()->addDay();

    $dates = [];

    for ($i = 0; $i < $daysToShow; $i++) {
        $date = $startDate->copy()->addDays($i);
        $dateString = $date->toDateString();

        $available = $type->isAcceptingAppointments($dateString);

        $dates[] = [
            'date' => $dateString,
            'month' => $date->format('M'),
            'day' => $date->format('d'),
            'day_name' => $date->format('D'),
            'formatted' => $date->format('M d, Y'),
            'available' => $available,
            'label' => $available ? 'Available' : 'Unavailable',
            'is_today' => $date->isSameDay($today),
            'is_selected' => $this->appointmentDate === $dateString,
        ];
    }

    return $dates;
}


    /** @return array<int, array<string, mixed>> */
    protected function buildDoctorAvailability(?ConsultationType $consultationType): array
    {
        if (! $consultationType) {
            return [];
        }

        $schedules = $consultationType->doctorSchedules;

        if ($schedules->isNotEmpty()) {
            $schedules->loadMissing('doctor');
        }

        if ($schedules->isEmpty()) {
            return [];
        }

        $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        return $schedules
            ->groupBy('user_id')
            ->map(function ($items) use ($dayNames): array {
                $doctor = $items->first()?->doctor;
                $regularDays = $items
                    ->where('schedule_type', 'regular')
                    ->pluck('day_of_week')
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values();

                $dayLabels = $regularDays
                    ->map(fn (int $day) => $dayNames[$day] ?? null)
                    ->filter()
                    ->values()
                    ->all();

                $timeRanges = $items
                    ->where('schedule_type', 'regular')
                    ->map(function ($schedule): ?string {
                        $start = $schedule->start_time?->format('H:i');
                        $end = $schedule->end_time?->format('H:i');

                        if (! $start && ! $end) {
                            return null;
                        }

                        return trim(($start ?? '') . ' - ' . ($end ?? ''));
                    })
                    ->filter()
                    ->unique()
                    ->values();

                $hoursLabel = null;
                if ($timeRanges->count() === 1) {
                    $hoursLabel = $timeRanges->first();
                } elseif ($timeRanges->count() > 1) {
                    $hoursLabel = 'Varies by day';
                }

                $unavailableDates = $items
                    ->where('schedule_type', 'exception')
                    ->where('is_available', false)
                    ->pluck('date')
                    ->filter()
                    ->map(fn ($date) => Carbon::parse($date)->format('M d'))
                    ->values()
                    ->all();

                $extraDates = $items
                    ->where('schedule_type', 'exception')
                    ->where('is_available', true)
                    ->pluck('date')
                    ->filter()
                    ->map(fn ($date) => Carbon::parse($date)->format('M d'))
                    ->values()
                    ->all();

                return [
                    'name' => $doctor?->name ?? __('Doctor'),
                    'days' => $dayLabels,
                    'hours' => $hoursLabel,
                    'unavailable' => $unavailableDates,
                    'extra' => $extraDates,
                ];
            })
            ->values()
            ->all();
    }

    protected function fillSelfPatientDetails(): void
    {
        $info = Auth::user()?->personalInformation;

        if (! $info) {
            return;
        }

        $this->patientFirstName = $info->first_name;
        $this->patientMiddleName = $info->middle_name;
        $this->patientLastName = $info->last_name;
        $this->patientDateOfBirth = $info->date_of_birth?->format('Y-m-d');
        $this->patientGender = $info->gender;
    }

  protected function isDateAvailable(string $date): bool
{
    return collect($this->availableDates)
        ->firstWhere('date', $date)['available'] ?? false;
}


    public function render(): View
    {
        $consultationTypes = ConsultationType::query()
            ->where('is_active', true)
            ->withCount('doctors')
            ->with(['doctors', 'doctorSchedules.doctor'])
            ->get();

        $selectedConsultation = $consultationTypes->firstWhere('id', $this->consultationTypeId);
        $availableDoctors = $selectedConsultation?->doctors ?? collect();
        $doctorAvailability = $this->buildDoctorAvailability($selectedConsultation);
        $doctorAvailabilityByType = $consultationTypes
            ->map(fn (ConsultationType $type) => [
                'type' => $type,
                'availability' => $this->buildDoctorAvailability($type),
            ])
            ->values()
            ->all();

        if ($this->consultationTypeId && ! $this->availableDates) {
            $this->availableDates = $this->buildAvailableDates();
        }

        $selectedDate = collect($this->availableDates)->firstWhere('date', $this->appointmentDate);

        return view('livewire.patient.book-appointment', [
            'consultationTypes' => $consultationTypes,
            'selectedConsultation' => $selectedConsultation,
            'availableDoctors' => $availableDoctors,
            'doctorAvailability' => $doctorAvailability,
          
            'availableDates' => $this->availableDates,
            'selectedDate' => $selectedDate,
        ])
            ->layout('layouts.app');
    }
}
