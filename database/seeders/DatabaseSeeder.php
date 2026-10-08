<?php

namespace Database\Seeders;

use App\Models\WelcomeMessage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        WelcomeMessage::query()->firstOrCreate([
            'page' => 'home',
        ], [
            'content' => 'Hello from PostgreSQL!',
        ]);

        WelcomeMessage::query()->firstOrCreate([
            'page' => 'about',
        ], [
            'content' => 'Welcome to About!',
        ]);

        WelcomeMessage::query()->firstOrCreate([
            'page' => 'services',
        ], [
            'content' => 'This is the Services page.',
        ]);

        WelcomeMessage::query()->firstOrCreate([
            'page' => 'contact',
        ], [
            'content' => 'This is the Contact page.',
        ]);
    }
}
