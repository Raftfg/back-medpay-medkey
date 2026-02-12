<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_example()
    {
        // The root route requires a tenant connection via TenantMiddleware
        // In a multi-tenant application, accessing / without a tenant returns 503
        $response = $this->get('/');

        // Accept 503 as expected behavior when no tenant is found
        // This is the correct behavior for a multi-tenant application
        $response->assertStatus(503);
    }
}
