<section class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">{{ __('Admin Dashboard') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('System overview and management') }}
            </flux:text>
        </div>
        <flux:button href="{{ route('admin.users') }}" wire:navigate variant="primary" icon="user-plus">
            {{ __('Manage Users') }}
        </flux:button>
    </div>

    {{-- User Stats --}}
    <div>
        <flux:heading size="sm" class="mb-3">{{ __('Users') }}</flux:heading>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon name="users" class="h-5 w-5 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['total_users'] }}</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total') }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon name="user" class="h-5 w-5 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['total_patients'] }}</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Patients') }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon name="heart" class="h-5 w-5 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['total_doctors'] }}</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Doctors') }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon name="clipboard-document-check" class="h-5 w-5 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['total_nurses'] }}</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Nurses') }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon name="banknotes" class="h-5 w-5 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['total_cashiers'] }}</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Cashiers') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Users --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="mb-4 flex items-center justify-between">
            <flux:heading size="sm">{{ __('Recent Users') }}</flux:heading>
            <flux:button href="{{ route('admin.users') }}" wire:navigate variant="ghost" size="sm" icon-trailing="arrow-right">
                {{ __('View All') }}
            </flux:button>
        </div>

        @if($this->recentUsers->isNotEmpty())
            <div class="space-y-2">
                @foreach($this->recentUsers as $user)
                    <div class="flex items-center justify-between rounded-lg border border-zinc-100 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-200 text-sm font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                {{ $user->initials() }}
                            </div>
                            <div>
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $user->name }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <flux:badge size="sm" color="zinc">
                                {{ $user->roles->first()?->name ?? 'No role' }}
                            </flux:badge>
                            <p class="mt-1 text-xs text-zinc-500">{{ $user->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-8 text-center">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No users yet') }}</p>
            </div>
        @endif
    </div>
</section>
