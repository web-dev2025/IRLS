<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'IRLS' }}</title>
    <script>
        (function() {
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 antialiased">

    <nav class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-5xl mx-auto px-4 flex items-center h-14 gap-6">
            <a href="{{ route('home') }}" class="font-semibold text-gray-900 dark:text-gray-100 hover:text-gray-600 dark:hover:text-gray-300" title="Interactive reading/learning system">
                IRLS
            </a>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('categories.index') }}"
                   class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 {{ request()->routeIs('categories.*') ? 'text-gray-900 dark:text-gray-100 font-medium' : '' }}">
                    Манга
                </a>
                <a href="{{ route('dictionary.index') }}"
                   class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 {{ request()->routeIs('dictionary.*') ? 'text-gray-900 dark:text-gray-100 font-medium' : '' }}">
                    Словарь
                </a>
                <a href="{{ route('quiz.index') }}"
                   class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 {{ request()->routeIs('quiz.*') ? 'text-gray-900 dark:text-gray-100 font-medium' : '' }}">
                    Тренировка
                </a>
            </div>
            <div class="ml-auto">
                <button onclick="toggleTheme()"
                        class="p-1.5 rounded-lg text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer transition-colors"
                        title="Переключить тему">
                    {{-- Moon: shown in light mode --}}
                    <svg class="h-4 w-4 block dark:hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    {{-- Sun: shown in dark mode --}}
                    <svg class="h-4 w-4 hidden dark:block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 py-8">

        @if (session('success'))
            <div class="mb-6 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    <script>
    function toggleTheme() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    }
    </script>

</body>
</html>
