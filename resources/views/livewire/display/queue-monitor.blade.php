<div class="flex min-h-screen flex-col" wire:poll.10s>
    {{-- Pass consultation type ID to JavaScript --}}
    <script>window.consultationTypeId = {{ $consultationTypeId ?: 'null' }};</script>

    {{-- Header --}}
    <header class="border-b border-zinc-800 bg-zinc-900 px-4 py-3 sm:px-8 sm:py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3 sm:gap-4">
                @auth
                    <a href="{{ route('queue-display.select') }}" class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-800 text-zinc-300 transition hover:bg-zinc-700 sm:h-12 sm:w-12" title="{{ __('Back to selector') }}">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                @endauth
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary sm:h-12 sm:w-12">
                    <svg class="h-5 w-5 text-white sm:h-7 sm:w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="truncate text-lg font-bold text-white sm:text-2xl">
                        @if($consultationType)
                            {{ $consultationType->name }}
                        @else
                            {{ __('All Services') }}
                        @endif
                    </h1>
                    <div class="flex items-center gap-2">
                        <p class="truncate text-xs text-zinc-400 sm:text-sm">{{ config('app.name') }}</p>
                        <span id="connection-status" class="h-2 w-2 rounded-full bg-warning" title="Connecting..."></span>
                        <button
                            id="sound-toggle"
                            onclick="toggleAudio()"
                            class="ml-1 flex h-7 w-7 items-center justify-center rounded-lg bg-zinc-700 text-zinc-300 transition hover:bg-zinc-600 sm:ml-2 sm:h-8 sm:w-8"
                            title="Sound disabled (click to enable)"
                        >
                            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-xl font-bold text-white sm:text-4xl" x-data x-text="new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })" x-init="setInterval(() => $el.textContent = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }), 1000)"></div>
                <div class="hidden text-sm text-zinc-400 sm:block">{{ now()->format('l, F j, Y') }}</div>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="flex flex-1 flex-col lg:flex-row">
        {{-- Left Panel: NOW CALLING (Big & Clear) --}}
        <div class="flex flex-col items-center justify-center border-b border-zinc-800 p-4 sm:p-8 lg:w-3/5 lg:border-b-0 lg:border-r">
            @if($calledQueues->isNotEmpty())
                @php $mainCalled = $calledQueues->first(); @endphp
                <div class="animate-fade-in-up text-center" data-called-id="{{ $mainCalled->id }}">
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-success px-5 py-2 sm:mb-6 sm:gap-3 sm:px-8 sm:py-3">
                        <svg class="h-5 w-5 animate-pulse sm:h-8 sm:w-8" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="text-base font-bold uppercase tracking-wider sm:text-2xl">{{ __('Now Calling') }}</span>
                    </div>

                    <div class="animate-pulse-scale rounded-2xl border-2 border-success bg-gradient-to-b from-success/30 to-zinc-900 px-8 py-6 shadow-2xl shadow-success/20 sm:rounded-3xl sm:border-4 sm:px-20 sm:py-12">
                        <div class="text-6xl font-bold leading-none tracking-tight text-white sm:text-8xl lg:text-[14rem]">
                            {{ $mainCalled->formatted_number }}
                        </div>
                    </div>

                    <p class="mt-4 text-lg text-zinc-300 sm:mt-8 sm:text-3xl">
                        {{ __('Please proceed to the nurse station') }}
                    </p>

                    {{-- Other called numbers --}}
                    @if($calledQueues->count() > 1)
                        <div class="mt-4 flex flex-wrap items-center justify-center gap-3 sm:mt-8 sm:gap-4">
                            <span class="text-base text-zinc-400 sm:text-xl">{{ __('Also calling:') }}</span>
                            @foreach($calledQueues->skip(1) as $queue)
                                <span class="rounded-lg bg-success px-4 py-2 text-2xl font-bold text-white sm:rounded-xl sm:px-6 sm:py-3 sm:text-4xl">
                                    {{ $queue->formatted_number }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                {{-- No one being called - show friendly message --}}
                <div class="text-center">
                    <div class="mb-4 inline-flex h-20 w-20 items-center justify-center rounded-full bg-zinc-800 sm:mb-8 sm:h-32 sm:w-32">
                        <svg class="h-10 w-10 text-zinc-500 sm:h-16 sm:w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold text-zinc-400 sm:text-5xl">{{ __('Please Wait') }}</h2>
                    <p class="mt-2 text-lg text-zinc-500 sm:mt-4 sm:text-2xl">{{ __('Your number will be called soon') }}</p>
                </div>
            @endif
        </div>

        {{-- Right Panel: NOW SERVING + NEXT --}}
        <div class="flex flex-col bg-zinc-900/50 lg:w-2/5">
            {{-- Now Serving Section --}}
            <div class="flex-1 border-b border-zinc-800 p-4 sm:p-6">
                <div class="mb-3 flex items-center gap-2 sm:mb-4 sm:gap-3">
                    <div class="h-3 w-3 rounded-full bg-primary sm:h-4 sm:w-4"></div>
                    <h2 class="text-lg font-bold uppercase tracking-wider text-primary sm:text-2xl">{{ __('Now Serving') }}</h2>
                </div>

                @if($servingQueues->isNotEmpty())
                    <div class="space-y-3 sm:space-y-4">
                        @foreach($servingQueues as $queue)
                            <div class="flex items-center gap-3 rounded-xl bg-primary/20 p-3 sm:gap-4 sm:rounded-2xl sm:p-4">
                                <div class="text-4xl font-bold text-white sm:text-6xl">
                                    {{ $queue->formatted_number }}
                                </div>
                                <div class="text-base text-primary/80 sm:text-xl">
                                    → {{ __('Nurse Station') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex h-20 items-center justify-center rounded-xl border-2 border-dashed border-zinc-700 sm:h-32 sm:rounded-2xl">
                        <p class="text-lg text-zinc-500 sm:text-2xl">{{ __('No one yet') }}</p>
                    </div>
                @endif
            </div>

            {{-- Next in Queue Section --}}
            <div class="flex-1 p-4 sm:p-6">
                <div class="mb-3 flex items-center justify-between sm:mb-4">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="h-3 w-3 rounded-full bg-warning sm:h-4 sm:w-4"></div>
                        <h2 class="text-lg font-bold uppercase tracking-wider text-warning sm:text-2xl">{{ __('Next') }}</h2>
                    </div>
                    <span class="rounded-full bg-zinc-800 px-3 py-1.5 text-sm font-medium text-zinc-300 sm:px-4 sm:py-2 sm:text-lg">
                        {{ $waitingCount }} {{ __('waiting') }}
                    </span>
                </div>

                @if($nextQueues->isNotEmpty())
                    <div class="flex flex-wrap gap-2 sm:gap-3">
                        @foreach($nextQueues as $index => $queue)
                            <div class="rounded-lg px-3 py-2 sm:rounded-xl sm:px-5 sm:py-3 {{ $index === 0 ? 'bg-warning text-white' : 'bg-zinc-800 text-zinc-300' }} {{ $queue->priority === 'emergency' ? 'ring-2 ring-destructive sm:ring-4' : ($queue->priority === 'urgent' ? 'ring-2 ring-warning sm:ring-4' : '') }}">
                                <span class="text-2xl font-bold sm:text-4xl">{{ $queue->formatted_number }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex h-20 items-center justify-center rounded-xl border-2 border-dashed border-zinc-700 sm:h-32 sm:rounded-2xl">
                        <p class="text-lg text-zinc-500 sm:text-2xl">{{ __('Queue is empty') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </main>
</div>
