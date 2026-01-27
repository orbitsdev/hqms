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
        // Audio context for chime (needs user interaction to start)
        let audioContext = null;
        let audioEnabled = false;

        // Connection status indicator
        document.addEventListener('DOMContentLoaded', function() {
            const indicator = document.getElementById('connection-status');

            if (typeof window.Echo !== 'undefined') {
                // Monitor connection state
                window.Echo.connector.pusher.connection.bind('connected', () => {
                    console.log('✅ Reverb connected');
                    if (indicator) {
                        indicator.className = 'h-2 w-2 rounded-full bg-green-500';
                        indicator.title = 'Live (Reverb)';
                    }
                });

                window.Echo.connector.pusher.connection.bind('disconnected', () => {
                    console.log('❌ Reverb disconnected');
                    if (indicator) {
                        indicator.className = 'h-2 w-2 rounded-full bg-red-500';
                        indicator.title = 'Disconnected';
                    }
                });

                window.Echo.connector.pusher.connection.bind('error', (error) => {
                    console.error('Reverb error:', error);
                });

                // Subscribe to channels and log
                const typeId = window.consultationTypeId;

                window.Echo.channel('queue.display.all')
                    .listen('.queue.updated', (e) => {
                        console.log('📡 Received on queue.display.all:', e);
                        Livewire.dispatch('refreshFromEcho', { event: e });
                    });

                if (typeId) {
                    window.Echo.channel('queue.display.' + typeId)
                        .listen('.queue.updated', (e) => {
                            console.log('📡 Received on queue.display.' + typeId + ':', e);
                            Livewire.dispatch('refreshFromEcho', { event: e });
                        });
                }
            } else {
                console.warn('Echo not initialized');
            }
        });

        // Enable audio on first click (browsers require user interaction)
        function enableAudio() {
            if (audioEnabled) return;

            try {
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
                audioEnabled = true;

                // Update sound button
                const soundBtn = document.getElementById('sound-toggle');
                if (soundBtn) {
                    soundBtn.innerHTML = '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" /></svg>';
                    soundBtn.title = 'Sound enabled';
                    soundBtn.classList.remove('bg-zinc-700');
                    soundBtn.classList.add('bg-emerald-600');
                }

                // Play test chime to confirm
                playChime();
                console.log('🔊 Audio enabled');
            } catch (e) {
                console.log('Audio not supported');
            }
        }

        // Toggle audio on/off
        function toggleAudio() {
            const soundBtn = document.getElementById('sound-toggle');

            if (!audioEnabled) {
                enableAudio();
            } else {
                audioEnabled = false;
                if (soundBtn) {
                    soundBtn.innerHTML = '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" /></svg>';
                    soundBtn.title = 'Sound disabled (click to enable)';
                    soundBtn.classList.remove('bg-emerald-600');
                    soundBtn.classList.add('bg-zinc-700');
                }
                console.log('🔇 Audio disabled');
            }
        }

        // Generate chime sound using Web Audio API
        function playChime() {
            if (!audioEnabled || !audioContext) return;

            try {
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
                console.log('Audio playback failed');
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
