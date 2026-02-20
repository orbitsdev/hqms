@props(['title' => null, 'background' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInLeft {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }
        @keyframes pulse-slow {
            0%, 100% { opacity: 0.15; }
            50% { opacity: 0.3; }
        }
        @keyframes dash-move {
            to { stroke-dashoffset: -100; }
        }
        .animate-fade-in-up { animation: fadeInUp 0.6s ease-out forwards; }
        .animate-fade-in-up-delay-1 { animation: fadeInUp 0.6s ease-out 0.15s forwards; opacity: 0; }
        .animate-fade-in-up-delay-2 { animation: fadeInUp 0.6s ease-out 0.3s forwards; opacity: 0; }
        .animate-fade-in-left { animation: fadeInLeft 0.8s ease-out forwards; }
        .animate-fade-in-left-delay-1 { animation: fadeInLeft 0.8s ease-out 0.2s forwards; opacity: 0; }
        .animate-fade-in-left-delay-2 { animation: fadeInLeft 0.8s ease-out 0.4s forwards; opacity: 0; }
        .animate-fade-in-left-delay-3 { animation: fadeInLeft 0.8s ease-out 0.6s forwards; opacity: 0; }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-float-delay { animation: float 8s ease-in-out 2s infinite; }
        .animate-pulse-slow { animation: pulse-slow 4s ease-in-out infinite; }
        .animate-dash { stroke-dasharray: 6 10; animation: dash-move 15s linear infinite; }
    </style>
</head>
<body class="min-h-screen bg-zinc-50 antialiased dark:bg-zinc-900">
    <div class="flex min-h-screen gap-0">
        {{-- Background Image Side --}}
        <div class="relative hidden w-1/2 shrink-0 lg:block overflow-hidden">
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat scale-110" style="background-image: url('{{ $background ?? '/images/hospital.webp' }}');"></div>
            {{-- Full dark overlay --}}
            <div class="absolute inset-0 bg-black/75"></div>
            {{-- Gradient accent --}}
            <div class="absolute inset-0 bg-gradient-to-br from-primary/20 via-transparent to-info/10"></div>

            {{-- Floating particles --}}
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="absolute top-[15%] left-[10%] w-2 h-2 rounded-full bg-primary/30 animate-float"></div>
                <div class="absolute top-[30%] right-[20%] w-1.5 h-1.5 rounded-full bg-white/20 animate-float-delay"></div>
                <div class="absolute top-[60%] left-[25%] w-1 h-1 rounded-full bg-primary/25 animate-float"></div>
                <div class="absolute top-[75%] right-[15%] w-2.5 h-2.5 rounded-full bg-white/15 animate-float-delay"></div>
                <div class="absolute top-[45%] left-[60%] w-1.5 h-1.5 rounded-full bg-primary/20 animate-float"></div>
                <div class="absolute top-[85%] left-[50%] w-1 h-1 rounded-full bg-white/25 animate-float-delay"></div>
            </div>

            {{-- Decorative SVG elements --}}
            <svg class="absolute inset-0 w-full h-full pointer-events-none" xmlns="http://www.w3.org/2000/svg">
                {{-- Corner accent lines --}}
                <line x1="0" y1="0" x2="200" y2="0" class="animate-dash" stroke="rgba(20,184,166,0.2)" stroke-width="1" />
                <line x1="0" y1="0" x2="0" y2="200" class="animate-dash" stroke="rgba(20,184,166,0.2)" stroke-width="1" />
                {{-- Bottom-right accent --}}
                <line x1="100%" y1="100%" x2="calc(100% - 180)" y2="100%" class="animate-dash" stroke="rgba(20,184,166,0.15)" stroke-width="1" />
                <line x1="100%" y1="100%" x2="100%" y2="calc(100% - 180)" class="animate-dash" stroke="rgba(20,184,166,0.15)" stroke-width="1" />
                {{-- Floating rings --}}
                <circle cx="85%" cy="20%" r="60" fill="none" stroke="rgba(20,184,166,0.08)" stroke-width="1" class="animate-pulse-slow" />
                <circle cx="15%" cy="80%" r="40" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="1" class="animate-pulse-slow" />
                {{-- Diagonal accent --}}
                <line x1="70%" y1="0" x2="100%" y2="40%" class="animate-dash" stroke="rgba(255,255,255,0.05)" stroke-width="1" />
            </svg>

            {{-- Content --}}
            <div class="relative z-10 flex h-full flex-col justify-between p-12">
                {{-- Logo top-left --}}
                <div class="animate-fade-in-left">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-3 group">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/10 backdrop-blur-sm border border-white/10 transition-transform group-hover:scale-110">
                            <svg class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M8 2v4" /><path d="M16 2v4" /><rect x="3" y="4" width="18" height="18" rx="3" /><path d="M12 10v6" /><path d="M9 13h6" />
                            </svg>
                        </div>
                        <span class="text-2xl font-bold tracking-tight text-white drop-shadow-lg">{{ config('app.name') }}</span>
                    </a>
                </div>

                {{-- Main heading left-aligned --}}
                <div class="space-y-8">
                    <div class="space-y-5 animate-fade-in-left-delay-1">
                        <div class="inline-flex items-center gap-2 rounded-full bg-primary/15 border border-primary/20 px-4 py-1.5">
                            <span class="h-2 w-2 rounded-full bg-primary animate-pulse"></span>
                            <span class="text-xs font-semibold uppercase tracking-widest text-primary">{{ __('Now Accepting Patients') }}</span>
                        </div>
                        <h2 class="text-5xl font-extrabold leading-[1.1] text-white">
                            {{ __('Quality') }}<br>
                            {{ __('Healthcare') }}<br>
                            <span class="bg-gradient-to-r from-primary to-teal-300 bg-clip-text text-transparent">{{ __('For Every Family') }}</span>
                        </h2>
                    </div>
                    <p class="max-w-xs text-base text-white/60 leading-relaxed animate-fade-in-left-delay-2">
                        {{ __('Providing compassionate care for mothers and children since 2010.') }}
                    </p>
                    {{-- Features list --}}
                    <div class="space-y-3 animate-fade-in-left-delay-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/15 border border-primary/20">
                                <svg class="h-4 w-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                            <span class="text-sm text-white/70">{{ __('Easy online appointment booking') }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/15 border border-primary/20">
                                <svg class="h-4 w-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <span class="text-sm text-white/70">{{ __('Real-time queue tracking') }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/15 border border-primary/20">
                                <svg class="h-4 w-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            </div>
                            <span class="text-sm text-white/70">{{ __('Digital medical records access') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <p class="text-xs text-white/30 animate-fade-in-left-delay-3">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
                </p>
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
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10">
                        <svg class="h-5 w-5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8 2v4" /><path d="M16 2v4" /><rect x="3" y="4" width="18" height="18" rx="3" /><path d="M12 10v6" /><path d="M9 13h6" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ config('app.name') }}</span>
                </a>
            </div>

            <div class="relative mx-auto w-full max-w-md animate-fade-in-up">
                {{ $slot }}
            </div>
        </div>
    </div>

    @fluxScripts
</body>
</html>
