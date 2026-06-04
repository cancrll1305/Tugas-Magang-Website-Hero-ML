<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DownloadHeroImages extends Command
{
    protected $signature = 'heroes:download-images';
    protected $description = 'Download hero images from JSON and save locally';

    public function handle()
    {
        $jsonPath = 'mlbb-heroes.json';

        if (!Storage::exists($jsonPath)) {
            $this->error('❌ JSON file not found!');
            return;
        }

        $heroes = json_decode(Storage::get($jsonPath), true);

        if (!$heroes) {
            $this->error('❌ Invalid JSON format!');
            return;
        }

        $saveDirectory = public_path('images/heroes');

        if (!file_exists($saveDirectory)) {
            mkdir($saveDirectory, 0755, true);
        }

        foreach ($heroes as &$hero) {

            if (!isset($hero['icon']) || !Str::startsWith($hero['icon'], 'http')) {
                continue;
            }

            $this->info("⬇ Downloading: {$hero['name']}");

            try {

                // 🔥 HAPUS /revision/... AGAR TIDAK ERROR
                $cleanUrl = preg_replace('/\/revision\/.*$/', '', $hero['icon']);

                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'Accept' => 'image/webp,image/apng,image/*,*/*;q=0.8',
                    'Referer' => 'https://www.google.com/'
                ])->timeout(60)->get($cleanUrl);

                if (!$response->successful()) {
                    $this->warn("⚠ Failed: {$hero['name']}");
                    continue;
                }

                // Detect extension dari Content-Type
                $contentType = $response->header('Content-Type');
                $extension = 'png';

                if ($contentType) {
                    $parts = explode('/', $contentType);
                    if (isset($parts[1])) {
                        $extension = explode(';', $parts[1])[0];
                    }
                }

                $filename = Str::slug($hero['name']) . '.' . $extension;
                $fullPath = $saveDirectory . '/' . $filename;

                // Skip kalau sudah ada
                if (file_exists($fullPath)) {
                    $this->line("✔ Already exists: {$filename}");
                } else {
                    file_put_contents($fullPath, $response->body());
                    $this->info("✔ Saved: {$filename}");
                }

                // Update JSON path
                $hero['icon'] = '/images/heroes/' . $filename;

            } catch (\Exception $e) {
                $this->error("❌ Error downloading {$hero['name']}");
            }
        }

        // Simpan JSON yang sudah diupdate
        Storage::put($jsonPath, json_encode($heroes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info('🎉 All images processed successfully!');
    }
}