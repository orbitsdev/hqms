<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| User Notification Channel
|--------------------------------------------------------------------------
|
| Private channel for user-specific notifications (appointment updates,
| queue status changes, etc.)
|
*/
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
|--------------------------------------------------------------------------
| Queue Display Channels
|--------------------------------------------------------------------------
|
| Public channels for queue display screens in waiting areas.
| These broadcast queue updates for specific consultation types.
|
*/
Broadcast::channel('queue.display.{consultationTypeId}', function () {
    // Public channel - anyone can listen to queue displays
    return true;
});

/*
|--------------------------------------------------------------------------
| Queue Status Channel (Staff)
|--------------------------------------------------------------------------
|
| Private channel for staff to receive real-time queue updates.
| Only authenticated users with nurse or doctor role can access.
|
*/
Broadcast::channel('queue.staff', function ($user) {
    return $user->hasRole(['nurse', 'doctor', 'admin']);
});

/*
|--------------------------------------------------------------------------
| Patient Queue Channel
|--------------------------------------------------------------------------
|
| Private channel for patients to receive updates about their queue position.
|
*/
Broadcast::channel('queue.patient.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
