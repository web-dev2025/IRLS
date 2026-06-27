<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizController extends Controller
{
    private const QUIZ_SIZE = 10;
    private const MIN_POOL_SIZE = 4;
    private const DISTRACTOR_COUNT = 3;

    public function index(Request $request): View
    {
        $categories = Category::orderBy('name')->get();
        $categoryId = $request->integer('category_id') ?: null;
        $mode       = in_array($request->input('mode'), ['en-ru', 'ru-en'])
            ? $request->input('mode')
            : 'en-ru';

        $allNotes = Note::whereNotNull('translation')
            ->where('translation', '!=', '')
            ->whereNotNull('phrase')
            ->where('phrase', '!=', '')
            ->when($categoryId, fn ($q) => $q->whereHas('page.chapter',
                fn ($q) => $q->where('category_id', $categoryId)))
            ->select(['id', 'phrase', 'translation', 'comment'])
            ->get();

        if ($allNotes->count() < self::MIN_POOL_SIZE) {
            return view('quiz.index', [
                'categories'  => $categories,
                'categoryId'  => $categoryId,
                'mode'        => $mode,
                'tooFew'      => true,
                'tooFewCount' => $allNotes->count(),
                'questions'   => '[]',
            ]);
        }

        $quizNotes = $allNotes->shuffle()->take(min(self::QUIZ_SIZE, $allNotes->count()));

        if ($mode === 'ru-en') {
            $pool = $allNotes->pluck('phrase')->all();
            $questions = $quizNotes->map(function (Note $note) use ($pool) {
                $distractors = collect($pool)
                    ->filter(fn ($p) => $p !== $note->phrase)
                    ->shuffle()
                    ->take(self::DISTRACTOR_COUNT)
                    ->values()
                    ->all();

                $options = collect([$note->phrase, ...$distractors])->shuffle()->values()->all();

                return [
                    'question' => $note->translation,
                    'answer'   => $note->phrase,
                    'comment'  => $note->comment,
                    'options'  => $options,
                ];
            })->values()->all();
        } else {
            $pool = $allNotes->pluck('translation')->all();
            $questions = $quizNotes->map(function (Note $note) use ($pool) {
                $distractors = collect($pool)
                    ->filter(fn ($t) => $t !== $note->translation)
                    ->shuffle()
                    ->take(self::DISTRACTOR_COUNT)
                    ->values()
                    ->all();

                $options = collect([$note->translation, ...$distractors])->shuffle()->values()->all();

                return [
                    'question' => $note->phrase,
                    'answer'   => $note->translation,
                    'comment'  => $note->comment,
                    'options'  => $options,
                ];
            })->values()->all();
        }

        return view('quiz.index', [
            'categories'  => $categories,
            'categoryId'  => $categoryId,
            'mode'        => $mode,
            'tooFew'      => false,
            'tooFewCount' => 0,
            'questions'   => json_encode($questions, JSON_UNESCAPED_UNICODE),
        ]);
    }
}
