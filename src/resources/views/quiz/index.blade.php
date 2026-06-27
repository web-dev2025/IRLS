<x-layouts.app title="Тренировка">

<style>
/*
 * Только стили для элементов, которые JavaScript создаёт или
 * чей className полностью заменяет — Tailwind их не увидит при сборке.
 */

/* Фраза вопроса — JS заменяет className целиком */
.quiz-phrase { font-size: 2.5rem; font-weight: 700; color: #111827; line-height: 1.15; min-height: 2.8rem; }
.quiz-phrase.mode-ru { font-size: 1.6rem; font-weight: 500; color: #374151; }

/* Прогресс-блоки — JS создаёт и добавляет классы состояний */
.progress-block { flex: 1; height: 4px; border-radius: 9999px; background: #f3f4f6; transition: background-color .15s; }
.progress-block.current      { background: #f59e0b; }
.progress-block.done-correct { background: #22c55e; }
.progress-block.done-wrong   { background: #ef4444; }

/* Варианты ответов — JS создаёт кнопки и добавляет классы состояний */
.option-btn {
    display: flex; align-items: center; gap: 16px;
    width: 100%; text-align: left; padding: 13px 24px;
    background: none; border: none; border-top: 1px solid #f3f4f6;
    color: #374151; font-size: 15px; cursor: pointer;
}
.option-btn:disabled { cursor: default; }
.option-btn:hover:not(:disabled) { background: #f9fafb; }
.option-btn.correct { background: #f0fdf4; color: #15803d; }
.option-btn.wrong   { background: #fef2f2; color: #b91c1c; }

/* Цифра-подсказка — часть innerHTML кнопки */
.key-hint {
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 11px; font-family: monospace; color: #9ca3af;
    background: #f3f4f6; border: 1px solid #e5e7eb;
    border-radius: 4px; padding: 1px 6px; min-width: 20px; flex-shrink: 0;
}
.option-btn.correct .key-hint { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
.option-btn.wrong   .key-hint { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }

/* Фидбек — JS добавляет ok/fail */
.feedback-text.ok   { color: #16a34a; }
.feedback-text.fail { color: #dc2626; }

/* Кнопка подсказки и текст — JS создаёт */
.hint-btn {
    margin-top: 10px; font-size: 12px; color: #9ca3af;
    background: none; border: 1px solid #e5e7eb; border-radius: 4px;
    padding: 3px 10px; cursor: pointer;
}
.hint-btn:hover { color: #6b7280; border-color: #d1d5db; }
.hint-text { margin-top: 8px; font-size: 13px; color: #9ca3af; font-style: italic; }
</style>

<div class="w-full">

    @if ($tooFew)

        <div class="bg-white border border-gray-200 rounded-xl px-6 py-16 text-center">
            <div class="text-4xl mb-4">📚</div>
            <p class="text-sm text-gray-500 leading-relaxed">
                Для тренировки нужно минимум 4 слова с переводом.
                @if ($tooFewCount > 0)
                    В словаре сейчас {{ $tooFewCount }} {{ trans_choice('слово|слова|слов', $tooFewCount) }}.
                @else
                    Словарь пуст.
                @endif
            </p>
            <a href="{{ route('dictionary.index') }}"
               class="mt-5 inline-block text-sm text-gray-500 underline hover:text-gray-700">
                Перейти в словарь
            </a>
        </div>

    @else

        {{-- Управление --}}
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">

                {{-- Режим --}}
                <div class="flex gap-0.5 bg-gray-100 rounded-lg p-0.5 text-sm">
                    <button onclick="switchMode('en-ru')"
                            class="px-3 py-1.5 rounded-md font-medium cursor-pointer transition-colors
                                   {{ $mode === 'en-ru' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        EN → RU
                    </button>
                    <button onclick="switchMode('ru-en')"
                            class="px-3 py-1.5 rounded-md font-medium cursor-pointer transition-colors
                                   {{ $mode === 'ru-en' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        RU → EN
                    </button>
                </div>

                @if ($categories->count() > 1)
                    <select onchange="switchCategory(this.value)"
                            class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white text-gray-600 focus:outline-none">
                        <option value="">Все серии</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <span class="text-sm text-gray-400" id="counter-text"></span>
        </div>

        {{-- Карточка квиза --}}
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden" id="quiz-card">

            {{-- Прогресс --}}
            <div class="flex gap-1 px-5 pt-4" id="progress-bar"></div>

            {{-- Вопрос --}}
            <div class="px-6 pt-6 pb-5 border-b border-gray-100">
                <p class="text-xs uppercase tracking-wider text-gray-400 mb-3" id="quiz-label"></p>
                <div id="quiz-phrase" class="quiz-phrase"></div>
                <div id="hint-container"></div>
            </div>

            {{-- Варианты --}}
            <div id="quiz-options"></div>

            {{-- Подвал --}}
            <div class="flex items-center justify-between px-6 py-3 border-t border-gray-100 min-h-[50px]">
                <span class="text-sm feedback-text" id="feedback-text"></span>
                <button onclick="advance()" id="next-btn" style="display:none"
                        class="text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg px-4 py-1.5 cursor-pointer transition-colors">
                    Далее →
                </button>
            </div>
        </div>

        {{-- Экран результата --}}
        <div class="bg-white border border-gray-200 rounded-xl px-8 py-12 text-center" id="score-screen" style="display:none">
            <div class="text-6xl font-extrabold text-gray-900" id="score-number"></div>
            <div class="text-sm text-gray-400 mt-2" id="score-label"></div>
            <div id="missed-list" class="mt-8 text-left"></div>
            <button onclick="playAgain()"
                    class="mt-8 px-8 py-2.5 bg-gray-900 text-white text-sm font-semibold rounded-lg hover:bg-gray-700 cursor-pointer">
                Ещё раз
            </button>
        </div>

    @endif
</div>

<script>
const QUESTIONS   = {!! $questions !!};
const MODE        = @json($mode);
const CATEGORY_ID = @json($categoryId);

let current  = 0;
let answered = false;
let results  = [];
let missed   = [];

function esc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function renderProgress() {
    const bar = document.getElementById('progress-bar');
    bar.innerHTML = '';
    for (let i = 0; i < QUESTIONS.length; i++) {
        const b = document.createElement('div');
        b.className = 'progress-block';
        if      (results[i] === 'correct') b.classList.add('done-correct');
        else if (results[i] === 'wrong')   b.classList.add('done-wrong');
        else if (i === current)            b.classList.add('current');
        bar.appendChild(b);
    }
}

function renderQuestion() {
    const q = QUESTIONS[current];
    answered = false;

    document.getElementById('counter-text').textContent = `${current + 1} / ${QUESTIONS.length}`;
    document.getElementById('quiz-label').textContent   = MODE === 'en-ru' ? 'переведи слово' : 'найди английское слово';

    const phraseEl = document.getElementById('quiz-phrase');
    phraseEl.textContent = q.question;
    phraseEl.className = 'quiz-phrase' + (MODE === 'ru-en' ? ' mode-ru' : '');

    const hintEl = document.getElementById('hint-container');
    hintEl.innerHTML = '';
    if (q.comment) {
        const btn = document.createElement('button');
        btn.className = 'hint-btn';
        btn.textContent = '💡 Подсказка';
        btn.onclick = function () {
            const hint = document.createElement('div');
            hint.className = 'hint-text';
            hint.textContent = q.comment;
            hintEl.appendChild(hint);
            btn.remove();
        };
        hintEl.appendChild(btn);
    }

    const optionsEl = document.getElementById('quiz-options');
    optionsEl.innerHTML = '';
    q.options.forEach((opt, i) => {
        const btn = document.createElement('button');
        btn.className = 'option-btn';
        btn.dataset.correct = opt === q.answer ? '1' : '0';
        btn.innerHTML = `<span class="key-hint">${i + 1}</span> ${esc(opt)}`;
        btn.onclick = () => selectAnswer(btn);
        optionsEl.appendChild(btn);
    });

    const fb = document.getElementById('feedback-text');
    fb.textContent = '';
    fb.className = 'text-sm feedback-text';
    document.getElementById('next-btn').style.display = 'none';
}

function selectAnswer(btn) {
    if (answered) return;
    answered = true;
    const isCorrect = btn.dataset.correct === '1';

    document.querySelectorAll('.option-btn').forEach(b => {
        b.disabled = true;
        if (b.dataset.correct === '1') b.classList.add('correct');
        else if (b === btn)            b.classList.add('wrong');
    });

    const fb = document.getElementById('feedback-text');
    if (isCorrect) {
        fb.textContent = 'Верно!';
        fb.className = 'text-sm feedback-text ok';
        results[current] = 'correct';
    } else {
        fb.textContent = 'Неверно';
        fb.className = 'text-sm feedback-text fail';
        results[current] = 'wrong';
        missed.push({ question: QUESTIONS[current].question, answer: QUESTIONS[current].answer });
    }

    document.getElementById('next-btn').style.display = '';
    renderProgress();
}

function advance() {
    if (!answered) return;
    current++;
    if (current >= QUESTIONS.length) showScore();
    else { renderQuestion(); renderProgress(); }
}

function showScore() {
    document.getElementById('quiz-card').style.display  = 'none';
    document.getElementById('score-screen').style.display = '';

    const correct = results.filter(r => r === 'correct').length;
    document.getElementById('score-number').textContent = correct;
    document.getElementById('score-label').textContent  = correct === QUESTIONS.length
        ? 'Отлично! Все слова верно.'
        : `правильных из ${QUESTIONS.length}`;

    const missedEl = document.getElementById('missed-list');
    if (missed.length) {
        missedEl.innerHTML = '<p style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:10px">Ошибки</p>';
        missed.forEach(m => {
            missedEl.insertAdjacentHTML('beforeend',
                `<div style="display:flex;justify-content:space-between;align-items:baseline;padding:10px 0;border-bottom:1px solid #f3f4f6;font-size:13px;gap:12px">
                    <span style="font-weight:500;color:#111827">${esc(m.question)}</span>
                    <span style="color:#9ca3af;text-align:right">${esc(m.answer)}</span>
                </div>`
            );
        });
    }
}

function buildParams(ov) {
    const p   = new URLSearchParams();
    const cat = ov.categoryId !== undefined ? ov.categoryId : CATEGORY_ID;
    const mod = ov.mode       !== undefined ? ov.mode       : MODE;
    if (cat) p.set('category_id', cat);
    if (mod !== 'en-ru') p.set('mode', mod);
    return p.toString() ? '?' + p.toString() : '';
}

function playAgain()       { window.location.href = '/quiz' + buildParams({}); }
function switchMode(m)     { window.location.href = '/quiz' + buildParams({ mode: m }); }
function switchCategory(c) { window.location.href = '/quiz' + buildParams({ categoryId: c || null }); }

document.addEventListener('keydown', e => {
    if (['1','2','3','4'].includes(e.key)) {
        const btns = document.querySelectorAll('.option-btn:not([disabled])');
        if (btns[+e.key - 1]) btns[+e.key - 1].click();
    }
    if ((e.key === 'Enter' || e.key === 'ArrowRight') && answered) advance();
});

if (QUESTIONS.length > 0) { renderProgress(); renderQuestion(); }
</script>

</x-layouts.app>
