<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CancelAppointmentRequest;
use App\Http\Requests\Api\CreateAppointmentRequest;
use App\Http\Resources\Api\AppointmentResource;
use App\Models\Appointment;
use App\Models\ConsultationType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Get authenticated user's appointments.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = $user->appointments()
            ->with(['consultationType', 'doctor.personalInformation', 'queue'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $statuses = explode(',', $request->query('status'));
            $query->whereIn('status', $statuses);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('appointment_date', '>=', $request->query('from_date'));
        }

        if ($request->has('to_date')) {
            $query->where('appointment_date', '<=', $request->query('to_date'));
        }

        // Upcoming only
        if ($request->boolean('upcoming')) {
            $query->where('appointment_date', '>=', today())
                ->whereIn('status', ['pending', 'approved']);
        }

        $appointments = $query->paginate($request->query('per_page', 15));

        return response()->json([
            'appointments' => AppointmentResource::collection($appointments),
            'meta' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
                'total' => $appointments->total(),
            ],
        ]);
    }

    /**
     * Create a new appointment.
     */
    public function store(CreateAppointmentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $consultationType = ConsultationType::findOrFail($validated['consultation_type_id']);

        // Check if consultation type is accepting appointments for this date
        if (! $consultationType->isAcceptingAppointments($validated['appointment_date'])) {
            return response()->json([
                'message' => 'This consultation type is fully booked for the selected date.',
                'error' => 'fully_booked',
            ], 422);
        }

        // Check for duplicate appointment
        $existingAppointment = $user->appointments()
            ->where('consultation_type_id', $validated['consultation_type_id'])
            ->whereDate('appointment_date', $validated['appointment_date'])
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingAppointment) {
            return response()->json([
                'message' => 'You already have an appointment for this consultation type on this date.',
                'error' => 'duplicate_appointment',
                'existing_appointment' => new AppointmentResource($existingAppointment->load('consultationType')),
            ], 422);
        }

        $appointment = Appointment::create([
            'user_id' => $user->id,
            'consultation_type_id' => $validated['consultation_type_id'],
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'] ?? null,
            'chief_complaints' => $validated['chief_complaints'] ?? null,
            'source' => 'online',
            'status' => 'pending',
        ]);

        $appointment->load(['consultationType', 'doctor.personalInformation']);

        return response()->json([
            'message' => 'Appointment request submitted successfully. Please wait for confirmation.',
            'appointment' => new AppointmentResource($appointment),
        ], 201);
    }

    /**
     * Get a single appointment.
     */
    public function show(Request $request, Appointment $appointment): JsonResponse
    {
        // Ensure user can only view their own appointments
        if ($appointment->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorized to view this appointment.',
            ], 403);
        }

        $appointment->load([
            'consultationType',
            'doctor.personalInformation',
            'approvedBy.personalInformation',
            'queue',
            'medicalRecord',
        ]);

        return response()->json([
            'appointment' => new AppointmentResource($appointment),
        ]);
    }

    /**
     * Cancel an appointment.
     */
    public function cancel(CancelAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        // Ensure user can only cancel their own appointments
        if ($appointment->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorized to cancel this appointment.',
            ], 403);
        }

        // Check if appointment can be cancelled
        if (! in_array($appointment->status, ['pending', 'approved'])) {
            return response()->json([
                'message' => 'This appointment cannot be cancelled.',
                'error' => 'invalid_status',
                'current_status' => $appointment->status,
            ], 422);
        }

        // Check if appointment date has passed
        if ($appointment->appointment_date->isPast()) {
            return response()->json([
                'message' => 'Cannot cancel past appointments.',
                'error' => 'past_appointment',
            ], 422);
        }

        $validated = $request->validated();

        $appointment->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['reason'] ?? null,
        ]);

        $appointment->load('consultationType');

        return response()->json([
            'message' => 'Appointment cancelled successfully.',
            'appointment' => new AppointmentResource($appointment),
        ]);
    }
}
