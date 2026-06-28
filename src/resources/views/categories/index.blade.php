<x-layouts.app title="Манга">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Манга</h1>
        <a href="{{ route('categories.create') }}"
           class="px-4 py-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-sm rounded-lg hover:bg-gray-700 dark:hover:bg-gray-100">
            + Добавить серию
        </a>
    </div>

    @if ($categories->isEmpty())
        <div class="text-center py-20 text-gray-400 dark:text-gray-500">
            <p class="text-lg">Пока нет ни одной серии манги.</p>
            <a href="{{ route('categories.create') }}" class="mt-3 inline-block text-sm text-gray-600 dark:text-gray-400 underline">
                Добавить первую
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 gap-3">
            @foreach ($categories as $category)
                <div class="flex items-center justify-between bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-5 py-4">
                    <a href="{{ route('categories.show', $category) }}" class="flex-1 min-w-0 hover:opacity-75">
                        <div class="font-medium">{{ $category->name }}</div>
                        @if ($category->description)
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $category->description }}</div>
                        @endif
                    </a>
                    <div class="flex items-center gap-4 ml-4 shrink-0">
                        <span class="text-sm text-gray-400 dark:text-gray-500">{{ $category->chapters_count }} гл.</span>
                        <a href="{{ route('categories.edit', $category) }}"
                           class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                            Изменить
                        </a>
                        <form action="{{ route('categories.destroy', $category) }}" method="POST"
                              onsubmit="return confirm('Удалить «{{ $category->name }}»? Все главы будут удалены.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                Удалить
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</x-layouts.app>
