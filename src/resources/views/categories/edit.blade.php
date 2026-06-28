<x-layouts.app title="Редактировать серию">

    <div class="mb-6">
        <a href="{{ route('categories.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
            ← Назад
        </a>
    </div>

    <h1 class="text-2xl font-semibold mb-6">Редактировать серию</h1>

    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-6 max-w-lg">
        <form action="{{ route('categories.update', $category) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Название</label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                       class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-gray-500 dark:focus:border-gray-400 @error('name') border-red-400 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Описание <span class="text-gray-400 dark:text-gray-500 font-normal">(необязательно)</span></label>
                <textarea name="description" rows="3"
                          class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-gray-500 dark:focus:border-gray-400 @error('description') border-red-400 @enderror">{{ old('description', $category->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="px-5 py-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-sm rounded-lg hover:bg-gray-700 dark:hover:bg-gray-100">
                    Сохранить
                </button>
                <a href="{{ route('categories.index') }}"
                   class="px-5 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700">
                    Отмена
                </a>
            </div>
        </form>
    </div>

</x-layouts.app>
