<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Models;

use App\Model\Product\Addon;
use App\Model\Product\ProductBundle;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\DBTestCase;

class ProductModelsTest extends DBTestCase
{
    private bool $addonsTableCreated = false;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        // addons and product_addon_relations do not exist in the live schema yet;
        // create them for this test run and drop them afterwards.
        if (! Schema::hasTable('addons')) {
            Schema::create('addons', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('product')->nullable();
                $table->string('subscription')->nullable();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('regular_price')->nullable();
                $table->string('selling_price')->nullable();
                $table->integer('tax_addon')->default(0);
                $table->integer('show_on_order')->default(0);
                $table->integer('auto_active_payment')->default(0);
                $table->integer('suspend_parent')->default(0);
                $table->timestamps();
            });
            $this->addonsTableCreated = true;
        }

        if (! Schema::hasTable('product_addon_relations')) {
            Schema::create('product_addon_relations', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('addon_id');
                $table->unsignedInteger('product_id');
                $table->timestamps();
            });
        }
    }

    protected function tearDown(): void
    {
        if ($this->addonsTableCreated) {
            Schema::dropIfExists('product_addon_relations');
            Schema::dropIfExists('addons');
        }

        parent::tearDown();
    }

    // =========================================================================
    // Addon
    // =========================================================================

    public function test_addon_can_be_created_and_persisted(): void
    {
        $addon = Addon::create([
            'name' => 'Test Addon',
            'product' => '1',
            'subscription' => '0',
        ]);

        $this->assertNotNull($addon->id);
        $this->assertDatabaseHas('addons', ['id' => $addon->id, 'name' => 'Test Addon']);
    }

    public function test_addon_relation_returns_has_many(): void
    {
        $addon = Addon::create([
            'name' => 'Relation Addon',
            'product' => '0',
        ]);

        $relation = $addon->relation();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    public function test_addon_delete_removes_record_from_database(): void
    {
        $addon = Addon::create([
            'name' => 'Delete Addon',
            'product' => '0',
        ]);

        $id = $addon->id;
        $addon->delete();

        $this->assertDatabaseMissing('addons', ['id' => $id]);
    }

    // =========================================================================
    // ProductBundle
    // =========================================================================

    public function test_product_bundle_can_be_created_and_persisted(): void
    {
        $bundle = ProductBundle::create([
            'name' => 'Test Bundle',
            'valid_from' => now(),
            'valid_till' => now()->addYear(),
            'uses' => 0,
            'maximum_uses' => 100,
            'allow-promotion' => 0,
            'show' => 1,
        ]);

        $this->assertNotNull($bundle->id);
        $this->assertDatabaseHas('product_bundles', ['id' => $bundle->id, 'name' => 'Test Bundle']);
    }

    public function test_product_bundle_relation_returns_has_many(): void
    {
        $bundle = ProductBundle::create([
            'name' => 'Relation Bundle',
            'valid_from' => now(),
            'valid_till' => now()->addYear(),
            'uses' => 0,
            'maximum_uses' => 10,
            'allow-promotion' => 0,
            'show' => 1,
        ]);

        $relation = $bundle->relation();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    public function test_product_bundle_delete_removes_record_from_database(): void
    {
        $bundle = ProductBundle::create([
            'name' => 'Delete Bundle',
            'valid_from' => now(),
            'valid_till' => now()->addYear(),
            'uses' => 0,
            'maximum_uses' => 10,
            'allow-promotion' => 0,
            'show' => 1,
        ]);

        $id = $bundle->id;
        $bundle->delete();

        $this->assertDatabaseMissing('product_bundles', ['id' => $id]);
    }
}
