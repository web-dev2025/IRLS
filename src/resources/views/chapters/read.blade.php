<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $chapter->title }} — {{ $chapter->category->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #111; }
        .page-container { position: relative; display: block; line-height: 0; }
        .page-container img { width: 100%; height: auto; display: block; }
        .page-container canvas {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            cursor: crosshair;
        }
    </style>
</head>
<body class="min-h-screen">

    {{-- Top bar --}}
    <div class="fixed top-0 left-0 right-0 z-50 bg-black/80 backdrop-blur-sm border-b border-white/10 px-4 h-12 flex items-center justify-between gap-2">
        <a href="{{ route('categories.show', $chapter->category) }}"
           class="text-sm text-gray-400 hover:text-white flex items-center gap-1 shrink-0">
            ← {{ $chapter->category->name }}
        </a>
        <div class="flex items-center gap-3 min-w-0">
            @if ($prevChapter)
                <a href="{{ route('chapters.read', $prevChapter) }}"
                   class="text-sm text-gray-500 hover:text-white shrink-0" title="{{ $prevChapter->title }}">‹ Пред.</a>
            @endif
            <span class="text-sm text-gray-400 truncate">{{ $chapter->title }}</span>
            @if ($nextChapter)
                <a href="{{ route('chapters.read', $nextChapter) }}"
                   class="text-sm text-gray-500 hover:text-white shrink-0" title="{{ $nextChapter->title }}">След. ›</a>
            @endif
        </div>
        <span class="text-sm text-gray-500 shrink-0">{{ $pages->count() }} стр.</span>
    </div>

    {{-- Pages --}}
    <div class="mx-auto pt-12" style="max-width: 800px;">
        @foreach ($pages as $page)
            <div class="page-container"
                 id="page-{{ $page->page_number }}"
                 data-page-id="{{ $page->id }}"
                 data-page-number="{{ $page->page_number }}"
                 style="scroll-margin-top: 48px;">
                <img src="{{ Storage::url($page->file_path) }}"
                     alt="Страница {{ $page->page_number }}"
                     loading="lazy">
                <canvas></canvas>
            </div>
        @endforeach

        {{-- Bottom navigation --}}
        <div class="flex items-center justify-between px-4 py-8">
            @if ($prevChapter)
                <a href="{{ route('chapters.read', $prevChapter) }}"
                   class="text-sm text-gray-400 hover:text-white flex items-center gap-2 border border-white/10 rounded-lg px-4 py-2 hover:border-white/30 transition-colors">
                    ← {{ $prevChapter->title }}
                </a>
            @else
                <span></span>
            @endif

            <a href="{{ route('categories.show', $chapter->category) }}"
               class="text-xs text-gray-600 hover:text-gray-400">
                {{ $chapter->category->name }}
            </a>

            @if ($nextChapter)
                <a href="{{ route('chapters.read', $nextChapter) }}"
                   class="text-sm text-gray-400 hover:text-white flex items-center gap-2 border border-white/10 rounded-lg px-4 py-2 hover:border-white/30 transition-colors">
                    {{ $nextChapter->title }} →
                </a>
            @else
                <span></span>
            @endif
        </div>
    </div>

    @php
        $notesJson = $pages->flatMap(fn($p) => $p->notes)->values()->toJson();
    @endphp
    <script>window.__EXISTING_NOTES__ = @json(json_decode($notesJson));</script>

    {{-- Note modal --}}
    <div id="note-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60" id="modal-backdrop"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <h2 class="font-semibold text-gray-900">Новая заметка</h2>

            <input type="hidden" id="note-page-id">
            <input type="hidden" id="note-x">
            <input type="hidden" id="note-y">
            <input type="hidden" id="note-width">
            <input type="hidden" id="note-height">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Слово или фраза</label>
                <input type="text" id="note-phrase" placeholder="Например: peculiar"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-500">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-sm font-medium text-gray-700">Перевод</label>
                    <button id="btn-translate" type="button"
                            class="text-xs text-blue-600 hover:text-blue-800">
                        Найти перевод
                    </button>
                </div>
                <input type="text" id="note-translation" placeholder="Перевод"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-500">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-sm font-medium text-gray-700">Комментарий</label>
                    <button id="btn-lookup" type="button"
                            class="text-xs text-blue-600 hover:text-blue-800">
                        Найти определение
                    </button>
                </div>
                <textarea id="note-comment" rows="2" placeholder="Необязательно"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-500"></textarea>
            </div>

            <div class="flex gap-3 pt-1">
                <button id="btn-save-note" type="button"
                        class="flex-1 py-2 bg-gray-900 text-white text-sm rounded-lg hover:bg-gray-700">
                    Сохранить
                </button>
                <button id="btn-cancel-note" type="button"
                        class="flex-1 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">
                    Отмена
                </button>
            </div>
        </div>
    </div>

</body>
</html>

<script>
(function () {
    const modal        = document.getElementById('note-modal');
    const backdrop     = document.getElementById('modal-backdrop');
    const btnCancel    = document.getElementById('btn-cancel-note');
    const btnSave      = document.getElementById('btn-save-note');
    const btnLookup    = document.getElementById('btn-lookup');
    const btnTranslate = document.getElementById('btn-translate');

    let activeCanvas = null;
    let drawing = false;
    let startX, startY;

    // ── Canvas setup ──────────────────────────────────────────────
    document.querySelectorAll('.page-container').forEach(container => {
        const img    = container.querySelector('img');
        const canvas = container.querySelector('canvas');
        const ctx    = canvas.getContext('2d');

        function resizeCanvas() {
            canvas.width  = img.naturalWidth  || img.offsetWidth;
            canvas.height = img.naturalHeight || img.offsetHeight;
        }

        img.addEventListener('load', resizeCanvas);
        if (img.complete) resizeCanvas();

        // Click → simple point note
        canvas.addEventListener('click', e => {
            if (drawing) return;
            const rect = canvas.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width)  * 100;
            const y = ((e.clientY - rect.top)  / rect.height) * 100;
            openModal(container, x, y, 0, 0);
        });

        // Drag → rectangle note
        canvas.addEventListener('mousedown', e => {
            drawing = true;
            activeCanvas = { container, canvas, ctx };
            const rect = canvas.getBoundingClientRect();
            startX = e.clientX - rect.left;
            startY = e.clientY - rect.top;
        });

        canvas.addEventListener('mousemove', e => {
            if (!drawing || activeCanvas?.canvas !== canvas) return;
            const rect = canvas.getBoundingClientRect();
            const curX = e.clientX - rect.left;
            const curY = e.clientY - rect.top;

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Scale to canvas internal resolution
            const scaleX = canvas.width  / rect.width;
            const scaleY = canvas.height / rect.height;

            ctx.strokeStyle = 'rgba(250, 200, 0, 0.9)';
            ctx.lineWidth   = 2 * scaleX;
            ctx.fillStyle   = 'rgba(250, 200, 0, 0.15)';

            const rx = startX * scaleX;
            const ry = startY * scaleY;
            const rw = (curX - startX) * scaleX;
            const rh = (curY - startY) * scaleY;

            ctx.fillRect(rx, ry, rw, rh);
            ctx.strokeRect(rx, ry, rw, rh);
        });

        canvas.addEventListener('mouseup', e => {
            if (!drawing || activeCanvas?.canvas !== canvas) return;
            drawing = false;

            const rect = canvas.getBoundingClientRect();
            const endX = e.clientX - rect.left;
            const endY = e.clientY - rect.top;
            const dist = Math.sqrt((endX - startX) ** 2 + (endY - startY) ** 2);

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (dist < 5) return; // too small — treat as click, click handler will fire

            const x = (Math.min(startX, endX) / rect.width)  * 100;
            const y = (Math.min(startY, endY) / rect.height) * 100;
            const w = (Math.abs(endX - startX) / rect.width)  * 100;
            const h = (Math.abs(endY - startY) / rect.height) * 100;

            // Crop selected area and run OCR before opening modal
            const base64 = cropToBase64(img, startX, endX, startY, endY, rect);
            openModal(container, x, y, w, h);
            runOcr(base64);
            activeCanvas = null;
        });
    });

    document.addEventListener('mouseup', () => { drawing = false; });

    // ── Modal ─────────────────────────────────────────────────────
    function openModal(container, x, y, w, h) {
        document.getElementById('note-page-id').value    = container.dataset.pageId;
        document.getElementById('note-x').value          = x.toFixed(4);
        document.getElementById('note-y').value          = y.toFixed(4);
        document.getElementById('note-width').value      = w.toFixed(4);
        document.getElementById('note-height').value     = h.toFixed(4);
        document.getElementById('note-phrase').value     = '';
        document.getElementById('note-translation').value = '';
        document.getElementById('note-comment').value    = '';
        modal.classList.remove('hidden');
        setTimeout(() => document.getElementById('note-phrase').focus(), 50);
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    backdrop.addEventListener('click', closeModal);
    btnCancel.addEventListener('click', closeModal);

    // ── Translation (MyMemory) ────────────────────────────────────
    btnTranslate.addEventListener('click', () => {
        const word = document.getElementById('note-phrase').value.trim();
        if (!word) return;

        btnTranslate.textContent = 'Загрузка...';
        btnTranslate.disabled    = true;

        fetch(`/api/translate/${encodeURIComponent(word)}`)
            .then(r => r.json())
            .then(data => {
                if (data.translation) {
                    document.getElementById('note-translation').value = data.translation;
                } else {
                    alert('Перевод не найден');
                }
            })
            .catch(() => alert('Ошибка запроса'))
            .finally(() => {
                btnTranslate.textContent = 'Найти перевод';
                btnTranslate.disabled    = false;
            });
    });

    // ── Dictionary lookup ─────────────────────────────────────────
    btnLookup.addEventListener('click', () => {
        const word = document.getElementById('note-phrase').value.trim();
        if (!word) return;

        btnLookup.textContent = 'Загрузка...';
        btnLookup.disabled    = true;

        fetch(`/api/dictionary/${encodeURIComponent(word)}`)
            .then(r => r.json())
            .then(data => {
                if (data.translation) {
                    document.getElementById('note-comment').value = data.translation;
                } else {
                    alert('Определение не найдено');
                }
            })
            .catch(() => alert('Ошибка запроса к словарю'))
            .finally(() => {
                btnLookup.textContent = 'Найти в словаре';
                btnLookup.disabled    = false;
            });
    });

    // ── Save note ─────────────────────────────────────────────────
    btnSave.addEventListener('click', () => {
        const phrase = document.getElementById('note-phrase').value.trim();
        if (!phrase) {
            document.getElementById('note-phrase').focus();
            return;
        }

        const payload = {
            page_id:     document.getElementById('note-page-id').value,
            phrase:      phrase,
            translation: document.getElementById('note-translation').value,
            comment:     document.getElementById('note-comment').value,
            x:           document.getElementById('note-x').value,
            y:           document.getElementById('note-y').value,
            width:       document.getElementById('note-width').value,
            height:      document.getElementById('note-height').value,
        };

        fetch('/api/notes', {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(payload),
        })
        .then(r => r.json())
        .then(note => {
            closeModal();
            renderNoteMark(note);
        })
        .catch(() => alert('Ошибка сохранения'));
    });

    // ── Render saved note marker on canvas ────────────────────────
    function renderNoteMark(note) {
        const container = document.querySelector(`[data-page-id="${note.page_id}"]`);
        if (!container) return;

        const isPoint = note.width === 0 && note.height === 0;

        const marker = document.createElement('div');
        marker.dataset.noteId = note.id;
        marker.title = `${note.phrase}${note.translation ? ' — ' + note.translation : ''}`;
        marker.title += `${note.comment ? ' = ' + note.comment : ''}`;
        marker.style.cssText = `
            position: absolute;
            left: ${note.x}%;
            top:  ${note.y}%;
            width:  ${isPoint ? '14px' : note.width + '%'};
            height: ${isPoint ? '14px' : note.height + '%'};
            border: 2px solid hsl(27, 100%, 49%);
            background: rgb(250, 246, 0);
            border-radius: ${isPoint ? '50%' : '3px'};
            transform: ${isPoint ? 'translate(-50%, -50%)' : 'none'};
            cursor: pointer;
            z-index: 10;
        `;
        marker.classList.add('animate-pulse');

        marker.addEventListener('click', e => {
            e.stopPropagation();
            showNotePopup(note, marker);
        });

        container.appendChild(marker);
    }

    // ── Note popup (view / delete) ────────────────────────────────
    function showNotePopup(note, markerEl) {
        document.querySelectorAll('.note-popup').forEach(p => p.remove());

        const popup = document.createElement('div');
        popup.className = 'note-popup';
        popup.style.cssText = `
            position: absolute;
            z-index: 10;
            background: white;
            border-radius: 10px;
            padding: 14px 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.25);
            max-width: 280px;
            min-width: 200px;
            font-size: 13px;
            color: #111;
        `;

        popup.innerHTML = `
            <div style="font-weight:600;margin-bottom:4px">${note.phrase}</div>
            ${note.translation ? `<div style="color:#555;margin-bottom:4px">${note.translation}</div>` : ''}
            ${note.comment     ? `<div style="color:#888;font-size:12px;margin-bottom:8px">${note.comment}</div>` : ''}
            <button class="delete-btn" style="font-size:12px;color:#e53e3e;cursor:pointer;background:none;border:none;padding:0">
                Удалить заметку
            </button>
        `;

        // Position near marker
        const rect = markerEl.getBoundingClientRect();
        popup.style.top  = (rect.bottom + 8 + window.scrollY) + 'px';
        popup.style.left = Math.min(rect.left, window.innerWidth - 300) + 'px';

        popup.querySelector('.delete-btn').addEventListener('click', () => {
            fetch(`/api/notes/${note.id}`, {
                method:  'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            }).then(() => {
                markerEl.remove();
                popup.remove();
            });
        });

        document.body.appendChild(popup);

        // Close popup on outside click
        setTimeout(() => {
            document.addEventListener('click', function handler() {
                popup.remove();
                document.removeEventListener('click', handler);
            }, { once: true });
        }, 10);
    }

    // ── OCR ───────────────────────────────────────────────────────
    function cropToBase64(img, startX, endX, startY, endY, rect) {
        const scaleX = img.naturalWidth  / rect.width;
        const scaleY = img.naturalHeight / rect.height;

        const cropX = Math.min(startX, endX) * scaleX;
        const cropY = Math.min(startY, endY) * scaleY;
        const cropW = Math.abs(endX - startX) * scaleX;
        const cropH = Math.abs(endY - startY) * scaleY;

        const tmp = document.createElement('canvas');
        tmp.width  = cropW;
        tmp.height = cropH;
        tmp.getContext('2d').drawImage(img, cropX, cropY, cropW, cropH, 0, 0, cropW, cropH);

        return tmp.toDataURL('image/png');
    }

    function runOcr(base64) {
        const phraseInput = document.getElementById('note-phrase');
        phraseInput.placeholder = 'Распознавание...';

        fetch('/api/ocr', {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ image: base64 }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.text) {
                phraseInput.value = data.text.toLowerCase();
            }
        })
        .finally(() => {
            phraseInput.placeholder = 'Например: peculiar';
        });
    }

    // ── Load existing notes ───────────────────────────────────────
    if (window.__EXISTING_NOTES__) {
        window.__EXISTING_NOTES__.forEach(note => renderNoteMark(note));
    }

    // ── Anchor scroll after images above target are loaded ────────
    if (location.hash) {
        const target = document.querySelector(location.hash);
        if (target) {
            const targetPageNum = parseInt(target.dataset.pageNumber);
            const pending = [];

            document.querySelectorAll('.page-container').forEach(container => {
                if (parseInt(container.dataset.pageNumber) <= targetPageNum) {
                    const img = container.querySelector('img');
                    if (img && !img.complete) {
                        img.removeAttribute('loading'); // отменяем lazy
                        pending.push(new Promise(resolve => {
                            img.addEventListener('load',  resolve, { once: true });
                            img.addEventListener('error', resolve, { once: true });
                        }));
                    }
                }
            });

            Promise.all(pending).then(() => {
                target.scrollIntoView({ behavior: 'instant' });
            });
        }
    }
})();
</script>
