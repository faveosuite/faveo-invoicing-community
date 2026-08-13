<?php

namespace Tests\Unit\Client\Product;

use App\Model\Payment\TaxClass;
use App\Model\Product\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class ProductEditTaxDisplayTest extends DBTestCase
{
    use DatabaseTransactions;

    public function test_edit_page_renders_when_a_tax_class_has_no_tax_rows(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
        $this->getLoggedInUser('admin');

        $product = Product::factory()->create();
        // The exact real-world condition that crashed both blade views:
        // a tax class with zero related `tax` rows.
        TaxClass::create(['name' => 'Standard-No-Rate']);

        $response = $this->get("/products/{$product->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Standard-No-Rate', false);
    }
}
