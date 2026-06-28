<x-layouts.app title="Словарь">

    <div class="flex items-start justify-between mb-6 gap-4">
        <h1 class="text-2xl font-semibold">Словарь</h1>

        <div class="flex items-center gap-3 flex-wrap justify-end">
            {{-- Filter by category --}}
            <form method="GET" action="{{ route('dictionary.index') }}" class="flex items-center gap-2" id="filter-form">
                <input type="hidden" name="learned" value="{{ request('learned') }}">
                <select name="category_id" onchange="this.form.submit()"
                        class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5 text-sm bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:outline-none focus:border-gray-500 dark:focus:border-gray-400">
                    <option value="">Все серии</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Learned filter --}}
                <select name="learned" onchange="this.form.submit()"
                        class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5 text-sm bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:outline-none focus:border-gray-500 dark:focus:border-gray-400">
                    <option value="" {{ request('learned') === null || request('learned') === '' ? 'selected' : '' }}>Все слова</option>
                    <option value="0" {{ request('learned') === '0' ? 'selected' : '' }}>Не выучены</option>
                    <option value="1" {{ request('learned') === '1' ? 'selected' : '' }}>Выучены</option>
                </select>
            </form>

            {{-- Quiz --}}
            <a href="{{ route('quiz.index', request()->only('category_id')) }}"
               class="px-4 py-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                Тренировка
            </a>

            {{-- Export --}}
            <a href="{{ route('dictionary.export', request()->only('category_id')) }}"
               class="px-4 py-1.5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-sm rounded-lg hover:bg-gray-700 dark:hover:bg-gray-100">
                Экспорт для Anki
            </a>
        </div>
    </div>

    @if ($notes->isEmpty())
        <div class="text-center py-20 text-gray-400 dark:text-gray-500">
            <p>Заметок пока нет.</p>
            <p class="text-sm mt-2">Откройте главу и кликните на изображение, чтобы добавить слово.</p>
        </div>
    @else
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-3 py-3 w-8"></th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400 w-1/4">Слово / фраза</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400 w-1/4">Перевод</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Комментарий</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400 w-1/5">Источник</th>
                        <th class="px-4 py-3 w-8"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($notes as $note)
                        <tr class="{{ $note->is_learned ? 'bg-gray-50 dark:bg-gray-800/50' : 'hover:bg-gray-50 dark:hover:bg-gray-800' }}"
                            data-note-id="{{ $note->id }}">

                            {{-- Learned toggle --}}
                            <td class="px-3 py-3 text-center">
                                <button type="button"
                                        onclick="toggleLearned({{ $note->id }}, this)"
                                        class="transition-colors cursor-pointer text-base leading-none {{ $note->is_learned ? 'text-green-500 hover:text-gray-300 dark:text-green-400 dark:hover:text-gray-600' : 'text-gray-200 hover:text-green-400 dark:text-gray-700 dark:hover:text-green-500' }}"
                                        title="{{ $note->is_learned ? 'Отметить как невыученное' : 'Отметить как выученное' }}">
                                    ✓
                                </button>
                            </td>

                            <td class="px-4 py-3 font-medium {{ $note->is_learned ? 'text-gray-400 dark:text-gray-500' : 'text-gray-900 dark:text-gray-100' }}">
                                {{ $note->phrase }}
                            </td>
                            <td class="px-4 py-3 {{ $note->is_learned ? 'text-gray-400 dark:text-gray-500' : 'text-gray-600 dark:text-gray-400' }}">
                                {{ $note->translation ?: '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-400 dark:text-gray-500 text-xs">
                                {{ $note->comment ?: '' }}
                            </td>
                            <td class="px-4 py-3 text-gray-400 dark:text-gray-500 text-xs">
                                @if ($note->page?->chapter)
                                    <a href="{{ route('chapters.read', $note->page->chapter) }}#page-{{ $note->page->page_number }}"
                                       class="hover:text-gray-700 dark:hover:text-gray-300 hover:underline">
                                        {{ $note->page->chapter->category->name }}<br>
                                        {{ $note->page->chapter->title }}, стр. {{ $note->page->page_number }}
                                    </a>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button type="button"
                                        onclick="deleteNote({{ $note->id }}, this)"
                                        class="text-gray-300 dark:text-gray-600 hover:text-red-500 dark:hover:text-red-400 transition-colors cursor-pointer"
                                        title="Удалить">
                                    ✕
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($notes->hasPages())
            <div class="mt-4">
                {{ $notes->links() }}
            </div>
        @endif

        <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">
            Всего: {{ $totalCount }} {{ trans_choice('слово|слова|слов', $totalCount) }}
            @if ($learnedCount > 0)
                · <span class="text-green-500 dark:text-green-400">{{ $learnedCount }} выучено</span>
                · {{ $totalCount - $learnedCount }} осталось
            @endif
        </p>
    @endif

</x-layouts.app>

<script>
function toggleLearned(id, btn) {
    fetch(`/api/notes/${id}/learned`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    })
    .then(r => r.json())
    .then(data => {
        const row = btn.closest('tr');
        const learned = data.is_learned;

        // Toggle button
        btn.className = 'transition-colors cursor-pointer text-base leading-none ' +
            (learned
                ? 'text-green-500 hover:text-gray-300 dark:text-green-400 dark:hover:text-gray-600'
                : 'text-gray-200 hover:text-green-400 dark:text-gray-700 dark:hover:text-green-500');
        btn.title = learned ? 'Отметить как невыученное' : 'Отметить как выученное';

        // Row background
        const phraseCell = row.cells[1];
        const transCell  = row.cells[2];
        if (learned) {
            row.classList.add('bg-gray-50', 'dark:bg-gray-800/50');
            row.classList.remove('hover:bg-gray-50', 'dark:hover:bg-gray-800');
            phraseCell.className = phraseCell.className
                .replace('text-gray-900', 'text-gray-400')
                .replace('dark:text-gray-100', 'dark:text-gray-500');
            transCell.className = transCell.className
                .replace('text-gray-600', 'text-gray-400')
                .replace('dark:text-gray-400', 'dark:text-gray-500');
        } else {
            row.classList.remove('bg-gray-50', 'dark:bg-gray-800/50');
            row.classList.add('hover:bg-gray-50', 'dark:hover:bg-gray-800');
            phraseCell.className = phraseCell.className
                .replace('text-gray-400', 'text-gray-900')
                .replace('dark:text-gray-500', 'dark:text-gray-100');
            transCell.className = transCell.className
                .replace('text-gray-400', 'text-gray-600')
                .replace('dark:text-gray-500', 'dark:text-gray-400');
        }
    });
}

function deleteNote(id, btn) {
    if (!confirm('Удалить слово?')) return;

    fetch(`/api/notes/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    }).then(r => {
        if (r.ok) btn.closest('tr').remove();
    });
}
</script>
