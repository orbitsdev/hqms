<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ConsultationTypeResource;
use App\Models\ConsultationType;
use App\Models\DoctorSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsultationTypeController extends Controller
{
    /**
     * List all active consultation types with availability info.
     */
    public function index(Request $request): JsonResponse
    {
        $date = $request->query('date', today()->toDateString());

        $types = ConsultationType::where('is_active', true)
            ->withCount([
                'appointments as booked_count' => function ($query) use ($date) {
                    $query->whereDate('appointment_date', $date)
                        ->whereIn('status', ['pending', 'approved', 'checked_in', 'in_progress']);
                },
            ])
            ->get()
            ->map(function ($type) use ($date) {
                $type->available_slots = max(0, $type->max_daily_patients - $type->booked_count);
                $type->is_available = $type->available_slots > 0;
                $type->query_date = $date;

                return $type;
            });

        return response()->json([
            'consultation_types' => ConsultationTypeResource::collection($types),
        ]);
    }

    /**
     * Get doctors' availability for a consultation type.
     */
    public function doctorAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'consultation_type_id' => ['required', 'exists:consultation_types,id'],
            'date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $consultationTypeId = $request->query('consultation_type_id');
        $date = $request->query('date', today()->toDateString());

        $consultationType = ConsultationType::findOrFail($consultationTypeId);

        // Get schedules for the given date
        $schedules = DoctorSchedule::with(['doctor.personalInformation'])
            ->where('consultation_type_id', $consultationTypeId)
            ->where('date', $date)
            ->where('is_available', true)
            ->get();

        // If no specific schedules, check for recurring availability
        if ($schedules->isEmpty()) {
            // Get doctors assigned to this consultation type
            $doctors = $consultationType->doctors()
                ->with('personalInformation')
                ->get();

            return response()->json([
                'consultation_type' => [
                    'id' => $consultationType->id,
                    'name' => $consultationType->name,
                    'code' => $consultationType->code,
                ],
                'date' => $date,
                'operating_hours' => [
                    'start' => $consultationType->start_time?->format('H:i'),
                    'end' => $consultationType->end_time?->format('H:i'),
                ],
                'doctors' => $doctors->map(fn ($doctor) => [
                    'id' => $doctor->id,
                    'name' => $doctor->personalInformation?->full_name ?? 'Dr. '.$doctor->email,
                ]),
                'schedules' => [],
                'message' => 'No specific schedules found. General operating hours apply.',
            ]);
        }

        return response()->json([
            'consultation_type' => [
                'id' => $consultationType->id,
                'name' => $consultationType->name,
                'code' => $consultationType->code,
            ],
            'date' => $date,
            'operating_hours' => [
                'start' => $consultationType->start_time?->format('H:i'),
                'end' => $consultationType->end_time?->format('H:i'),
            ],
            'schedules' => $schedules->map(fn ($schedule) => [
                'id' => $schedule->id,
                'doctor' => [
                    'id' => $schedule->doctor->id,
                    'name' => $schedule->doctor->personalInformation?->full_name ?? 'Dr. '.$schedule->doctor->email,
                ],
                'start_time' => $schedule->start_time?->format('H:i'),
                'end_time' => $schedule->end_time?->format('H:i'),
                'max_patients' => $schedule->max_patients,
            ]),
        ]);
    }
}
