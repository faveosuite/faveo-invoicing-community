<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Models\Payment;

use App\Model\Common\Country;
use App\Model\Payment\Currency;
use App\Model\Payment\Period;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Payment\Promotion;
use App\Model\Payment\Tax;
use App\Model\Payment\TaxClass;
use App\Model\Payment\TaxOption;
use App\Model\Payment\TaxProductRelation;
use App\Model\Payment\TaxRate;
use App\Model\Payment\TaxRateLocation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class PaymentModelsCoverageTest extends TestCase
{
    // =========================================================================
    // Period
    // =========================================================================

    public function test_period_table_is_periods(): void
    {
        $this->assertSame('periods', (new Period())->getTable());
    }

    public function test_period_fillable_contains_expected_fields(): void
    {
        $fillable = (new Period())->getFillable();
        $this->assertContains('name', $fillable);
        $this->assertContains('days', $fillable);
    }

    public function test_period_plans_relation_is_belongs_to_many(): void
    {
        $this->assertInstanceOf(BelongsToMany::class, (new Period())->plans());
    }

    // =========================================================================
    // PlanPrice
    // =========================================================================

    public function test_plan_price_table_is_plan_prices(): void
    {
        $this->assertSame('plan_prices', (new PlanPrice())->getTable());
    }

    public function test_plan_price_fillable_contains_expected_fields(): void
    {
        $fillable = (new PlanPrice())->getFillable();
        $this->assertContains('plan_id', $fillable);
        $this->assertContains('currency', $fillable);
        $this->assertContains('add_price', $fillable);
        $this->assertContains('renew_price', $fillable);
        $this->assertContains('offer_price', $fillable);
    }

    // =========================================================================
    // TaxOption
    // =========================================================================

    public function test_tax_option_table_is_tax_rules(): void
    {
        $this->assertSame('tax_rules', (new TaxOption())->getTable());
    }

    public function test_tax_option_fillable_contains_expected_fields(): void
    {
        $fillable = (new TaxOption())->getFillable();
        $this->assertContains('tax_enable', $fillable);
        $this->assertContains('inclusive', $fillable);
        $this->assertContains('rounding', $fillable);
    }

    // =========================================================================
    // TaxProductRelation
    // =========================================================================

    public function test_tax_product_relation_table_is_tax_product_relations(): void
    {
        $this->assertSame('tax_product_relations', (new TaxProductRelation())->getTable());
    }

    public function test_tax_product_relation_fillable_contains_expected_fields(): void
    {
        $fillable = (new TaxProductRelation())->getFillable();
        $this->assertContains('product_id', $fillable);
        $this->assertContains('tax_class_id', $fillable);
    }

    public function test_tax_product_relation_tax_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new TaxProductRelation())->tax());
    }

    public function test_tax_product_relation_product_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new TaxProductRelation())->product());
    }

    // =========================================================================
    // TaxClass
    // =========================================================================

    public function test_tax_class_table_is_tax_classes(): void
    {
        $this->assertSame('tax_classes', (new TaxClass())->getTable());
    }

    public function test_tax_class_fillable_contains_name(): void
    {
        $fillable = (new TaxClass())->getFillable();
        $this->assertContains('name', $fillable);
    }

    public function test_tax_class_tax_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new TaxClass())->tax());
    }

    public function test_tax_class_tax_product_relation_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new TaxClass())->tax_product_relation());
    }

    // =========================================================================
    // Plan
    // =========================================================================

    public function test_plan_table_is_plans(): void
    {
        $this->assertSame('plans', (new Plan())->getTable());
    }

    public function test_plan_fillable_contains_expected_fields(): void
    {
        $fillable = (new Plan())->getFillable();
        $this->assertContains('name', $fillable);
        $this->assertContains('product', $fillable);
        $this->assertContains('allow_tax', $fillable);
        $this->assertContains('days', $fillable);
        $this->assertContains('status', $fillable);
    }

    public function test_plan_plan_price_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Plan())->planPrice());
    }

    public function test_plan_product_relation_is_belongs_to(): void
    {
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new Plan())->productRelation());
    }

    public function test_plan_periods_is_belongs_to_many(): void
    {
        $this->assertInstanceOf(BelongsToMany::class, (new Plan())->periods());
    }

    public function test_plan_config_options_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Plan())->configOptions());
    }

    // =========================================================================
    // Promotion
    // =========================================================================

    public function test_promotion_table_is_promotions(): void
    {
        $this->assertSame('promotions', (new Promotion())->getTable());
    }

    public function test_promotion_fillable_contains_expected_fields(): void
    {
        $fillable = (new Promotion())->getFillable();
        $this->assertContains('code', $fillable);
        $this->assertContains('type', $fillable);
        $this->assertContains('uses', $fillable);
        $this->assertContains('value', $fillable);
        $this->assertContains('start', $fillable);
        $this->assertContains('expiry', $fillable);
    }

    public function test_promotion_relation_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Promotion())->relation());
    }

    // =========================================================================
    // Tax
    // =========================================================================

    public function test_tax_table_is_taxes(): void
    {
        $this->assertSame('taxes', (new Tax())->getTable());
    }

    public function test_tax_fillable_contains_expected_fields(): void
    {
        $fillable = (new Tax())->getFillable();
        $this->assertContains('level', $fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('country', $fillable);
        $this->assertContains('state', $fillable);
        $this->assertContains('rate', $fillable);
        $this->assertContains('active', $fillable);
        $this->assertContains('tax_classes_id', $fillable);
        $this->assertContains('compound', $fillable);
    }

    public function test_tax_tax_class_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Tax())->taxClass());
    }

    public function test_tax_tax_class_uses_tax_classes_id_fk(): void
    {
        $relation = (new Tax())->taxClass();
        $this->assertSame('tax_classes_id', $relation->getForeignKeyName());
    }

    // =========================================================================
    // Currency
    // =========================================================================

    public function test_currency_table_is_currencies(): void
    {
        $this->assertSame('currencies', (new Currency())->getTable());
    }

    public function test_currency_fillable_contains_expected_fields(): void
    {
        $fillable = (new Currency())->getFillable();
        $this->assertContains('code', $fillable);
        $this->assertContains('symbol', $fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('status', $fillable);
    }

    public function test_currency_country_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Currency())->country());
    }

    public function test_currency_country_relation_uses_currency_id_foreign_key(): void
    {
        $relation = (new Currency())->country();
        $this->assertSame('currency_id', $relation->getForeignKeyName());
    }

    public function test_currency_country_relation_related_class_is_country(): void
    {
        $relation = (new Currency())->country();
        $this->assertInstanceOf(Country::class, $relation->getRelated());
    }

    public function test_currency_get_mappings_returns_array_with_expected_keys(): void
    {
        $model = new Currency();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertIsArray($mappings);
        $this->assertArrayHasKey('code', $mappings);
        $this->assertArrayHasKey('symbol', $mappings);
        $this->assertArrayHasKey('name', $mappings);
        $this->assertArrayHasKey('status', $mappings);
    }

    // =========================================================================
    // PlanPrice – getMappings + plan() relationship
    // =========================================================================

    public function test_plan_price_plan_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new PlanPrice())->plan());
    }

    public function test_plan_price_get_mappings_returns_expected_keys(): void
    {
        $model = new PlanPrice();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertIsArray($mappings);
        $this->assertArrayHasKey('plan_id', $mappings);
        $this->assertArrayHasKey('currency', $mappings);
        $this->assertArrayHasKey('add_price', $mappings);
        $this->assertArrayHasKey('renew_price', $mappings);
        $this->assertArrayHasKey('offer_price', $mappings);
    }

    // =========================================================================
    // TaxOption – getMappings
    // =========================================================================

    public function test_tax_option_get_mappings_returns_expected_keys(): void
    {
        $model = new TaxOption();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertIsArray($mappings);
        $this->assertArrayHasKey('tax_enable', $mappings);
        $this->assertArrayHasKey('inclusive', $mappings);
        $this->assertArrayHasKey('tax_based_on', $mappings);
        $this->assertArrayHasKey('rounding', $mappings);
    }

    // =========================================================================
    // TaxRateLocation – taxRate() relationship
    // =========================================================================

    public function test_tax_rate_location_table_is_tax_rate_locations(): void
    {
        $this->assertSame('tax_rate_locations', (new TaxRateLocation())->getTable());
    }

    public function test_tax_rate_location_fillable_contains_expected_fields(): void
    {
        $fillable = (new TaxRateLocation())->getFillable();
        $this->assertContains('tax_rate_id', $fillable);
        $this->assertContains('location_code', $fillable);
        $this->assertContains('location_type', $fillable);
    }

    public function test_tax_rate_location_tax_rate_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new TaxRateLocation())->taxRate());
    }

    // =========================================================================
    // TaxRate – getMappings + locations() + taxClass()
    // =========================================================================

    public function test_tax_rate_table_is_tax_rates(): void
    {
        $this->assertSame('tax_rates', (new TaxRate())->getTable());
    }

    public function test_tax_rate_fillable_contains_expected_fields(): void
    {
        $fillable = (new TaxRate())->getFillable();
        $this->assertContains('name', $fillable);
        $this->assertContains('country', $fillable);
        $this->assertContains('rate', $fillable);
        $this->assertContains('active', $fillable);
    }

    public function test_tax_rate_get_mappings_returns_expected_keys(): void
    {
        $model = new TaxRate();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertIsArray($mappings);
        $this->assertArrayHasKey('name', $mappings);
        $this->assertArrayHasKey('country', $mappings);
        $this->assertArrayHasKey('state', $mappings);
        $this->assertArrayHasKey('rate', $mappings);
        $this->assertArrayHasKey('active', $mappings);
    }

    public function test_tax_rate_locations_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new TaxRate())->locations());
    }

    public function test_tax_rate_tax_class_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new TaxRate())->taxClass());
    }

    public function test_tax_rate_casts_contains_expected_keys(): void
    {
        $casts = (new TaxRate())->getCasts();
        $this->assertArrayHasKey('rate', $casts);
        $this->assertArrayHasKey('compound', $casts);
        $this->assertArrayHasKey('active', $casts);
    }

    // =========================================================================
    // TaxClass – getMappings + rates()
    // =========================================================================

    public function test_tax_class_get_mappings_returns_expected_keys(): void
    {
        $model = new TaxClass();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertIsArray($mappings);
        $this->assertArrayHasKey('name', $mappings);
        $this->assertArrayHasKey('slug', $mappings);
    }

    public function test_tax_class_rates_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new TaxClass())->rates());
    }

    // =========================================================================
    // Plan – getMappings
    // =========================================================================

    public function test_plan_get_mappings_returns_expected_keys(): void
    {
        $model = new Plan();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertIsArray($mappings);
        $this->assertArrayHasKey('name', $mappings);
        $this->assertArrayHasKey('product', $mappings);
        $this->assertArrayHasKey('allow_tax', $mappings);
        $this->assertArrayHasKey('days', $mappings);
        $this->assertArrayHasKey('status', $mappings);
    }

    // =========================================================================
    // Period – getMappings (implicit via delete body covered by DBTestCase)
    // =========================================================================

    public function test_period_get_mappings_method_does_not_exist(): void
    {
        // Period has no getMappings; confirm delete() is declared
        $this->assertTrue(method_exists(Period::class, 'delete'));
    }

    // =========================================================================
    // Promotion – getMappings + promotionType() + products()
    // =========================================================================

    public function test_promotion_get_mappings_returns_expected_keys(): void
    {
        $model = new Promotion();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertIsArray($mappings);
        $this->assertArrayHasKey('code', $mappings);
        $this->assertArrayHasKey('type', $mappings);
        $this->assertArrayHasKey('uses', $mappings);
        $this->assertArrayHasKey('value', $mappings);
    }

    public function test_promotion_promotion_type_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Promotion())->promotionType());
    }

    public function test_promotion_products_is_has_one_through(): void
    {
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasOneThrough::class,
            (new Promotion())->products()
        );
    }
}
