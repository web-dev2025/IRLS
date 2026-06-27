<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DictionaryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::orderBy('name')->get();

        $learnedFilter = $request->input('learned'); // null = все, '1' = выучены, '0' = не выучены

        $notes = Note::with(['page.chapter.category'])
            ->when($request->category_id, fn ($q) =>
                $q->whereHas('page.chapter', fn ($q) =>
                    $q->where('category_id', $request->category_id)
                )
            )
            ->when($learnedFilter === '1', fn ($q) => $q->where('is_learned', true))
            ->when($learnedFilter === '0', fn ($q) => $q->where('is_learned', false))
            ->orderBy('phrase')
            ->paginate(50)
            ->withQueryString();

        $learnedCount = Note::when($request->category_id, fn ($q) =>
                $q->whereHas('page.chapter', fn ($q) =>
                    $q->where('category_id', $request->category_id)
                )
            )
            ->where('is_learned', true)
            ->count();

        $totalCount = Note::when($request->category_id, fn ($q) =>
                $q->whereHas('page.chapter', fn ($q) =>
                    $q->where('category_id', $request->category_id)
                )
            )
            ->count();

        return view('dictionary.index', compact('notes', 'categories', 'learnedCount', 'totalCount'));
    }

    public function export(Request $request): Response
    {
        $notes = Note::with(['page.chapter.category'])
            ->when($request->category_id, fn($q) =>
                $q->whereHas('page.chapter', fn($q) =>
                    $q->where('category_id', $request->category_id)
                )
            )
            ->orderBy('phrase')
            ->get();

        $lines = [];

        foreach ($notes as $note) {
            $tag        = $note->page?->chapter?->category?->slug ?? 'manga';
            $phrase     = $this->escapeCsv($note->phrase);
            $translation = $this->escapeCsv($note->translation ?? '');
            $comment    = $this->escapeCsv($note->comment ?? '');

            // Anki format: front TAB back TAB tags
            $lines[] = "{$phrase}\t{$translation}\t{$tag}";
        }

        $content = implode("\n", $lines);

        return response($content, 200, [
            'Content-Type'        => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="anki-export.txt"',
        ]);
    }

    private function escapeCsv(string $value): string
    {
        // Anki tab-separated: replace tabs and newlines inside values
        return str_replace(["\t", "\n", "\r"], [' ', ' ', ''], $value);
    }
}
