<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Traits;

use App\Traits\PaginationTotal;
use App\User;
use Illuminate\Http\Request;
use Tests\DBTestCase;

class PaginationTotalTest extends DBTestCase
{
    private function subject(): object
    {
        return new class {
            use PaginationTotal;

            public function total(string|object $model, Request $req, array $keys = []): ?int
            {
                return $this->cachedTotal($model, $req, $keys);
            }

            public function paginate(mixed $paginator, ?int $total): \Illuminate\Http\JsonResponse
            {
                return $this->paginateResponse($paginator, $total);
            }
        };
    }

    // =========================================================================
    // cachedTotal()
    // =========================================================================

    public function test_cached_total_returns_integer_when_no_filters(): void
    {
        $req    = Request::create('/', 'GET', []);
        $result = $this->subject()->total(User::class, $req, ['role']);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function test_cached_total_returns_null_when_search_is_active(): void
    {
        $req    = Request::create('/', 'GET', ['search-query' => 'John']);
        $result = $this->subject()->total(User::class, $req);

        // Search active → skip cache → return null (caller counts live)
        $this->assertNull($result);
    }

    public function test_cached_total_returns_null_when_filter_key_present(): void
    {
        $req    = Request::create('/', 'GET', ['role' => 'admin']);
        $result = $this->subject()->total(User::class, $req, ['role']);

        $this->assertNull($result);
    }

    public function test_cached_total_count_matches_actual_model_count(): void
    {
        // Create some users and verify cachedTotal reports the right count
        $this->getLoggedInUser('user');
        User::factory()->count(3)->create();

        $req    = Request::create('/', 'GET', []);
        $result = $this->subject()->total(User::class, $req);

        $this->assertSame(User::count(), $result);
    }

    // =========================================================================
    // paginateResponse()
    // =========================================================================

    public function test_paginate_response_returns_success_json(): void
    {
        $this->getLoggedInUser('user');
        $paginator = User::paginate(5);

        $response = $this->subject()->paginate($paginator, 42);

        $this->assertSame(200, $response->getStatusCode());
        $json = $response->getData(true);
        $this->assertTrue($json['success']);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('total', $json['data']);
        $this->assertSame(42, $json['data']['total']);
    }

    public function test_paginate_response_with_null_total_still_returns_200(): void
    {
        $this->getLoggedInUser('user');
        $paginator = User::paginate(5);

        $response = $this->subject()->paginate($paginator, null);

        $this->assertSame(200, $response->getStatusCode());
    }

    
}
