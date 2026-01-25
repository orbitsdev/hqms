<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Notifications extends Component
{
    use WithPagination;

    public string $filter = 'all';

    /** @var array<string, string> */
    protected array $queryString = [
        'filter' => ['except' => 'all'],
    ];

    /** @return array<string, string> */
    protected function getListeners(): array
    {
        $userId = Auth::id();

        if (! $userId) {
            return [];
        }

        return [
            "echo:notifications.{$userId},.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated" => '$refresh',
        ];
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function markAsRead(string $notificationId): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $notification = $user->notifications()->find($notificationId);
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

    public function deleteNotification(string $notificationId): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $notification = $user->notifications()->find($notificationId);
        $notification?->delete();
    }

    public function getUnreadCountProperty(): int
    {
        return Auth::user()?->unreadNotifications()->count() ?? 0;
    }

    public function render(): View
    {
        $user = Auth::user();

        $query = $user->notifications();

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->latest()->paginate(15);

        return view('livewire.notifications', [
            'notifications' => $notifications,
            'unreadCount' => $this->unreadCount,
        ])->layout('layouts.app');
    }
}
