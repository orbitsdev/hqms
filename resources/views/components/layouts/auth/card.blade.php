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
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        .animate-fade-in-up { animation: fadeInUp 0.6s ease-out forwards; }
        .animate-fade-in-up-delay-1 { animation: fadeInUp 0.6s ease-out 0.1s forwards; opacity: 0; }
        .animate-fade-in-up-delay-2 { animation: fadeInUp 0.6s ease-out 0.2s forwards; opacity: 0; }
        .animate-fade-in-up-delay-3 { animation: fadeInUp 0.6s ease-out 0.3s forwards; opacity: 0; }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .glass-card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .dark .glass-card {
            background: rgba(24, 24, 27, 0.88);
        }
    </style>
</head>
<body class="min-h-screen antialiased">
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden">
        {{-- Full-Screen Background Image --}}
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat scale-105 transition-transform duration-[20s]" style="background-image: url('{{ $background ?? '/images/hospital.webp' }}');"></div>
            {{-- Gradient overlay --}}
            <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/40 to-black/70"></div>
        </div>

        {{-- Floating decorative elements --}}
        <div class="absolute inset-0 z-[1] pointer-events-none overflow-hidden">
            <div class="absolute -top-20 -right-20 h-64 w-64 rounded-full bg-primary/10 blur-3xl animate-float"></div>
            <div class="absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-info/10 blur-3xl animate-float" style="animation-delay: -3s;"></div>
        </div>

        {{-- Top branding bar --}}
        <div class="absolute top-0 left-0 right-0 z-20 flex items-center justify-between px-6 py-4 lg:px-10">
            <a href="{{ url('/') }}" class="flex items-center gap-3 group transition-opacity hover:opacity-80">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 backdrop-blur-sm drop-shadow-lg transition-transform group-hover:scale-110">
                    <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8 2v4" /><path d="M16 2v4" /><rect x="3" y="4" width="18" height="18" rx="3" /><path d="M12 10v6" /><path d="M9 13h6" />
                    </svg>
                </div>
                <span class="text-lg font-bold tracking-tight text-white drop-shadow-lg">{{ config('app.name') }}</span>
            </a>
        </div>

        {{-- Main Card --}}
        <div class="relative z-10 w-full max-w-md mx-4 animate-fade-in-up">
            <div class="glass-card rounded-2xl border border-white/20 p-8 shadow-2xl dark:border-zinc-700/50 sm:p-10">
                {{ $slot }}
            </div>

            {{-- Hospital info below card --}}
            <div class="mt-6 text-center animate-fade-in-up-delay-3">
                <p class="text-sm text-white/70 drop-shadow-md">
                    {{ __('Guardiano Maternity and Children Clinic and Hospital') }}
                </p>
                <p class="mt-1 text-xs text-white/50">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
                </p>
            </div>
        </div>
    </div>

    @fluxScripts
</body>
</html>
