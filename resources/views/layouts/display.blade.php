<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="refresh" content="300"> {{-- Auto refresh every 5 minutes as fallback --}}

    <title>{{ $title ?? __('Queue Display') }} - {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Prevent screen from sleeping/dimming */
        body {
            cursor: none;
        }

        /* Animation for called number */
        @keyframes pulse-scale {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        .animate-pulse-scale {
            animation: pulse-scale 2s ease-in-out infinite;
        }

        @keyframes fade-in-up {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fade-in-up 0.5s ease-out;
        }
    </style>
</head>
<body class="min-h-screen bg-zinc-900 text-white antialiased">
    {{ $slot }}

    {{-- Audio for queue call notification --}}
    <audio id="queue-chime" preload="auto">
        <source src="{{ asset('sounds/chime.mp3') }}" type="audio/mpeg">
    </audio>

    <script>
        // Play chime when new number is called
        document.addEventListener('livewire:init', () => {
            let lastCalledId = null;

            Livewire.hook('morph.updated', ({ el, component }) => {
                const calledEl = document.querySelector('[data-called-id]');
                if (calledEl) {
                    const newId = calledEl.dataset.calledId;
                    if (lastCalledId !== null && lastCalledId !== newId) {
                        // New number called - play sound
                        const chime = document.getElementById('queue-chime');
                        if (chime) {
                            chime.currentTime = 0;
                            chime.play().catch(() => {});
                        }
                    }
                    lastCalledId = newId;
                }
            });
        });

        // Keep screen awake (for browsers that support it)
        if ('wakeLock' in navigator) {
            navigator.wakeLock.request('screen').catch(() => {});
        }
    </script>
</body>
</html>
