<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'HQMS') }} - Hospital Queue Management System</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-50 antialiased dark:bg-zinc-900">
    {{-- Hero Section with Background --}}
    <div class="relative min-h-screen">
        {{-- Background Image with Darker Overlay for text visibility --}}
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('/images/bg.png');"></div>
            <div class="absolute inset-0 bg-black/50"></div>
        </div>

        {{-- Navigation --}}
        <nav class="relative z-10 flex items-center justify-between px-6 py-4 lg:px-12">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <x-app-logo class="h-10 w-10" />
                <span class="text-xl font-bold tracking-tight text-white drop-shadow-lg">{{ config('app.name') }}</span>
            </a>

            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ url('/dashboard') }}" class="rounded-lg bg-white/20 px-5 py-2.5 text-sm font-medium text-white shadow-lg backdrop-blur-sm transition hover:bg-white/30">
                        {{ __('Dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-5 py-2.5 text-sm font-medium text-white drop-shadow-md transition hover:text-white/80">
                        {{ __('Log in') }}
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="rounded-lg bg-white px-5 py-2.5 text-sm font-medium text-primary shadow-lg transition hover:bg-zinc-100 hover:shadow-xl">
                            {{ __('Register') }}
                        </a>
                    @endif
                @endauth
            </div>
        </nav>

        {{-- Hero Content --}}
        <div class="relative z-10 flex min-h-[calc(100vh-80px)] items-center px-6 lg:px-12">
            <div class="mx-auto max-w-6xl">
                <div class="grid gap-12 lg:grid-cols-2 lg:gap-16">
                    {{-- Text Content --}}
                    <div class="flex flex-col justify-center text-white">
                        <div class="mb-4 inline-flex w-fit items-center gap-2 rounded-full bg-white/20 px-4 py-2 text-sm">
                            <span class="h-2 w-2 animate-pulse rounded-full bg-success"></span>
                            {{ __('Now accepting appointments') }}
                        </div>

                        <h1 class="mb-6 text-4xl font-bold leading-tight lg:text-5xl">
                            {{ __('Quality Healthcare') }}<br>
                            <span class="text-white/90">{{ __('For Every Family') }}</span>
                        </h1>

                        <p class="mb-8 max-w-lg text-base text-white/90 lg:text-lg">
                            {{ __('Guardiano Maternity and Children Clinic and Hospital - Providing compassionate care for mothers and children. Book your appointment today and experience healthcare made simple.') }}
                        </p>

                        <div class="flex flex-wrap gap-4">
                            @guest
                                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 font-semibold text-primary shadow-xl transition hover:bg-zinc-100 hover:shadow-2xl lg:px-8 lg:py-4">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ __('Book Appointment') }}
                                </a>
                            @else
                                <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 font-semibold text-primary shadow-xl transition hover:bg-zinc-100 hover:shadow-2xl lg:px-8 lg:py-4">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                    {{ __('Go to Dashboard') }}
                                </a>
                            @endguest
                            <a href="#services" class="inline-flex items-center gap-2 rounded-xl border-2 border-white/40 bg-white/10 px-6 py-3 font-semibold text-white transition hover:bg-white/20 lg:px-8 lg:py-4">
                                {{ __('Learn More') }}
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    {{-- Stats Cards --}}
                    <div class="hidden items-center justify-center lg:flex">
                        <div class="grid gap-4">
                            <div class="rounded-2xl bg-white/20 p-6 shadow-xl backdrop-blur-md">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-white/30">
                                        <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-3xl font-bold text-white drop-shadow-md">10,000+</p>
                                        <p class="text-white/80">{{ __('Patients Served') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="rounded-2xl bg-white/20 p-6 shadow-xl backdrop-blur-md">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-white/30">
                                        <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-3xl font-bold text-white drop-shadow-md">24/7</p>
                                        <p class="text-white/80">{{ __('Emergency Care') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="rounded-2xl bg-white/20 p-6 shadow-xl backdrop-blur-md">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-white/30">
                                        <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-3xl font-bold text-white drop-shadow-md">15+</p>
                                        <p class="text-white/80">{{ __('Expert Doctors') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Wave Decoration --}}
        <div class="absolute bottom-0 left-0 right-0 z-10">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                <path d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="white" class="dark:fill-zinc-900"/>
            </svg>
        </div>
    </div>

    {{-- Services Section --}}
    <section id="services" class="bg-white py-20 dark:bg-zinc-900">
        <div class="mx-auto max-w-6xl px-6 lg:px-12">
            <div class="mb-12 text-center">
                <h2 class="mb-4 text-3xl font-bold text-zinc-900 dark:text-white">{{ __('Our Services') }}</h2>
                <p class="mx-auto max-w-2xl text-zinc-600 dark:text-zinc-400">{{ __('Comprehensive healthcare services designed for your family\'s well-being.') }}</p>
            </div>

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                {{-- Service Cards --}}
                @php
                    $services = [
                        ['icon' => 'heart', 'title' => 'Maternity Care', 'desc' => 'Complete prenatal, delivery, and postnatal care for expecting mothers.', 'color' => 'primary'],
                        ['icon' => 'child', 'title' => 'Pediatrics', 'desc' => 'Expert healthcare for infants, children, and adolescents.', 'color' => 'success'],
                        ['icon' => 'stethoscope', 'title' => 'General Consultation', 'desc' => 'Comprehensive health checkups and consultations.', 'color' => 'info'],
                        ['icon' => 'syringe', 'title' => 'Immunization', 'desc' => 'Complete vaccination programs for children and adults.', 'color' => 'warning'],
                        ['icon' => 'microscope', 'title' => 'Laboratory', 'desc' => 'Accurate diagnostic testing and laboratory services.', 'color' => 'destructive'],
                        ['icon' => 'ambulance', 'title' => 'Emergency', 'desc' => '24/7 emergency care and urgent medical services.', 'color' => 'primary'],
                    ];
                @endphp

                @foreach($services as $service)
                    <div class="group rounded-2xl border border-zinc-200 bg-white p-6 transition hover:border-{{ $service['color'] }}/30 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-800/50">
                        <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-{{ $service['color'] }}/10 transition group-hover:bg-{{ $service['color'] }}/20">
                            <svg class="h-7 w-7 text-{{ $service['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-zinc-900 dark:text-white">{{ __($service['title']) }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __($service['desc']) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="bg-gradient-to-r from-primary to-primary/80 py-16">
        <div class="mx-auto max-w-4xl px-6 text-center lg:px-12">
            <h2 class="mb-4 text-3xl font-bold text-white">{{ __('Ready to Get Started?') }}</h2>
            <p class="mb-8 text-white/80">{{ __('Book your appointment today and experience quality healthcare for your family.') }}</p>
            @guest
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-4 font-semibold text-primary shadow-xl transition hover:bg-zinc-100">
                    {{ __('Create Free Account') }}
                </a>
            @else
                <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-4 font-semibold text-primary shadow-xl transition hover:bg-zinc-100">
                    {{ __('Go to Dashboard') }}
                </a>
            @endguest
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-zinc-900 py-12 text-white">
        <div class="mx-auto max-w-6xl px-6 lg:px-12">
            <div class="flex flex-col items-center justify-between gap-6 md:flex-row">
                <div class="flex items-center gap-3">
                    <x-app-logo class="h-8 w-8 text-white" />
                    <span class="font-bold">{{ config('app.name') }}</span>
                </div>
                <p class="text-sm text-zinc-400">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
