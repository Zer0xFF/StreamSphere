<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Provider;
use App\Models\CategoryAction;
use App\Models\CategoryFilter;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XtreamCodeController extends Controller
{
    private function reformatJson($filteredCategories)
    {
        $id = 0;
        $reformattedJson = [];

        foreach($filteredCategories as $category)
            $reformattedJson[$id++] = $category;

        return $reformattedJson;
    }

    public function PlayerApi(Request $request)
    {
        Log::info('Request to /player_api.php:', $request->all());
        set_time_limit(60);
        ini_set('memory_limit', '512M');

        $action = $request->query('action', '');
        $username = $request->query('username', 'default');

        $provider = $request->get('provider');

        $queryParams = $request->except(['username', 'password', 'action']);
        $queryParamsString = http_build_query($queryParams);
        $cacheKey = "{$provider->name}_{$action}_{$provider->username}_{$queryParamsString}";

        $queryParams['username'] = $provider->username;
        $queryParams['password'] = $provider->password;
        $queryParams['action'] = $action;

        $jsonReturn = Cache::remember($cacheKey, 1 * 60 * 60, function () use ($provider, $queryParams) {
            $response = Http::get("{$provider->portal_url}/player_api.php", $queryParams);
            return $response->json();
        });

        // TODO
        // if($noFilter)
        //     return response()->json($jsonReturn);

        if(!empty($action) && !preg_match('/_info$/', $action))
        {
            $categoriesAction = CategoryAction::where('action', str_replace(['get_', '_streams', '_categories'], '', $action))
                ->where('provider_id', $provider->id)
                ->pluck('category_id')
                ->toArray();

            if($categoriesAction)
            {
                $jsonReturn = array_filter($jsonReturn, function ($item) use ($categoriesAction) {
                    return !in_array($item['category_id'], $categoriesAction);
                });
                $jsonReturn = $this->reformatJson($jsonReturn);
            }
        }

        return response()->json($jsonReturn);
    }

    public function clearCache(Request $request)
    {
        Cache::flush();
        return ["CACHE" => "CLEARED"];
    }

    public function getEPG(Request $request)
    {
        $provider = $request->get('provider');
        $cacheKey = "{$provider->name}_xmltv";

        ini_set('memory_limit', '512M');

        $body = Cache::remember($cacheKey, 1 * 60 * 60, function () use ($provider) {
            $response = Http::get("{$provider->portal_url}/xmltv.php", [
                'username' => $provider->username,
                'password' => $provider->password,
            ]);

            return $response->body();
        });

        return $body;
    }

    public function redirectToExternal($username, $password, $filename)
    {
        $provider = $request->get('provider');
        $targetUrl = "{$provider->portal_url}/live/{$provider->username}/{$provider->password}/{$filename}";

        return redirect()->away($targetUrl);
    }

    public function refreshCategories(Request $request)
    {
        Log::info('Request to /refreshcats', $request->all());
        set_time_limit(300);

        $username = $request->query('username', 'default');
        $provider = $request->get('provider');

        $queryParams = $request->except(['username', 'password', 'action']);
        $queryParamsString = http_build_query($queryParams);

        $queryParams['username'] = $provider->username;
        $queryParams['password'] = $provider->password;

        foreach(['get_vod_categories', 'get_series_categories', 'get_live_categories'] as $action)
        {
            $cacheKey = "{$provider->name}_{$action}_{$provider->username}_{$queryParamsString}";
            $queryParams['action'] = $action;

            $jsonReturn = Cache::remember($cacheKey, 1 * 60 * 60, function () use ($provider, $queryParams) {
                $response = Http::get("{$provider->portal_url}/player_api.php", $queryParams);
                return $response->json();
            });

            $action = str_replace(['get_', '_categories'], '', $action);
            foreach($jsonReturn as $category)
            {
                $categoryAction = CategoryAction::withoutGlobalScope('hidden')->updateOrCreate(
                    [
                        'provider_id' => $provider->id,
                        'category_id' => $category['category_id'],
                        'action' => $action,
                    ],
                    [
                        'category_name' => $category['category_name'],
                    ]
                );
                $categoryAction->save();
            }
        }

        return response()->json(["DONE" => "0"]);
    }
}
