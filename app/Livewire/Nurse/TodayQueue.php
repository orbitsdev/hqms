<?php

namespace App\Livewire\Nurse;

use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\MedicalRecord;
use App\Models\Queue;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class TodayQueue extends Component
{
    public string $status = 'waiting';

    public string $consultationTypeFilter = '';

    // Check-in modal
    public bool $showCheckInModal = false;

    #[Locked]
    public ?int $checkInAppointmentId = null;

    // Vital signs modal
    public bool $showVitalSignsModal = false;

    #[Locked]
    public ?int $vitalSignsQueueId = null;

    // Vital signs data
    public ?string $temperature = null;

    public ?string $bloodPressure = null;

    public ?int $cardiacRate = null;

    public ?int $respiratoryRate = null;

    public ?string $weight = null;

    public ?string $height = null;

    public ?string $headCircumference = null;

    public ?string $chestCircumference = null;

    public ?int $fetalHeartTone = null;

    public ?string $fundalHeight = null;

    public ?string $lastMenstrualPeriod = null;

    public ?string $chiefComplaintsUpdated = null;

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /** @return array<string, int> */
    public function getStatusCountsProperty(): array
    {
        $baseQuery = Queue::query()->today();

        return [
            'all' => (clone $baseQuery)->count(),
            'waiting' => (clone $baseQuery)->where('status', 'waiting')->count(),
            'called' => (clone $baseQuery)->where('status', 'called')->count(),
            'serving' => (clone $baseQuery)->where('status', 'serving')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
        ];
    }

    /** @return array<string, int> */
    public function getPendingCheckInsProperty(): array
    {
        return [
            'count' => Appointment::query()
                ->where('status', 'approved')
                ->whereDate('appointment_date', today())
                ->count(),
        ];
    }

    // Check-in Methods
    public function openCheckInModal(int $appointmentId): void
    {
        $this->checkInAppointmentId = $appointmentId;
        $this->showCheckInModal = true;
    }

    public function closeCheckInModal(): void
    {
        $this->showCheckInModal = false;
        $this->checkInAppointmentId = null;
    }

    public function confirmCheckIn(): void
    {
        if (! $this->checkInAppointmentId) {
            return;
        }

        $appointment = Appointment::with('queue')->find($this->checkInAppointmentId);

        if (! $appointment || $appointment->status !== 'approved') {
            Toaster::error(__('This appointment cannot be checked in.'));
            $this->closeCheckInModal();

            return;
        }

        $appointment->update([
            'status' => 'checked_in',
            'checked_in_at' => now(),
        ]);

        Toaster::success(__('Patient checked in successfully.'));

        $this->closeCheckInModal();
    }

    // Queue Actions
    public function callPatient(int $queueId): void
    {
        $queue = Queue::find($queueId);

        if (! $queue || $queue->status !== 'waiting') {
            Toaster::error(__('This patient cannot be called.'));

            return;
        }

        $queue->update([
            'status' => 'called',
            'called_at' => now(),
        ]);

        if ($queue->appointment?->user) {
            $queue->appointment->user->notify(new GenericNotification([
                'type' => 'queue.called',
                'title' => __('Your Turn'),
                'message' => __('Queue :number, please proceed to the nurse station.', [
                    'number' => $queue->formatted_number,
                ]),
                'queue_id' => $queue->id,
                'sender_id' => Auth::id(),
                'sender_role' => 'nurse',
            ]));
        }

        Toaster::success(__('Patient called: :number', ['number' => $queue->formatted_number]));
    }

    public function startServing(int $queueId): void
    {
        $queue = Queue::with(['appointment', 'consultationType'])->find($queueId);

        if (! $queue || ! in_array($queue->status, ['waiting', 'called'])) {
            Toaster::error(__('Cannot start serving this patient.'));

            return;
        }

        $nurse = Auth::user();

        DB::transaction(function () use ($queue, $nurse): void {
            $queue->update([
                'status' => 'serving',
                'serving_started_at' => now(),
                'served_by' => $nurse->id,
            ]);

            if ($queue->appointment) {
                $queue->appointment->update([
                    'status' => 'in_progress',
                ]);
            }

            // Create medical record if not exists
            if (! $queue->medicalRecord) {
                $appointment = $queue->appointment;

                MedicalRecord::create([
                    'user_id' => $queue->user_id,
                    'consultation_type_id' => $queue->consultation_type_id,
                    'appointment_id' => $appointment?->id,
                    'queue_id' => $queue->id,
                    'nurse_id' => $nurse->id,
                    'patient_first_name' => $appointment?->patient_first_name ?? 'Walk-in',
                    'patient_middle_name' => $appointment?->patient_middle_name,
                    'patient_last_name' => $appointment?->patient_last_name ?? 'Patient',
                    'patient_date_of_birth' => $appointment?->patient_date_of_birth,
                    'patient_gender' => $appointment?->patient_gender,
                    'patient_contact_number' => $appointment?->patient_phone,
                    'patient_province' => $appointment?->patient_province,
                    'patient_municipality' => $appointment?->patient_municipality,
                    'patient_barangay' => $appointment?->patient_barangay,
                    'patient_street' => $appointment?->patient_street,
                    'visit_date' => today(),
                    'time_in' => now(),
                    'time_in_period' => now()->format('a'),
                    'visit_type' => 'new',
                    'service_type' => 'checkup',
                    'chief_complaints_initial' => $appointment?->chief_complaints,
                    'status' => 'in_progress',
                ]);
            }
        });

        Toaster::success(__('Now serving patient: :number', ['number' => $queue->formatted_number]));
    }

    public function skipPatient(int $queueId): void
    {
        $queue = Queue::find($queueId);

        if (! $queue || ! in_array($queue->status, ['waiting', 'called'])) {
            Toaster::error(__('Cannot skip this patient.'));

            return;
        }

        $queue->update([
            'status' => 'skipped',
        ]);

        Toaster::success(__('Patient skipped: :number', ['number' => $queue->formatted_number]));
    }

    public function requeuePatient(int $queueId): void
    {
        $queue = Queue::find($queueId);

        if (! $queue || $queue->status !== 'skipped') {
            Toaster::error(__('Cannot requeue this patient.'));

            return;
        }

        $queue->update([
            'status' => 'waiting',
            'called_at' => null,
        ]);

        Toaster::success(__('Patient requeued: :number', ['number' => $queue->formatted_number]));
    }

    // Vital Signs Methods
    public function openVitalSignsModal(int $queueId): void
    {
        $queue = Queue::with('medicalRecord')->find($queueId);

        if (! $queue) {
            return;
        }

        $this->vitalSignsQueueId = $queueId;

        // Pre-fill with existing data if available
        if ($record = $queue->medicalRecord) {
            $this->temperature = $record->temperature;
            $this->bloodPressure = $record->blood_pressure;
            $this->cardiacRate = $record->cardiac_rate;
            $this->respiratoryRate = $record->respiratory_rate;
            $this->weight = $record->weight;
            $this->height = $record->height;
            $this->headCircumference = $record->head_circumference;
            $this->chestCircumference = $record->chest_circumference;
            $this->fetalHeartTone = $record->fetal_heart_tone;
            $this->fundalHeight = $record->fundal_height;
            $this->lastMenstrualPeriod = $record->last_menstrual_period?->format('Y-m-d');
            $this->chiefComplaintsUpdated = $record->chief_complaints_updated;
        }

        $this->showVitalSignsModal = true;
    }

    public function closeVitalSignsModal(): void
    {
        $this->showVitalSignsModal = false;
        $this->vitalSignsQueueId = null;
        $this->resetVitalSignsForm();
    }

    protected function resetVitalSignsForm(): void
    {
        $this->temperature = null;
        $this->bloodPressure = null;
        $this->cardiacRate = null;
        $this->respiratoryRate = null;
        $this->weight = null;
        $this->height = null;
        $this->headCircumference = null;
        $this->chestCircumference = null;
        $this->fetalHeartTone = null;
        $this->fundalHeight = null;
        $this->lastMenstrualPeriod = null;
        $this->chiefComplaintsUpdated = null;
    }

    public function saveVitalSigns(): void
    {
        $this->validate([
            'temperature' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'bloodPressure' => ['nullable', 'string', 'max:20', 'regex:/^\d{2,3}\/\d{2,3}$/'],
            'cardiacRate' => ['nullable', 'integer', 'min:30', 'max:250'],
            'respiratoryRate' => ['nullable', 'integer', 'min:5', 'max:60'],
            'weight' => ['nullable', 'numeric', 'min:0.1', 'max:500'],
            'height' => ['nullable', 'numeric', 'min:10', 'max:300'],
            'headCircumference' => ['nullable', 'numeric', 'min:20', 'max:100'],
            'chestCircumference' => ['nullable', 'numeric', 'min:20', 'max:200'],
            'fetalHeartTone' => ['nullable', 'integer', 'min:60', 'max:200'],
            'fundalHeight' => ['nullable', 'numeric', 'min:5', 'max:50'],
            'lastMenstrualPeriod' => ['nullable', 'date', 'before_or_equal:today'],
            'chiefComplaintsUpdated' => ['nullable', 'string', 'max:2000'],
        ], [
            'bloodPressure.regex' => __('Blood pressure format: 120/80'),
        ]);

        $queue = Queue::with('medicalRecord')->find($this->vitalSignsQueueId);

        if (! $queue || ! $queue->medicalRecord) {
            Toaster::error(__('Medical record not found.'));
            $this->closeVitalSignsModal();

            return;
        }

        $queue->medicalRecord->update([
            'temperature' => $this->temperature,
            'blood_pressure' => $this->bloodPressure,
            'cardiac_rate' => $this->cardiacRate,
            'respiratory_rate' => $this->respiratoryRate,
            'weight' => $this->weight,
            'height' => $this->height,
            'head_circumference' => $this->headCircumference,
            'chest_circumference' => $this->chestCircumference,
            'fetal_heart_tone' => $this->fetalHeartTone,
            'fundal_height' => $this->fundalHeight,
            'last_menstrual_period' => $this->lastMenstrualPeriod,
            'chief_complaints_updated' => $this->chiefComplaintsUpdated,
            'vital_signs_recorded_at' => now(),
        ]);

        Toaster::success(__('Vital signs saved successfully.'));

        $this->closeVitalSignsModal();
    }

    public function forwardToDoctor(int $queueId): void
    {
        $queue = Queue::with(['medicalRecord', 'appointment', 'consultationType'])->find($queueId);

        if (! $queue || $queue->status !== 'serving') {
            Toaster::error(__('Cannot forward this patient.'));

            return;
        }

        if (! $queue->medicalRecord?->vital_signs_recorded_at) {
            Toaster::error(__('Please record vital signs before forwarding to doctor.'));

            return;
        }

        DB::transaction(function () use ($queue): void {
            $queue->update([
                'status' => 'completed',
                'serving_ended_at' => now(),
            ]);

            if ($queue->medicalRecord) {
                $queue->medicalRecord->update([
                    'status' => 'in_progress',
                ]);
            }

            // Notify doctors of the consultation type
            $doctors = User::role('doctor')
                ->whereHas('consultationTypes', function ($q) use ($queue) {
                    $q->where('consultation_type_id', $queue->consultation_type_id);
                })
                ->get();

            foreach ($doctors as $doctor) {
                $doctor->notify(new GenericNotification([
                    'type' => 'queue.ready_for_doctor',
                    'title' => __('Patient Ready'),
                    'message' => __(':name is ready for consultation.', [
                        'name' => $queue->appointment?->patient_first_name ?? 'Patient',
                    ]),
                    'queue_id' => $queue->id,
                    'medical_record_id' => $queue->medicalRecord?->id,
                    'sender_id' => Auth::id(),
                    'sender_role' => 'nurse',
                ]));
            }
        });

        Toaster::success(__('Patient forwarded to doctor successfully.'));
    }

    public function render(): View
    {
        $queues = Queue::query()
            ->with([
                'appointment.user',
                'consultationType',
                'medicalRecord',
                'servedBy',
            ])
            ->today()
            ->when($this->status !== 'all', fn (Builder $q) => $q->where('status', $this->status))
            ->when($this->consultationTypeFilter !== '', fn (Builder $q) => $q->where('consultation_type_id', $this->consultationTypeFilter))
            ->orderByRaw("FIELD(priority, 'emergency', 'urgent', 'normal')")
            ->orderBy('queue_number')
            ->get();

        $pendingCheckIns = Appointment::query()
            ->with(['user.personalInformation', 'consultationType', 'queue'])
            ->where('status', 'approved')
            ->whereDate('appointment_date', today())
            ->orderBy('appointment_time')
            ->get();

        $consultationTypes = ConsultationType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $currentServing = Queue::query()
            ->with('consultationType')
            ->today()
            ->serving()
            ->get()
            ->groupBy('consultation_type_id');

        return view('livewire.nurse.today-queue', [
            'queues' => $queues,
            'pendingCheckIns' => $pendingCheckIns,
            'consultationTypes' => $consultationTypes,
            'statusCounts' => $this->statusCounts,
            'currentServing' => $currentServing,
        ])->layout('layouts.app');
    }
}
