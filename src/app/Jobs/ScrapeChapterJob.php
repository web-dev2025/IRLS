<?php

namespace App\Jobs;

use App\Models\Chapter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ScrapeChapterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct(public Chapter $chapter) {}

    public function handle(): void
    {
        $this->chapter->update(['status' => 'downloading']);

        try {
            $imageUrls = $this->resolveImageUrls();

            if (empty($imageUrls)) {
                $this->chapter->update(['status' => 'failed']);
                return;
            }

            $this->downloadImages($imageUrls);

            $saved = $this->chapter->pages()->count();
            $this->chapter->update(['status' => $saved > 0 ? 'ready' : 'failed']);
        } catch (\Throwable $e) {
            $this->chapter->update(['status' => 'failed']);
            throw $e;
        }
    }

    private function resolveImageUrls(): array
    {
        // Manual URLs take highest priority
        if ($this->chapter->image_urls) {
            return array_filter(
                array_map('trim', explode("\n", $this->chapter->image_urls)),
                fn($url) => filter_var($url, FILTER_VALIDATE_URL)
            );
        }

        // Pasted browser HTML (bypasses JS rendering and Cloudflare)
        if ($this->chapter->source_html) {
            return $this->parseHtml($this->chapter->source_html, $this->chapter->source_url);
        }

        if ($this->chapter->source_url) {
            return $this->scrapeFromPage($this->chapter->source_url);
        }

        return [];
    }

    private function scrapeFromPage(string $url): array
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        ])->timeout(30)->get($url);

        if (! $response->successful()) {
            return [];
        }

        return $this->parseHtml($response->body(), $url);
    }

    private function parseHtml(string $html, ?string $baseUrl = null): array
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $nodes = $xpath->query('//img');

        $imageUrls = [];
        foreach ($nodes as $node) {
            // Check common lazy-load attributes first, fall back to src
            $src = $node->getAttribute('data-src')
                ?: $node->getAttribute('data-lazy-src')
                ?: $node->getAttribute('data-original')
                ?: $node->getAttribute('src');

            if ($src && ! str_starts_with($src, 'data:') && ! $this->isLikelyIcon($src)) {
                $imageUrls[] = $baseUrl ? $this->makeAbsolute($src, $baseUrl) : $src;
            }
        }

        return array_values(array_unique($imageUrls));
    }

    private function isLikelyIcon(string $url): bool
    {
        $lower = strtolower($url);
        foreach (['favicon', 'logo', 'icon', 'avatar', 'banner', '/ads/', 'sprite', 'button', 'pixel'] as $keyword) {
            if (str_contains($lower, $keyword)) {
                return true;
            }
        }
        return false;
    }

    private function makeAbsolute(string $src, string $baseUrl): string
    {
        if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://')) {
            return $src;
        }
        $parsed = parse_url($baseUrl);
        $scheme = $parsed['scheme'] ?? 'https';
        $host   = $parsed['host'] ?? '';

        if (str_starts_with($src, '//')) {
            return $scheme . ':' . $src;
        }
        if (str_starts_with($src, '/')) {
            return $scheme . '://' . $host . $src;
        }
        return $scheme . '://' . $host . '/' . ltrim($src, '/');
    }

    private function downloadImages(array $imageUrls): void
    {
        $dir = "chapters/{$this->chapter->id}";
        Storage::disk('public')->makeDirectory($dir);

        foreach ($imageUrls as $index => $imageUrl) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Referer'    => $this->chapter->source_url ?? $imageUrl,
                ])->timeout(60)->get($imageUrl);

                if (! $response->successful()) {
                    continue;
                }

                $ext      = $this->guessExtension($imageUrl, $response->header('Content-Type'));
                $pageNum  = $index + 1;
                $filename = str_pad($pageNum, 3, '0', STR_PAD_LEFT) . '.' . $ext;
                $path     = $dir . '/' . $filename;

                Storage::disk('public')->put($path, $response->body());

                $this->chapter->pages()->create([
                    'page_number' => $pageNum,
                    'file_path'   => $path,
                ]);
            } catch (\Throwable) {
                continue;
            }
        }
    }

    private function guessExtension(string $url, ?string $contentType): string
    {
        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            return $ext === 'jpeg' ? 'jpg' : $ext;
        }
        return match (true) {
            str_contains((string) $contentType, 'jpeg') => 'jpg',
            str_contains((string) $contentType, 'png')  => 'png',
            str_contains((string) $contentType, 'gif')  => 'gif',
            str_contains((string) $contentType, 'webp') => 'webp',
            default                                      => 'jpg',
        };
    }
}
