<x-layouts.app title="Словарь">

    <div class="flex items-start justify-between mb-6 gap-4">
        <h1 class="text-2xl font-semibold">Словарь</h1>

        <div class="flex items-center gap-3">
            {{-- Filter by category --}}
            <form method="GET" action="{{ route('dictionary.index') }}" class="flex items-center gap-2">
                <select name="category_id" onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-gray-500 bg-white">
                    <option value="">Все серии</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </form>

            {{-- Quiz --}}
            <a href="{{ route('quiz.index', request()->only('category_id')) }}"
               class="px-4 py-1.5 bg-white border border-gray-200 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                Тренировка
            </a>

            {{-- Export --}}
            <a href="{{ route('dictionary.export', request()->only('category_id')) }}"
               class="px-4 py-1.5 bg-gray-900 text-white text-sm rounded-lg hover:bg-gray-700">
                Экспорт для Anki
            </a>
        </div>
    </div>

    @if ($notes->isEmpty())
        <div class="text-center py-20 text-gray-400">
            <p>Заметок пока нет.</p>
            <p class="text-sm mt-2">Откройте главу и кликните на изображение, чтобы добавить слово.</p>
        </div>
    @else
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 w-1/4">Слово / фраза</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 w-1/4">Перевод</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Комментарий</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 w-1/5">Источник</th>
                        <th class="px-4 py-3 w-8"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($notes as $note)
                        <tr class="hover:bg-gray-50" data-note-id="{{ $note->id }}">
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ $note->phrase }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $note->translation ?: '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $note->comment ?: '' }}
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs">
                                @if ($note->page?->chapter)
                                    <a href="{{ route('chapters.read', $note->page->chapter) }}#page-{{ $note->page->page_number }}"
                                       class="hover:text-gray-700 hover:underline">
                                        {{ $note->page->chapter->category->name }}<br>
                                        {{ $note->page->chapter->title }}, стр. {{ $note->page->page_number }}
                                    </a>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button type="button"
                                        onclick="deleteNote({{ $note->id }}, this)"
                                        class="text-gray-300 hover:text-red-500 transition-colors cursor-pointer"
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

        <p class="mt-3 text-xs text-gray-400">
            Всего: {{ $notes->total() }} {{ trans_choice('слово|слова|слов', $notes->total()) }}
        </p>
    @endif

</x-layouts.app>

<script>
function deleteNote(id, btn) {
    if (!confirm('Удалить слово?')) return;

    fetch(`/api/notes/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    }).then(r => {
        if (r.ok) {
            btn.closest('tr').remove();
        }
    });
}
</script>
