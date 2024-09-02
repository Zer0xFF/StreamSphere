<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Provider;
use App\Models\CategoryAction;

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

    public function refreshFilters(Request $request)
    {
        Log::info('Request to /refreshfilters', $request->all());
        set_time_limit(300);

        $username = $request->query('username', 'default');
        $provider = $request->get('provider');

        $queryParams = $request->except(['username', 'password', 'action']);
        $queryParamsString = http_build_query($queryParams);

        $queryParams['username'] = $provider->username;
        $queryParams['password'] = $provider->password;

        function filterCategoriesPattern($categories, $pattern)
        {
            return array_filter($categories, function ($category) use ($pattern) {
                $categoryName = $category['category_name'];

                // Check for "18+" or "adult"
                if(preg_match('/18\+|adult/i', $categoryName))
                    return true;

                // Check for the provided pattern
                if(preg_match($pattern, $categoryName, $matches))
                {
                    $langCode = !empty($matches[1]) ? $matches[1] : $matches[2];
                    // Allow only AR, EN, UK, US, AU language codes
                    if(!in_array($langCode, ['AR', 'AU', 'EN', 'IE', 'UK', 'US']))
                    {
                        if($langCode === 'CA' && preg_match('/EN$/', $categoryName))
                            return false;
                        // Log::info('EXCLUDED:', $category);
                        return true;
                    }
                }
                // Log::info('INCLUDED:', $category);
                return false;
            });
        }

        foreach(['get_vod_categories', 'get_series_categories', 'get_live_categories'] as $action)
        {
            $cacheKey = "{$provider->name}_{$action}_{$provider->username}_{$queryParamsString}";
            $queryParams['action'] = $action;

            $jsonReturn = Cache::remember($cacheKey, 1 * 60 * 60, function () use ($provider, $queryParams) {
                $response = Http::get("{$provider->portal_url}/player_api.php", $queryParams);
                return $response->json();
            });

            switch($action)
            {
                case 'get_series_categories':
                {
                    $isArabic = fn($string) => preg_match('/[\p{Arabic}]/u', $string);
                    $isEnglish = fn($string) => preg_match('/[a-zA-Z]/', $string);

                    function isNonEnglishOrArabic($string)
                    {
                        if(stripos($string, "(SUB EN)") !== false)
                            return false;
                        // A list of non-English and non-Arabic keywords/languages to exclude
                        $excludedKeywords = [
                            'ESPAÑA', 'QUÉBEC', 'TURKISH', 'TURKSIH', 'GREECE', 'GREEK', 'ITALY', 
                            'FRANCE', 'ALBANIA', 'INDIA', 'PAKISTAN', 'NETHERLANDS', 'VIDEOLAND',
                            'GERMANY', 'POLSKA', 'PT/BR', 'BULGARIYA', 'RUSSIA', 
                            'PHILIPPINES', 'NORDIC', 'SVENSK', 'SVENSKA', 'DANSK', 'DANSKE', 
                            'NORSK', 'SUOMI'
                        ];
                    
                        foreach($excludedKeywords as $keyword)
                        {
                            if(stripos($string, $keyword) !== false)
                                return true;
                        }
                        return false;
                    }

                    $isAllowedCategory = fn($categoryName) => ($isArabic($categoryName) || $isEnglish($categoryName)) && !isNonEnglishOrArabic($categoryName);

                    $jsonReturn = array_filter($jsonReturn, function ($category) use($isAllowedCategory) {
                        $categoryName = $category['category_name'];

                        if(!$isAllowedCategory($categoryName))
                        {
                            return true;
                        }
                        return false;
                    });
                }
                break;
                case 'get_vod_categories':
                    // Pattern for "LANG_CODE - CAT_NAME" or "PT/BR - CAT_NAME"
                    $pattern = '/^([A-Z]{2})\ - |^(PT\/BR)\ - /';
                    $jsonReturn = filterCategoriesPattern($jsonReturn, $pattern);
                break;

                case 'get_live_categories':
                    // Pattern for "LANG_CODE| CAT_NAME"
                    $pattern = '/^([A-Z]{2})\| /';
                    $jsonReturn = filterCategoriesPattern($jsonReturn, $pattern);
                break;
                default:
                    ;
            }

            $action = str_replace(['get_', '_categories'], '', $action);
            foreach($jsonReturn as $category)
            {
                CategoryAction::firstOrCreate([
                    'provider_id' => $provider->id,
                    'category_name' => $category['category_name'],
                    'action' => $action,
                    'category_id' => $category['category_id'],
                ]);
            }
        }

        return response()->json(["DONE" => "0"]);
    }
}
