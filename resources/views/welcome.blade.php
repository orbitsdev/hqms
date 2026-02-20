<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'HQMS') }} - Hospital Queue Management System</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- GSAP CDN with plugins --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <style>
        /* Custom styles for animations */
        .char { display: inline-block; }
        .word { display: inline-block; overflow: hidden; }
        .word .char { transform: translateY(100%); }
        .line { overflow: hidden; }

        /* Magnetic button effect */
        .magnetic-btn {
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* Parallax layers */
        .parallax-slow { will-change: transform; }
        .parallax-fast { will-change: transform; }

        /* Glow effect on hover */
        .glow-hover:hover {
            box-shadow: 0 0 30px rgba(20, 184, 166, 0.4);
        }

        /* Scroll indicator */
        .scroll-indicator {
            animation: scrollBounce 2s infinite;
        }
        @keyframes scrollBounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        /* Animated wave layers */
        @keyframes waveShift1 {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(-15px); }
        }
        @keyframes waveShift2 {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(20px); }
        }
        .wave-layer-1 { animation: waveShift1 6s ease-in-out infinite; }
        .wave-layer-2 { animation: waveShift2 8s ease-in-out infinite; }

        /* Pulse for divider dots */
        @keyframes dotPulse {
            0%, 100% { opacity: 0.2; r: 2; }
            50% { opacity: 0.6; r: 4; }
        }
        .divider-dot { animation: dotPulse 3s ease-in-out infinite; }
        .divider-dot:nth-child(2) { animation-delay: 1s; }
        .divider-dot:nth-child(3) { animation-delay: 2s; }

        /* Animated dash for divider lines */
        .divider-line {
            stroke-dasharray: 8 12;
            animation: dashMove 20s linear infinite;
        }
        @keyframes dashMove {
            to { stroke-dashoffset: -200; }
        }
    </style>
</head>
<body class="min-h-screen bg-zinc-50 antialiased dark:bg-zinc-900 overflow-x-hidden">
    {{-- Custom Cursor - Hospital Themed (desktop only) --}}
    <div id="cursor-plus" class="hidden lg:block fixed pointer-events-none z-[9999]" style="transform: translate(-50%, -50%);">
        {{-- Medical cross/plus cursor - bigger --}}
        <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
            <rect x="11" y="2" width="6" height="24" rx="2" fill="white" opacity="0.95"/>
            <rect x="2" y="11" width="24" height="6" rx="2" fill="white" opacity="0.95"/>
        </svg>
    </div>
    <div id="cursor-pulse" class="hidden lg:block fixed w-14 h-14 rounded-full border-2 pointer-events-none z-[9998]" style="transform: translate(-50%, -50%); border-color: rgba(94,234,212,0.7);"></div>
    <div id="cursor-glow" class="hidden lg:block fixed w-48 h-48 rounded-full pointer-events-none z-[9997] opacity-0" style="transform: translate(-50%, -50%); background: radial-gradient(circle, rgba(20,184,166,0.18) 0%, rgba(20,184,166,0.05) 40%, transparent 70%);"></div>
    <canvas id="cursor-ekg" class="hidden lg:block fixed inset-0 pointer-events-none z-[9996]"></canvas>

    {{-- Hero Section with Background --}}
    <div id="hero-section" class="relative min-h-screen overflow-hidden">
        {{-- Background Image with Parallax --}}
        <div class="absolute inset-0 z-0">
            <div id="hero-bg" class="parallax-slow absolute inset-0 scale-110 bg-cover bg-center bg-no-repeat will-change-transform" style="background-image: url('/images/hospital.webp');"></div>
            <div id="hero-overlay" class="absolute inset-0 bg-black/50"></div>
        </div>

        {{-- Floating particles --}}
        <div id="particles" class="absolute inset-0 z-[1] pointer-events-none overflow-hidden">
            @for($i = 0; $i < 20; $i++)
                <div class="particle absolute w-2 h-2 rounded-full bg-white/20" style="left: {{ rand(0, 100) }}%; top: {{ rand(0, 100) }}%;"></div>
            @endfor
        </div>

        {{-- Navigation --}}
        <nav id="navbar" class="relative z-20 flex items-center justify-between px-6 py-4 lg:px-12">
            <a href="{{ url('/') }}" class="logo-link flex items-center gap-3 group">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/15 transition-transform group-hover:scale-110">
                    <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8 2v4" /><path d="M16 2v4" /><rect x="3" y="4" width="18" height="18" rx="3" /><path d="M12 10v6" /><path d="M9 13h6" />
                    </svg>
                </div>
                <span class="text-xl font-bold tracking-tight text-white drop-shadow-lg">{{ config('app.name') }}</span>
            </a>

            <div class="flex items-center gap-2 sm:gap-4">
                @auth
                    <a href="{{ url('/dashboard') }}" class="magnetic-btn whitespace-nowrap rounded-lg bg-white/15 px-3 sm:px-5 py-2 sm:py-2.5 text-sm font-medium text-white transition hover:bg-white/25">
                        {{ __('Dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="nav-link magnetic-btn whitespace-nowrap rounded-lg bg-white/10 px-3 sm:px-5 py-2 sm:py-2.5 text-sm font-medium text-white transition hover:bg-white/20">
                        {{ __('Log in') }}
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="nav-link magnetic-btn glow-hover whitespace-nowrap rounded-lg bg-white px-3 sm:px-5 py-2 sm:py-2.5 text-sm font-medium text-primary transition hover:bg-zinc-100">
                            {{ __('Sign up') }}
                        </a>
                    @endif
                @endauth
            </div>
        </nav>

        {{-- Hero Content --}}
        <div class="relative z-10 flex min-h-[calc(100vh-80px)] items-center px-6 lg:px-12">
            <div class="mx-auto max-w-6xl w-full">
                <div class="grid gap-12 lg:grid-cols-2 lg:gap-16">
                    {{-- Text Content --}}
                    <div class="flex flex-col justify-center text-white">
                        <div id="hero-badge" class="mb-4 inline-flex w-fit items-center gap-2 rounded-full bg-white/20 px-4 py-2 text-sm backdrop-blur-sm">
                            <span class="h-2 w-2 animate-pulse rounded-full bg-success"></span>
                            <span class="badge-text">{{ __('Now accepting appointments') }}</span>
                        </div>

                        <h1 id="hero-title" class="mb-6 text-4xl font-bold leading-tight lg:text-6xl">
                            <span class="line block"><span class="title-word">{{ __('Quality') }}</span> <span class="title-word">{{ __('Healthcare') }}</span></span>
                            <span class="line block text-white/90"><span class="title-word">{{ __('For') }}</span> <span class="title-word">{{ __('Every') }}</span> <span class="title-word">{{ __('Family') }}</span></span>
                        </h1>

                        <p id="hero-description" class="mb-8 max-w-lg text-base text-white/90 lg:text-lg">
                            {{ __('Guardiano Maternity and Children Clinic and Hospital - Providing compassionate care for mothers and children. Book your appointment today and experience healthcare made simple.') }}
                        </p>

                        <div id="hero-buttons" class="flex flex-wrap gap-4">
                            @guest
                                <a href="{{ route('register') }}" class="magnetic-btn glow-hover inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 font-semibold text-primary shadow-xl transition lg:px-8 lg:py-4">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ __('Book Appointment') }}
                                </a>
                            @else
                                <a href="{{ url('/dashboard') }}" class="magnetic-btn glow-hover inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 font-semibold text-primary shadow-xl transition lg:px-8 lg:py-4">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                    {{ __('Go to Dashboard') }}
                                </a>
                            @endguest
                            <a href="#services" class="magnetic-btn inline-flex items-center gap-2 rounded-xl border-2 border-white/40 bg-white/10 px-6 py-3 font-semibold text-white backdrop-blur-sm transition hover:bg-white/20 lg:px-8 lg:py-4">
                                {{ __('Learn More') }}
                                <svg class="h-5 w-5 scroll-indicator" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    {{-- Stats Cards --}}
                    <div class="hidden items-center justify-center lg:flex">
                        <div class="grid gap-4">
                            <div class="stat-card rounded-2xl bg-white/20 p-6 shadow-xl backdrop-blur-md transition-all hover:bg-white/30 hover:scale-105">
                                <div class="flex items-center gap-4">
                                    <div class="stat-icon flex h-14 w-14 items-center justify-center rounded-xl bg-white/30">
                                        <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="stat-number text-3xl font-bold text-white drop-shadow-md" data-value="5000">0</p>
                                        <p class="text-white/80">{{ __('Patients Served') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="stat-card rounded-2xl bg-white/20 p-6 shadow-xl backdrop-blur-md transition-all hover:bg-white/30 hover:scale-105">
                                <div class="flex items-center gap-4">
                                    <div class="stat-icon flex h-14 w-14 items-center justify-center rounded-xl bg-white/30">
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
                            <div class="stat-card rounded-2xl bg-white/20 p-6 shadow-xl backdrop-blur-md transition-all hover:bg-white/30 hover:scale-105">
                                <div class="flex items-center gap-4">
                                    <div class="stat-icon flex h-14 w-14 items-center justify-center rounded-xl bg-white/30">
                                        <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="stat-number text-3xl font-bold text-white drop-shadow-md" data-value="10">0</p>
                                        <p class="text-white/80">{{ __('Expert Doctors') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Scroll indicator --}}
        <div id="scroll-hint" class="absolute bottom-8 left-1/2 -translate-x-1/2 z-10 text-white/60 text-center">
            <p class="text-xs uppercase tracking-widest mb-2">Scroll</p>
            <div class="w-6 h-10 rounded-full border-2 border-white/40 mx-auto flex justify-center">
                <div class="scroll-dot w-1.5 h-1.5 bg-white rounded-full mt-2"></div>
            </div>
        </div>

        {{-- Animated Layered Wave Divider --}}
        <div class="absolute bottom-0 left-0 right-0 z-10 pointer-events-none">
            <svg viewBox="0 0 1440 180" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="block w-full h-[100px] lg:h-[140px]">
                <path class="wave-layer-1 fill-white/10 dark:fill-zinc-800/10" d="M0,180 L48,165 C96,150 192,120 288,110 C384,100 480,110 576,125 C672,140 768,160 864,155 C960,150 1056,120 1152,105 C1248,90 1344,90 1392,90 L1440,90 L1440,180 L0,180 Z" />
                <path class="wave-layer-2 fill-white/30 dark:fill-zinc-900/30" d="M0,180 L48,170 C96,160 192,140 288,125 C384,110 480,100 576,110 C672,120 768,150 864,155 C960,160 1056,140 1152,120 C1248,100 1344,100 1392,100 L1440,100 L1440,180 L0,180 Z" />
                <path class="wave-layer-3" d="M0,180 L48,175 C96,170 192,160 288,148 C384,136 480,122 576,128 C672,134 768,160 864,165 C960,170 1056,154 1152,138 C1248,122 1344,126 1392,128 L1440,130 L1440,180 L0,180 Z" fill="white" class="dark:fill-zinc-900" />
            </svg>
        </div>
    </div>

    {{-- Services Section --}}
    <section id="services" class="bg-white py-20 dark:bg-zinc-900 relative overflow-hidden">
        {{-- Background decoration --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-primary/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-success/5 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2"></div>

        <div class="mx-auto max-w-6xl px-6 lg:px-12 relative">
            <div class="mb-12 text-center">
                <h2 id="services-title" class="mb-4 text-3xl lg:text-4xl font-bold text-zinc-900 dark:text-white">
                    <span class="services-word">{{ __('Our') }}</span> <span class="services-word">{{ __('Services') }}</span>
                </h2>
                <p id="services-description" class="mx-auto max-w-2xl text-zinc-600 dark:text-zinc-400">{{ __('Comprehensive healthcare services designed for your family\'s well-being.') }}</p>
            </div>

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                {{-- Service Cards --}}
                @php
                    $services = [
                        ['icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', 'title' => 'Maternity Care', 'desc' => 'Complete prenatal, delivery, and postnatal care for expecting mothers.', 'color' => 'primary'],
                        ['icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z', 'title' => 'Pediatrics', 'desc' => 'Expert healthcare for infants, children, and adolescents.', 'color' => 'success'],
                        ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'title' => 'General Consultation', 'desc' => 'Comprehensive health checkups and consultations.', 'color' => 'info'],
                        ['icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z', 'title' => 'Immunization', 'desc' => 'Complete vaccination programs for children and adults.', 'color' => 'warning'],
                        ['icon' => 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z', 'title' => 'Laboratory', 'desc' => 'Accurate diagnostic testing and laboratory services.', 'color' => 'destructive'],
                        ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'title' => 'Emergency', 'desc' => '24/7 emergency care and urgent medical services.', 'color' => 'primary'],
                    ];
                @endphp

                @foreach($services as $index => $service)
                    <div class="service-card group rounded-2xl border border-zinc-200 bg-white p-6 transition-all duration-500 hover:border-{{ $service['color'] }}/30 hover:shadow-2xl dark:border-zinc-800 dark:bg-zinc-800/50" data-index="{{ $index }}">
                        <div class="service-icon mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-{{ $service['color'] }}/10 transition-all duration-500 group-hover:bg-{{ $service['color'] }}/20 group-hover:scale-110 group-hover:rotate-6">
                            <svg class="h-7 w-7 text-{{ $service['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $service['icon'] }}" />
                            </svg>
                        </div>
                        <h3 class="service-title mb-2 text-lg font-semibold text-zinc-900 dark:text-white">{{ __($service['title']) }}</h3>
                        <p class="service-desc text-sm text-zinc-600 dark:text-zinc-400">{{ __($service['desc']) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Diagonal Tech Divider: Services → CTA --}}
    <div class="relative h-24 lg:h-32 overflow-hidden">
        <div class="absolute inset-0 bg-white dark:bg-zinc-900"></div>
        <svg viewBox="0 0 1440 100" preserveAspectRatio="none" class="absolute bottom-0 w-full h-full">
            <defs>
                <linearGradient id="divider-grad-1" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" style="stop-color: var(--color-primary); stop-opacity: 1;" />
                    <stop offset="100%" style="stop-color: var(--color-primary); stop-opacity: 0.8;" />
                </linearGradient>
            </defs>
            {{-- Zigzag shape - subtle modern sawtooth --}}
            <path d="M0,100 L0,50 L90,28 L180,50 L270,28 L360,50 L450,28 L540,50 L630,28 L720,50 L810,28 L900,50 L990,28 L1080,50 L1170,28 L1260,50 L1350,28 L1440,50 L1440,100 Z" fill="url(#divider-grad-1)" />
        </svg>
    </div>

    {{-- CTA Section --}}
    <section id="cta-section" class="relative overflow-hidden bg-gradient-to-r from-primary to-primary/80 py-20 lg:py-28">
        {{-- Grid pattern overlay --}}
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute inset-0 opacity-[0.04]" style="background-image: linear-gradient(rgba(255,255,255,.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.1) 1px, transparent 1px); background-size: 40px 40px;"></div>
        </div>

        <div class="mx-auto max-w-4xl px-6 text-center lg:px-12 relative">
            <h2 id="cta-title" class="mb-4 text-3xl lg:text-5xl font-bold text-white">
                <span class="cta-word">{{ __('Ready') }}</span> <span class="cta-word">{{ __('to') }}</span> <span class="cta-word">{{ __('Get') }}</span> <span class="cta-word">{{ __('Started?') }}</span>
            </h2>
            <p id="cta-description" class="mb-8 text-lg text-white/80">{{ __('Book your appointment today and experience quality healthcare for your family.') }}</p>
            @guest
                <a id="cta-button" href="{{ route('register') }}" class="magnetic-btn inline-flex items-center gap-2 rounded-xl bg-white px-8 py-4 font-semibold text-primary shadow-xl transition-all duration-300 hover:shadow-2xl hover:scale-105">
                    {{ __('Create Free Account') }}
                    <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            @else
                <a id="cta-button" href="{{ url('/dashboard') }}" class="magnetic-btn inline-flex items-center gap-2 rounded-xl bg-white px-8 py-4 font-semibold text-primary shadow-xl transition-all duration-300 hover:shadow-2xl hover:scale-105">
                    {{ __('Go to Dashboard') }}
                    <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            @endguest
        </div>
    </section>

    {{-- Footer --}}
    <footer id="footer" class="relative bg-zinc-900 pt-16 pb-8 text-white overflow-hidden">
        {{-- Square grid pattern background --}}
        <svg aria-hidden="true" class="absolute inset-0 z-0 size-full pointer-events-none mask-[radial-gradient(100%_100%_at_top_right,white,transparent)] stroke-white/25">
            <defs>
                <pattern id="footer-grid-pattern" width="40" height="40" x="50%" y="-1" patternUnits="userSpaceOnUse">
                    <path d="M.5 40V.5H40" fill="none" />
                </pattern>
            </defs>
            <svg x="50%" y="-1" class="overflow-visible fill-zinc-800/20">
                <path d="M-200 0h201v201h-201Z M600 0h201v201h-201Z M-400 600h201v201h-201Z M200 800h201v201h-201Z" stroke-width="0" />
            </svg>
            <rect width="100%" height="100%" fill="url(#footer-grid-pattern)" stroke-width="0" />
        </svg>

        <div class="mx-auto max-w-6xl px-6 lg:px-12 relative z-10">
            {{-- Top section --}}
            <div class="grid gap-10 md:grid-cols-3 mb-12">
                {{-- Brand --}}
                <div class="footer-item">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/10">
                            <svg class="h-6 w-6 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M8 2v4" /><path d="M16 2v4" /><rect x="3" y="4" width="18" height="18" rx="3" /><path d="M12 10v6" /><path d="M9 13h6" />
                            </svg>
                        </div>
                        <span class="text-xl font-bold tracking-tight">{{ config('app.name') }}</span>
                    </div>
                    <p class="text-sm text-zinc-400 leading-relaxed max-w-xs">
                        {{ __('Guardiano Maternity and Children Clinic and Hospital - Providing compassionate care for mothers and children since 2010.') }}
                    </p>
                </div>

                {{-- Quick Links --}}
                <div class="footer-item">
                    <h4 class="text-sm font-semibold uppercase tracking-wider text-zinc-300 mb-4">{{ __('Quick Links') }}</h4>
                    <ul class="space-y-2.5">
                        <li>
                            <a href="#services" class="text-sm text-zinc-400 transition-colors hover:text-white inline-flex items-center gap-2 group">
                                <span class="h-px w-0 bg-primary transition-all group-hover:w-4"></span>
                                {{ __('Our Services') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('login') }}" class="text-sm text-zinc-400 transition-colors hover:text-white inline-flex items-center gap-2 group">
                                <span class="h-px w-0 bg-primary transition-all group-hover:w-4"></span>
                                {{ __('Log in') }}
                            </a>
                        </li>
                        @if (Route::has('register'))
                        <li>
                            <a href="{{ route('register') }}" class="text-sm text-zinc-400 transition-colors hover:text-white inline-flex items-center gap-2 group">
                                <span class="h-px w-0 bg-primary transition-all group-hover:w-4"></span>
                                {{ __('Create Account') }}
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>

                {{-- Contact / Hours --}}
                <div class="footer-item">
                    <h4 class="text-sm font-semibold uppercase tracking-wider text-zinc-300 mb-4">{{ __('Clinic Hours') }}</h4>
                    <ul class="space-y-2.5 text-sm text-zinc-400">
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ __('Mon - Sat: 8:00 AM - 5:00 PM') }}
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            {{ __('Emergency: 24/7') }}
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            {{ __('Tacloban City, Leyte') }}
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Bottom bar --}}
            <div class="border-t border-zinc-800 pt-6 flex flex-col items-center justify-between gap-4 md:flex-row">
                <p class="footer-text text-xs text-zinc-500">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
                </p>
                <p class="footer-text text-xs text-zinc-600">
                    {{ __('Hospital Queue Management System') }}
                </p>
            </div>
        </div>
    </footer>

    {{-- GSAP Animations --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Register ScrollTrigger
            gsap.registerPlugin(ScrollTrigger);

            // ============================================
            // Hospital-Themed Cursor - EKG Heartbeat Trail
            // ============================================
            const cursorPlus = document.getElementById('cursor-plus');
            const cursorPulse = document.getElementById('cursor-pulse');
            const cursorGlow = document.getElementById('cursor-glow');
            const ekgCanvas = document.getElementById('cursor-ekg');

            if (cursorPlus && ekgCanvas && window.innerWidth > 1024) {
                document.body.style.cursor = 'none';
                document.querySelectorAll('a, button, .service-card, .stat-card').forEach(el => el.style.cursor = 'none');

                let mouseX = 0, mouseY = 0;
                let prevMouseX = 0, prevMouseY = 0;
                let isHovering = false;
                let heartbeatPhase = 0;

                // EKG trail state
                const trailHistory = []; // {x, y, age}
                const maxTrailLength = 50;
                const burstParticles = []; // click burst particles

                // Canvas setup
                const ctx = ekgCanvas.getContext('2d');
                function resizeCanvas() {
                    ekgCanvas.width = window.innerWidth;
                    ekgCanvas.height = window.innerHeight;
                }
                resizeCanvas();
                window.addEventListener('resize', resizeCanvas);

                document.addEventListener('mousemove', (e) => {
                    mouseX = e.clientX;
                    mouseY = e.clientY;
                });

                // Heartbeat pulse on the ring (72 BPM = ~833ms)
                function heartbeatPulse() {
                    if (!isHovering) {
                        // Double-beat like a real heartbeat: lub-dub
                        gsap.timeline()
                            .to(cursorPulse, { scale: 1.5, borderColor: 'rgba(167,243,208,0.9)', duration: 0.1, ease: 'power2.out' })
                            .to(cursorPulse, { scale: 1, borderColor: 'rgba(94,234,212,0.7)', duration: 0.15, ease: 'power2.in' })
                            .to(cursorPulse, { scale: 1.25, borderColor: 'rgba(167,243,208,0.6)', duration: 0.08, ease: 'power2.out' }, '+=0.05')
                            .to(cursorPulse, { scale: 1, borderColor: 'rgba(94,234,212,0.7)', duration: 0.2, ease: 'power2.in' });
                    }
                    setTimeout(heartbeatPulse, 850 + Math.random() * 100);
                }
                setTimeout(heartbeatPulse, 1500);

                // EKG waveform shape generator (QRS complex) - BIGGER amplitude
                // Returns a Y offset for the given phase (0-1)
                function ekgWaveform(phase) {
                    // P wave (small bump)
                    if (phase < 0.1) return Math.sin(phase / 0.1 * Math.PI) * 8;
                    // Flat
                    if (phase < 0.2) return 0;
                    // Q dip
                    if (phase < 0.25) return -((phase - 0.2) / 0.05) * 12;
                    // R spike (sharp up)
                    if (phase < 0.35) return -12 + ((phase - 0.25) / 0.1) * 55;
                    // S dip (sharp down)
                    if (phase < 0.45) return 43 - ((phase - 0.35) / 0.1) * 58;
                    // Return to baseline
                    if (phase < 0.55) return -15 + ((phase - 0.45) / 0.1) * 15;
                    // Flat
                    if (phase < 0.7) return 0;
                    // T wave (gentle bump)
                    if (phase < 0.85) return Math.sin((phase - 0.7) / 0.15 * Math.PI) * 10;
                    // Flat
                    return 0;
                }

                // Pulse ring state (expanding circles like heart monitor blips)
                const pulseRings = []; // {x, y, radius, maxRadius, life, lineWidth}

                // Main animation loop
                function animate() {
                    const speed = Math.sqrt(
                        (mouseX - prevMouseX) ** 2 + (mouseY - prevMouseY) ** 2
                    );
                    const angle = Math.atan2(mouseY - prevMouseY, mouseX - prevMouseX);

                    // Move cursor elements
                    gsap.to(cursorPlus, { x: mouseX, y: mouseY, duration: 0.08, overwrite: true });
                    gsap.to(cursorPulse, { x: mouseX, y: mouseY, duration: 0.2, ease: 'power2.out', overwrite: true });
                    gsap.to(cursorGlow, { x: mouseX, y: mouseY, duration: 0.4, ease: 'power1.out', overwrite: true });

                    // Glow intensity
                    gsap.to(cursorGlow, { opacity: isHovering ? 0.5 : Math.min(speed / 40, 0.35), duration: 0.3, overwrite: 'auto' });

                    // Plus rotation based on velocity
                    if (speed > 3 && !isHovering) {
                        gsap.to(cursorPlus.querySelector('svg'), {
                            rotation: '+=' + (speed * 0.8),
                            duration: 0.2, overwrite: true
                        });
                    }

                    // Add trail point
                    if (speed > 1) {
                        // Advance heartbeat phase based on distance traveled
                        heartbeatPhase += speed * 0.008;
                        if (heartbeatPhase > 1) heartbeatPhase -= 1;

                        const ekgOffset = ekgWaveform(heartbeatPhase);
                        // Perpendicular offset to movement direction
                        const perpX = -Math.sin(angle) * ekgOffset;
                        const perpY = Math.cos(angle) * ekgOffset;

                        trailHistory.push({
                            x: mouseX + perpX,
                            y: mouseY + perpY,
                            rawX: mouseX,
                            rawY: mouseY,
                            age: 0,
                            ekgOffset: ekgOffset
                        });
                        if (trailHistory.length > maxTrailLength) {
                            trailHistory.shift();
                        }
                    }

                    // Draw EKG trail
                    ctx.clearRect(0, 0, ekgCanvas.width, ekgCanvas.height);

                    if (trailHistory.length > 2) {
                        // Main EKG line
                        ctx.beginPath();
                        ctx.moveTo(trailHistory[0].x, trailHistory[0].y);
                        for (let i = 1; i < trailHistory.length; i++) {
                            const p = trailHistory[i];
                            const prev = trailHistory[i - 1];
                            // Smooth curve between points
                            const cpx = (prev.x + p.x) / 2;
                            const cpy = (prev.y + p.y) / 2;
                            ctx.quadraticCurveTo(prev.x, prev.y, cpx, cpy);
                        }
                        // Gradient stroke from teal to transparent
                        const gradient = ctx.createLinearGradient(
                            trailHistory[0].x, trailHistory[0].y,
                            trailHistory[trailHistory.length - 1].x, trailHistory[trailHistory.length - 1].y
                        );
                        gradient.addColorStop(0, 'rgba(45, 212, 191, 0)');
                        gradient.addColorStop(0.3, 'rgba(45, 212, 191, 0.3)');
                        gradient.addColorStop(0.7, 'rgba(45, 212, 191, 0.6)');
                        gradient.addColorStop(1, 'rgba(45, 212, 191, 0.9)');
                        ctx.strokeStyle = gradient;
                        ctx.lineWidth = 3;
                        ctx.lineCap = 'round';
                        ctx.lineJoin = 'round';
                        ctx.stroke();

                        // Glow line (thicker, more transparent)
                        ctx.beginPath();
                        ctx.moveTo(trailHistory[0].x, trailHistory[0].y);
                        for (let i = 1; i < trailHistory.length; i++) {
                            const p = trailHistory[i];
                            const prev = trailHistory[i - 1];
                            const cpx = (prev.x + p.x) / 2;
                            const cpy = (prev.y + p.y) / 2;
                            ctx.quadraticCurveTo(prev.x, prev.y, cpx, cpy);
                        }
                        const glowGradient = ctx.createLinearGradient(
                            trailHistory[0].x, trailHistory[0].y,
                            trailHistory[trailHistory.length - 1].x, trailHistory[trailHistory.length - 1].y
                        );
                        glowGradient.addColorStop(0, 'rgba(45, 212, 191, 0)');
                        glowGradient.addColorStop(0.5, 'rgba(45, 212, 191, 0.08)');
                        glowGradient.addColorStop(1, 'rgba(45, 212, 191, 0.2)');
                        ctx.strokeStyle = glowGradient;
                        ctx.lineWidth = 10;
                        ctx.stroke();

                        // Draw small dots at EKG peaks (R-wave peaks) - bigger
                        trailHistory.forEach((p, i) => {
                            if (Math.abs(p.ekgOffset) > 20) {
                                const alpha = (i / trailHistory.length) * 0.9;
                                // Outer glow
                                ctx.beginPath();
                                ctx.arc(p.x, p.y, 8, 0, Math.PI * 2);
                                ctx.fillStyle = `rgba(94, 234, 212, ${alpha * 0.2})`;
                                ctx.fill();
                                // Inner dot
                                ctx.beginPath();
                                ctx.arc(p.x, p.y, 4, 0, Math.PI * 2);
                                ctx.fillStyle = `rgba(94, 234, 212, ${alpha})`;
                                ctx.fill();
                            }
                        });
                    }

                    // Draw pulse rings (expanding circles like heart monitor)
                    for (let i = pulseRings.length - 1; i >= 0; i--) {
                        const ring = pulseRings[i];
                        ring.radius += 1.5;
                        ring.life -= 0.025;
                        if (ring.life <= 0 || ring.radius > ring.maxRadius) {
                            pulseRings.splice(i, 1); continue;
                        }
                        // Outer glow ring
                        ctx.beginPath();
                        ctx.arc(ring.x, ring.y, ring.radius, 0, Math.PI * 2);
                        ctx.strokeStyle = `rgba(94, 234, 212, ${ring.life * 0.15})`;
                        ctx.lineWidth = ring.lineWidth * 3;
                        ctx.stroke();
                        // Main ring
                        ctx.beginPath();
                        ctx.arc(ring.x, ring.y, ring.radius, 0, Math.PI * 2);
                        ctx.strokeStyle = `rgba(94, 234, 212, ${ring.life * 0.5})`;
                        ctx.lineWidth = ring.lineWidth * ring.life;
                        ctx.stroke();
                        // Bright inner ring
                        ctx.beginPath();
                        ctx.arc(ring.x, ring.y, ring.radius, 0, Math.PI * 2);
                        ctx.strokeStyle = `rgba(255, 255, 255, ${ring.life * 0.3})`;
                        ctx.lineWidth = ring.lineWidth * 0.5 * ring.life;
                        ctx.stroke();
                    }

                    // Spawn pulse rings from EKG peaks
                    if (trailHistory.length > 5 && Math.random() < 0.06) {
                        const peakPoints = trailHistory.filter(p => Math.abs(p.ekgOffset) > 20);
                        if (peakPoints.length > 0) {
                            const pk = peakPoints[peakPoints.length - 1];
                            pulseRings.push({
                                x: pk.x, y: pk.y,
                                radius: 3, maxRadius: 35 + Math.random() * 20,
                                life: 1.0, lineWidth: 2
                            });
                        }
                    }

                    // Draw click burst particles (medical cross shaped)
                    for (let i = burstParticles.length - 1; i >= 0; i--) {
                        const bp = burstParticles[i];
                        bp.x += bp.vx;
                        bp.y += bp.vy;
                        bp.vx *= 0.95;
                        bp.vy *= 0.95;
                        bp.life -= 0.025;
                        if (bp.life <= 0) { burstParticles.splice(i, 1); continue; }

                        ctx.save();
                        ctx.translate(bp.x, bp.y);
                        ctx.rotate(bp.rotation);
                        ctx.globalAlpha = bp.life;
                        // Draw tiny plus/cross
                        const s = bp.size * bp.life;
                        ctx.fillStyle = bp.color;
                        ctx.fillRect(-s/2, -s*1.5, s, s*3); // vertical
                        ctx.fillRect(-s*1.5, -s/2, s*3, s); // horizontal
                        ctx.restore();
                    }

                    // Age out old trail points slowly
                    if (trailHistory.length > 0 && speed < 0.5) {
                        trailHistory[0].age++;
                        if (trailHistory[0].age > 8) {
                            trailHistory.shift();
                        }
                    }

                    prevMouseX = mouseX;
                    prevMouseY = mouseY;
                    requestAnimationFrame(animate);
                }
                animate();

                // Hover effects for interactive elements
                document.querySelectorAll('a, button, .magnetic-btn').forEach(el => {
                    el.addEventListener('mouseenter', () => {
                        isHovering = true;
                        gsap.to(cursorPulse, {
                            width: 56, height: 56, borderWidth: 2,
                            borderColor: 'rgba(167,243,208,0.8)',
                            duration: 0.4, ease: 'back.out(2)'
                        });
                        gsap.to(cursorPlus.querySelector('svg'), { scale: 0.7, duration: 0.3 });
                        // Continuous rotation
                        gsap.to(cursorPlus.querySelector('svg'), { rotation: '+=360', duration: 3, repeat: -1, ease: 'none' });
                    });
                    el.addEventListener('mouseleave', () => {
                        isHovering = false;
                        gsap.killTweensOf(cursorPlus.querySelector('svg'), 'rotation');
                        gsap.to(cursorPulse, {
                            width: 56, height: 56, borderWidth: 2,
                            borderColor: 'rgba(94,234,212,0.7)',
                            duration: 0.4, ease: 'back.out(1.5)'
                        });
                        gsap.to(cursorPlus.querySelector('svg'), { scale: 1, rotation: 0, duration: 0.3 });
                    });
                });

                // Service cards - heartbeat intensifies
                document.querySelectorAll('.service-card').forEach(card => {
                    card.addEventListener('mouseenter', () => {
                        isHovering = true;
                        gsap.to(cursorPulse, {
                            width: 64, height: 64, borderColor: 'rgba(167,243,208,0.9)',
                            borderWidth: 2, duration: 0.4, ease: 'back.out(2)'
                        });
                        gsap.to(cursorPlus.querySelector('svg rect'), {
                            fill: '#14b8a6', duration: 0.3
                        });
                    });
                    card.addEventListener('mouseleave', () => {
                        isHovering = false;
                        gsap.to(cursorPulse, {
                            width: 56, height: 56, borderColor: 'rgba(94,234,212,0.7)',
                            duration: 0.3
                        });
                        gsap.to(cursorPlus.querySelector('svg rect'), {
                            fill: 'white', duration: 0.3
                        });
                    });
                });

                // Click burst - lightning explosion + medical cross particles
                document.addEventListener('mousedown', () => {
                    gsap.fromTo(cursorPulse,
                        { scale: 0.7 },
                        { scale: 2, opacity: 0, duration: 0.5, ease: 'power2.out',
                          onComplete: () => gsap.to(cursorPulse, { scale: 1, opacity: 1, duration: 0.2 })
                        }
                    );
                    gsap.fromTo(cursorPlus.querySelector('svg'),
                        { scale: 0.5 },
                        { scale: 1.2, duration: 0.3, ease: 'back.out(3)' }
                    );
                    // Spawn defibrillator shockwave pulse rings from click
                    for (let i = 0; i < 3; i++) {
                        pulseRings.push({
                            x: mouseX, y: mouseY,
                            radius: 5 + i * 4, maxRadius: 60 + i * 20,
                            life: 1.0, lineWidth: 3 - i * 0.5
                        });
                    }
                    // Spawn cross-shaped burst particles
                    const colors = ['rgba(45,212,191,1)', 'rgba(94,234,212,1)', 'rgba(255,255,255,0.8)'];
                    for (let i = 0; i < 12; i++) {
                        const a = (Math.PI * 2 / 12) * i + Math.random() * 0.3;
                        const spd = 3 + Math.random() * 5;
                        burstParticles.push({
                            x: mouseX, y: mouseY,
                            vx: Math.cos(a) * spd,
                            vy: Math.sin(a) * spd,
                            life: 0.8 + Math.random() * 0.2,
                            size: 2 + Math.random() * 2.5,
                            rotation: Math.random() * Math.PI,
                            color: colors[Math.floor(Math.random() * colors.length)]
                        });
                    }
                });

                // Hide cursor when leaving window
                document.addEventListener('mouseleave', () => {
                    gsap.to([cursorPlus, cursorPulse, cursorGlow], { opacity: 0, duration: 0.2 });
                });
                document.addEventListener('mouseenter', () => {
                    gsap.to([cursorPlus, cursorPulse], { opacity: 1, duration: 0.2 });
                });
            }

            // ============================================
            // Magnetic Button Effect
            // ============================================
            document.querySelectorAll('.magnetic-btn').forEach(btn => {
                btn.addEventListener('mousemove', (e) => {
                    const rect = btn.getBoundingClientRect();
                    const x = e.clientX - rect.left - rect.width / 2;
                    const y = e.clientY - rect.top - rect.height / 2;
                    gsap.to(btn, { x: x * 0.2, y: y * 0.2, duration: 0.3 });
                });
                btn.addEventListener('mouseleave', () => {
                    gsap.to(btn, { x: 0, y: 0, duration: 0.5, ease: 'elastic.out(1, 0.5)' });
                });
            });

            // ============================================
            // Floating Particles Animation
            // ============================================
            gsap.utils.toArray('.particle').forEach((particle, i) => {
                gsap.to(particle, {
                    y: 'random(-100, 100)',
                    x: 'random(-50, 50)',
                    opacity: 'random(0.1, 0.5)',
                    duration: 'random(3, 6)',
                    repeat: -1,
                    yoyo: true,
                    ease: 'sine.inOut',
                    delay: i * 0.1
                });
            });

            // ============================================
            // Scroll Indicator Animation
            // ============================================
            gsap.to('.scroll-dot', {
                y: 20,
                opacity: 0,
                duration: 1.5,
                repeat: -1,
                ease: 'power2.in'
            });

            // Hide scroll indicator on scroll
            ScrollTrigger.create({
                trigger: '#services',
                start: 'top bottom',
                onEnter: () => gsap.to('#scroll-hint', { opacity: 0, duration: 0.3 }),
                onLeaveBack: () => gsap.to('#scroll-hint', { opacity: 1, duration: 0.3 })
            });

            // ============================================
            // Hero Section Animations
            // ============================================

            // Initial states (navbar stays visible for UX)
            gsap.set(['#hero-badge', '#hero-title', '#hero-description', '#hero-buttons', '.stat-card'], {
                opacity: 0
            });

            // Background parallax on scroll
            gsap.to('#hero-bg', {
                yPercent: 30,
                ease: 'none',
                scrollTrigger: {
                    trigger: '#hero-section',
                    start: 'top top',
                    end: 'bottom top',
                    scrub: true
                }
            });

            // Hero background zoom on load
            gsap.fromTo('#hero-bg',
                { scale: 1.2 },
                { scale: 1, duration: 2, ease: 'power2.out' }
            );

            // Navbar animation (subtle slide down)
            gsap.from('#navbar', {
                y: -30,
                opacity: 0.5,
                duration: 0.8,
                ease: 'power3.out',
                delay: 0.2
            });

            // Hero content timeline
            const heroTl = gsap.timeline({ delay: 0.6 });

            heroTl
                // Badge
                .to('#hero-badge', { opacity: 1, duration: 0.6 })
                .from('#hero-badge', { y: 40, scale: 0.8, duration: 0.8, ease: 'back.out(2)' }, '<')

                // Title words
                .to('#hero-title', { opacity: 1, duration: 0.1 }, '-=0.3')
                .from('.title-word', {
                    y: 100,
                    opacity: 0,
                    rotationX: -90,
                    stagger: 0.1,
                    duration: 1,
                    ease: 'power4.out'
                }, '<')

                // Description
                .to('#hero-description', { opacity: 1, duration: 0.6 }, '-=0.5')
                .from('#hero-description', { y: 30, duration: 0.8, ease: 'power3.out' }, '<')

                // Buttons
                .to('#hero-buttons', { opacity: 1, duration: 0.6 }, '-=0.4')
                .from('#hero-buttons', { y: 30, duration: 0.8, ease: 'power3.out' }, '<');

            // Stats cards animation
            gsap.to('.stat-card', {
                opacity: 1,
                x: 0,
                rotationY: 0,
                duration: 1,
                stagger: 0.2,
                ease: 'power3.out',
                delay: 1.5
            });
            gsap.from('.stat-card', {
                x: 150,
                rotationY: 45,
                duration: 1,
                stagger: 0.2,
                ease: 'power3.out',
                delay: 1.5
            });

            // Stat icons rotation
            gsap.from('.stat-icon', {
                rotation: -180,
                scale: 0,
                duration: 0.8,
                stagger: 0.2,
                ease: 'back.out(2)',
                delay: 2
            });

            // Counter animation
            document.querySelectorAll('.stat-number').forEach(el => {
                const value = parseInt(el.dataset.value);
                if (value) {
                    gsap.to(el, {
                        innerHTML: value,
                        duration: 2.5,
                        delay: 2,
                        ease: 'power2.out',
                        snap: { innerHTML: 1 },
                        onUpdate: function() {
                            el.innerHTML = Math.floor(this.targets()[0].innerHTML).toLocaleString() + '+';
                        }
                    });
                }
            });

            // ============================================
            // Services Section Animations
            // ============================================

            // Title animation
            gsap.from('.services-word', {
                scrollTrigger: {
                    trigger: '#services-title',
                    start: 'top 90%',
                    toggleActions: 'play none none none'
                },
                y: 40,
                opacity: 0,
                stagger: 0.1,
                duration: 0.8,
                ease: 'power3.out'
            });

            // Description
            gsap.from('#services-description', {
                scrollTrigger: {
                    trigger: '#services-description',
                    start: 'top 90%',
                    toggleActions: 'play none none none'
                },
                y: 30,
                opacity: 0,
                duration: 0.6,
                ease: 'power3.out'
            });

            // Service cards with stagger
            const serviceCards = gsap.utils.toArray('.service-card');
            serviceCards.forEach((card, i) => {
                gsap.from(card, {
                    scrollTrigger: {
                        trigger: card,
                        start: 'top 90%',
                        toggleActions: 'play none none none'
                    },
                    y: 60,
                    opacity: 0,
                    duration: 0.8,
                    delay: i * 0.1,
                    ease: 'power3.out'
                });
            });

            // ============================================
            // CTA Section Animations
            // ============================================

            // Background shapes animation
            gsap.to('.cta-shape', {
                scrollTrigger: {
                    trigger: '#cta-section',
                    start: 'top bottom',
                    end: 'bottom top',
                    scrub: 1
                },
                rotation: 180,
                scale: 1.2,
                ease: 'none'
            });

            // CTA content animation
            gsap.from('.cta-word', {
                scrollTrigger: {
                    trigger: '#cta-title',
                    start: 'top 90%',
                    toggleActions: 'play none none none'
                },
                y: 40,
                opacity: 0,
                stagger: 0.08,
                duration: 0.8,
                ease: 'power3.out'
            });

            gsap.from('#cta-description', {
                scrollTrigger: {
                    trigger: '#cta-description',
                    start: 'top 90%',
                    toggleActions: 'play none none none'
                },
                y: 30,
                opacity: 0,
                duration: 0.6,
                ease: 'power3.out'
            });

            gsap.from('#cta-button', {
                scrollTrigger: {
                    trigger: '#cta-button',
                    start: 'top 95%',
                    toggleActions: 'play none none none'
                },
                y: 20,
                opacity: 0,
                scale: 0.9,
                duration: 0.6,
                ease: 'power3.out'
            });

            // ============================================
            // Footer Animation
            // ============================================
            gsap.from('.footer-item', {
                scrollTrigger: {
                    trigger: '#footer',
                    start: 'top 90%',
                    toggleActions: 'play none none none'
                },
                y: 40,
                opacity: 0,
                stagger: 0.15,
                duration: 0.8,
                ease: 'power3.out'
            });

            gsap.from('.footer-text', {
                scrollTrigger: {
                    trigger: '#footer',
                    start: 'top 80%',
                    toggleActions: 'play none none none'
                },
                y: 20,
                opacity: 0,
                stagger: 0.1,
                duration: 0.6,
                delay: 0.3,
                ease: 'power3.out'
            });

            // ============================================
            // Smooth Scroll
            // ============================================
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        const offsetTop = target.offsetTop;
                        gsap.to(window, {
                            duration: 1.2,
                            scrollTo: { y: offsetTop - 50, autoKill: false },
                            ease: 'power3.inOut'
                        });
                    }
                });
            });
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollToPlugin.min.js"></script>
</body>
</html>
