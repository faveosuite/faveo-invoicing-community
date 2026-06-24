<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Models\Product;

use App\Model\Product\CloudProducts;
use App\Model\Product\Product;
use App\Model\Product\ProductCategory;
use App\Model\Product\ProductGroup;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use App\Model\Product\Type;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class ProductModelsCoverageTest extends TestCase
{
    // =========================================================================
    // ProductCategory
    // =========================================================================

    public function test_product_category_table_is_product_categories(): void
    {
        $this->assertSame('product_categories', (new ProductCategory())->getTable());
    }

    public function test_product_category_fillable_contains_expected_fields(): void
    {
        $fillable = (new ProductCategory())->getFillable();
        $this->assertContains('category_name', $fillable);
    }

    public function test_product_category_get_description_for_event_created(): void
    {
        $cat = new ProductCategory();
        $result = $cat->getDescriptionForEvent('created');
        $this->assertStringContainsString('created', $result);
    }

    public function test_product_category_get_description_for_event_updated(): void
    {
        $cat = new ProductCategory();
        $result = $cat->getDescriptionForEvent('updated');
        $this->assertStringContainsString('updated', $result);
    }

    public function test_product_category_get_description_for_event_deleted(): void
    {
        $cat = new ProductCategory();
        $result = $cat->getDescriptionForEvent('deleted');
        $this->assertStringContainsString('deleted', $result);
    }

    public function test_product_category_get_description_for_event_other_returns_empty(): void
    {
        $cat = new ProductCategory();
        $result = $cat->getDescriptionForEvent('some_other_event');
        $this->assertSame('', $result);
    }

    // =========================================================================
    // ProductUpload
    // =========================================================================

    public function test_product_upload_table_is_product_uploads(): void
    {
        $this->assertSame('product_uploads', (new ProductUpload())->getTable());
    }

    public function test_product_upload_fillable_contains_expected_fields(): void
    {
        $fillable = (new ProductUpload())->getFillable();
        $this->assertContains('product_id', $fillable);
        $this->assertContains('title', $fillable);
        $this->assertContains('version', $fillable);
        $this->assertContains('file', $fillable);
    }

    public function test_product_upload_product_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new ProductUpload())->product());
    }

    public function test_product_upload_order_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new ProductUpload())->order());
    }

    public function test_product_upload_dependencies_accessor_decodes_json(): void
    {
        $upload = new ProductUpload();
        $upload->setRawAttributes(['dependencies' => json_encode(['dep1', 'dep2'])]);
        $result = $upload->dependencies;
        $this->assertIsArray((array) $result);
    }

    public function test_product_upload_dependencies_accessor_null_json(): void
    {
        $upload = new ProductUpload();
        $upload->setRawAttributes(['dependencies' => null]);
        $result = $upload->dependencies;
        $this->assertNull($result);
    }

    // =========================================================================
    // Type
    // =========================================================================

    public function test_type_table_is_product_types(): void
    {
        $this->assertSame('product_types', (new Type())->getTable());
    }

    public function test_type_fillable_contains_expected_fields(): void
    {
        $fillable = (new Type())->getFillable();
        $this->assertContains('name', $fillable);
        $this->assertContains('description', $fillable);
    }

    public function test_type_product_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Type())->product());
    }

    // =========================================================================
    // CloudProducts
    // =========================================================================

    public function test_cloud_products_table_is_cloud_products(): void
    {
        $this->assertSame('cloud_products', (new CloudProducts())->getTable());
    }

    public function test_cloud_products_guarded_is_empty(): void
    {
        $this->assertEmpty((new CloudProducts())->getGuarded());
    }

    public function test_cloud_products_product_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new CloudProducts())->product());
    }

    public function test_cloud_products_plan_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new CloudProducts())->plan());
    }

    public function test_cloud_products_product_uses_cloud_product_key(): void
    {
        $relation = (new CloudProducts())->product();
        $this->assertSame('cloud_product', $relation->getForeignKeyName());
    }

    public function test_cloud_products_plan_uses_cloud_free_plan_key(): void
    {
        $relation = (new CloudProducts())->plan();
        $this->assertSame('cloud_free_plan', $relation->getForeignKeyName());
    }

    // =========================================================================
    // ProductGroup
    // =========================================================================

    public function test_product_group_table_is_product_groups(): void
    {
        $this->assertSame('product_groups', (new ProductGroup())->getTable());
    }

    public function test_product_group_fillable_contains_expected_fields(): void
    {
        $fillable = (new ProductGroup())->getFillable();
        $this->assertContains('name', $fillable);
        $this->assertContains('headline', $fillable);
        $this->assertContains('tagline', $fillable);
        $this->assertContains('status', $fillable);
    }

    public function test_product_group_config_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new ProductGroup())->config());
    }

    public function test_product_group_features_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new ProductGroup())->features());
    }

    public function test_product_group_product_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new ProductGroup())->product());
    }

    public function test_product_group_pricing_template_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new ProductGroup())->pricingTemplate());
    }

    // =========================================================================
    // Subscription
    // =========================================================================

    public function test_subscription_table_is_subscriptions(): void
    {
        $this->assertSame('subscriptions', (new Subscription())->getTable());
    }

    public function test_subscription_fillable_contains_expected_fields(): void
    {
        $fillable = (new Subscription())->getFillable();
        $this->assertContains('user_id', $fillable);
        $this->assertContains('plan_id', $fillable);
        $this->assertContains('order_id', $fillable);
        $this->assertContains('product_id', $fillable);
        $this->assertContains('ends_at', $fillable);
    }

    public function test_subscription_casts_ends_at_as_datetime(): void
    {
        $casts = (new Subscription())->getCasts();
        $this->assertArrayHasKey('ends_at', $casts);
        $this->assertSame('datetime', $casts['ends_at']);
    }

    public function test_subscription_plan_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Subscription())->plan());
    }

    public function test_subscription_product_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Subscription())->product());
    }

    public function test_subscription_user_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Subscription())->user());
    }

    public function test_subscription_order_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Subscription())->order());
    }

    public function test_subscription_get_log_url_returns_url_string(): void
    {
        $sub = new Subscription();
        $sub->forceFill(['order_id' => 42]);
        $url = $sub->getLogUrl();
        $this->assertStringContainsString('orders/42', $url);
    }

    // =========================================================================
    // Product
    // =========================================================================

    public function test_product_table_is_products(): void
    {
        $this->assertSame('products', (new Product())->getTable());
    }

    public function test_product_fillable_contains_expected_fields(): void
    {
        $fillable = (new Product())->getFillable();
        $this->assertContains('name', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('type', $fillable);
        $this->assertContains('group', $fillable);
        $this->assertContains('status', $fillable);
    }

    public function test_product_order_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Product())->order());
    }

    public function test_product_subscription_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Product())->subscription());
    }

    public function test_product_license_type_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Product())->licenseType());
    }

    public function test_product_price_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Product())->price());
    }

    public function test_product_promo_relation_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Product())->PromoRelation());
    }

    public function test_product_tax_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Product())->tax());
    }

    public function test_product_product_upload_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Product())->productUpload());
    }

    public function test_product_plan_relation_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Product())->planRelation());
    }

    public function test_product_group_relation_is_belongs_to(): void
    {
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new Product())->groupRelation());
    }

    public function test_product_product_plugin_groups_as_product_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Product())->productPluginGroupsAsProduct());
    }

    public function test_product_product_plugin_groups_as_plugin_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Product())->productPluginGroupsAsPlugin());
    }

    public function test_product_config_options_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Product())->configOptions());
    }

    public function test_product_product_comp_with_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Product())->productCompWith());
    }

    public function test_product_plugin_comp_with_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Product())->pluginCompWith());
    }

    public function test_product_parent_attribute_explodes_comma_string(): void
    {
        $product = new Product();
        $product->setRawAttributes(['parent' => '1,2,3']);
        $result = $product->parent;
        $this->assertIsArray($result);
        $this->assertSame(['1', '2', '3'], $result);
    }

    public function test_product_parent_attribute_with_empty_string(): void
    {
        $product = new Product();
        $product->setRawAttributes(['parent' => '']);
        $result = $product->parent;
        $this->assertIsArray($result);
    }
}
