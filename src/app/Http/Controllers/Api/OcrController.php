<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;

class OcrController extends Controller
{
    public function recognize(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|string',
        ]);

        $imageData = $request->input('image');

        // Strip data URI prefix (data:image/png;base64,...)
        if (str_contains($imageData, ',')) {
            $imageData = explode(',', $imageData)[1];
        }

        $decoded = base64_decode($imageData, strict: true);

        if (! $decoded) {
            return response()->json(['text' => '']);
        }

        $tmpPath = sys_get_temp_dir() . '/ocr_' . uniqid() . '.png';
        file_put_contents($tmpPath, $decoded);

        try {
            $text = (new TesseractOCR($tmpPath))
                ->lang('eng')
                ->run();

            $text = trim(preg_replace('/\s+/', ' ', $text));
        } catch (\Throwable) {
            $text = '';
        } finally {
            if (file_exists($tmpPath)) {
                unlink($tmpPath);
            }
        }

        return response()->json(['text' => $text]);
    }
}
