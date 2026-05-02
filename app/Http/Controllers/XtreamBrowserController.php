<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $categories = $this->requestPlayerApi($provider, $action);

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

        $action = match ($type) {
            'movie' => 'get_vod_streams',
            'series' => 'get_series',
            default => 'get_live_streams',
        };

        $items = $this->requestPlayerApi($provider, $action, array_filter([
            'category_id' => $categoryId,
        ]));

        $normalizedQuery = mb_strtolower($query);

        $filtered = collect($items)
            ->filter(fn (array $item) => str_contains(mb_strtolower((string) ($item['name'] ?? '')), $normalizedQuery))
            ->values()
            ->map(function (array $item) {
                return [
                    'name' => $item['name'] ?? '',
                    'category_id' => $item['category_id'] ?? null,
                    'stream_id' => $item['stream_id'] ?? ($item['series_id'] ?? null),
                ];
            });

        return response()->json(['data' => $filtered]);
    }

    private function resolveProvider(Request $request): Provider
    {
        return Provider::findOrFail((int) $request->query('provider_id'));
    }

    private function requestPlayerApi(Provider $provider, string $action, array $params = []): array
    {
        $response = Http::get("{$provider->portal_url}/player_api.php", [
            'username' => $provider->username,
            'password' => $provider->password,
            'action' => $action,
            ...$params,
        ]);

        return $response->json() ?: [];
    }
}
