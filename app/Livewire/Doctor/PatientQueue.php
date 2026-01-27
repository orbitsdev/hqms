<?php

namespace App\Livewire\Doctor;

use App\Models\ConsultationType;
use App\Models\MedicalRecord;
use App\Models\Queue;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class PatientQueue extends Component
{
    public string $status = 'waiting';

    public string $consultationTypeFilter = '';

    public ?int $selectedQueueId = null;

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->selectedQueueId = null;
    }

    public function setConsultationType(string $id): void
    {
        $this->consultationTypeFilter = $id;
        $this->selectedQueueId = null;
    }

    public function selectQueue(int $id): void
    {
        $this->selectedQueueId = $this->selectedQueueId === $id ? null : $id;
    }

    public function startExamination(?int $queueId = null): void
    {
        $queueId = $queueId ?? $this->selectedQueueId;

        if (! $queueId) {
            Toaster::error(__('Please select a patient first.'));

            return;
        }

        $queue = Queue::with('medicalRecord')->find($queueId);

        if (! $queue || ! $queue->medicalRecord) {
            Toaster::error(__('Patient record not found.'));

            return;
        }

        $medicalRecord = $queue->medicalRecord;

        // Check if already being examined
        if ($medicalRecord->examined_at && ! $medicalRecord->examination_ended_at) {
            Toaster::error(__('This patient is already being examined.'));

            return;
        }

        // Check if already completed
        if ($medicalRecord->status !== 'in_progress') {
            Toaster::error(__('This patient has already been processed.'));

            return;
        }

        // Start examination
        $medicalRecord->update([
            'doctor_id' => Auth::id(),
            'examined_at' => now(),
            'examination_time' => now()->format('A') === 'AM' ? 'am' : 'pm',
        ]);

        // Redirect to examination page
        $this->redirect(route('doctor.examine', $medicalRecord), navigate: true);
    }

    public function startNextPatient(): void
    {
        $doctor = Auth::user();
        $consultationTypeIds = $doctor->consultationTypes()->pluck('consultation_types.id');

        $nextQueue = Queue::query()
            ->whereDate('queue_date', today())
            ->where('status', 'completed')
            ->whereIn('consultation_type_id', $consultationTypeIds)
            ->when($this->consultationTypeFilter !== '', fn ($q) => $q->where('consultation_type_id', $this->consultationTypeFilter))
            ->whereHas('medicalRecord', fn ($q) => $q->where('status', 'in_progress')->whereNull('examined_at'))
            ->orderByRaw("CASE priority WHEN 'emergency' THEN 1 WHEN 'urgent' THEN 2 WHEN 'normal' THEN 3 END")
            ->orderBy('serving_ended_at')
            ->first();

        if (! $nextQueue) {
            Toaster::info(__('No patients waiting.'));

            return;
        }

        $this->startExamination($nextQueue->id);
    }

    #[Computed]
    public function selectedQueue(): ?Queue
    {
        if (! $this->selectedQueueId) {
            return null;
        }

        return Queue::with([
            'medicalRecord.prescriptions',
            'consultationType',
            'appointment',
        ])->find($this->selectedQueueId);
    }

    #[Computed]
    public function statusCounts(): array
    {
        $doctor = Auth::user();
        $today = today();
        $consultationTypeIds = $doctor->consultationTypes()->pluck('consultation_types.id');

        // Waiting = forwarded by nurse, not yet examined
        $waiting = Queue::query()
            ->whereDate('queue_date', $today)
            ->where('status', 'completed')
            ->whereIn('consultation_type_id', $consultationTypeIds)
            ->whereHas('medicalRecord', fn ($q) => $q->where('status', 'in_progress')->whereNull('examined_at'))
            ->count();

        // Examining = being examined by this doctor
        $examining = MedicalRecord::query()
            ->whereDate('visit_date', $today)
            ->where('doctor_id', $doctor->id)
            ->where('status', 'in_progress')
            ->whereNotNull('examined_at')
            ->whereNull('examination_ended_at')
            ->count();

        // Completed = examined and finished
        $completed = MedicalRecord::query()
            ->whereDate('visit_date', $today)
            ->where('doctor_id', $doctor->id)
            ->whereIn('status', ['for_billing', 'for_admission', 'completed'])
            ->count();

        return [
            'waiting' => $waiting,
            'examining' => $examining,
            'completed' => $completed,
        ];
    }

    public function render(): View
    {
        $doctor = Auth::user();
        $today = today();
        $consultationTypeIds = $doctor->consultationTypes()->pluck('consultation_types.id');

        // Get queues based on status
        if ($this->status === 'waiting') {
            $queues = Queue::query()
                ->with(['medicalRecord', 'consultationType', 'appointment'])
                ->whereDate('queue_date', $today)
                ->where('status', 'completed')
                ->whereIn('consultation_type_id', $consultationTypeIds)
                ->when($this->consultationTypeFilter !== '', fn ($q) => $q->where('consultation_type_id', $this->consultationTypeFilter))
                ->whereHas('medicalRecord', fn ($q) => $q->where('status', 'in_progress')->whereNull('examined_at'))
                ->orderByRaw("CASE priority WHEN 'emergency' THEN 1 WHEN 'urgent' THEN 2 WHEN 'normal' THEN 3 END")
                ->orderBy('serving_ended_at')
                ->get();
        } elseif ($this->status === 'examining') {
            $queues = Queue::query()
                ->with(['medicalRecord', 'consultationType', 'appointment'])
                ->whereDate('queue_date', $today)
                ->whereIn('consultation_type_id', $consultationTypeIds)
                ->whereHas('medicalRecord', fn ($q) => $q
                    ->where('doctor_id', $doctor->id)
                    ->where('status', 'in_progress')
                    ->whereNotNull('examined_at')
                    ->whereNull('examination_ended_at'))
                ->get();
        } else {
            // Completed
            $queues = Queue::query()
                ->with(['medicalRecord', 'consultationType', 'appointment'])
                ->whereDate('queue_date', $today)
                ->whereIn('consultation_type_id', $consultationTypeIds)
                ->when($this->consultationTypeFilter !== '', fn ($q) => $q->where('consultation_type_id', $this->consultationTypeFilter))
                ->whereHas('medicalRecord', fn ($q) => $q
                    ->where('doctor_id', $doctor->id)
                    ->whereIn('status', ['for_billing', 'for_admission', 'completed']))
                ->orderByDesc('updated_at')
                ->get();
        }

        // Consultation types for filter
        $consultationTypes = ConsultationType::query()
            ->whereIn('id', $consultationTypeIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.doctor.patient-queue', [
            'queues' => $queues,
            'consultationTypes' => $consultationTypes,
        ])->layout('layouts.app');
    }
}
