<?php

namespace Tests\Unit\Admin\Product;

use App\Model\Product\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EditProductTest extends TestCase
{
    use DatabaseTransactions;

    /* group ProductController */
    public function test_productController_updateProductDetails()
    {
        $this->withoutMiddleware();
        $product = factory(Product::class)->create();
        $response = $this->call('PATCH', 'products/'.$product->id, [
        'name'           => 'helpdesk',
        'type'           => $product->type,
        'group'          => $product->group,
        'category'       => $product->category,
        'require_domain' => 1,
         'subscription'  => 1,

        ]);
        // dd($response);
        $response->assertSessionHas('success');
    }
}
