@props([
    'status' => null,
    'message' => null,
    'variant' => 'success',
    'heading' => null,
    'dismissMs' => 4000,
])

@php
    $fallbackMessage = $status ? session($status . '_message') : null;
    $text = $message ?? $fallbackMessage ?? session('status');
    $shouldShow = $status ? session('status') === $status : (bool) $text;
@endphp

@if($shouldShow && $text)
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, {{ (int) $dismissMs }} )" x-show="show" x-transition x-cloak>
        <flux:callout :variant="$variant" icon="check-circle" :heading="$heading">
            <div class="flex items-start justify-between gap-3">
                <flux:text>{{ $text }}</flux:text>
                <flux:button size="xs" variant="ghost" icon="x-mark" @click="show = false" />
            </div>
        </flux:callout>
    </div>
@endif
