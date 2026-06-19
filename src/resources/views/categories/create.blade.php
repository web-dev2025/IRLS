<x-layouts.app title="Новая серия">

    <div class="mb-6">
        <a href="{{ route('categories.index') }}" class="text-sm text-gray-500 hover:text-gray-900">
            ← Назад
        </a>
    </div>

    <h1 class="text-2xl font-semibold mb-6">Новая серия манги</h1>

    <div class="bg-white border border-gray-200 rounded-lg p-6 max-w-lg">
        <form action="{{ route('categories.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Название</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       placeholder="Например: One Piece"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-500 @error('name') border-red-400 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Описание <span class="text-gray-400 font-normal">(необязательно)</span></label>
                <textarea name="description" rows="3"
                          placeholder="Краткое описание серии"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-500 @error('description') border-red-400 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="px-5 py-2 bg-gray-900 text-white text-sm rounded-lg hover:bg-gray-700">
                    Создать
                </button>
                <a href="{{ route('categories.index') }}"
                   class="px-5 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">
                    Отмена
                </a>
            </div>
        </form>
    </div>

</x-layouts.app>
