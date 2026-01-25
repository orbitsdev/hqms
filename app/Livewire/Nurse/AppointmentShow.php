<?php

namespace App\Livewire\Nurse;

use App\Models\Appointment;
use App\Models\Queue;
use App\Notifications\GenericNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class AppointmentShow extends Component
{
    #[Locked]
    public Appointment $appointment;

    public bool $showApproveModal = false;

    public bool $showCancelModal = false;

    public string $cancelReason = '';

    public string $notes = '';

    public function mount(Appointment $appointment): void
    {
        $this->appointment = $appointment->load([
            'user.personalInformation',
            'consultationType',
            'doctor',
            'queue.consultationType',
            'approvedBy',
        ]);

        $this->notes = $appointment->notes ?? '';
    }

    public function openApproveModal(): void
    {
        if ($this->appointment->status !== 'pending') {
            Toaster::error(__('Only pending appointments can be approved.'));

            return;
        }

        $this->showApproveModal = true;
    }

    public function closeApproveModal(): void
    {
        $this->showApproveModal = false;
    }

    public function openCancelModal(): void
    {
        if (! in_array($this->appointment->status, ['pending', 'approved'])) {
            Toaster::error(__('This appointment cannot be cancelled.'));

            return;
        }

        $this->cancelReason = '';
        $this->showCancelModal = true;
    }

    public function closeCancelModal(): void
    {
        $this->showCancelModal = false;
        $this->cancelReason = '';
    }

    public function approveAppointment(): void
    {
        $this->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($this->appointment->status !== 'pending') {
            Toaster::error(__('Only pending appointments can be approved.'));
            $this->closeApproveModal();

            return;
        }

        $nurse = Auth::user();

        if (! $nurse) {
            abort(403);
        }

        DB::transaction(function () use ($nurse): void {
            $queueNumber = $this->generateQueueNumber();

            $queue = Queue::create([
                'appointment_id' => $this->appointment->id,
                'user_id' => $this->appointment->user_id,
                'consultation_type_id' => $this->appointment->consultation_type_id,
                'doctor_id' => $this->appointment->doctor_id,
                'queue_number' => $queueNumber,
                'queue_date' => $this->appointment->appointment_date,
                'session_number' => 1,
                'priority' => 'normal',
                'status' => 'waiting',
                'source' => $this->appointment->source,
                'notes' => $this->notes ?: null,
            ]);

            $this->appointment->update([
                'status' => 'approved',
                'approved_by' => $nurse->id,
                'approved_at' => now(),
                'notes' => $this->notes ?: null,
            ]);

            $this->appointment->user->notify(new GenericNotification([
                'type' => 'appointment.approved',
                'title' => __('Appointment Approved'),
                'message' => __('Your appointment for :type on :date has been approved. Queue number: :queue', [
                    'type' => $this->appointment->consultationType->name,
                    'date' => $this->appointment->appointment_date->format('M d, Y'),
                    'queue' => $queue->formatted_number,
                ]),
                'appointment_id' => $this->appointment->id,
                'queue_id' => $queue->id,
                'queue_number' => $queue->formatted_number,
                'sender_id' => $nurse->id,
                'sender_role' => 'nurse',
                'url' => route('patient.appointments.show', $this->appointment),
            ]));
        });

        $this->appointment->refresh()->load([
            'user.personalInformation',
            'consultationType',
            'doctor',
            'queue.consultationType',
            'approvedBy',
        ]);

        $this->closeApproveModal();

        Toaster::success(__('Appointment approved and queue number assigned.'));
    }

    public function cancelAppointment(): void
    {
        $this->validate([
            'cancelReason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'cancelReason.required' => __('Please provide a reason for cancellation.'),
            'cancelReason.min' => __('Cancellation reason must be at least 10 characters.'),
        ]);

        if (! in_array($this->appointment->status, ['pending', 'approved'])) {
            Toaster::error(__('This appointment cannot be cancelled.'));
            $this->closeCancelModal();

            return;
        }

        $nurse = Auth::user();

        if (! $nurse) {
            abort(403);
        }

        DB::transaction(function () use ($nurse): void {
            if ($this->appointment->queue) {
                $this->appointment->queue->update([
                    'status' => 'cancelled',
                ]);
            }

            $this->appointment->update([
                'status' => 'cancelled',
                'cancellation_reason' => $this->cancelReason,
            ]);

            $this->appointment->user->notify(new GenericNotification([
                'type' => 'appointment.cancelled',
                'title' => __('Appointment Cancelled'),
                'message' => __('Your appointment for :type on :date has been cancelled. Reason: :reason', [
                    'type' => $this->appointment->consultationType->name,
                    'date' => $this->appointment->appointment_date->format('M d, Y'),
                    'reason' => $this->cancelReason,
                ]),
                'appointment_id' => $this->appointment->id,
                'sender_id' => $nurse->id,
                'sender_role' => 'nurse',
                'url' => route('patient.appointments.show', $this->appointment),
            ]));
        });

        $this->appointment->refresh()->load([
            'user.personalInformation',
            'consultationType',
            'doctor',
            'queue.consultationType',
            'approvedBy',
        ]);

        $this->closeCancelModal();

        Toaster::success(__('Appointment cancelled.'));
    }

    protected function generateQueueNumber(): int
    {
        $lastQueue = Queue::query()
            ->where('consultation_type_id', $this->appointment->consultation_type_id)
            ->where('queue_date', $this->appointment->appointment_date)
            ->where('session_number', 1)
            ->max('queue_number');

        return ($lastQueue ?? 0) + 1;
    }

    public function getPatientAgeProperty(): ?string
    {
        if (! $this->appointment->patient_date_of_birth) {
            return null;
        }

        $years = $this->appointment->patient_date_of_birth->age;

        if ($years < 1) {
            $months = $this->appointment->patient_date_of_birth->diffInMonths(now());

            return $months.' '.__('months');
        }

        return $years.' '.__('years old');
    }

    public function getPatientAddressProperty(): ?string
    {
        $parts = array_filter([
            $this->appointment->patient_street,
            $this->appointment->patient_barangay,
            $this->appointment->patient_municipality,
            $this->appointment->patient_province,
        ]);

        return $parts ? implode(', ', $parts) : null;
    }

    public function render(): View
    {
        return view('livewire.nurse.appointment-show', [
            'patientAge' => $this->patientAge,
            'patientAddress' => $this->patientAddress,
        ])->layout('layouts.app');
    }
}
