<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'IRLS' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">

    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-5xl mx-auto px-4 flex items-center h-14 gap-6">
            <a href="{{ route('categories.index') }}" class="font-semibold text-gray-900 hover:text-gray-600" title="Interactive reading/learning system">
                IRLS
            </a>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('categories.index') }}"
                   class="text-gray-500 hover:text-gray-900 {{ request()->routeIs('categories.*') ? 'text-gray-900 font-medium' : '' }}">
                    Манга
                </a>
                <a href="{{ route('dictionary.index') }}"
                   class="text-gray-500 hover:text-gray-900 {{ request()->routeIs('dictionary.*') ? 'text-gray-900 font-medium' : '' }}">
                    Словарь
                </a>
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 py-8">

        @if (session('success'))
            <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{ $slot }}
    </main>

</body>
</html>
