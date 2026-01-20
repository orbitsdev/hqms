<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MedicalRecordResource;
use App\Models\MedicalRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    /**
     * Get authenticated user's medical records (history).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = $user->medicalRecords()
            ->with([
                'consultationType',
                'doctor.personalInformation',
                'nurse.personalInformation',
                'prescriptions.doctor.personalInformation',
            ])
            ->where('status', 'completed')
            ->where('is_pre_visit', false)
            ->orderBy('visit_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Filter by consultation type
        if ($request->has('consultation_type_id')) {
            $query->where('consultation_type_id', $request->query('consultation_type_id'));
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('visit_date', '>=', $request->query('from_date'));
        }

        if ($request->has('to_date')) {
            $query->where('visit_date', '<=', $request->query('to_date'));
        }

        $records = $query->paginate($request->query('per_page', 15));

        return response()->json([
            'medical_records' => MedicalRecordResource::collection($records),
            'meta' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
            ],
        ]);
    }

    /**
     * Get a single medical record.
     */
    public function show(Request $request, MedicalRecord $medicalRecord): JsonResponse
    {
        // Ensure user can only view their own medical records
        if ($medicalRecord->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorized to view this medical record.',
            ], 403);
        }

        $medicalRecord->load([
            'consultationType',
            'doctor.personalInformation',
            'nurse.personalInformation',
            'prescriptions.doctor.personalInformation',
            'appointment',
        ]);

        return response()->json([
            'medical_record' => new MedicalRecordResource($medicalRecord),
        ]);
    }

    /**
     * Get authenticated user's prescriptions.
     */
    public function prescriptions(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get prescriptions from completed medical records
        $prescriptions = $user->medicalRecords()
            ->where('status', 'completed')
            ->where('is_pre_visit', false)
            ->with(['prescriptions.doctor.personalInformation', 'prescriptions.medicalRecord'])
            ->get()
            ->pluck('prescriptions')
            ->flatten()
            ->sortByDesc('created_at');

        // Filter by date range if provided
        if ($request->has('from_date')) {
            $fromDate = $request->query('from_date');
            $prescriptions = $prescriptions->filter(function ($prescription) use ($fromDate) {
                return $prescription->created_at >= $fromDate;
            });
        }

        if ($request->has('to_date')) {
            $toDate = $request->query('to_date');
            $prescriptions = $prescriptions->filter(function ($prescription) use ($toDate) {
                return $prescription->created_at <= $toDate;
            });
        }

        // Paginate manually
        $perPage = $request->query('per_page', 15);
        $page = $request->query('page', 1);
        $total = $prescriptions->count();
        $prescriptions = $prescriptions->forPage($page, $perPage)->values();

        return response()->json([
            'prescriptions' => $prescriptions->map(function ($prescription) {
                return [
                    'id' => $prescription->id,
                    'medication_name' => $prescription->medication_name,
                    'dosage' => $prescription->dosage,
                    'frequency' => $prescription->frequency,
                    'duration' => $prescription->duration,
                    'instructions' => $prescription->instructions,
                    'quantity' => $prescription->quantity,
                    'is_hospital_drug' => $prescription->is_hospital_drug,
                    'prescribed_by' => [
                        'id' => $prescription->doctor?->id,
                        'name' => $prescription->doctor?->personalInformation?->full_name,
                    ],
                    'visit_date' => $prescription->medicalRecord?->visit_date?->format('Y-m-d'),
                    'consultation_type' => $prescription->medicalRecord?->consultationType?->name,
                    'created_at' => $prescription->created_at?->toIso8601String(),
                ];
            }),
            'meta' => [
                'current_page' => (int) $page,
                'last_page' => (int) ceil($total / $perPage),
                'per_page' => (int) $perPage,
                'total' => $total,
            ],
        ]);
    }
}
