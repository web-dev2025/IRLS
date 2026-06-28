<x-layouts.app title="IRLS — учи английский через мангу">

    <div class="w-full">

        <div class="mb-10">
            <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100 mb-3">
                Учи английский через мангу
            </h1>
            <p class="text-gray-500 dark:text-gray-400 text-lg leading-relaxed">
                Читай мангу, сохраняй незнакомые слова прямо на странице и экспортируй словарь в Anki.
            </p>
        </div>

        {{-- Steps --}}
        <div class="space-y-4 mb-16">
            @foreach ([
                ['01', 'Добавь серию и главу', 'Вставь URL страницы манги — система скачает все изображения автоматически. Либо укажи ссылки на картинки вручную.'],
                ['02', 'Читай в вертикальной читалке', 'Все страницы главы отображаются вертикально, как в браузерных ридерах манги.'],
                ['03', 'Сохраняй слова прямо на странице', 'Кликни на любое место или выдели область — откроется форма заметки. Укажи перевод и комментарий. OCR распознаёт текст автоматически при выделении.'],
                ['04', 'Экспортируй в Anki', 'Все сохранённые слова собираются в словаре. Одним кликом выгружай в формате для импорта в Anki.'],
                ['05', 'Проверяй себя', 'Открой тренировку и выбери правильный перевод из четырёх вариантов. Режимы EN→RU и RU→EN, фильтр по серии, итоговый счёт после каждого раунда.'],
            ] as [$num, $title, $desc])
                <div class="flex gap-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-5 py-4">
                    <span class="text-2xl font-bold text-gray-100 dark:text-gray-800 shrink-0 w-8">{{ $num }}</span>
                    <div>
                        <div class="font-medium text-gray-900 dark:text-gray-100 mb-0.5">{{ $title }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $desc }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- CTA --}}
        <div class="flex gap-3">
            <a href="{{ route('categories.index') }}"
               class="px-6 py-2.5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-sm rounded-lg hover:bg-gray-700 dark:hover:bg-gray-100">
                Перейти к манге
            </a>
            <a href="{{ route('dictionary.index') }}"
               class="px-6 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                Открыть словарь
            </a>
        </div>

    </div>

</x-layouts.app>
