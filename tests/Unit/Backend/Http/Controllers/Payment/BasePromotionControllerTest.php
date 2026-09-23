<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Payment;

use App\Http\Controllers\Payment\BasePromotionController;
use Tests\TestCase;

class BasePromotionControllerTest extends TestCase
{
    private BasePromotionController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->controller = new BasePromotionController();
    }

    // =========================================================================
    // getCode()
    // =========================================================================

    public function test_get_code_returns_success_response(): void
    {
        $response = $this->controller->getCode();
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
    }

    public function test_get_code_returns_six_char_uppercase_string(): void
    {
        $response = $this->controller->getCode();
        $data = json_decode($response->getContent(), true);
        $code = $data['data'];
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{6}$/', $code);
    }

    // =========================================================================
    // findCost() – pure logic, no DB needed
    // =========================================================================

    public function test_find_cost_percentage_type_returns_discounted_price(): void
    {
        // type=1 → percentage discount: 100 - (100 * 10/100) = 90
        $result = $this->controller->findCost(1, 10, 100, 1);
        $this->assertSame(90.0, (float) $result);
    }

    public function test_find_cost_fixed_type_returns_price_minus_value(): void
    {
        // type=2 → fixed discount: 100 - 20 = 80
        $result = $this->controller->findCost(2, 20, 100, 1);
        $this->assertSame(80, $result);
    }

    public function test_find_cost_fixed_type_throws_when_value_exceeds_price(): void
    {
        $this->expectException(\Exception::class);
        $this->controller->findCost(2, 150, 100, 1);
    }

    public function test_find_cost_unknown_type_returns_null(): void
    {
        // type not 1 or 2 → falls through switch, returns null
        $result = $this->controller->findCost(99, 10, 100, 1);
        $this->assertNull($result);
    }

    // =========================================================================
    // getPromotionDetails() – empty code throws
    // =========================================================================

    public function test_get_promotion_details_throws_on_empty_code(): void
    {
        $this->expectException(\Exception::class);
        $this->controller->getPromotionDetails('');
    }

    public function test_get_promotion_details_throws_on_nonexistent_code(): void
    {
        $this->expectException(\Exception::class);
        $this->controller->getPromotionDetails('NONEXISTENT_XYZ_999');
    }
}
