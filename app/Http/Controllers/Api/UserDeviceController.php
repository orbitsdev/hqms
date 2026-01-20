<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserDeviceResource;
use App\Models\UserDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserDeviceController extends Controller
{
    /**
     * Register or update a device for push notifications.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:255',
            'fcm_token' => 'required|string',
            'platform' => 'required|in:android,ios,web',
            'device_model' => 'nullable|string|max:255',
            'app_version' => 'nullable|string|max:50',
        ]);

        $user = $request->user();

        // Remove this device from other users (device transferred to new account)
        UserDevice::where('device_id', $validated['device_id'])
            ->where('user_id', '!=', $user->id)
            ->delete();

        // Update or create device for current user
        $device = UserDevice::updateOrCreate(
            [
                'user_id' => $user->id,
                'device_id' => $validated['device_id'],
            ],
            [
                'fcm_token' => $validated['fcm_token'],
                'platform' => $validated['platform'],
                'device_model' => $validated['device_model'] ?? null,
                'app_version' => $validated['app_version'] ?? null,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Device registered successfully.',
            'device' => new UserDeviceResource($device),
        ]);
    }

    /**
     * Deactivate a device (logout from push notifications).
     */
    public function logout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
        ]);

        $updated = UserDevice::where('user_id', $request->user()->id)
            ->where('device_id', $validated['device_id'])
            ->update([
                'is_active' => false,
                'fcm_token' => null,
            ]);

        if ($updated === 0) {
            return response()->json([
                'message' => 'Device not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Device logged out successfully.',
        ]);
    }

    /**
     * List all devices for the authenticated user.
     */
    public function list(Request $request): JsonResponse
    {
        $devices = $request->user()
            ->devices()
            ->orderBy('last_used_at', 'desc')
            ->get();

        return response()->json([
            'devices' => UserDeviceResource::collection($devices),
        ]);
    }

    /**
     * Remove a specific device.
     */
    public function destroy(Request $request, string $deviceId): JsonResponse
    {
        $deleted = UserDevice::where('user_id', $request->user()->id)
            ->where('device_id', $deviceId)
            ->delete();

        if ($deleted === 0) {
            return response()->json([
                'message' => 'Device not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Device removed successfully.',
        ]);
    }

    /**
     * Update FCM token for an existing device.
     */
    public function updateToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'fcm_token' => 'required|string',
        ]);

        $device = UserDevice::where('user_id', $request->user()->id)
            ->where('device_id', $validated['device_id'])
            ->first();

        if (! $device) {
            return response()->json([
                'message' => 'Device not found. Please register the device first.',
            ], 404);
        }

        $device->update([
            'fcm_token' => $validated['fcm_token'],
            'is_active' => true,
            'last_used_at' => now(),
        ]);

        return response()->json([
            'message' => 'FCM token updated successfully.',
            'device' => new UserDeviceResource($device),
        ]);
    }
}
