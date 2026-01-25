<?php

namespace App\Livewire\Nurse;

use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\Queue;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class Appointments extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = 'pending';

    public string $consultationTypeFilter = '';

    public string $dateFilter = '';

    public string $sortBy = 'appointment_date';

    public string $sortDirection = 'asc';

    public string $sourceFilter = '';

    // Modal states
    public bool $showViewModal = false;

    public bool $showApproveModal = false;

    public bool $showCancelModal = false;

    #[Locked]
    public ?int $selectedAppointmentId = null;

    public string $cancelReason = '';

    public string $notes = '';

    /** @var array<string, mixed> */
    protected array $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'pending'],
        'consultationTypeFilter' => ['except' => ''],
        'dateFilter' => ['except' => ''],
        'sourceFilter' => ['except' => ''],
        'sortBy' => ['except' => 'appointment_date'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedConsultationTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSourceFilter(): void
    {
        $this->resetPage();
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'consultationTypeFilter', 'dateFilter', 'sourceFilter']);
        $this->resetPage();
    }

    // Modal methods
    public function viewAppointment(int $id): void
    {
        $this->selectedAppointmentId = $id;
        $this->showViewModal = true;
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->selectedAppointmentId = null;
    }

    public function openApproveModal(int $id): void
    {
        $this->selectedAppointmentId = $id;
        $this->notes = '';
        $this->showApproveModal = true;
    }

    public function closeApproveModal(): void
    {
        $this->showApproveModal = false;
        $this->notes = '';
    }

    public function openCancelModal(int $id): void
    {
        $this->selectedAppointmentId = $id;
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

        $appointment = Appointment::find($this->selectedAppointmentId);

        if (! $appointment || $appointment->status !== 'pending') {
            Toaster::error(__('Only pending appointments can be approved.'));
            $this->closeApproveModal();

            return;
        }

        $nurse = Auth::user();

        if (! $nurse) {
            abort(403);
        }

        DB::transaction(function () use ($appointment, $nurse): void {
            $queueNumber = $this->generateQueueNumber($appointment);

            $queue = Queue::create([
                'appointment_id' => $appointment->id,
                'user_id' => $appointment->user_id,
                'consultation_type_id' => $appointment->consultation_type_id,
                'doctor_id' => $appointment->doctor_id,
                'queue_number' => $queueNumber,
                'queue_date' => $appointment->appointment_date,
                'session_number' => 1,
                'priority' => 'normal',
                'status' => 'waiting',
                'source' => $appointment->source,
                'notes' => $this->notes ?: null,
            ]);

            $appointment->update([
                'status' => 'approved',
                'approved_by' => $nurse->id,
                'approved_at' => now(),
                'notes' => $this->notes ?: null,
            ]);

            $appointment->user->notify(new GenericNotification([
                'type' => 'appointment.approved',
                'title' => __('Appointment Approved'),
                'message' => __('Your appointment for :type on :date has been approved. Queue number: :queue', [
                    'type' => $appointment->consultationType->name,
                    'date' => $appointment->appointment_date->format('M d, Y'),
                    'queue' => $queue->formatted_number,
                ]),
                'appointment_id' => $appointment->id,
                'queue_id' => $queue->id,
                'queue_number' => $queue->formatted_number,
                'sender_id' => $nurse->id,
                'sender_role' => 'nurse',
                'url' => route('patient.appointments.show', $appointment),
            ]));
        });

        $this->closeApproveModal();
        $this->closeViewModal();

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

        $appointment = Appointment::find($this->selectedAppointmentId);

        if (! $appointment || ! in_array($appointment->status, ['pending', 'approved'])) {
            Toaster::error(__('This appointment cannot be cancelled.'));
            $this->closeCancelModal();

            return;
        }

        $nurse = Auth::user();

        if (! $nurse) {
            abort(403);
        }

        DB::transaction(function () use ($appointment, $nurse): void {
            if ($appointment->queue) {
                $appointment->queue->update([
                    'status' => 'cancelled',
                ]);
            }

            $appointment->update([
                'status' => 'cancelled',
                'cancellation_reason' => $this->cancelReason,
            ]);

            $appointment->user->notify(new GenericNotification([
                'type' => 'appointment.cancelled',
                'title' => __('Appointment Cancelled'),
                'message' => __('Your appointment for :type on :date has been cancelled. Reason: :reason', [
                    'type' => $appointment->consultationType->name,
                    'date' => $appointment->appointment_date->format('M d, Y'),
                    'reason' => $this->cancelReason,
                ]),
                'appointment_id' => $appointment->id,
                'sender_id' => $nurse->id,
                'sender_role' => 'nurse',
                'url' => route('patient.appointments.show', $appointment),
            ]));
        });

        $this->closeCancelModal();
        $this->closeViewModal();

        Toaster::success(__('Appointment cancelled.'));
    }

    protected function generateQueueNumber(Appointment $appointment): int
    {
        $lastQueue = Queue::query()
            ->where('consultation_type_id', $appointment->consultation_type_id)
            ->where('queue_date', $appointment->appointment_date)
            ->where('session_number', 1)
            ->max('queue_number');

        return ($lastQueue ?? 0) + 1;
    }

    /** @return array<string, int> */
    public function getStatusCountsProperty(): array
    {
        $baseQuery = Appointment::query();

        return [
            'all' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
            'today' => (clone $baseQuery)->whereDate('appointment_date', today())->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
        ];
    }

    public function getSelectedAppointmentProperty(): ?Appointment
    {
        if (! $this->selectedAppointmentId) {
            return null;
        }

        return Appointment::with([
            'user.personalInformation',
            'consultationType',
            'doctor',
            'queue.consultationType',
            'approvedBy',
        ])->find($this->selectedAppointmentId);
    }

    public function render(): View
    {
        $search = trim($this->search);

        $appointments = Appointment::query()
            ->with([
                'user.personalInformation',
                'consultationType',
                'doctor',
                'queue.consultationType',
                'approvedBy',
            ])
            ->when($this->status === 'pending', fn (Builder $q) => $q->where('status', 'pending'))
            ->when($this->status === 'approved', fn (Builder $q) => $q->where('status', 'approved'))
            ->when($this->status === 'cancelled', fn (Builder $q) => $q->where('status', 'cancelled'))
            ->when($this->status === 'today', fn (Builder $q) => $q->whereDate('appointment_date', today()))
            ->when($this->consultationTypeFilter !== '', fn (Builder $q) => $q->where('consultation_type_id', $this->consultationTypeFilter))
            ->when($this->dateFilter !== '', fn (Builder $q) => $q->whereDate('appointment_date', $this->dateFilter))
            ->when($this->sourceFilter !== '', fn (Builder $q) => $q->where('source', $this->sourceFilter))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $likeSearch = '%'.$search.'%';

                $query->where(function (Builder $q) use ($likeSearch): void {
                    $q->where('patient_first_name', 'like', $likeSearch)
                        ->orWhere('patient_middle_name', 'like', $likeSearch)
                        ->orWhere('patient_last_name', 'like', $likeSearch)
                        ->orWhere('patient_phone', 'like', $likeSearch)
                        ->orWhere('chief_complaints', 'like', $likeSearch)
                        ->orWhereHas('consultationType', fn (Builder $ct) => $ct->where('name', 'like', $likeSearch))
                        ->orWhereHas('user', fn (Builder $u) => $u->where('email', 'like', $likeSearch));
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $consultationTypes = ConsultationType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.nurse.appointments', [
            'appointments' => $appointments,
            'consultationTypes' => $consultationTypes,
            'statusCounts' => $this->statusCounts,
            'selectedAppointment' => $this->selectedAppointment,
        ])->layout('layouts.app');
    }
}
