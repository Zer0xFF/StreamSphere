<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class XtreamBrowserController extends Controller
{
    public function index()
    {
        $providers = Provider::query()->orderBy('name')->get(['id', 'name']);

        return view('xtream-browser', compact('providers'));
    }

    public function categories(Request $request): JsonResponse
    {
        $provider = $this->resolveProvider($request);
        $type = $request->query('type', 'live');

        $action = match ($type) {
            'movie' => 'get_vod_categories',
            'series' => 'get_series_categories',
            default => 'get_live_categories',
        };

        $categories = $this->requestPlayerApiCached($provider, $action);

        return response()->json(['data' => $categories]);
    }

    public function search(Request $request): JsonResponse
    {
        $provider = $this->resolveProvider($request);
        $query = trim((string) $request->query('q', ''));
        $type = $request->query('type', 'live');
        $categoryId = $request->query('category_id');

        if ($query === '') {
            return response()->json(['data' => []]);
        }
        $items = $this->buildSearchIndex($provider, $type);

        $normalizedQuery = mb_strtolower($query);

        $filtered = collect($items)
            ->when($categoryId, fn ($collection) => $collection->where('category_id', (string) $categoryId))
            ->filter(fn (array $item) => str_contains($item['name_normalized'], $normalizedQuery))
            ->values()
            ->map(fn (array $item) => [
                'name' => $item['name'],
                'category_id' => $item['category_id'],
                'stream_id' => $item['stream_id'],
            ]);

        return response()->json(['data' => $filtered]);
    }


    public function refreshCache(Request $request): JsonResponse
    {
        $provider = $this->resolveProvider($request);
        $type = $request->query('type', 'live');

        $actions = match ($type) {
            'movie' => ['get_vod_categories', 'get_vod_streams'],
            'series' => ['get_series_categories', 'get_series'],
            default => ['get_live_categories', 'get_live_streams'],
        };

        foreach ($actions as $action) {
            Cache::forget($this->cacheKey($provider, $action, []));
        }

        Cache::forget($this->searchIndexCacheKey($provider, $type));

        $this->requestPlayerApiCached($provider, $actions[0]);
        $this->buildSearchIndex($provider, $type);

        return response()->json(['status' => 'ok']);
    }

    private function resolveProvider(Request $request): Provider
    {
        return Provider::findOrFail((int) $request->query('provider_id'));
    }

    private function requestPlayerApiCached(Provider $provider, string $action, array $params = []): array
    {
        $cacheKey = $this->cacheKey($provider, $action, $params);

        $responseBody = Cache::remember($cacheKey, now()->addHour(), function () use ($provider, $action, $params) {
            $response = Http::get("{$provider->portal_url}/player_api.php", [
                'username' => $provider->username,
                'password' => $provider->password,
                'action' => $action,
                ...$params,
            ]);

            return $response->body();
        });

        if (is_array($responseBody)) {
            return $responseBody;
        }

        if (!is_string($responseBody)) {
            return [];
        }

        $decoded = json_decode($responseBody, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function buildSearchIndex(Provider $provider, string $type): array
    {
        return Cache::remember($this->searchIndexCacheKey($provider, $type), now()->addHour(), function () use ($provider, $type) {
            $action = match ($type) {
                'movie' => 'get_vod_streams',
                'series' => 'get_series',
                default => 'get_live_streams',
            };

            $items = $this->requestPlayerApiCached($provider, $action);

            return collect($items)->map(fn (array $item) => [
                'name' => (string) ($item['name'] ?? ''),
                'name_normalized' => mb_strtolower((string) ($item['name'] ?? '')),
                'category_id' => (string) ($item['category_id'] ?? ''),
                'stream_id' => $item['stream_id'] ?? ($item['series_id'] ?? null),
            ])->all();
        });
    }

    private function cacheKey(Provider $provider, string $action, array $params): string
    {
        ksort($params);

        return sprintf('xtream_browser:v2:%d:%s:%s', $provider->id, $action, md5(json_encode($params)));
    }

    private function searchIndexCacheKey(Provider $provider, string $type): string
    {
        return "xtream_browser:{$provider->id}:search_index:{$type}";
    }
}
