<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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

        if (!$adminUserName || !$adminUserEmail || !$adminUserPassword) {
            throw new \Exception('Required environment variables XTREAM_ADMIN_USER_NAME, XTREAM_ADMIN_USER_EMAIL, and XTREAM_ADMIN_USER_PASSWORD are missing.');
        }

        // Create the user with the environment variable values
        User::create([
            'name' => $adminUserName,
            'email' => $adminUserEmail,
            'password' => Hash::make($adminUserPassword),
        ]);
    }
}
