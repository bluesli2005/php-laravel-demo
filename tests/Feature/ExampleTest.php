<?php

namespace Tests\Feature;

use App\Models\WelcomeMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_displays_the_database_message_in_vue(): void
    {
        WelcomeMessage::query()->create([
            'page' => 'home',
            'content' => 'Home database welcome.',
        ]);
        WelcomeMessage::query()->create([
            'page' => 'about',
            'content' => 'About database welcome.',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->where('welcomeMessage', 'Home database welcome.')
        );
    }

    public function test_the_about_page_displays_its_own_database_message_in_vue(): void
    {
        WelcomeMessage::query()->create([
            'page' => 'home',
            'content' => 'Home database welcome.',
        ]);
        WelcomeMessage::query()->create([
            'page' => 'about',
            'content' => 'About database welcome.',
        ]);

        $this->get('/about')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('About')
                ->where('welcomeMessage', 'About database welcome.')
            );
    }

    public function test_the_services_and_contact_pages_use_vue(): void
    {
        foreach (['/services' => 'Services', '/contact' => 'Contact'] as $path => $component) {
            $this->get($path)
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page->component($component));
        }
    }

    public function test_the_old_welcome_page_is_removed(): void
    {
        $this->get('/welcome')->assertNotFound();
    }
}
