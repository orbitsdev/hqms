<x-layouts::app.sidebar :title="$title ?? null">
    @php($isPatient = auth()->check() && auth()->user()->isPatient())

    <flux:main class="{{ $isPatient ? 'pb-24 lg:pb-0' : '' }}">
        @if($isPatient)
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
                {{ $slot }}
            </div>
        @else
            {{ $slot }}
        @endif
    </flux:main>
</x-layouts::app.sidebar>
