<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\QueueResource;
use App\Models\Queue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    /**
     * Get the authenticated user's active queue for today.
     */
    public function active(Request $request): JsonResponse
    {
        $user = $request->user();

        $activeQueue = $user->queues()
            ->today()
            ->whereIn('status', ['waiting', 'called', 'serving'])
            ->with(['consultationType', 'doctor.personalInformation', 'appointment'])
            ->first();

        if (! $activeQueue) {
            return response()->json([
                'message' => 'No active queue found.',
                'queue' => null,
            ]);
        }

        // Calculate position in queue
        $aheadCount = Queue::where('consultation_type_id', $activeQueue->consultation_type_id)
            ->today()
            ->whereIn('status', ['waiting', 'called'])
            ->where('queue_number', '<', $activeQueue->queue_number)
            ->count();

        // Get current serving number
        $currentServing = Queue::where('consultation_type_id', $activeQueue->consultation_type_id)
            ->today()
            ->where('status', 'serving')
            ->first();

        $activeQueue->ahead_count = $aheadCount;

        return response()->json([
            'queue' => new QueueResource($activeQueue),
            'current_serving' => $currentServing ? [
                'queue_number' => $currentServing->queue_number,
                'formatted_number' => $currentServing->formatted_number,
            ] : null,
            'ahead_count' => $aheadCount,
        ]);
    }

    /**
     * Get all queues for the authenticated user for today.
     */
    public function myQueues(Request $request): JsonResponse
    {
        $user = $request->user();

        $queues = $user->queues()
            ->today()
            ->with(['consultationType', 'doctor.personalInformation', 'appointment'])
            ->orderBy('queue_number')
            ->get();

        return response()->json([
            'queues' => QueueResource::collection($queues),
        ]);
    }

    /**
     * Get queue history for the authenticated user.
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = $user->queues()
            ->with(['consultationType', 'doctor.personalInformation'])
            ->orderBy('queue_date', 'desc')
            ->orderBy('queue_number', 'desc');

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('queue_date', '>=', $request->query('from_date'));
        }

        if ($request->has('to_date')) {
            $query->where('queue_date', '<=', $request->query('to_date'));
        }

        // Filter by status
        if ($request->has('status')) {
            $statuses = explode(',', $request->query('status'));
            $query->whereIn('status', $statuses);
        }

        $queues = $query->paginate($request->query('per_page', 15));

        return response()->json([
            'queues' => QueueResource::collection($queues),
            'meta' => [
                'current_page' => $queues->currentPage(),
                'last_page' => $queues->lastPage(),
                'per_page' => $queues->perPage(),
                'total' => $queues->total(),
            ],
        ]);
    }

    /**
     * Get a specific queue details.
     */
    public function show(Request $request, Queue $queue): JsonResponse
    {
        // Ensure user can only view their own queue
        if ($queue->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorized to view this queue.',
            ], 403);
        }

        $queue->load(['consultationType', 'doctor.personalInformation', 'appointment']);

        // Calculate position if queue is active
        if (in_array($queue->status, ['waiting', 'called'])) {
            $aheadCount = Queue::where('consultation_type_id', $queue->consultation_type_id)
                ->where('queue_date', $queue->queue_date)
                ->whereIn('status', ['waiting', 'called'])
                ->where('queue_number', '<', $queue->queue_number)
                ->count();

            $queue->ahead_count = $aheadCount;
        }

        return response()->json([
            'queue' => new QueueResource($queue),
        ]);
    }
}
