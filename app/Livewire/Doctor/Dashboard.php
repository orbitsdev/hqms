<?php

namespace App\Livewire\Doctor;

use App\Models\MedicalRecord;
use App\Models\Queue;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    public function render(): View
    {
        $doctor = Auth::user();
        $today = today();

        // Get doctor's consultation types
        $consultationTypeIds = $doctor->consultationTypes()->pluck('consultation_types.id');

        // Today's statistics
        $waitingCount = Queue::query()
            ->whereDate('queue_date', $today)
            ->where('status', 'completed') // Forwarded by nurse
            ->whereIn('consultation_type_id', $consultationTypeIds)
            ->whereHas('medicalRecord', fn ($q) => $q->where('status', 'in_progress')->whereNull('examined_at'))
            ->count();

        $examiningCount = MedicalRecord::query()
            ->whereDate('visit_date', $today)
            ->where('doctor_id', $doctor->id)
            ->where('status', 'in_progress')
            ->whereNotNull('examined_at')
            ->whereNull('examination_ended_at')
            ->count();

        $completedCount = MedicalRecord::query()
            ->whereDate('visit_date', $today)
            ->where('doctor_id', $doctor->id)
            ->whereIn('status', ['for_billing', 'for_admission', 'completed'])
            ->count();

        // Waiting patients (forwarded by nurse, not yet examined)
        $waitingPatients = Queue::query()
            ->with(['medicalRecord', 'consultationType', 'appointment'])
            ->whereDate('queue_date', $today)
            ->where('status', 'completed')
            ->whereIn('consultation_type_id', $consultationTypeIds)
            ->whereHas('medicalRecord', fn ($q) => $q->where('status', 'in_progress')->whereNull('examined_at'))
            ->orderByRaw("FIELD(priority, 'emergency', 'urgent', 'normal')")
            ->orderBy('serving_ended_at')
            ->limit(10)
            ->get();

        // Currently examining (by this doctor)
        $currentlyExamining = MedicalRecord::query()
            ->with(['queue.consultationType', 'consultationType'])
            ->whereDate('visit_date', $today)
            ->where('doctor_id', $doctor->id)
            ->where('status', 'in_progress')
            ->whereNotNull('examined_at')
            ->whereNull('examination_ended_at')
            ->first();

        // Recent completed
        $recentCompleted = MedicalRecord::query()
            ->with(['consultationType'])
            ->whereDate('visit_date', $today)
            ->where('doctor_id', $doctor->id)
            ->whereIn('status', ['for_billing', 'for_admission', 'completed'])
            ->orderByDesc('examination_ended_at')
            ->limit(5)
            ->get();

        return view('livewire.doctor.dashboard', [
            'waitingCount' => $waitingCount,
            'examiningCount' => $examiningCount,
            'completedCount' => $completedCount,
            'waitingPatients' => $waitingPatients,
            'currentlyExamining' => $currentlyExamining,
            'recentCompleted' => $recentCompleted,
            'consultationTypeIds' => $consultationTypeIds,
        ])->layout('layouts.app');
    }
}
