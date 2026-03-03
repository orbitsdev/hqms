<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8">
        <flux:heading size="xl">{{ __('Queue Display') }}</flux:heading>
        <flux:text class="mt-1">{{ __('View real-time queue status on any screen or device.') }}</flux:text>
    </div>

    <flux:callout variant="info" class="mb-8">
        <flux:callout.heading>{{ __('How it works') }}</flux:callout.heading>
        <flux:callout.text>{{ __('Select a display below to open the real-time queue monitor. The display opens in a new tab and is designed for TVs, tablets, and phones.') }}</flux:callout.text>
    </flux:callout>

    {{-- All Services Featured Card --}}
    <a href="{{ route('display.all') }}" target="_blank" class="group mb-6 block rounded-xl border border-zinc-200 bg-gradient-to-br from-zinc-50 to-white p-6 shadow-sm transition hover:border-zinc-300 hover:shadow-md dark:border-zinc-700 dark:from-zinc-800 dark:to-zinc-900 dark:hover:border-zinc-600">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-zinc-900 text-xl font-bold text-white dark:bg-white dark:text-zinc-900">
                ALL
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('All Services') }}</h3>
                    <flux:icon name="arrow-top-right-on-square" class="h-4 w-4 text-zinc-400 opacity-0 transition group-hover:opacity-100" />
                </div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Combined queue display for all consultation types') }}</p>
            </div>
            <div class="hidden items-center gap-4 sm:flex">
                <div class="text-center">
                    <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $totalWaiting }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Waiting') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $totalServing }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Serving') }}</div>
                </div>
            </div>
        </div>
    </a>

    {{-- Type Cards Grid --}}
    @if($consultationTypes->isNotEmpty())
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($consultationTypes as $type)
                <a href="{{ route('display.type', $type->id) }}" target="_blank" wire:key="type-{{ $type->id }}" class="group rounded-xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:border-zinc-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800/50 dark:hover:border-zinc-600">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-sm font-bold text-primary dark:bg-primary/20">
                            {{ $type->short_name }}
                        </div>
                        <flux:icon name="arrow-top-right-on-square" class="h-4 w-4 text-zinc-400 opacity-0 transition group-hover:opacity-100" />
                    </div>
                    <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $type->name }}</h3>
                    @if($type->description)
                        <p class="mt-1 line-clamp-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $type->description }}</p>
                    @endif
                    <div class="mt-3 flex items-center gap-4 border-t border-zinc-100 pt-3 dark:border-zinc-700">
                        <div class="flex items-center gap-1.5">
                            <div class="h-2 w-2 rounded-full bg-warning"></div>
                            <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $type->waiting_count }} {{ __('waiting') }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="h-2 w-2 rounded-full bg-primary"></div>
                            <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $type->serving_count }} {{ __('serving') }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="rounded-xl border-2 border-dashed border-zinc-200 p-12 text-center dark:border-zinc-700">
            <flux:icon name="tv" class="mx-auto mb-3 h-12 w-12 text-zinc-400" />
            <flux:heading size="lg">{{ __('No consultation types available') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Consultation types will appear here once configured.') }}</flux:text>
        </div>
    @endif

    {{-- Spacer for patient mobile nav --}}
    <div class="h-20 lg:hidden"></div>
</div>
