<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiOnlyApplicationTest extends TestCase
{
    public function test_laravel_does_not_serve_spa_pages(): void
    {
        foreach (['/', '/about', '/services', '/contact'] as $path) {
            $this->get($path)->assertNotFound();
        }
    }
}
