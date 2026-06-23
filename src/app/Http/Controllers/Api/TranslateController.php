<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class TranslateController extends Controller
{
    public function translate(string $text): JsonResponse
    {
        $text = trim($text);

        if (empty($text)) {
            return response()->json(['translation' => null]);
        }

        $response = Http::timeout(10)
            ->get('https://api.mymemory.translated.net/get', [
                'q'        => $text,
                'langpair' => 'en|ru',
            ]);

        if (! $response->successful()) {
            return response()->json(['translation' => null]);
        }

        $translation = data_get($response->json(), 'responseData.translatedText');

        return response()->json(['translation' => $translation ?: null]);
    }
}
