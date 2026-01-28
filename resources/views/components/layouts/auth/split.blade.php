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
        <div class="relative flex w-full flex-col justify-center px-6 py-12 lg:w-1/2 lg:px-16 overflow-hidden">
            {{-- Grid Pattern Background --}}
            <svg aria-hidden="true" class="absolute inset-0 -z-10 h-full w-full stroke-zinc-200 dark:stroke-zinc-700/50 [mask-image:radial-gradient(100%_100%_at_top_right,white,transparent)]">
                <defs>
                    <pattern id="auth-grid-pattern" width="40" height="40" x="50%" y="-1" patternUnits="userSpaceOnUse">
                        <path d="M.5 40V.5H40" fill="none" />
                    </pattern>
                </defs>
                <svg x="50%" y="-1" class="overflow-visible fill-zinc-50 dark:fill-zinc-800/20">
                    <path d="M-200 0h201v201h-201Z M600 0h201v201h-201Z M-400 600h201v201h-201Z M200 800h201v201h-201Z" stroke-width="0" />
                </svg>
                <rect width="100%" height="100%" fill="url(#auth-grid-pattern)" stroke-width="0" />
            </svg>

            {{-- Gradient Blur Decoration --}}
            <div aria-hidden="true" class="absolute top-0 right-0 -z-10 transform-gpu blur-3xl">
                <div style="clip-path: polygon(73.6% 51.7%, 91.7% 11.8%, 100% 46.4%, 97.4% 82.2%, 92.5% 84.9%, 75.7% 64%, 55.3% 47.5%, 46.5% 49.4%, 45% 62.9%, 50.3% 87.2%, 21.3% 64.1%, 0.1% 100%, 5.4% 51.1%, 21.4% 63.9%, 58.9% 0.2%, 73.6% 51.7%)" class="aspect-[1108/632] w-[40rem] bg-gradient-to-r from-primary/20 to-info/20 opacity-30"></div>
            </div>

            {{-- Mobile Logo --}}
            <div class="mb-8 flex justify-center lg:hidden">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/caretime_logo.png') }}" alt="CareTime" class="h-9 w-9 object-contain" />
                    <span class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ config('app.name') }}</span>
                </a>
            </div>

            <div class="relative mx-auto w-full max-w-md">
                {{ $slot }}
            </div>
        </div>
    </div>

    @fluxScripts
</body>
</html>
