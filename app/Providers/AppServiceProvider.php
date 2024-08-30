<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try
        {
            if(User::count() == 0)
            {
                User::create([
                    'name' => env('XTREAM_ADMIN_USER_NAME'),
                    'email' => env('XTREAM_ADMIN_USER_EMAIL'),
                    'password' => Hash::make(env('XTREAM_ADMIN_USER_PASSWORD')),
                ]);
            }
        }
        catch (\Exception $e)
        {
        }
    }
}
