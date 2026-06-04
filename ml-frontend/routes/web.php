<?php

use App\Http\Controllers\HeroController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

Route::redirect('/', '/heroes');
Route::get('/heroes', [HeroController::class, 'index']);
Route::get('/heroes/{name}', [HeroController::class, 'show']);
Route::get('/hero-image', function (Request $request) {
    $url = $request->query('url');

    if (!$url) {
        abort(404);
    }

    if (Str::startsWith($url, ['/images/', 'images/'])) {
        $relativePath = ltrim($url, '/');
        $localPath = public_path($relativePath);

        if (!File::exists($localPath)) {
            abort(404);
        }

        return response()->file($localPath, [
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        abort(404);
    }

    $extension = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'img';
    $cacheDirectory = storage_path('app/public/hero-image-cache');
    $cacheFile = $cacheDirectory . DIRECTORY_SEPARATOR . sha1($url) . '.' . strtolower($extension);

    if (File::exists($cacheFile)) {
        return response()->file($cacheFile, [
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    File::ensureDirectoryExists($cacheDirectory);

    $image = '';
    $mime = null;

    try {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
            'Referer' => 'https://mobile-legends.fandom.com/',
        ])->withOptions([
            'verify' => false,
            'allow_redirects' => true,
        ])->timeout(20)->retry(2, 250)->get($url);

        if ($response->successful()) {
            $image = $response->body();
            $mime = $response->header('Content-Type');
        }
    } catch (\Throwable $e) {
        $image = '';
    }

    if ($image === '') {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", [
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'Accept: image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                    'Referer: https://mobile-legends.fandom.com/',
                ]),
                'timeout' => 20,
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $image = @file_get_contents($url, false, $context);

        if ($image === false || $image === '') {
            abort(404);
        }
    }

    File::put($cacheFile, $image);

    if (!$mime) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($image) ?: 'application/octet-stream';
    }

    return response($image)
        ->header('Content-Type', $mime)
        ->header('Cache-Control', 'public, max-age=604800');
});
