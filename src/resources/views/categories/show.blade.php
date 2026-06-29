<x-layouts.app :title="$category->name">

    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('categories.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">← Манга</a>
            <h1 class="text-2xl font-semibold mt-1">{{ $category->name }}</h1>
            @if ($category->description)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $category->description }}</p>
            @endif
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('categories.chapters.sort', $category) }}"
               class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                Сортировка
            </a>
            <a href="{{ route('categories.chapters.create', $category) }}"
               class="px-4 py-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-sm rounded-lg hover:bg-gray-700 dark:hover:bg-gray-100">
                + Добавить главу
            </a>
        </div>
    </div>

    @if ($chapters->isEmpty())
        <div class="text-center py-20 text-gray-400 dark:text-gray-500">
            <p>Глав пока нет.</p>
            <a href="{{ route('categories.chapters.create', $category) }}" class="mt-3 inline-block text-sm text-gray-600 dark:text-gray-400 underline">
                Добавить первую
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 gap-3">
            @foreach ($chapters as $chapter)
                <div class="flex items-center justify-between bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-5 py-4 {{ $chapter->is_read ? 'opacity-60' : '' }}"
                     data-chapter-id="{{ $chapter->id }}"
                     data-status="{{ $chapter->status }}">

                    <div class="flex items-center gap-3 min-w-0">
                        {{-- Read toggle --}}
                        <button type="button"
                                onclick="toggleRead({{ $chapter->id }}, this)"
                                class="shrink-0 transition-colors cursor-pointer text-lg leading-none {{ $chapter->is_read ? 'text-green-500 dark:text-green-400 hover:text-gray-300 dark:hover:text-gray-600' : 'text-gray-200 dark:text-gray-700 hover:text-green-400 dark:hover:text-green-500' }}"
                                title="{{ $chapter->is_read ? 'Отметить как непрочитанное' : 'Отметить как прочитанное' }}">
                            ✓
                        </button>

                        <div>
                            <div class="font-medium">{{ $chapter->title }}</div>
                            <div class="text-sm text-gray-400 dark:text-gray-500 mt-0.5">
                                <span class="chapter-pages-count">{{ $chapter->pages_count }}</span> стр.
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 ml-4 shrink-0">
                        {{-- Status badge --}}
                        <span class="chapter-status-badge">
                            @if ($chapter->status === 'ready')
                                <span class="text-sm text-green-600 dark:text-green-400 font-medium">Готово</span>
                            @elseif ($chapter->status === 'failed')
                                <span class="text-sm text-red-500 dark:text-red-400">Ошибка</span>
                            @else
                                <span class="text-sm text-gray-400 dark:text-gray-500 flex items-center gap-1">
                                    <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                    Загрузка...
                                </span>
                            @endif
                        </span>

                        @if ($chapter->status === 'ready')
                            <a href="{{ route('chapters.read', $chapter) }}"
                               class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">Читать</a>
                        @endif

                        <form action="{{ route('categories.chapters.destroy', [$category, $chapter]) }}" method="POST"
                              onsubmit="return confirm('Удалить главу «{{ $chapter->title }}»?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-400 dark:text-red-500 hover:text-red-600 dark:hover:text-red-400">Удалить</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        @php $readCount = $chapters->where('is_read', true)->count(); @endphp
        @if ($readCount > 0)
            <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">
                <span class="text-green-500 dark:text-green-400">{{ $readCount }} прочитано</span>
                · {{ $chapters->count() - $readCount }} осталось
            </p>
        @endif
    @endif

</x-layouts.app>

<script>
function toggleRead(id, btn) {
    fetch(`/api/chapters/${id}/read`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    })
    .then(r => r.json())
    .then(data => {
        const row = btn.closest('[data-chapter-id]');
        const isRead = data.is_read;

        btn.className = 'shrink-0 transition-colors cursor-pointer text-lg leading-none ' +
            (isRead
                ? 'text-green-500 dark:text-green-400 hover:text-gray-300 dark:hover:text-gray-600'
                : 'text-gray-200 dark:text-gray-700 hover:text-green-400 dark:hover:text-green-500');
        btn.title = isRead ? 'Отметить как непрочитанное' : 'Отметить как прочитанное';

        row.classList.toggle('opacity-60', isRead);
    });
}

// Poll status for chapters that are still processing
(function () {
    const cards = document.querySelectorAll('[data-chapter-id]');
    const pending = Array.from(cards).filter(
        c => c.dataset.status === 'pending' || c.dataset.status === 'downloading'
    );

    if (!pending.length) return;

    function pollChapter(card) {
        const id = card.dataset.chapterId;

        fetch(`/chapters/${id}/status`)
            .then(r => r.json())
            .then(data => {
                card.dataset.status = data.status;

                const badge = card.querySelector('.chapter-status-badge');
                const pagesEl = card.querySelector('.chapter-pages-count');

                pagesEl.textContent = data.pages_count;

                if (data.status === 'ready') {
                    badge.innerHTML = '<span class="text-sm text-green-600 dark:text-green-400 font-medium">Готово</span>';
                    const actionsDiv = badge.parentElement;
                    if (!actionsDiv.querySelector('.read-link')) {
                        const link = document.createElement('a');
                        link.href = `/chapters/${id}/read`;
                        link.className = 'read-link text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300';
                        link.textContent = 'Читать';
                        actionsDiv.insertBefore(link, badge.nextSibling);
                    }
                } else if (data.status === 'failed') {
                    badge.innerHTML = '<span class="text-sm text-red-500 dark:text-red-400">Ошибка</span>';
                } else {
                    setTimeout(() => pollChapter(card), 2000);
                }
            })
            .catch(() => setTimeout(() => pollChapter(card), 5000));
    }

    pending.forEach(card => setTimeout(() => pollChapter(card), 2000));
})();
</script>
