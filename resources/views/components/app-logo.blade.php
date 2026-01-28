@props([
'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="CareTime" {{ $attributes }}>
        <x-slot name="logo">
            <img src="{{ asset('images/caretime_logo.png') }}" alt="CareTime" class="size-8 object-contain" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="CareTime" {{ $attributes }}>
        <x-slot name="logo">
            <img src="{{ asset('images/caretime_logo.png') }}" alt="CareTime" class="size-8 object-contain" />
        </x-slot>
    </flux:brand>
@endif
