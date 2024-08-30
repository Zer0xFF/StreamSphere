<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StringTestController extends Controller
{
    private function generateRandomCategoryName() {
        $langCodes = ['AR', 'EN', 'UK', 'US', 'AU', 'FR', 'ES', 'DE', 'IT', 'JP', 'RU', 'CA'];
        $randomLangCode = $langCodes[array_rand($langCodes)];
        $randomCategory = strtoupper(bin2hex(random_bytes(3))); // Random hex string as category name
        $suffix = rand(0, 1) ? ' EN' : '';

        $formats = [
            "$randomLangCode|$randomCategory$suffix",
            "$randomLangCode|$randomCategory",
            "$randomCategory$suffix",
            "$randomCategory"
        ];

        return $formats[array_rand($formats)];
    }

    private function generateTestCategories($count = 1000) {
        $categories = [];
        for ($i = 0; $i < $count; $i++) {
            $categories[] = $this->generateRandomCategoryName();
        }
        return $categories;
    }

    private function filterCategoryUsingRegex($categoryName)
    {
        if (preg_match('/18\+|adult/i', $categoryName)) {
            return false;
        }

        if (preg_match('/^([A-Z]{2})\|/', $categoryName, $matches)) {
            $langCode = $matches[1];
            $allowedLangCodes = ['AR', 'EN', 'UK', 'US', 'AU'];

            if ($langCode === 'CA') {
                return preg_match('/EN$/', $categoryName);
            }

            return in_array($langCode, $allowedLangCodes);
        }

        return true;
    }

    private function filterCategoryUsingStringChecks($categoryName)
    {
        if (stripos($categoryName, '18+') !== false || stripos($categoryName, 'adult') !== false) {
            return false;
        }

        if (strpos($categoryName, '|') !== false) {
            list($langCode, $catName) = explode('|', $categoryName, 2);
            $langCode = strtoupper($langCode);
            $allowedLangCodes = ['AR', 'EN', 'UK', 'US', 'AU'];

            if ($langCode === 'CA') {
                return substr($catName, -2) === 'EN';
            }

            return in_array($langCode, $allowedLangCodes);
        }

        return true;
    }

    public function benchmark(Request $request)
    {
        // Generate 1000 random categories
        $categories = $this->generateTestCategories(100000);

        // Benchmark Regex Approach
        $startRegex = microtime(true);
        $regexResults = array_map([$this, 'filterCategoryUsingRegex'], $categories);
        $endRegex = microtime(true);
        $regexTime = $endRegex - $startRegex;

        // Benchmark String Checks Approach
        $startString = microtime(true);
        $stringCheckResults = array_map([$this, 'filterCategoryUsingStringChecks'], $categories);
        $endString = microtime(true);
        $stringCheckTime = $endString - $startString;

        // Count and compare the results
        $regexKept = count(array_filter($regexResults));
        $stringCheckKept = count(array_filter($stringCheckResults));

        // Return the results as a JSON response
        return response()->json([
            'regex_kept' => $regexKept,
            'string_check_kept' => $stringCheckKept,
            'regex_time' => number_format($regexTime, 6) . ' seconds',
            'string_check_time' => number_format($stringCheckTime, 6) . ' seconds',
        ]);
    }
}
