<x-layouts.app :title="$category->name">

    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('categories.index') }}" class="text-sm text-gray-500 hover:text-gray-900">← Манга</a>
            <h1 class="text-2xl font-semibold mt-1">{{ $category->name }}</h1>
            @if ($category->description)
                <p class="text-sm text-gray-500 mt-0.5">{{ $category->description }}</p>
            @endif
        </div>
        <a href="{{ route('categories.chapters.create', $category) }}"
           class="px-4 py-2 bg-gray-900 text-white text-sm rounded-lg hover:bg-gray-700 shrink-0">
            + Добавить главу
        </a>
    </div>

    @if ($chapters->isEmpty())
        <div class="text-center py-20 text-gray-400">
            <p>Глав пока нет.</p>
            <a href="{{ route('categories.chapters.create', $category) }}" class="mt-3 inline-block text-sm text-gray-600 underline">
                Добавить первую
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 gap-3">
            @foreach ($chapters as $chapter)
                <div class="flex items-center justify-between bg-white border border-gray-200 rounded-lg px-5 py-4"
                     data-chapter-id="{{ $chapter->id }}"
                     data-status="{{ $chapter->status }}">

                    <div>
                        <div class="font-medium">{{ $chapter->title }}</div>
                        <div class="text-sm text-gray-400 mt-0.5">
                            <span class="chapter-pages-count">{{ $chapter->pages_count }}</span> стр.
                        </div>
                    </div>

                    <div class="flex items-center gap-4 ml-4 shrink-0">

                        {{-- Status badge --}}
                        <span class="chapter-status-badge">
                            @if ($chapter->status === 'ready')
                                <span class="text-sm text-green-600 font-medium">Готово</span>
                            @elseif ($chapter->status === 'failed')
                                <span class="text-sm text-red-500">Ошибка</span>
                            @else
                                <span class="text-sm text-gray-400 flex items-center gap-1">
                                    <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                    Загрузка...
                                </span>
                            @endif
                        </span>

                        {{-- Read button (active only when ready) --}}
                        @if ($chapter->status === 'ready')
                            <a href="{{ route('chapters.read', $chapter) }}"
                               class="text-sm text-blue-600 hover:text-blue-800">Читать</a>
                        @endif

                        <form action="{{ route('categories.chapters.destroy', [$category, $chapter]) }}" method="POST"
                              onsubmit="return confirm('Удалить главу «{{ $chapter->title }}»?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-400 hover:text-red-600">Удалить</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</x-layouts.app>

<script>
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
                    badge.innerHTML = '<span class="text-sm text-green-600 font-medium">Готово</span>';
                    const actionsDiv = badge.parentElement;
                    if (!actionsDiv.querySelector('.read-link')) {
                        const link = document.createElement('a');
                        link.href = `/chapters/${id}/read`;
                        link.className = 'read-link text-sm text-blue-600 hover:text-blue-800';
                        link.textContent = 'Читать';
                        actionsDiv.insertBefore(link, badge.nextSibling);
                    }
                } else if (data.status === 'failed') {
                    badge.innerHTML = '<span class="text-sm text-red-500">Ошибка</span>';
                } else {
                    setTimeout(() => pollChapter(card), 2000);
                }
            })
            .catch(() => setTimeout(() => pollChapter(card), 5000));
    }

    pending.forEach(card => setTimeout(() => pollChapter(card), 2000));
})();
</script>
