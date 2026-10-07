<?php

namespace Tests\Feature;

use App\Models\WelcomeMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        WelcomeMessage::query()->create([
            'content' => 'Database connection works.',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Database connection works.');
    }
}
