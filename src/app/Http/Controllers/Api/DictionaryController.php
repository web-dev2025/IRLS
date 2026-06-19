<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class DictionaryController extends Controller
{
    public function lookup(string $word): JsonResponse
    {
        $word = trim($word);

        if (empty($word)) {
            return response()->json(['translation' => null]);
        }

        $response = Http::timeout(10)
            ->get("https://api.dictionaryapi.dev/api/v2/entries/en/{$word}");

        if (! $response->successful()) {
            return response()->json(['translation' => null]);
        }

        $data = $response->json();

        // Extract first definition from first meaning
        $definition = data_get($data, '0.meanings.0.definitions.0.definition');
        $partOfSpeech = data_get($data, '0.meanings.0.partOfSpeech');
        $phonetic = data_get($data, '0.phonetic');

        $translation = null;
        if ($definition) {
            $translation = $partOfSpeech ? "[{$partOfSpeech}] {$definition}" : $definition;
        }

        return response()->json([
            'translation' => $translation,
            'phonetic'    => $phonetic,
        ]);
    }
}
