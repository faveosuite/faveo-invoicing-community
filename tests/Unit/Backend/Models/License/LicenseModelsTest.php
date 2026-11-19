<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Models\License;

use App\Model\License\LicenseType;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class LicenseModelsTest extends TestCase
{
    // ───────────── LicenseType ─────────────

    public function test_license_type_table_name(): void
    {
        $this->assertSame('license_types', new LicenseType()->getTable());
    }

    public function test_license_type_fillable(): void
    {
        $model = new LicenseType;
        $this->assertContains('id', $model->getFillable());
        $this->assertContains('name', $model->getFillable());
    }

    public function test_license_type_products_relation(): void
    {
        $this->assertInstanceOf(HasMany::class, new LicenseType()->products());
    }

    public function test_license_type_get_mappings(): void
    {
        $model = new LicenseType;
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('name', $mappings);
    }
}
