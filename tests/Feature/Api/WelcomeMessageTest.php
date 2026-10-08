<?php

namespace Tests\Feature\Api;

use App\Models\WelcomeMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WelcomeMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_returns_only_public_fields_in_page_order(): void
    {
        $this->seed();

        $this->get('/api/v1/welcome-messages')->assertOk()->assertExactJson(['data' => [
            ['page' => 'about', 'content' => 'Welcome to About!'],
            ['page' => 'contact', 'content' => 'This is the Contact page.'],
            ['page' => 'home', 'content' => 'Hello from PostgreSQL!'],
            ['page' => 'services', 'content' => 'This is the Services page.'],
        ]]);
    }

    public function test_empty_list_returns_an_empty_data_array(): void
    {
        $this->get('/api/v1/welcome-messages')->assertOk()->assertExactJson(['data' => []]);
    }

    #[DataProvider('pages')]
    public function test_each_supported_page_can_be_created(string $page): void
    {
        $data = ['page' => $page, 'content' => 'New page content.'];

        $this->postJson('/api/v1/welcome-messages', $data + ['id' => 99])
            ->assertCreated()->assertExactJson(['data' => $data]);

        $this->assertDatabaseHas('welcome_messages', $data);
        $this->assertDatabaseMissing('welcome_messages', ['id' => 99]);
    }

    public static function pages(): array
    {
        return ['home' => ['home'], 'about' => ['about'], 'services' => ['services'], 'contact' => ['contact']];
    }

    public function test_detail_is_bound_by_page_instead_of_id(): void
    {
        WelcomeMessage::query()->create(['page' => 'home', 'content' => 'Stored content.']);

        $this->get('/api/v1/welcome-messages/home')->assertOk()
            ->assertExactJson(['data' => ['page' => 'home', 'content' => 'Stored content.']]);
    }

    #[DataProvider('updateMethods')]
    public function test_updates_content_without_changing_the_page(string $method): void
    {
        WelcomeMessage::query()->create(['page' => 'home', 'content' => 'Original.']);

        $this->json($method, '/api/v1/welcome-messages/home', ['page' => 'home', 'content' => 'Updated.'])
            ->assertOk()->assertExactJson(['data' => ['page' => 'home', 'content' => 'Updated.']]);

        $this->assertDatabaseHas('welcome_messages', ['page' => 'home', 'content' => 'Updated.']);
    }

    public static function updateMethods(): array
    {
        return ['PUT' => ['PUT'], 'PATCH' => ['PATCH']];
    }

    public function test_delete_returns_deleted_data_and_removes_only_the_target(): void
    {
        $this->seed();

        $this->deleteJson('/api/v1/welcome-messages/services')->assertOk()
            ->assertExactJson(['data' => ['page' => 'services', 'content' => 'This is the Services page.']]);

        $this->assertDatabaseMissing('welcome_messages', ['page' => 'services']);
        $this->assertDatabaseCount('welcome_messages', 3);
    }

    public function test_duplicate_page_returns_422_without_overwriting_content(): void
    {
        WelcomeMessage::query()->create(['page' => 'home', 'content' => 'Original.']);

        $this->postJson('/api/v1/welcome-messages', ['page' => 'home', 'content' => 'Replacement.'])
            ->assertUnprocessable()->assertJsonValidationErrors(['page' => 'The page has already been taken.']);

        $this->assertDatabaseHas('welcome_messages', ['page' => 'home', 'content' => 'Original.']);
        $this->assertDatabaseCount('welcome_messages', 1);
    }

    public function test_validation_is_json_even_without_an_accept_header(): void
    {
        $this->post('/api/v1/welcome-messages', [])->assertUnprocessable()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure(['message', 'errors' => ['page', 'content']]);

        $this->assertDatabaseCount('welcome_messages', 0);
    }

    #[DataProvider('invalidStoreData')]
    public function test_invalid_create_input_returns_422(array $data, string $field, string $message): void
    {
        $this->postJson('/api/v1/welcome-messages', $data)->assertUnprocessable()
            ->assertJsonValidationErrors([$field => $message]);

        $this->assertDatabaseCount('welcome_messages', 0);
    }

    public static function invalidStoreData(): array
    {
        return [
            'unknown page' => [['page' => 'news', 'content' => 'Valid.'], 'page', 'The selected page is invalid.'],
            'non-string page' => [['page' => ['home'], 'content' => 'Valid.'], 'page', 'The page field must be a string.'],
            'empty content' => [['page' => 'home', 'content' => '  '], 'content', 'The content field is required.'],
            'null content' => [['page' => 'home', 'content' => null], 'content', 'The content field is required.'],
            'non-string content' => [['page' => 'home', 'content' => ['text']], 'content', 'The content field must be a string.'],
            'long content' => [['page' => 'home', 'content' => str_repeat('a', 256)], 'content', 'The content field must not be greater than 255 characters.'],
        ];
    }

    #[DataProvider('invalidUpdateData')]
    public function test_invalid_update_returns_422_and_preserves_data(string $method, array $data, string $field): void
    {
        WelcomeMessage::query()->create(['page' => 'home', 'content' => 'Original.']);

        $this->json($method, '/api/v1/welcome-messages/home', $data)->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors'])->assertJsonValidationErrors($field);

        $this->assertDatabaseHas('welcome_messages', ['page' => 'home', 'content' => 'Original.']);
        $this->assertDatabaseCount('welcome_messages', 1);
    }

    public static function invalidUpdateData(): array
    {
        $cases = [];
        foreach (['PUT', 'PATCH'] as $method) {
            foreach ([
                'missing content' => [[], 'content'],
                'empty content' => [['content' => ' '], 'content'],
                'null content' => [['content' => null], 'content'],
                'non-string content' => [['content' => 123], 'content'],
                'long content' => [['content' => str_repeat('a', 256)], 'content'],
                'rename' => [['page' => 'about', 'content' => 'Changed.'], 'page'],
                'unknown page' => [['page' => 'news', 'content' => 'Changed.'], 'page'],
            ] as $name => [$data, $field]) {
                $cases[$method.' '.$name] = [$method, $data, $field];
            }
        }

        return $cases;
    }

    public function test_content_at_database_length_limit_can_be_updated_without_page_in_body(): void
    {
        WelcomeMessage::query()->create(['page' => 'home', 'content' => 'Original.']);
        $content = str_repeat('文', 255);

        $this->patchJson('/api/v1/welcome-messages/home', ['content' => $content])->assertOk()
            ->assertExactJson(['data' => ['page' => 'home', 'content' => $content]]);

        $this->assertDatabaseHas('welcome_messages', ['page' => 'home', 'content' => $content]);
    }

    #[DataProvider('missingRoutes')]
    public function test_missing_or_unsupported_pages_return_json_404(string $method, string $page): void
    {
        $this->json($method, '/api/v1/welcome-messages/'.$page, ['content' => 'Changed.'])
            ->assertNotFound()->assertHeader('Content-Type', 'application/json')->assertJsonStructure(['message']);

        $this->assertDatabaseCount('welcome_messages', 0);
    }

    public static function missingRoutes(): array
    {
        $cases = [];
        foreach (['GET', 'PUT', 'PATCH', 'DELETE'] as $method) {
            foreach (['home', 'news', '1'] as $page) {
                $cases[$method.' '.$page] = [$method, $page];
            }
        }

        return $cases;
    }

    public function test_seeding_is_repeatable_and_preserves_user_edits(): void
    {
        foreach (['home', 'about', 'services', 'contact'] as $page) {
            WelcomeMessage::query()->create(['page' => $page, 'content' => 'Edited '.$page]);
        }

        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('welcome_messages', 4);
        foreach (['home', 'about', 'services', 'contact'] as $page) {
            $this->assertDatabaseHas('welcome_messages', ['page' => $page, 'content' => 'Edited '.$page]);
        }
    }

    #[DataProvider('productionWrites')]
    public function test_nonlocal_environment_rejects_writes(string $environment, string $method, string $path): void
    {
        $this->seed();
        $this->app->instance('env', $environment);

        $this->json($method, '/api/v1/welcome-messages'.$path, ['page' => 'home', 'content' => 'Changed.'])
            ->assertForbidden()->assertJsonPath('message', 'API writes are only available in local and testing environments.');

        $this->assertDatabaseCount('welcome_messages', 4);
        $this->assertDatabaseHas('welcome_messages', ['page' => 'home', 'content' => 'Hello from PostgreSQL!']);
    }

    public static function productionWrites(): array
    {
        $cases = [];
        foreach (['production', 'staging'] as $environment) {
            foreach (['POST' => '', 'PUT' => '/home', 'PATCH' => '/home', 'DELETE' => '/home'] as $method => $path) {
                $cases[$environment.' '.$method] = [$environment, $method, $path];
            }
        }

        return $cases;
    }

    public function test_production_reads_remain_available(): void
    {
        $this->seed();
        $this->app->instance('env', 'production');

        $this->get('/api/v1/welcome-messages/home')->assertOk()
            ->assertExactJson(['data' => ['page' => 'home', 'content' => 'Hello from PostgreSQL!']]);
    }

    public function test_cors_allows_only_the_configured_frontend_origin(): void
    {
        config()->set('cors.allowed_origins', ['https://frontend.example.test']);

        $this->withHeaders([
            'Origin' => 'https://frontend.example.test',
            'Access-Control-Request-Method' => 'GET',
        ])->options('/api/v1/welcome-messages/home')
            ->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', 'https://frontend.example.test');

        $this->withHeaders([
            'Origin' => 'https://other.example.test',
            'Access-Control-Request-Method' => 'GET',
        ])->options('/api/v1/welcome-messages/home')
            ->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', 'https://frontend.example.test');
    }
}
