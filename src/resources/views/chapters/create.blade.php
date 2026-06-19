<x-layouts.app title="Добавить главу">

    <div class="mb-6">
        <a href="{{ route('categories.show', $category) }}" class="text-sm text-gray-500 hover:text-gray-900">
            ← {{ $category->name }}
        </a>
    </div>

    <h1 class="text-2xl font-semibold mb-6">Добавить главу</h1>

    <div class="bg-white border border-gray-200 rounded-lg p-6 max-w-lg">
        <form action="{{ route('categories.chapters.store', $category) }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Название главы</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       placeholder="Например: Глава 1"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-500 @error('title') border-red-400 @enderror">
                @error('title')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Option A: URL for scraping --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    URL страницы манги
                    <span class="text-gray-400 font-normal">(для автоматической загрузки)</span>
                </label>
                <input type="url" name="source_url" value="{{ old('source_url') }}"
                       placeholder="https://..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-500 @error('source_url') border-red-400 @enderror">
                @error('source_url')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="relative flex items-center gap-3">
                <div class="flex-1 border-t border-gray-200"></div>
                <span class="text-xs text-gray-400">или</span>
                <div class="flex-1 border-t border-gray-200"></div>
            </div>

            {{-- Option B: Manual image URLs --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Ссылки на изображения
                    <span class="text-gray-400 font-normal">(по одной на строку)</span>
                </label>
                <textarea name="image_urls" rows="6"
                          placeholder="https://example.com/page1.jpg&#10;https://example.com/page2.jpg&#10;..."
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-gray-500 @error('image_urls') border-red-400 @enderror">{{ old('image_urls') }}</textarea>
                <p class="mt-1 text-xs text-gray-400">Используется если URL страницы не указан или скрейпинг не сработал.</p>
                @error('image_urls')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 pt-1">
                <button type="submit"
                        class="px-5 py-2 bg-gray-900 text-white text-sm rounded-lg hover:bg-gray-700">
                    Добавить и загрузить
                </button>
                <a href="{{ route('categories.show', $category) }}"
                   class="px-5 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">
                    Отмена
                </a>
            </div>
        </form>
    </div>

</x-layouts.app>
