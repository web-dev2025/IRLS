<x-layouts.app :title="'Сортировка — ' . $category->name">

<style>
.chapter-row {
    display: flex; align-items: center; gap: 12px;
    background: #fff; border: 1px solid #e5e7eb;
    border-radius: 8px; padding: 14px 16px;
    cursor: grab; user-select: none;
    transition: box-shadow .15s, opacity .15s;
}
.chapter-row:active { cursor: grabbing; }
.chapter-row.dragging { opacity: .4; }
.chapter-row.drag-over { box-shadow: 0 0 0 2px #6b7280; }
.drag-icon { color: #d1d5db; font-size: 18px; line-height: 1; flex-shrink: 0; }
.chapter-row:hover .drag-icon { color: #9ca3af; }
</style>

<div class="max-w-xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('categories.show', $category) }}"
               class="text-sm text-gray-500 hover:text-gray-900">← {{ $category->name }}</a>
            <h1 class="text-xl font-semibold mt-1">Порядок глав</h1>
        </div>
        <span class="text-sm text-gray-400" id="save-status"></span>
    </div>

    @if ($chapters->isEmpty())
        <p class="text-gray-400 text-sm">Глав пока нет.</p>
    @else
        <div class="flex flex-col gap-2" id="sort-list">
            @foreach ($chapters as $chapter)
                <div class="chapter-row" data-chapter-id="{{ $chapter->id }}">
                    <span class="drag-icon">⠿</span>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-gray-900 truncate">{{ $chapter->title }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">
                            @if ($chapter->status === 'ready')
                                {{ $chapter->pages()->count() }} стр.
                            @elseif ($chapter->status === 'failed')
                                <span class="text-red-400">Ошибка загрузки</span>
                            @else
                                <span class="text-gray-400">Загружается...</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

<script>
(function () {
    const list    = document.getElementById('sort-list');
    const status  = document.getElementById('save-status');
    const reorderUrl = '{{ route('categories.chapters.reorder', $category) }}';
    const csrfToken  = document.querySelector('meta[name="csrf-token"]').content;

    if (!list) return;

    let dragged = null;

    list.querySelectorAll('.chapter-row').forEach(row => {
        row.addEventListener('dragstart', () => {
            dragged = row;
            setTimeout(() => row.classList.add('dragging'), 0);
        });

        row.addEventListener('dragend', () => {
            row.classList.remove('dragging');
            list.querySelectorAll('.chapter-row').forEach(r => r.classList.remove('drag-over'));
            saveOrder();
        });

        row.addEventListener('dragover', e => {
            e.preventDefault();
            if (!dragged || dragged === row) return;
            list.querySelectorAll('.chapter-row').forEach(r => r.classList.remove('drag-over'));
            row.classList.add('drag-over');
            const mid = row.getBoundingClientRect().top + row.offsetHeight / 2;
            list.insertBefore(dragged, e.clientY < mid ? row : row.nextSibling);
        });

        row.draggable = true;
    });

    function saveOrder() {
        const ids = [...list.querySelectorAll('[data-chapter-id]')]
            .map(r => parseInt(r.dataset.chapterId));

        status.textContent = 'Сохранение...';

        fetch(reorderUrl, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ ids }),
        })
        .then(r => r.json())
        .then(() => {
            status.textContent = 'Сохранено';
            setTimeout(() => { status.textContent = ''; }, 2000);
        })
        .catch(() => { status.textContent = 'Ошибка сохранения'; });
    }
})();
</script>

</x-layouts.app>
