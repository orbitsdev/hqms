<div class="flex min-h-screen flex-col" wire:poll.10s>
    {{-- Pass consultation type ID to JavaScript --}}
    <script>window.consultationTypeId = {{ $consultationTypeId ?? 'null' }};</script>

    {{-- Header --}}
    <header class="border-b border-zinc-800 bg-zinc-900 px-8 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary">
                    <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">
                        @if($consultationType)
                            {{ $consultationType->name }}
                        @else
                            {{ __('All Services') }}
                        @endif
                    </h1>
                    <div class="flex items-center gap-2">
                        <p class="text-sm text-zinc-400">{{ config('app.name') }}</p>
                        <span id="connection-status" class="h-2 w-2 rounded-full bg-warning" title="Connecting..."></span>
                        <button
                            id="sound-toggle"
                            onclick="toggleAudio()"
                            class="ml-2 flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-700 text-zinc-300 transition hover:bg-zinc-600"
                            title="Sound disabled (click to enable)"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-4xl font-bold text-white" x-data x-text="new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })" x-init="setInterval(() => $el.textContent = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }), 1000)"></div>
                <div class="text-sm text-zinc-400">{{ now()->format('l, F j, Y') }}</div>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="flex flex-1">
        {{-- Left Panel: NOW CALLING (Big & Clear) --}}
        <div class="flex w-3/5 flex-col items-center justify-center border-r border-zinc-800 p-8">
            @if($calledQueues->isNotEmpty())
                @php $mainCalled = $calledQueues->first(); @endphp
                <div class="animate-fade-in-up text-center" data-called-id="{{ $mainCalled->id }}">
                    <div class="mb-6 inline-flex items-center gap-3 rounded-full bg-success px-8 py-3">
                        <svg class="h-8 w-8 animate-pulse" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="text-2xl font-bold uppercase tracking-wider">{{ __('Now Calling') }}</span>
                    </div>

                    <div class="animate-pulse-scale rounded-3xl border-4 border-success bg-gradient-to-b from-success/30 to-zinc-900 px-20 py-12 shadow-2xl shadow-success/20">
                        <div class="text-[14rem] font-bold leading-none tracking-tight text-white">
                            {{ $mainCalled->formatted_number }}
                        </div>
                    </div>

                    <p class="mt-8 text-3xl text-zinc-300">
                        {{ __('Please proceed to the nurse station') }}
                    </p>

                    {{-- Other called numbers --}}
                    @if($calledQueues->count() > 1)
                        <div class="mt-8 flex items-center justify-center gap-4">
                            <span class="text-xl text-zinc-400">{{ __('Also calling:') }}</span>
                            @foreach($calledQueues->skip(1) as $queue)
                                <span class="rounded-xl bg-success px-6 py-3 text-4xl font-bold text-white">
                                    {{ $queue->formatted_number }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                {{-- No one being called - show friendly message --}}
                <div class="text-center">
                    <div class="mb-8 inline-flex h-32 w-32 items-center justify-center rounded-full bg-zinc-800">
                        <svg class="h-16 w-16 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-5xl font-bold text-zinc-400">{{ __('Please Wait') }}</h2>
                    <p class="mt-4 text-2xl text-zinc-500">{{ __('Your number will be called soon') }}</p>
                </div>
            @endif
        </div>

        {{-- Right Panel: NOW SERVING + NEXT --}}
        <div class="flex w-2/5 flex-col bg-zinc-900/50">
            {{-- Now Serving Section --}}
            <div class="flex-1 border-b border-zinc-800 p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="h-4 w-4 rounded-full bg-primary"></div>
                    <h2 class="text-2xl font-bold uppercase tracking-wider text-primary">{{ __('Now Serving') }}</h2>
                </div>

                @if($servingQueues->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($servingQueues as $queue)
                            <div class="flex items-center gap-4 rounded-2xl bg-primary/20 p-4">
                                <div class="text-6xl font-bold text-white">
                                    {{ $queue->formatted_number }}
                                </div>
                                <div class="text-xl text-primary/80">
                                    → {{ __('Nurse Station') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex h-32 items-center justify-center rounded-2xl border-2 border-dashed border-zinc-700">
                        <p class="text-2xl text-zinc-500">{{ __('No one yet') }}</p>
                    </div>
                @endif
            </div>

            {{-- Next in Queue Section --}}
            <div class="flex-1 p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-4 w-4 rounded-full bg-warning"></div>
                        <h2 class="text-2xl font-bold uppercase tracking-wider text-warning">{{ __('Next') }}</h2>
                    </div>
                    <span class="rounded-full bg-zinc-800 px-4 py-2 text-lg font-medium text-zinc-300">
                        {{ $waitingCount }} {{ __('waiting') }}
                    </span>
                </div>

                @if($nextQueues->isNotEmpty())
                    <div class="flex flex-wrap gap-3">
                        @foreach($nextQueues as $index => $queue)
                            <div class="rounded-xl px-5 py-3 {{ $index === 0 ? 'bg-warning text-white' : 'bg-zinc-800 text-zinc-300' }} {{ $queue->priority === 'emergency' ? 'ring-4 ring-destructive' : ($queue->priority === 'urgent' ? 'ring-4 ring-warning' : '') }}">
                                <span class="text-4xl font-bold">{{ $queue->formatted_number }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex h-32 items-center justify-center rounded-2xl border-2 border-dashed border-zinc-700">
                        <p class="text-2xl text-zinc-500">{{ __('Queue is empty') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </main>
</div>
