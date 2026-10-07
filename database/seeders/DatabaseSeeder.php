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
        WelcomeMessage::query()->updateOrCreate([
            'id' => 1,
        ], [
            'content' => 'Hello from PostgreSQL!',
        ]);
    }
}
