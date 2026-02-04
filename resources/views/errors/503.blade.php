<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Be Right Back') }} - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    @vite(['resources/css/app.css'])
    <style>
        .coin {
            position: absolute;
            font-size: 1.5rem;
            opacity: 0;
            pointer-events: none;
        }
        @media (min-width: 640px) {
            .coin { font-size: 2rem; }
        }
        .piggy-container {
            position: relative;
            display: inline-block;
        }
        .tear {
            position: absolute;
            width: 6px;
            height: 10px;
            background: linear-gradient(180deg, #60a5fa 0%, #3b82f6 100%);
            border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
            opacity: 0;
        }
        @media (min-width: 640px) {
            .tear { width: 8px; height: 12px; }
        }
        .tear-left { left: 25%; top: 35%; }
        .tear-right { right: 25%; top: 35%; }
        .empty-wallet {
            animation: shake 0.5s ease-in-out infinite;
        }
        @keyframes shake {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-5deg); }
            75% { transform: rotate(5deg); }
        }
        .invoice-stamp {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            font-size: 1.75rem;
            font-weight: 900;
            color: #ef4444;
            border: 4px solid #ef4444;
            padding: 0.25rem 0.75rem;
            border-radius: 0.5rem;
            opacity: 0;
            text-transform: uppercase;
            white-space: nowrap;
        }
        @media (min-width: 640px) {
            .invoice-stamp {
                font-size: 3rem;
                border-width: 6px;
                padding: 0.5rem 1.5rem;
            }
        }
        .progress-bar {
            background: linear-gradient(90deg, #ef4444 0%, #f97316 50%, #22c55e 100%);
        }
        .moth {
            position: absolute;
            font-size: 1.25rem;
            opacity: 0;
        }
        @media (min-width: 640px) {
            .moth { font-size: 1.5rem; }
        }
        .timer-pulse {
            animation: timerPulse 2s ease-in-out infinite;
        }
        @keyframes timerPulse {
            0%, 100% { box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.1), 0 2px 4px -1px rgba(239, 68, 68, 0.06); }
            50% { box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.3), 0 4px 6px -1px rgba(239, 68, 68, 0.2); }
        }
    </style>
</head>
<body class="min-h-screen bg-zinc-50 dark:bg-zinc-900 flex items-center justify-center p-3 sm:p-4 overflow-hidden">
    {{-- Floating coins that fall --}}
    <div id="coins-container" class="fixed inset-0 pointer-events-none"></div>

    <div class="text-center w-full max-w-2xl relative z-10 px-2 sm:px-0">
        {{-- Sad Piggy Bank --}}
        <div class="piggy-container mb-4 sm:mb-8">
            <div id="piggy" class="text-6xl sm:text-8xl md:text-9xl">
                🐷
            </div>
            <div class="tear tear-left" id="tear-left"></div>
            <div class="tear tear-right" id="tear-right"></div>
            {{-- Moths flying out of empty wallet --}}
            <div class="moth" id="moth1">🦋</div>
            <div class="moth" id="moth2">🦋</div>
            <div class="moth" id="moth3">🦋</div>
        </div>

        {{-- Invoice with UNPAID stamp --}}
        <div class="relative inline-block w-full sm:w-auto mb-4 sm:mb-8 p-4 sm:p-6 bg-white dark:bg-zinc-800 rounded-xl sm:rounded-2xl shadow-xl">
            <div class="text-left space-y-1.5 sm:space-y-2 text-sm sm:text-base">
                <div class="flex justify-between items-center border-b border-zinc-200 dark:border-zinc-700 pb-2 gap-4">
                    <span class="text-zinc-500 dark:text-zinc-400 text-xs sm:text-sm">Invoice #2026-001</span>
                    <span class="text-red-500 font-bold text-xs sm:text-sm">OVERDUE</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-zinc-600 dark:text-zinc-300">Website Hosting</span>
                    <span class="text-zinc-900 dark:text-white font-mono text-sm sm:text-base" id="amount">₱950.00</span>
                </div>
                <div class="flex justify-between gap-4 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                    <span class="text-zinc-900 dark:text-white font-bold">Total Due</span>
                    <span class="text-red-500 font-bold font-mono text-sm sm:text-base">₱950.00</span>
                </div>
            </div>
            <div class="invoice-stamp" id="stamp">UNPAID</div>
        </div>

        {{-- Payment Progress Bar (stuck at 0%) --}}
        <div class="mb-4 sm:mb-8">
            <div class="flex justify-between text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mb-2">
                <span>Payment Progress</span>
                <span id="progress-text">0%</span>
            </div>
            <div class="h-3 sm:h-4 bg-zinc-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                <div id="progress-fill" class="h-full progress-bar rounded-full" style="width: 0%"></div>
            </div>
            <p class="text-[10px] sm:text-xs text-zinc-400 dark:text-zinc-500 mt-1 italic">*Progress may increase upon payment</p>
        </div>

        <h1 class="text-2xl sm:text-4xl md:text-5xl font-black text-zinc-900 dark:text-white mb-2 sm:mb-4 leading-tight" id="title">
            <span id="title-text">Website is Taking a Nap</span>
            <span id="zzz" class="inline-block text-xl sm:text-4xl">💤</span>
        </h1>

        <p class="text-sm sm:text-lg text-zinc-600 dark:text-zinc-400 mb-3 sm:mb-4 px-2" id="subtitle">
            Our servers are on vacation until the invoice goes from
            <span class="text-red-500 font-bold">UNPAID</span> to
            <span class="text-green-500 font-bold">PAID</span>
        </p>

        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg sm:rounded-xl p-3 sm:p-4 mb-4 sm:mb-8">
            <p class="text-red-800 dark:text-red-200 text-xs sm:text-sm">
                <span class="font-bold">📢 Paalala:</span>
                Palihog bayari ang imo developer!
                <span class="empty-wallet inline-block">💸</span>
                <br>
                <span class="text-red-600 dark:text-red-300 font-semibold">Gutom na ang developer mo, boss! Bayad anay antes ma-online ang website. 🙏</span>
            </p>
        </div>

        {{-- Overdue Timer - counting UP --}}
        <p class="text-red-500 dark:text-red-400 text-xs sm:text-sm font-bold mb-2 uppercase tracking-wider">
            ⚠️ Payment Overdue By:
        </p>
        <div class="grid grid-cols-4 gap-1.5 sm:gap-4 mb-4 sm:mb-8" id="countdown">
            <div class="bg-white dark:bg-zinc-800 rounded-lg sm:rounded-xl p-2 sm:p-4 shadow-lg border-2 border-red-200 dark:border-red-900 timer-pulse">
                <div class="text-xl sm:text-3xl font-black text-red-500" id="days">00</div>
                <div class="text-[9px] sm:text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Days</div>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-lg sm:rounded-xl p-2 sm:p-4 shadow-lg border-2 border-red-200 dark:border-red-900 timer-pulse" style="animation-delay: 0.5s;">
                <div class="text-xl sm:text-3xl font-black text-red-500" id="hours">00</div>
                <div class="text-[9px] sm:text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Hours</div>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-lg sm:rounded-xl p-2 sm:p-4 shadow-lg border-2 border-red-200 dark:border-red-900 timer-pulse" style="animation-delay: 1s;">
                <div class="text-xl sm:text-3xl font-black text-red-500" id="minutes">00</div>
                <div class="text-[9px] sm:text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Mins</div>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-lg sm:rounded-xl p-2 sm:p-4 shadow-lg border-2 border-red-200 dark:border-red-900 timer-pulse" style="animation-delay: 1.5s;">
                <div class="text-xl sm:text-3xl font-black text-red-500" id="seconds">00</div>
                <div class="text-[9px] sm:text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Secs</div>
            </div>
        </div>

        <p class="text-zinc-400 dark:text-zinc-500 text-xs sm:text-sm">
            Questions? The answer is probably "payment"
            <span class="inline-block">😉</span>
        </p>
    </div>

    <script>
        // GSAP Animations
        document.addEventListener('DOMContentLoaded', () => {
            // Piggy bounce animation
            gsap.to('#piggy', {
                y: -20,
                duration: 0.5,
                repeat: -1,
                yoyo: true,
                ease: 'power1.inOut'
            });

            // Crying tears animation
            function animateTear(id, delay) {
                gsap.to(id, {
                    opacity: 1,
                    y: 40,
                    duration: 1,
                    delay: delay,
                    repeat: -1,
                    repeatDelay: 0.5,
                    ease: 'power1.in',
                    onRepeat: function() {
                        gsap.set(id, { y: 0, opacity: 0 });
                    }
                });
            }
            animateTear('#tear-left', 0);
            animateTear('#tear-right', 0.5);

            // Moths flying out animation
            function animateMoth(id, delay) {
                gsap.set(id, { x: 0, y: 0, opacity: 0 });
                gsap.to(id, {
                    opacity: 1,
                    x: gsap.utils.random(-100, 100),
                    y: gsap.utils.random(-150, -50),
                    rotation: gsap.utils.random(-180, 180),
                    duration: 2,
                    delay: delay,
                    repeat: -1,
                    repeatDelay: 1,
                    ease: 'power1.out',
                    onRepeat: function() {
                        gsap.set(id, { x: 0, y: 0, opacity: 0, rotation: 0 });
                    }
                });
            }
            animateMoth('#moth1', 0);
            animateMoth('#moth2', 0.7);
            animateMoth('#moth3', 1.4);

            // UNPAID stamp slam animation
            gsap.fromTo('#stamp',
                { opacity: 0, scale: 3, rotation: -30 },
                {
                    opacity: 1,
                    scale: 1,
                    rotation: -15,
                    duration: 0.3,
                    delay: 1,
                    ease: 'back.out(1.7)'
                }
            );

            // Progress bar "trying" to fill but failing
            function fakeProgress() {
                gsap.to('#progress-fill', {
                    width: '3%',
                    duration: 2,
                    ease: 'power2.out',
                    onComplete: () => {
                        gsap.to('#progress-fill', {
                            width: '0%',
                            duration: 0.5,
                            delay: 0.5,
                            ease: 'power2.in',
                            onComplete: fakeProgress
                        });
                    }
                });
                gsap.to('#progress-text', {
                    innerText: '3%',
                    duration: 2,
                    snap: { innerText: 1 },
                    onComplete: () => {
                        gsap.to('#progress-text', {
                            innerText: '0%',
                            duration: 0.5,
                            delay: 0.5,
                            snap: { innerText: 1 }
                        });
                    }
                });
            }
            fakeProgress();

            // ZZZ floating animation
            gsap.to('#zzz', {
                y: -10,
                opacity: 0.5,
                duration: 1,
                repeat: -1,
                yoyo: true,
                ease: 'power1.inOut'
            });

            // Falling coins
            function createCoin() {
                const coin = document.createElement('div');
                coin.className = 'coin';
                coin.innerText = ['💰', '💵', '💸', '🪙'][Math.floor(Math.random() * 4)];
                coin.style.left = Math.random() * 100 + 'vw';
                coin.style.top = '-50px';
                document.getElementById('coins-container').appendChild(coin);

                gsap.to(coin, {
                    y: window.innerHeight + 100,
                    x: gsap.utils.random(-50, 50),
                    rotation: gsap.utils.random(-360, 360),
                    opacity: 1,
                    duration: gsap.utils.random(3, 6),
                    ease: 'power1.in',
                    onComplete: () => coin.remove()
                });
            }

            // Create coins periodically
            setInterval(createCoin, 800);

            // Title entrance animation
            gsap.from('#title', {
                y: 50,
                opacity: 0,
                duration: 1,
                ease: 'back.out(1.7)'
            });

            gsap.from('#subtitle', {
                y: 30,
                opacity: 0,
                duration: 1,
                delay: 0.3,
                ease: 'power2.out'
            });

            // Countdown boxes entrance
            gsap.from('#countdown > div', {
                scale: 0,
                opacity: 0,
                duration: 0.5,
                stagger: 0.1,
                delay: 0.5,
                ease: 'back.out(1.7)'
            });

            // Overdue timer - counting UP from due date
            // Set to 48 hours ago from now
            const dueDate = new Date(Date.now() - (48 * 60 * 60 * 1000)); // 48 hours overdue

            function updateOverdueTimer() {
                const now = new Date();
                const diff = now - dueDate; // milliseconds overdue

                if (diff > 0) {
                    const seconds = Math.floor((diff / 1000) % 60);
                    const minutes = Math.floor((diff / (1000 * 60)) % 60);
                    const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));

                    // Update with animation
                    const daysEl = document.getElementById('days');
                    const hoursEl = document.getElementById('hours');
                    const minutesEl = document.getElementById('minutes');
                    const secondsEl = document.getElementById('seconds');

                    // Animate number changes
                    if (daysEl.innerText !== String(days).padStart(2, '0')) {
                        gsap.fromTo(daysEl, { scale: 1.2 }, { scale: 1, duration: 0.2 });
                    }
                    if (secondsEl.innerText !== String(seconds).padStart(2, '0')) {
                        gsap.fromTo(secondsEl, { scale: 1.3, color: '#ef4444' }, { scale: 1, color: '#ef4444', duration: 0.3 });
                    }

                    daysEl.innerText = String(days).padStart(2, '0');
                    hoursEl.innerText = String(hours).padStart(2, '0');
                    minutesEl.innerText = String(minutes).padStart(2, '0');
                    secondsEl.innerText = String(seconds).padStart(2, '0');
                }
            }

            // Update immediately and then every second
            updateOverdueTimer();
            setInterval(updateOverdueTimer, 1000);
        });
    </script>
</body>
</html>
