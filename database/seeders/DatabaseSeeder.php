<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Check if the required environment variables are set
        $adminUserName = env('XTREAM_ADMIN_USER_NAME');
        $adminUserEmail = env('XTREAM_ADMIN_USER_EMAIL');
        $adminUserPassword = env('XTREAM_ADMIN_USER_PASSWORD');

        if(!$adminUserName || !$adminUserEmail || !$adminUserPassword)
        {
            throw new \Exception('Required environment variables XTREAM_ADMIN_USER_NAME, XTREAM_ADMIN_USER_EMAIL, and XTREAM_ADMIN_USER_PASSWORD are missing.');
        }

        if(!User::where('email', $adminUserEmail)->exists())
        {
            // Create the user if it does not exist
            User::create([
                'name' => env('XTREAM_ADMIN_USER_NAME'),
                'email' => $adminUserEmail,
                'password' => Hash::make(env('XTREAM_ADMIN_USER_PASSWORD')),
            ]);
        }
        else
        {
            // Optionally, update the existing user details
            User::where('email', $adminUserEmail)->update([
                'password' => Hash::make(env('XTREAM_ADMIN_USER_PASSWORD')),
            ]);
        }

    }
}
