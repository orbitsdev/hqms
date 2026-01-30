<?php

namespace App\Livewire\Nurse;

use App\Events\QueueUpdated;
use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\MedicalRecord;
use App\Models\Queue;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Services\QueueSmsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class TodayQueue extends Component
{
    /**
     * Refresh queue when updates come in via Echo.
     */
    #[On('echo-private:queue.staff,queue.updated')]
    public function refreshOnQueueUpdate(): void
    {
        // Component will automatically re-render
    }

    public string $status = 'waiting';

    public string $consultationTypeFilter = '';

    public string $search = '';

    // Selected queue for detail view
    #[Locked]
    public ?int $selectedQueueId = null;

    // Check-in modal
    public bool $showCheckInModal = false;

    #[Locked]
    public ?int $checkInAppointmentId = null;

    // Stop serving modal
    public bool $showStopServingModal = false;

    #[Locked]
    public ?int $stopServingQueueId = null;

    // Skip patient modal
    public bool $showSkipModal = false;

    public bool $skipConfirmed = false;

    #[Locked]
    public ?int $skipQueueId = null;

    // Requeue patient modal
    public bool $showRequeueModal = false;

    #[Locked]
    public ?int $requeueQueueId = null;

    // Priority modal
    public bool $showPriorityModal = false;

    #[Locked]
    public ?int $priorityQueueId = null;

    public string $selectedPriority = 'normal';

    // Print ticket modal
    public bool $showPrintTicketModal = false;

    #[Locked]
    public ?int $printTicketQueueId = null;

    // Patient Interview Modal
    public bool $showInterviewModal = false;

    #[Locked]
    public ?int $interviewQueueId = null;

    public string $interviewStep = 'patient';

    // Patient Information
    public ?string $patientFirstName = null;

    public ?string $patientMiddleName = null;

    public ?string $patientLastName = null;

    public ?string $patientDateOfBirth = null;

    public ?string $patientGender = null;

    public ?string $patientContactNumber = null;

    public ?string $patientEmail = null;

    // Address
    public ?string $patientProvince = null;

    public ?string $patientMunicipality = null;

    public ?string $patientBarangay = null;

    public ?string $patientStreet = null;

    public ?string $patientZipCode = null;

    // Companion Information
    public ?string $companionName = null;

    public ?string $companionContact = null;

    public ?string $companionRelationship = null;

    // Emergency Contact
    public ?string $emergencyContactName = null;

    public ?string $emergencyContactNumber = null;

    public ?string $emergencyContactRelationship = null;

    // Medical Background
    public ?string $patientBloodType = null;

    public ?string $patientAllergies = null;

    public ?string $patientChronicConditions = null;

    public ?string $patientCurrentMedications = null;

    public ?string $patientPastMedicalHistory = null;

    public ?string $patientFamilyMedicalHistory = null;

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

    public function setConsultationType(string $typeId): void
    {
        $this->consultationTypeFilter = $typeId;
        // Reset to waiting status when changing type for better UX
        $this->status = 'waiting';
        $this->selectedQueueId = null;
    }

    public function selectQueue(int $queueId): void
    {
        $this->selectedQueueId = $queueId;
    }

    public function clearSelection(): void
    {
        $this->selectedQueueId = null;
    }

    /** @return array<string, int> */
    public function getStatusCountsProperty(): array
    {
        $baseQuery = Queue::query()
            ->today()
            ->when($this->consultationTypeFilter !== '', fn (Builder $q) => $q->where('consultation_type_id', $this->consultationTypeFilter));

        return [
            'all' => (clone $baseQuery)->count(),
            'waiting' => (clone $baseQuery)->where('status', 'waiting')->count(),
            'called' => (clone $baseQuery)->where('status', 'called')->count(),
            'serving' => (clone $baseQuery)->where('status', 'serving')->count(),
            'skipped' => (clone $baseQuery)->where('status', 'skipped')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
        ];
    }

    /** @return array<int, array<string, int>> */
    public function getTypeCountsProperty(): array
    {
        $counts = Queue::query()
            ->today()
            ->selectRaw('consultation_type_id, COUNT(*) as total')
            ->groupBy('consultation_type_id')
            ->pluck('total', 'consultation_type_id')
            ->toArray();

        $counts['all'] = array_sum($counts);

        return $counts;
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

    public function getInterviewQueueProperty(): ?Queue
    {
        if (! $this->interviewQueueId) {
            return null;
        }

        return Queue::with(['medicalRecord', 'consultationType', 'appointment'])
            ->find($this->interviewQueueId);
    }

    /**
     * Get vital signs alerts based on current values.
     *
     * @return array<string, array{level: string, message: string}>
     */
    public function getVitalAlertsProperty(): array
    {
        $alerts = [];

        // Temperature checks
        if ($this->temperature !== null && $this->temperature !== '') {
            $temp = (float) $this->temperature;
            if ($temp >= 39.0) {
                $alerts['temperature'] = ['level' => 'danger', 'message' => __('High fever')];
            } elseif ($temp >= 38.0) {
                $alerts['temperature'] = ['level' => 'warning', 'message' => __('Fever')];
            } elseif ($temp <= 35.0) {
                $alerts['temperature'] = ['level' => 'danger', 'message' => __('Hypothermia')];
            } elseif ($temp <= 36.0) {
                $alerts['temperature'] = ['level' => 'warning', 'message' => __('Low temperature')];
            }
        }

        // Blood pressure checks
        if ($this->bloodPressure !== null && $this->bloodPressure !== '') {
            if (preg_match('/^(\d+)\/(\d+)$/', $this->bloodPressure, $matches)) {
                $systolic = (int) $matches[1];
                $diastolic = (int) $matches[2];

                if ($systolic >= 180 || $diastolic >= 120) {
                    $alerts['bloodPressure'] = ['level' => 'danger', 'message' => __('Hypertensive crisis')];
                } elseif ($systolic >= 140 || $diastolic >= 90) {
                    $alerts['bloodPressure'] = ['level' => 'warning', 'message' => __('High BP')];
                } elseif ($systolic <= 90 || $diastolic <= 60) {
                    $alerts['bloodPressure'] = ['level' => 'warning', 'message' => __('Low BP')];
                }
            }
        }

        // Heart rate checks
        if ($this->cardiacRate !== null) {
            if ($this->cardiacRate >= 120) {
                $alerts['cardiacRate'] = ['level' => 'danger', 'message' => __('Tachycardia')];
            } elseif ($this->cardiacRate >= 100) {
                $alerts['cardiacRate'] = ['level' => 'warning', 'message' => __('Elevated HR')];
            } elseif ($this->cardiacRate <= 50) {
                $alerts['cardiacRate'] = ['level' => 'danger', 'message' => __('Bradycardia')];
            } elseif ($this->cardiacRate <= 60) {
                $alerts['cardiacRate'] = ['level' => 'warning', 'message' => __('Low HR')];
            }
        }

        // Respiratory rate checks
        if ($this->respiratoryRate !== null) {
            if ($this->respiratoryRate >= 25) {
                $alerts['respiratoryRate'] = ['level' => 'danger', 'message' => __('Tachypnea')];
            } elseif ($this->respiratoryRate >= 20) {
                $alerts['respiratoryRate'] = ['level' => 'warning', 'message' => __('Elevated RR')];
            } elseif ($this->respiratoryRate <= 10) {
                $alerts['respiratoryRate'] = ['level' => 'danger', 'message' => __('Bradypnea')];
            } elseif ($this->respiratoryRate <= 12) {
                $alerts['respiratoryRate'] = ['level' => 'warning', 'message' => __('Low RR')];
            }
        }

        // Fetal heart tone checks (for OB)
        if ($this->fetalHeartTone !== null) {
            if ($this->fetalHeartTone >= 180) {
                $alerts['fetalHeartTone'] = ['level' => 'danger', 'message' => __('Fetal tachycardia')];
            } elseif ($this->fetalHeartTone >= 160) {
                $alerts['fetalHeartTone'] = ['level' => 'warning', 'message' => __('Elevated FHT')];
            } elseif ($this->fetalHeartTone <= 100) {
                $alerts['fetalHeartTone'] = ['level' => 'danger', 'message' => __('Fetal bradycardia')];
            } elseif ($this->fetalHeartTone <= 110) {
                $alerts['fetalHeartTone'] = ['level' => 'warning', 'message' => __('Low FHT')];
            }
        }

        return $alerts;
    }

    /**
     * Get patient's previous medical records for history preview.
     *
     * @return \Illuminate\Support\Collection<int, MedicalRecord>
     */
    public function getPatientHistoryProperty(): \Illuminate\Support\Collection
    {
        if (! $this->interviewQueueId) {
            return collect();
        }

        $queue = Queue::with('appointment')->find($this->interviewQueueId);

        if (! $queue || ! $queue->appointment) {
            return collect();
        }

        // Try to find previous records by matching patient name and DOB
        $firstName = $queue->appointment->patient_first_name;
        $lastName = $queue->appointment->patient_last_name;
        $dob = $queue->appointment->patient_date_of_birth;

        return MedicalRecord::query()
            ->where('patient_first_name', $firstName)
            ->where('patient_last_name', $lastName)
            ->when($dob, fn ($q) => $q->where('patient_date_of_birth', $dob))
            ->where('id', '!=', $queue->medicalRecord?->id ?? 0)
            ->whereNotNull('vital_signs_recorded_at')
            ->with('consultationType')
            ->orderByDesc('visit_date')
            ->limit(5)
            ->get();
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
        $queue = Queue::with(['appointment', 'consultationType'])->find($queueId);

        if (! $queue || $queue->status !== 'waiting') {
            Toaster::error(__('This patient cannot be called.'));

            return;
        }

        $queue->update([
            'status' => 'called',
            'called_at' => now(),
        ]);

        // Broadcast queue update
        event(new QueueUpdated($queue->fresh(), 'called'));

        // Send in-app notification
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

        // Send SMS notification if enabled and not already notified
        // TODO: Uncomment when SMS is configured in production
        // $cacheKey = "queue_sms_called_{$queue->id}";
        // if (! Cache::has($cacheKey)) {
        //     $smsService = app(QueueSmsService::class);
        //     $smsService->notifyPatientCalled($queue);
        //     Cache::put($cacheKey, true, now()->endOfDay());
        // }

        Toaster::success(__('Patient called: :number', ['number' => $queue->formatted_number]));
    }

    public function requeueCalled(int $queueId): void
    {
        $queue = Queue::find($queueId);

        if (! $queue || $queue->status !== 'called') {
            Toaster::error(__('Cannot requeue this patient.'));

            return;
        }

        $formattedNumber = $queue->formatted_number;

        $queue->update([
            'status' => 'waiting',
            'called_at' => null,
        ]);

        // Broadcast queue update
        event(new QueueUpdated($queue->fresh(), 'requeued'));

        Toaster::success(__('Patient requeued: :number', ['number' => $formattedNumber]));
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

        // Broadcast queue update
        event(new QueueUpdated($queue->fresh(), 'serving'));

        // Automatically switch to the "Serving" tab
        $this->status = 'serving';

        Toaster::success(__('Now serving patient: :number', ['number' => $queue->formatted_number]));
    }

    public function openSkipModal(int $queueId): void
    {
        $queue = Queue::find($queueId);

        if (! $queue || ! in_array($queue->status, ['waiting', 'called'])) {
            Toaster::error(__('Cannot skip this patient.'));

            return;
        }

        $this->skipQueueId = $queueId;
        $this->showSkipModal = true;
    }

    public function closeSkipModal(): void
    {
        $this->showSkipModal = false;
        $this->skipQueueId = null;
        $this->skipConfirmed = false;
    }

    public function confirmSkip(): void
    {
        if (! $this->skipQueueId) {
            return;
        }

        $queue = Queue::find($this->skipQueueId);

        if (! $queue || ! in_array($queue->status, ['waiting', 'called'])) {
            Toaster::error(__('Cannot skip this patient.'));
            $this->closeSkipModal();

            return;
        }

        $queue->update([
            'status' => 'skipped',
        ]);

        // Broadcast queue update
        event(new QueueUpdated($queue->fresh(), 'skipped'));

        // Show success state with requeue option
        $this->skipConfirmed = true;
    }

    public function requeueFromSkipModal(): void
    {
        if (! $this->skipQueueId) {
            return;
        }

        $queue = Queue::find($this->skipQueueId);

        if (! $queue || $queue->status !== 'skipped') {
            Toaster::error(__('Cannot requeue this patient.'));
            $this->closeSkipModal();

            return;
        }

        $formattedNumber = $queue->formatted_number;

        $queue->update([
            'status' => 'waiting',
            'called_at' => null,
        ]);

        // Broadcast queue update
        event(new QueueUpdated($queue->fresh(), 'requeued'));

        Toaster::success(__('Patient requeued: :number', ['number' => $formattedNumber]));

        $this->closeSkipModal();
    }

    public function serveNextAvailable(): void
    {
        $nextQueue = Queue::query()
            ->today()
            ->whereIn('status', ['waiting', 'called'])
            ->when($this->consultationTypeFilter !== '', fn (Builder $q) => $q->where('consultation_type_id', $this->consultationTypeFilter))
            ->orderByRaw("CASE priority WHEN 'emergency' THEN 1 WHEN 'urgent' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END")
            ->orderBy('queue_number')
            ->first();

        if (! $nextQueue) {
            Toaster::info(__('No patients waiting in queue.'));

            return;
        }

        $this->startServing($nextQueue->id);
    }

    public function openRequeueModal(int $queueId): void
    {
        $queue = Queue::find($queueId);

        if (! $queue || $queue->status !== 'skipped') {
            Toaster::error(__('Cannot requeue this patient.'));

            return;
        }

        $this->requeueQueueId = $queueId;
        $this->showRequeueModal = true;
    }

    public function closeRequeueModal(): void
    {
        $this->showRequeueModal = false;
        $this->requeueQueueId = null;
    }

    public function confirmRequeue(): void
    {
        if (! $this->requeueQueueId) {
            return;
        }

        $queue = Queue::find($this->requeueQueueId);

        if (! $queue || $queue->status !== 'skipped') {
            Toaster::error(__('Cannot requeue this patient.'));
            $this->closeRequeueModal();

            return;
        }

        $formattedNumber = $queue->formatted_number;

        $queue->update([
            'status' => 'waiting',
            'called_at' => null,
        ]);

        // Broadcast queue update
        event(new QueueUpdated($queue->fresh(), 'requeued'));

        Toaster::success(__('Patient requeued: :number', ['number' => $formattedNumber]));

        $this->closeRequeueModal();
    }

    public function openStopServingModal(int $queueId): void
    {
        $queue = Queue::find($queueId);

        if (! $queue || $queue->status !== 'serving') {
            Toaster::error(__('Cannot stop serving this patient.'));

            return;
        }

        $this->stopServingQueueId = $queueId;
        $this->showStopServingModal = true;
    }

    public function closeStopServingModal(): void
    {
        $this->showStopServingModal = false;
        $this->stopServingQueueId = null;
    }

    public function confirmStopServing(): void
    {
        if (! $this->stopServingQueueId) {
            return;
        }

        $queue = Queue::with(['medicalRecord', 'appointment'])->find($this->stopServingQueueId);

        if (! $queue || $queue->status !== 'serving') {
            Toaster::error(__('Cannot stop serving this patient.'));
            $this->closeStopServingModal();

            return;
        }

        $formattedNumber = $queue->formatted_number;

        DB::transaction(function () use ($queue): void {
            // Delete medical record if no vital signs were recorded yet
            if ($queue->medicalRecord && ! $queue->medicalRecord->vital_signs_recorded_at) {
                $queue->medicalRecord->delete();
            }

            // Reset appointment status if it was changed
            if ($queue->appointment && $queue->appointment->status === 'in_progress') {
                $queue->appointment->update([
                    'status' => 'checked_in',
                ]);
            }

            // Reset queue to waiting state
            $queue->update([
                'status' => 'waiting',
                'serving_started_at' => null,
                'served_by' => null,
                'called_at' => null,
            ]);
        });

        // Broadcast queue update
        event(new QueueUpdated($queue->fresh(), 'stopped'));

        Toaster::success(__('Stopped serving patient: :number', ['number' => $formattedNumber]));

        $this->closeStopServingModal();
    }

    // Patient Interview Methods
    public function openInterviewModal(int $queueId): void
    {
        $queue = Queue::with('medicalRecord')->find($queueId);

        if (! $queue) {
            return;
        }

        $this->interviewQueueId = $queueId;
        $this->interviewStep = 'patient';

        // Pre-fill with existing data if available
        if ($record = $queue->medicalRecord) {
            // Patient Information
            $this->patientFirstName = $record->patient_first_name;
            $this->patientMiddleName = $record->patient_middle_name;
            $this->patientLastName = $record->patient_last_name;
            $this->patientDateOfBirth = $record->patient_date_of_birth?->format('Y-m-d');
            $this->patientGender = $record->patient_gender;
            $this->patientContactNumber = $record->patient_contact_number;
            $this->patientEmail = $record->patient_email;

            // Address
            $this->patientProvince = $record->patient_province;
            $this->patientMunicipality = $record->patient_municipality;
            $this->patientBarangay = $record->patient_barangay;
            $this->patientStreet = $record->patient_street;
            $this->patientZipCode = $record->patient_zip_code;

            // Companion
            $this->companionName = $record->companion_name;
            $this->companionContact = $record->companion_contact;
            $this->companionRelationship = $record->companion_relationship;

            // Emergency Contact
            $this->emergencyContactName = $record->emergency_contact_name;
            $this->emergencyContactNumber = $record->emergency_contact_number;
            $this->emergencyContactRelationship = $record->emergency_contact_relationship;

            // Medical Background
            $this->patientBloodType = $record->patient_blood_type;
            $this->patientAllergies = $record->patient_allergies;
            $this->patientChronicConditions = $record->patient_chronic_conditions;
            $this->patientCurrentMedications = $record->patient_current_medications;
            $this->patientPastMedicalHistory = $record->patient_past_medical_history;
            $this->patientFamilyMedicalHistory = $record->patient_family_medical_history;

            // Vital Signs
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

        $this->showInterviewModal = true;
    }

    public function closeInterviewModal(): void
    {
        $this->showInterviewModal = false;
        $this->interviewQueueId = null;
        $this->interviewStep = 'patient';
        $this->resetInterviewForm();
        $this->resetValidation();
    }

    public function setInterviewStep(string $step): void
    {
        $this->interviewStep = $step;
    }

    public function nextInterviewStep(): void
    {
        $steps = ['patient', 'address', 'companion', 'medical', 'vitals'];
        $currentIndex = array_search($this->interviewStep, $steps);

        if ($currentIndex !== false && $currentIndex < count($steps) - 1) {
            $this->interviewStep = $steps[$currentIndex + 1];
        }
    }

    public function previousInterviewStep(): void
    {
        $steps = ['patient', 'address', 'companion', 'medical', 'vitals'];
        $currentIndex = array_search($this->interviewStep, $steps);

        if ($currentIndex !== false && $currentIndex > 0) {
            $this->interviewStep = $steps[$currentIndex - 1];
        }
    }

    protected function resetInterviewForm(): void
    {
        // Patient Information
        $this->patientFirstName = null;
        $this->patientMiddleName = null;
        $this->patientLastName = null;
        $this->patientDateOfBirth = null;
        $this->patientGender = null;
        $this->patientContactNumber = null;
        $this->patientEmail = null;

        // Address
        $this->patientProvince = null;
        $this->patientMunicipality = null;
        $this->patientBarangay = null;
        $this->patientStreet = null;
        $this->patientZipCode = null;

        // Companion
        $this->companionName = null;
        $this->companionContact = null;
        $this->companionRelationship = null;

        // Emergency Contact
        $this->emergencyContactName = null;
        $this->emergencyContactNumber = null;
        $this->emergencyContactRelationship = null;

        // Medical Background
        $this->patientBloodType = null;
        $this->patientAllergies = null;
        $this->patientChronicConditions = null;
        $this->patientCurrentMedications = null;
        $this->patientPastMedicalHistory = null;
        $this->patientFamilyMedicalHistory = null;

        // Vital Signs
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

    public function saveInterview(): void
    {
        $this->validate([
            'patientFirstName' => ['required', 'string', 'max:100'],
            'patientLastName' => ['required', 'string', 'max:100'],
            'patientMiddleName' => ['nullable', 'string', 'max:100'],
            'patientDateOfBirth' => ['required', 'date', 'before_or_equal:today'],
            'patientGender' => ['nullable', 'in:male,female'],
            'patientContactNumber' => ['nullable', 'string', 'max:20'],
            'patientEmail' => ['nullable', 'email', 'max:255'],
            'patientProvince' => ['nullable', 'string', 'max:100'],
            'patientMunicipality' => ['nullable', 'string', 'max:100'],
            'patientBarangay' => ['nullable', 'string', 'max:100'],
            'patientStreet' => ['nullable', 'string', 'max:255'],
            'patientZipCode' => ['nullable', 'string', 'max:10'],
            'companionName' => ['nullable', 'string', 'max:200'],
            'companionContact' => ['nullable', 'string', 'max:20'],
            'companionRelationship' => ['nullable', 'string', 'max:100'],
            'emergencyContactName' => ['nullable', 'string', 'max:200'],
            'emergencyContactNumber' => ['nullable', 'string', 'max:20'],
            'emergencyContactRelationship' => ['nullable', 'string', 'max:100'],
            'patientBloodType' => ['nullable', 'string', 'max:10'],
            'patientAllergies' => ['nullable', 'string', 'max:1000'],
            'patientChronicConditions' => ['nullable', 'string', 'max:1000'],
            'patientCurrentMedications' => ['nullable', 'string', 'max:1000'],
            'patientPastMedicalHistory' => ['nullable', 'string', 'max:2000'],
            'patientFamilyMedicalHistory' => ['nullable', 'string', 'max:2000'],
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

        $queue = Queue::with('medicalRecord')->find($this->interviewQueueId);

        if (! $queue || ! $queue->medicalRecord) {
            Toaster::error(__('Medical record not found.'));
            $this->closeInterviewModal();

            return;
        }

        // Check if vital signs were added
        $hasVitals = $this->temperature || $this->bloodPressure || $this->cardiacRate || $this->respiratoryRate;
        $vitalSignsRecordedAt = $hasVitals && ! $queue->medicalRecord->vital_signs_recorded_at
            ? now()
            : $queue->medicalRecord->vital_signs_recorded_at;

        try {
            $queue->medicalRecord->update([
                // Patient Information
                'patient_first_name' => $this->patientFirstName,
                'patient_middle_name' => $this->patientMiddleName,
                'patient_last_name' => $this->patientLastName,
                'patient_date_of_birth' => $this->patientDateOfBirth,
                'patient_gender' => $this->patientGender ?: null,
                'patient_contact_number' => $this->patientContactNumber,
                'patient_email' => $this->patientEmail,

                // Address
                'patient_province' => $this->patientProvince,
                'patient_municipality' => $this->patientMunicipality,
                'patient_barangay' => $this->patientBarangay,
                'patient_street' => $this->patientStreet,
                'patient_zip_code' => $this->patientZipCode,

                // Companion
                'companion_name' => $this->companionName,
                'companion_contact' => $this->companionContact,
                'companion_relationship' => $this->companionRelationship,

                // Emergency Contact
                'emergency_contact_name' => $this->emergencyContactName,
                'emergency_contact_number' => $this->emergencyContactNumber,
                'emergency_contact_relationship' => $this->emergencyContactRelationship,

                // Medical Background
                'patient_blood_type' => $this->patientBloodType ?: null,
                'patient_allergies' => $this->patientAllergies,
                'patient_chronic_conditions' => $this->patientChronicConditions,
                'patient_current_medications' => $this->patientCurrentMedications,
                'patient_past_medical_history' => $this->patientPastMedicalHistory,
                'patient_family_medical_history' => $this->patientFamilyMedicalHistory,

                // Vital Signs
                'temperature' => $this->temperature ?: null,
                'blood_pressure' => $this->bloodPressure ?: null,
                'cardiac_rate' => $this->cardiacRate ?: null,
                'respiratory_rate' => $this->respiratoryRate ?: null,
                'weight' => $this->weight ?: null,
                'height' => $this->height ?: null,
                'head_circumference' => $this->headCircumference ?: null,
                'chest_circumference' => $this->chestCircumference ?: null,
                'fetal_heart_tone' => $this->fetalHeartTone ?: null,
                'fundal_height' => $this->fundalHeight ?: null,
                'last_menstrual_period' => $this->lastMenstrualPeriod ?: null,
                'chief_complaints_updated' => $this->chiefComplaintsUpdated,
                'vital_signs_recorded_at' => $vitalSignsRecordedAt,
            ]);

            Toaster::success(__('Patient record updated successfully.'));

            $this->closeInterviewModal();
        } catch (\Exception $e) {
            Toaster::error(__('Failed to save: ').$e->getMessage());
        }
    }

    // Priority Methods
    public function openPriorityModal(int $queueId): void
    {
        $queue = Queue::find($queueId);

        if (! $queue || $queue->status === 'completed') {
            Toaster::error(__('Cannot change priority for this patient.'));

            return;
        }

        $this->priorityQueueId = $queueId;
        $this->selectedPriority = $queue->priority ?? 'normal';
        $this->showPriorityModal = true;
    }

    public function closePriorityModal(): void
    {
        $this->showPriorityModal = false;
        $this->priorityQueueId = null;
        $this->selectedPriority = 'normal';
    }

    public function savePriority(): void
    {
        if (! $this->priorityQueueId) {
            return;
        }

        $queue = Queue::find($this->priorityQueueId);

        if (! $queue || $queue->status === 'completed') {
            Toaster::error(__('Cannot change priority for this patient.'));
            $this->closePriorityModal();

            return;
        }

        $oldPriority = $queue->priority;
        $queue->update(['priority' => $this->selectedPriority]);

        // Broadcast queue update
        event(new QueueUpdated($queue->fresh(), 'priority_changed'));

        $priorityLabels = [
            'normal' => __('Normal'),
            'urgent' => __('Urgent'),
            'emergency' => __('Emergency'),
        ];

        Toaster::success(__('Priority changed to :priority for :number', [
            'priority' => $priorityLabels[$this->selectedPriority] ?? $this->selectedPriority,
            'number' => $queue->formatted_number,
        ]));

        $this->closePriorityModal();
    }

    public function markUrgent(int $queueId): void
    {
        $queue = Queue::find($queueId);

        if (! $queue || $queue->status === 'completed') {
            Toaster::error(__('Cannot change priority for this patient.'));

            return;
        }

        $queue->update(['priority' => 'urgent']);
        event(new QueueUpdated($queue->fresh(), 'priority_changed'));

        Toaster::success(__('Patient :number marked as Urgent', ['number' => $queue->formatted_number]));
    }

    public function markEmergency(int $queueId): void
    {
        $queue = Queue::find($queueId);

        if (! $queue || $queue->status === 'completed') {
            Toaster::error(__('Cannot change priority for this patient.'));

            return;
        }

        $queue->update(['priority' => 'emergency']);
        event(new QueueUpdated($queue->fresh(), 'priority_changed'));

        Toaster::success(__('Patient :number marked as Emergency', ['number' => $queue->formatted_number]));
    }

    // Print Ticket Methods
    public function openPrintTicketModal(int $queueId): void
    {
        $queue = Queue::find($queueId);

        if (! $queue) {
            Toaster::error(__('Queue not found.'));

            return;
        }

        $this->printTicketQueueId = $queueId;
        $this->showPrintTicketModal = true;
    }

    public function closePrintTicketModal(): void
    {
        $this->showPrintTicketModal = false;
        $this->printTicketQueueId = null;
    }

    public function getPrintTicketQueueProperty(): ?Queue
    {
        if (! $this->printTicketQueueId) {
            return null;
        }

        return Queue::with(['appointment', 'consultationType'])
            ->find($this->printTicketQueueId);
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

        $consultationTypeId = $queue->consultation_type_id;

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

        // Broadcast queue update
        event(new QueueUpdated($queue->fresh(), 'forwarded'));

        // Notify patients who are near in queue (queue has advanced)
        $this->notifyNearQueuePatients($consultationTypeId);

        Toaster::success(__('Patient forwarded to doctor successfully.'));
    }

    /**
     * Notify patients who are near in queue (both in-app and SMS).
     */
    protected function notifyNearQueuePatients(?int $consultationTypeId = null): void
    {
        $threshold = (int) config('services.sms.queue_near_threshold', 3);

        $waitingQueues = Queue::query()
            ->with(['appointment.user', 'consultationType'])
            ->today()
            ->where('status', 'waiting')
            ->when($consultationTypeId, fn ($q) => $q->where('consultation_type_id', $consultationTypeId))
            ->orderByRaw("CASE priority WHEN 'emergency' THEN 1 WHEN 'urgent' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END")
            ->orderBy('queue_number')
            ->limit($threshold)
            ->get();

        foreach ($waitingQueues as $index => $waitingQueue) {
            $position = $index + 1;
            $estimatedMinutes = ($position - 1) * 10;

            // Use cache to prevent duplicate notifications (expires at end of day)
            $cacheKey = "queue_near_notified_{$waitingQueue->id}";

            if (Cache::has($cacheKey)) {
                continue; // Already notified today
            }

            // Send in-app notification if patient has account
            if ($waitingQueue->appointment?->user) {
                $waitingQueue->appointment->user->notify(new GenericNotification([
                    'type' => 'queue.near',
                    'title' => __('Almost Your Turn!'),
                    'message' => __('Your queue :number is #:position in line (~:minutes min). Please stay nearby.', [
                        'number' => $waitingQueue->formatted_number,
                        'position' => $position,
                        'minutes' => $estimatedMinutes,
                    ]),
                    'queue_id' => $waitingQueue->id,
                    'sender_id' => Auth::id(),
                    'sender_role' => 'nurse',
                ]));
            }

            // Send SMS notification if enabled
            // TODO: Uncomment when SMS is configured in production
            // $smsService = app(QueueSmsService::class);
            // $smsService->notifyPatientNearQueue($waitingQueue, $position);

            // Mark as notified in cache (expires at end of day)
            Cache::put($cacheKey, true, now()->endOfDay());
        }
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
            ->when($this->status !== 'all', function (Builder $q) {
                // Show both 'waiting' and 'called' when viewing waiting tab
                // so nurse can see patients they've called
                if ($this->status === 'waiting') {
                    $q->whereIn('status', ['waiting', 'called']);
                } else {
                    $q->where('status', $this->status);
                }
            })
            ->when($this->consultationTypeFilter !== '', fn (Builder $q) => $q->where('consultation_type_id', $this->consultationTypeFilter))
            ->when($this->search !== '', function (Builder $q) {
                $search = '%'.$this->search.'%';
                $q->where(function (Builder $query) use ($search) {
                    $query->where('queue_number', 'like', $search)
                        ->orWhereHas('appointment', function (Builder $appointmentQuery) use ($search) {
                            $appointmentQuery->where('patient_first_name', 'like', $search)
                                ->orWhere('patient_last_name', 'like', $search);
                        });
                });
            })
            ->orderByRaw("CASE priority WHEN 'emergency' THEN 1 WHEN 'urgent' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END")
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

        // Get the selected queue with full details
        $selectedQueue = $this->selectedQueueId
            ? $queues->firstWhere('id', $this->selectedQueueId)
            : null;

        return view('livewire.nurse.today-queue', [
            'queues' => $queues,
            'pendingCheckIns' => $pendingCheckIns,
            'consultationTypes' => $consultationTypes,
            'statusCounts' => $this->statusCounts,
            'typeCounts' => $this->typeCounts,
            'currentServing' => $currentServing,
            'selectedQueue' => $selectedQueue,
        ])->layout('layouts.app');
    }
}
