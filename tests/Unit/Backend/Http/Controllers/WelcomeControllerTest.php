<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers;

use Tests\DBTestCase;

class WelcomeControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
    }

    public function test_get_country_returns_200(): void
    {
        $response = $this->getJson('/get-country');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_get_country_accepts_search_query(): void
    {
        $response = $this->getJson('/get-country?search-query=United&limit=5');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    public function test_get_country_accepts_sort_parameters(): void
    {
        $response = $this->getJson('/get-country?sort-field=country_name&sort-order=desc');
        $response->assertStatus(200);
    }
}
