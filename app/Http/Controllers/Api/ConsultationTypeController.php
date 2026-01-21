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
        $dayOfWeek = \Carbon\Carbon::parse($date)->dayOfWeek;

        $types = ConsultationType::where('is_active', true)
            ->withCount([
                'appointments as booked_count' => function ($query) use ($date) {
                    $query->whereDate('appointment_date', $date)
                        ->whereIn('status', ['pending', 'approved', 'checked_in', 'in_progress']);
                },
            ])
            ->get()
            ->map(function ($type) use ($date, $dayOfWeek) {
                // Check if any doctor is available for this type on this date
                $hasException = DoctorSchedule::where('consultation_type_id', $type->id)
                    ->where('schedule_type', 'exception')
                    ->where('date', $date)
                    ->first();

                if ($hasException) {
                    $type->is_available = $hasException->is_available;
                } else {
                    // Check regular schedule
                    $type->is_available = DoctorSchedule::where('consultation_type_id', $type->id)
                        ->where('schedule_type', 'regular')
                        ->where('day_of_week', $dayOfWeek)
                        ->exists();
                }

                $type->query_date = $date;

                return $type;
            });

        return response()->json([
            'consultation_types' => ConsultationTypeResource::collection($types),
        ]);
    }

    /**
     * Get doctors' availability for a consultation type on a specific date.
     */
    public function doctorAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'consultation_type_id' => ['required', 'exists:consultation_types,id'],
            'date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $consultationTypeId = $request->query('consultation_type_id');
        $date = $request->query('date', today()->toDateString());
        $dayOfWeek = \Carbon\Carbon::parse($date)->dayOfWeek;

        $consultationType = ConsultationType::findOrFail($consultationTypeId);

        // Check for exceptions on this specific date
        $exceptions = DoctorSchedule::with(['doctor.personalInformation'])
            ->where('consultation_type_id', $consultationTypeId)
            ->where('schedule_type', 'exception')
            ->where('date', $date)
            ->get();

        // Check regular schedules for this day of week
        $regularSchedules = DoctorSchedule::with(['doctor.personalInformation'])
            ->where('consultation_type_id', $consultationTypeId)
            ->where('schedule_type', 'regular')
            ->where('day_of_week', $dayOfWeek)
            ->get();

        // Determine availability
        $availableDoctors = collect();

        foreach ($regularSchedules as $regular) {
            // Check if this doctor has an exception for this date
            $exception = $exceptions->where('user_id', $regular->user_id)->first();

            if ($exception) {
                if ($exception->is_available) {
                    $availableDoctors->push([
                        'id' => $regular->doctor->id,
                        'name' => $regular->doctor->personalInformation?->full_name ?? 'Dr. '.$regular->doctor->email,
                        'start_time' => $exception->start_time?->format('H:i'),
                        'end_time' => $exception->end_time?->format('H:i'),
                        'note' => $exception->reason,
                    ]);
                }
                // If exception is_available=false, doctor is not available (skip)
            } else {
                // No exception, use regular schedule
                $availableDoctors->push([
                    'id' => $regular->doctor->id,
                    'name' => $regular->doctor->personalInformation?->full_name ?? 'Dr. '.$regular->doctor->email,
                    'start_time' => $regular->start_time?->format('H:i'),
                    'end_time' => $regular->end_time?->format('H:i'),
                    'note' => null,
                ]);
            }
        }

        // Check for extra clinic days (exception with is_available=true for doctors not in regular schedule)
        foreach ($exceptions->where('is_available', true) as $exception) {
            if (! $regularSchedules->contains('user_id', $exception->user_id)) {
                $availableDoctors->push([
                    'id' => $exception->doctor->id,
                    'name' => $exception->doctor->personalInformation?->full_name ?? 'Dr. '.$exception->doctor->email,
                    'start_time' => $exception->start_time?->format('H:i'),
                    'end_time' => $exception->end_time?->format('H:i'),
                    'note' => $exception->reason,
                ]);
            }
        }

        return response()->json([
            'consultation_type' => [
                'id' => $consultationType->id,
                'name' => $consultationType->name,
                'code' => $consultationType->code,
            ],
            'date' => $date,
            'is_available' => $availableDoctors->isNotEmpty(),
            'doctors' => $availableDoctors,
        ]);
    }
}
