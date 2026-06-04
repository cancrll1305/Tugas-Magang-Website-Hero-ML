<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class HeroController extends Controller
{
    private function getHeroDataPath(): string
    {
        $workspaceRoot = dirname(base_path());

        $candidates = collect([
            $workspaceRoot . DIRECTORY_SEPARATOR . 'Scraper' . DIRECTORY_SEPARATOR . 'mlbb-heroes-detail.json',
            $workspaceRoot . DIRECTORY_SEPARATOR . 'mlbb-heroes-detail.json',
            storage_path('app/private/mlbb-heroes-detail.json'),
        ])->filter(fn ($path) => File::exists($path))
          ->map(fn ($path) => [
              'path' => $path,
              'modified' => File::lastModified($path),
          ])
          ->sortByDesc('modified')
          ->values();

        if ($candidates->isEmpty()) {
            abort(500, 'Hero data file not found.');
        }

        return $candidates->first()['path'];
    }

    private function getHeroes()
    {
        $json = File::get($this->getHeroDataPath());

        return collect(json_decode($json, true))
            ->filter(fn ($hero) => is_array($hero) && !empty($hero['name']))
            ->sortBy('name')
            ->values();
    }

    public function index(Request $request)
    {
        $allHeroes = $this->getHeroes();
        $heroes = $allHeroes;
        $currentRole = $request->query('role');
        $currentQuery = trim((string) $request->query('q', ''));

        $roles = $allHeroes->map(function ($hero) {
            return $hero['info']['Role'] ?? null;
        })
        ->filter()
        ->flatMap(function ($role) {
            return array_map('trim', explode('/', $role));
        })
        ->unique()
        ->sort()
        ->values();

        if ($currentRole) {
            $heroes = $heroes->filter(function ($hero) use ($currentRole) {
                $heroRole = $hero['info']['Role'] ?? '';
                $rolesArray = array_map('trim', explode('/', $heroRole));

                return in_array($currentRole, $rolesArray);
            });
        }

        if ($currentQuery !== '') {
            $query = mb_strtolower($currentQuery);

            $heroes = $heroes->filter(function ($hero) use ($query) {
                $haystacks = [
                    $hero['name'] ?? '',
                    $hero['title'] ?? '',
                    $hero['info']['Role'] ?? '',
                    $hero['info']['Specialty'] ?? '',
                    $hero['info']['Lane Recc.'] ?? '',
                    implode(' ', $hero['lead']['summary'] ?? []),
                ];

                foreach ($haystacks as $value) {
                    if (mb_stripos((string) $value, $query) !== false) {
                        return true;
                    }
                }

                return false;
            });
        }

        return view('Heroes.index', [
            'heroes' => $heroes->values(),
            'roles' => $roles,
            'currentRole' => $currentRole,
            'currentQuery' => $currentQuery,
            'heroCount' => $heroes->count(),
            'totalHeroCount' => $allHeroes->count(),
        ]);
    }

    public function show($name)
    {
        $heroes = $this->getHeroes();

        $name = urldecode($name);

        $hero = $heroes->firstWhere('name', $name);

        if (!$hero) {
            abort(404);
        }

        return view('Heroes.show', compact('hero'));
    }
}
