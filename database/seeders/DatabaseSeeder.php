<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@abfootball.test'],
            [
                'name' => 'Agency Admin',
                'password' => 'password',
                'role' => 'admin',
                'theme' => 'dark',
                'email_verified_at' => now(),
            ],
        );

        User::updateOrCreate(
            ['email' => 'editor@abfootball.test'],
            [
                'name' => 'Content Editor',
                'password' => 'password',
                'role' => 'editor',
                'theme' => 'light',
                'email_verified_at' => now(),
            ],
        );

        $this->call([
            SettingSeeder::class,
            DemoContentSeeder::class,
        ]);
    }
}
