<?php

namespace App\Http\Middleware;

use App\Models\Device;
use App\Models\Provider;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProviderMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

     private function GetProvider($username)
     {
         $device = Device::where('username', $username)->first();
         if($device)
             return $device->provider;
        // some IPTV players get the username and password from player_api.php
        return Provider::where('username', $username)->first();
     }

    public function handle(Request $request, Closure $next): Response
    {
        $username = $request->query('username', 'default');
        $provider = $this->GetProvider($username);
        if($provider)
        {
            $request->merge(['provider' => $provider]);
            return $next($request);
        }
        return response()->json(['error' => 'Device not found'], 404);
    }
}
