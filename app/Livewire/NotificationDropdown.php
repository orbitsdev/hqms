<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class NotificationDropdown extends Component
{
    public bool $showDropdown = false;

    /** @return array<string, string> */
    protected function getListeners(): array
    {
        $userId = Auth::id();

        if (! $userId) {
            return [];
        }

        return [
            "echo:notifications.{$userId},.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated" => 'handleNewNotification',
        ];
    }

    public function handleNewNotification(): void
    {
        // Refresh the component when a new notification arrives
    }

    public function toggleDropdown(): void
    {
        $this->showDropdown = ! $this->showDropdown;
    }

    public function closeDropdown(): void
    {
        $this->showDropdown = false;
    }

    public function markAsRead(string $notificationId): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $notification = $user->unreadNotifications()->find($notificationId);
        $notification?->markAsRead();
    }

    public function markAllAsRead(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $user->unreadNotifications->markAsRead();
    }

    public function getUnreadCountProperty(): int
    {
        return Auth::user()?->unreadNotifications()->count() ?? 0;
    }

    public function render(): View
    {
        $user = Auth::user();

        $notifications = $user
            ? $user->notifications()->latest()->take(10)->get()
            : collect();

        return view('livewire.notification-dropdown', [
            'notifications' => $notifications,
            'unreadCount' => $this->unreadCount,
        ]);
    }
}
