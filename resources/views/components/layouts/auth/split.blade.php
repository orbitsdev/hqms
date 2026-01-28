@props(['title' => null, 'background' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-zinc-50 antialiased dark:bg-zinc-900">
    <div class="flex min-h-screen">
        {{-- Background Image Side --}}
        <div class="relative hidden w-1/2 lg:block">
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('{{ $background ?? '/images/login_bg.png' }}');"></div>
            {{-- Darker overlay for text visibility --}}
            <div class="absolute inset-0 bg-black/50"></div>
            {{-- Branding overlay --}}
            <div class="relative z-10 flex h-full flex-col justify-between p-10">
                <div>
                    <a href="{{ url('/') }}" class="flex items-center gap-3">
                        <x-app-logo class="h-10 w-10" />
                        <span class="text-xl font-bold tracking-tight text-white drop-shadow-lg">{{ config('app.name') }}</span>
                    </a>
                </div>
                <div class="max-w-md space-y-4">
                    <h2 class="text-3xl font-bold leading-tight text-white drop-shadow-lg">
                        {{ __('Quality Healthcare') }}<br>
                        {{ __('For Every Family') }}
                    </h2>
                    <p class="text-white/90 drop-shadow-md">
                        {{ __('Guardiano Maternity and Children Clinic and Hospital - Providing compassionate care for mothers and children since 2010.') }}
                    </p>
                </div>
                <div class="text-sm text-white/80">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
                </div>
            </div>
        </div>

        {{-- Form Side --}}
        <div class="flex w-full flex-col justify-center px-6 py-12 lg:w-1/2 lg:px-16">
            {{-- Mobile Logo --}}
            <div class="mb-8 flex justify-center lg:hidden">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <x-app-logo class="h-9 w-9" />
                    <span class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ config('app.name') }}</span>
                </a>
            </div>

            <div class="mx-auto w-full max-w-md">
                {{ $slot }}
            </div>
        </div>
    </div>

    @fluxScripts
</body>
</html>
