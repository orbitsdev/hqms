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
    </style>
</head>
<body class="min-h-screen bg-zinc-50 antialiased dark:bg-zinc-900 overflow-x-hidden">
    {{-- Custom Cursor (desktop only) --}}
    <div id="cursor" class="hidden lg:block fixed w-5 h-5 rounded-full border-2 border-primary pointer-events-none z-[9999] mix-blend-difference" style="transform: translate(-50%, -50%);"></div>
    <div id="cursor-follower" class="hidden lg:block fixed w-10 h-10 rounded-full bg-primary/20 pointer-events-none z-[9998]" style="transform: translate(-50%, -50%);"></div>

    {{-- Hero Section with Background --}}
    <div id="hero-section" class="relative min-h-screen overflow-hidden">
        {{-- Background Image with Parallax --}}
        <div class="absolute inset-0 z-0">
            <div id="hero-bg" class="parallax-slow absolute inset-0 scale-110 bg-cover bg-center bg-no-repeat will-change-transform" style="background-image: url('/images/bg.png');"></div>
            <div id="hero-overlay" class="absolute inset-0 bg-black/50"></div>
        </div>

        {{-- Floating particles --}}
        <div id="particles" class="absolute inset-0 z-[1] pointer-events-none overflow-hidden">
            @for($i = 0; $i < 20; $i++)
                <div class="particle absolute w-2 h-2 rounded-full bg-white/20" style="left: {{ rand(0, 100) }}%; top: {{ rand(0, 100) }}%;"></div>
            @endfor
        </div>

        {{-- Navigation --}}
        <nav id="navbar" class="relative z-20 flex items-center justify-between px-6 py-4 lg:px-12 bg-black/20 backdrop-blur-sm">
            <a href="{{ url('/') }}" class="logo-link flex items-center gap-3 group">
                <img src="{{ asset('images/caretime_logo.png') }}" alt="CareTime" class="h-10 w-10 object-contain transition-transform group-hover:scale-110" />
                <span class="text-xl font-bold tracking-tight text-white drop-shadow-lg">{{ config('app.name') }}</span>
            </a>

            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ url('/dashboard') }}" class="magnetic-btn rounded-lg bg-white/20 px-5 py-2.5 text-sm font-medium text-white shadow-lg backdrop-blur-sm transition hover:bg-white/30">
                        {{ __('Dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="nav-link magnetic-btn rounded-lg bg-white/10 px-5 py-2.5 text-sm font-medium text-white shadow-lg backdrop-blur-sm transition hover:bg-white/20">
                        {{ __('Log in') }}
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="nav-link magnetic-btn glow-hover rounded-lg bg-white px-5 py-2.5 text-sm font-medium text-primary shadow-lg transition hover:bg-zinc-100 hover:shadow-xl">
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
                                        <p class="stat-number text-3xl font-bold text-white drop-shadow-md" data-value="10000">0</p>
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
                                        <p class="stat-number text-3xl font-bold text-white drop-shadow-md" data-value="15">0</p>
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

        {{-- Wave Decoration --}}
        <div class="absolute bottom-0 left-0 right-0 z-10 pointer-events-none">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="block w-full h-auto">
                <path d="M0,120 L60,110 C120,100 240,80 360,70 C480,60 600,60 720,65 C840,70 960,80 1080,85 C1200,90 1320,90 1380,90 L1440,90 L1440,120 L0,120 Z" fill="white" class="dark:fill-zinc-900"></path>
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

    {{-- CTA Section --}}
    <section id="cta-section" class="relative overflow-hidden bg-gradient-to-r from-primary to-primary/80 py-20">
        {{-- Animated background shapes --}}
        <div class="absolute inset-0 overflow-hidden">
            <div class="cta-shape absolute -top-20 -left-20 w-64 h-64 bg-white/10 rounded-full"></div>
            <div class="cta-shape absolute -bottom-20 -right-20 w-96 h-96 bg-white/5 rounded-full"></div>
            <div class="cta-shape absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] border border-white/10 rounded-full"></div>
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
    <footer id="footer" class="bg-zinc-900 py-12 text-white">
        <div class="mx-auto max-w-6xl px-6 lg:px-12">
            <div class="flex flex-col items-center justify-between gap-6 md:flex-row">
                <div class="footer-logo flex items-center gap-3">
                    <x-app-logo class="h-8 w-8 text-white" />
                    <span class="font-bold">{{ config('app.name') }}</span>
                </div>
                <p class="footer-text text-sm text-zinc-400">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
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
            // Custom Cursor
            // ============================================
            const cursor = document.getElementById('cursor');
            const follower = document.getElementById('cursor-follower');

            if (cursor && follower && window.innerWidth > 1024) {
                document.addEventListener('mousemove', (e) => {
                    gsap.to(cursor, { x: e.clientX, y: e.clientY, duration: 0.1 });
                    gsap.to(follower, { x: e.clientX, y: e.clientY, duration: 0.3 });
                });

                // Cursor hover effects
                document.querySelectorAll('a, button, .magnetic-btn').forEach(el => {
                    el.addEventListener('mouseenter', () => {
                        gsap.to(cursor, { scale: 2, borderColor: '#14b8a6' });
                        gsap.to(follower, { scale: 1.5, opacity: 0.5 });
                    });
                    el.addEventListener('mouseleave', () => {
                        gsap.to(cursor, { scale: 1, borderColor: '#14b8a6' });
                        gsap.to(follower, { scale: 1, opacity: 1 });
                    });
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
            gsap.from('.footer-logo, .footer-text', {
                scrollTrigger: {
                    trigger: '#footer',
                    start: 'top 98%',
                    toggleActions: 'play none none none'
                },
                y: 20,
                opacity: 0,
                stagger: 0.15,
                duration: 0.6,
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
