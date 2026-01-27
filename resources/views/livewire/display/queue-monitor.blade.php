<div class="flex min-h-screen flex-col" wire:poll.10s>
    {{-- Pass consultation type ID to JavaScript --}}
    <script>window.consultationTypeId = {{ $consultationTypeId ?? 'null' }};</script>
    {{-- Header --}}
    <header class="border-b border-zinc-800 bg-zinc-900 px-8 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-600">
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
                        <span id="connection-status" class="h-2 w-2 rounded-full bg-yellow-500" title="Connecting..."></span>
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
    <main class="flex flex-1 flex-col items-center justify-center p-8">
        @if($calledQueues->isNotEmpty())
            {{-- Someone is being called --}}
            <div class="animate-fade-in-up text-center" data-called-id="{{ $calledQueues->first()->id }}">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-emerald-600 px-6 py-2">
                    <svg class="h-6 w-6 animate-pulse" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="text-xl font-semibold uppercase tracking-wider">{{ __('Now Calling') }}</span>
                </div>

                @if($calledQueues->count() === 1)
                    {{-- Single called patient - show big --}}
                    <div class="animate-pulse-scale rounded-3xl border-4 border-emerald-500 bg-gradient-to-b from-emerald-900/50 to-zinc-900 px-24 py-16 shadow-2xl shadow-emerald-500/20">
                        <div class="text-[12rem] font-bold leading-none tracking-tight text-white">
                            {{ $calledQueues->first()->formatted_number }}
                        </div>
                    </div>
                @else
                    {{-- Multiple called patients - show side by side --}}
                    <div class="flex flex-wrap items-center justify-center gap-6">
                        @foreach($calledQueues as $index => $queue)
                            <div class="animate-pulse-scale rounded-3xl border-4 border-emerald-500 bg-gradient-to-b from-emerald-900/50 to-zinc-900 shadow-2xl shadow-emerald-500/20 {{ $index === 0 ? 'px-16 py-12' : 'px-10 py-8' }}">
                                <div class="{{ $index === 0 ? 'text-[10rem]' : 'text-7xl' }} font-bold leading-none tracking-tight text-white">
                                    {{ $queue->formatted_number }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <p class="mt-8 text-2xl text-zinc-300">
                    {{ __('Please proceed to the nurse station') }}
                </p>
            </div>
        @elseif($servingQueues->isNotEmpty())
            {{-- Currently serving (no one being called) --}}
            <div class="text-center">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-blue-600 px-6 py-2">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-xl font-semibold uppercase tracking-wider">{{ __('Now Serving') }}</span>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-6">
                    @foreach($servingQueues as $queue)
                        <div class="rounded-2xl border-2 border-blue-500/50 bg-blue-900/30 px-12 py-8">
                            <div class="text-7xl font-bold text-white">
                                {{ $queue->formatted_number }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="mt-8 text-xl text-zinc-400">
                    {{ __('Please wait for your number to be called') }}
                </p>
            </div>
        @else
            {{-- No one in queue --}}
            <div class="text-center">
                <div class="mb-6 inline-flex h-24 w-24 items-center justify-center rounded-full bg-zinc-800">
                    <svg class="h-12 w-12 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h2 class="text-3xl font-medium text-zinc-400">{{ __('Waiting for patients') }}</h2>
                <p class="mt-2 text-xl text-zinc-600">{{ __('The queue will appear here') }}</p>
            </div>
        @endif
    </main>

    {{-- Footer: Next in Queue --}}
    @if($nextQueues->isNotEmpty())
        <footer class="border-t border-zinc-800 bg-zinc-900/80 px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-lg font-medium text-zinc-400">{{ __('Next') }}:</span>
                    <div class="flex items-center gap-3">
                        @foreach($nextQueues as $queue)
                            <span class="rounded-lg bg-zinc-800 px-4 py-2 text-xl font-bold text-white {{ $queue->priority === 'emergency' ? 'border-2 border-red-500 bg-red-900/30' : ($queue->priority === 'urgent' ? 'border-2 border-amber-500 bg-amber-900/30' : '') }}">
                                {{ $queue->formatted_number }}
                            </span>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-center gap-2 text-zinc-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="text-lg">{{ $waitingCount }} {{ __('waiting') }}</span>
                </div>
            </div>
        </footer>
    @endif
</div>
