<?php

namespace App\Http\Controllers;

use App\Jobs\ScrapeChapterJob;
use App\Models\Category;
use App\Models\Chapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChapterController extends Controller
{
    public function create(Category $category): View
    {
        return view('chapters.create', compact('category'));
    }

    public function store(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'source_url' => 'nullable|url|max:2048',
            'image_urls' => 'nullable|string',
        ]);

        $chapter = $category->chapters()->create([
            'title'      => $data['title'],
            'source_url' => $data['source_url'] ?? null,
            'image_urls' => $data['image_urls'] ?? null,
            'status'     => 'pending',
            'sort_order' => $category->chapters()->max('sort_order') + 1,
        ]);

        ScrapeChapterJob::dispatch($chapter);

        return redirect()
            ->route('categories.show', $category)
            ->with('success', 'Глава добавлена, начинается загрузка изображений.');
    }

    public function destroy(Category $category, Chapter $chapter): RedirectResponse
    {
        $chapter->delete();

        return redirect()
            ->route('categories.show', $category)
            ->with('success', 'Глава удалена.');
    }

    public function read(Chapter $chapter): View
    {
        abort_if($chapter->status !== 'ready', 404);

        $pages = $chapter->pages()->with('notes')->orderBy('page_number')->get();

        $prevChapter = $chapter->category->chapters()
            ->where('sort_order', '<', $chapter->sort_order)
            ->where('status', 'ready')
            ->orderBy('sort_order', 'desc')
            ->first();

        $nextChapter = $chapter->category->chapters()
            ->where('sort_order', '>', $chapter->sort_order)
            ->where('status', 'ready')
            ->orderBy('sort_order')
            ->first();

        return view('chapters.read', compact('chapter', 'pages', 'prevChapter', 'nextChapter'));
    }

    public function sort(Category $category): View
    {
        $chapters = $category->chapters()->get();

        return view('chapters.sort', compact('category', 'chapters'));
    }

    public function reorder(Request $request, Category $category): JsonResponse
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];

        foreach ($ids as $index => $id) {
            $category->chapters()->where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['ok' => true]);
    }

    public function status(Chapter $chapter): JsonResponse
    {
        return response()->json([
            'status'      => $chapter->status,
            'pages_count' => $chapter->pages()->count(),
        ]);
    }
}
