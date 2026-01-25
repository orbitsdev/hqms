<div class="relative" wire:poll.30s>
    <flux:dropdown position="bottom" align="end">
        <button
            type="button"
            class="relative flex items-center justify-center w-9 h-9 rounded-lg text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:text-zinc-100 dark:hover:bg-zinc-800 transition"
        >
            <flux:icon name="bell" class="w-5 h-5" />

            @if($unreadCount > 0)
                <span class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full">
                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                </span>
            @endif
        </button>

        <flux:menu class="w-80 max-h-96 overflow-y-auto">
            <div class="flex items-center justify-between px-3 py-2 border-b border-zinc-200 dark:border-zinc-700">
                <flux:heading size="sm">{{ __('Notifications') }}</flux:heading>

                @if($unreadCount > 0)
                    <button
                        type="button"
                        wire:click="markAllAsRead"
                        class="text-xs text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100"
                    >
                        {{ __('Mark all as read') }}
                    </button>
                @endif
            </div>

            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = is_null($notification->read_at);
                    $url = $data['url'] ?? null;
                @endphp

                <div
                    @class([
                        'px-3 py-2 border-b border-zinc-100 dark:border-zinc-800 last:border-0',
                        'bg-zinc-50 dark:bg-zinc-800/50' => $isUnread,
                    ])
                >
                    <div class="flex items-start gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span @class([
                                    'text-sm font-medium truncate',
                                    'text-zinc-900 dark:text-zinc-100' => $isUnread,
                                    'text-zinc-700 dark:text-zinc-300' => !$isUnread,
                                ])>
                                    {{ $data['title'] ?? __('Notification') }}
                                </span>

                                @if($isUnread)
                                    <span class="w-2 h-2 bg-blue-500 rounded-full shrink-0"></span>
                                @endif
                            </div>

                            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400 line-clamp-2">
                                {{ $data['message'] ?? '' }}
                            </p>

                            <div class="flex items-center gap-3 mt-1">
                                <span class="text-[10px] text-zinc-400 dark:text-zinc-500">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>

                                @if($url)
                                    <a
                                        href="{{ $url }}"
                                        wire:navigate
                                        wire:click="markAsRead('{{ $notification->id }}')"
                                        class="text-[10px] text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100"
                                    >
                                        {{ __('View') }}
                                    </a>
                                @endif

                                @if($isUnread)
                                    <button
                                        type="button"
                                        wire:click="markAsRead('{{ $notification->id }}')"
                                        class="text-[10px] text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100"
                                    >
                                        {{ __('Mark read') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-3 py-8 text-center">
                    <flux:icon name="bell-slash" class="w-8 h-8 mx-auto text-zinc-300 dark:text-zinc-600" />
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('No notifications yet') }}
                    </p>
                </div>
            @endforelse
        </flux:menu>
    </flux:dropdown>
</div>
