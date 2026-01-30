<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Access Denied') }} - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-50 dark:bg-zinc-900 flex items-center justify-center p-4">
    <div class="text-center max-w-lg">
        <img
            src="{{ asset('images/undraw_void_wez2.svg') }}"
            alt="Access denied"
            class="w-full max-w-sm mx-auto mb-8 opacity-80"
        />

        <h1 class="text-6xl font-bold text-zinc-900 dark:text-white mb-2">403</h1>
        <h2 class="text-xl font-semibold text-zinc-700 dark:text-zinc-300 mb-4">
            {{ __('Access Denied') }}
        </h2>
        <p class="text-zinc-500 dark:text-zinc-400 mb-8">
            {{ __('Sorry, you do not have permission to access this page.') }}
        </p>

        <a
            href="{{ route('dashboard') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-6 py-3 text-sm font-medium text-white transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
            </svg>
            {{ __('Go to Dashboard') }}
        </a>
    </div>
</body>
</html>
