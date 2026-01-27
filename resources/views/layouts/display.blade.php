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

    <script>
        // Generate chime sound using Web Audio API (no file needed)
        function playChime() {
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();

                // Create a pleasant two-tone chime
                const frequencies = [880, 1108.73]; // A5 and C#6 - pleasant interval

                frequencies.forEach((freq, index) => {
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();

                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);

                    oscillator.frequency.value = freq;
                    oscillator.type = 'sine';

                    const startTime = audioContext.currentTime + (index * 0.15);
                    const duration = 0.4;

                    gainNode.gain.setValueAtTime(0, startTime);
                    gainNode.gain.linearRampToValueAtTime(0.3, startTime + 0.02);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, startTime + duration);

                    oscillator.start(startTime);
                    oscillator.stop(startTime + duration);
                });
            } catch (e) {
                console.log('Audio not supported');
            }
        }

        // Play chime when new number is called
        document.addEventListener('livewire:init', () => {
            let lastCalledId = null;

            Livewire.hook('morph.updated', ({ el, component }) => {
                const calledEl = document.querySelector('[data-called-id]');
                if (calledEl) {
                    const newId = calledEl.dataset.calledId;
                    if (lastCalledId !== null && lastCalledId !== newId) {
                        // New number called - play chime
                        playChime();
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
