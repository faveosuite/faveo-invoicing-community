<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Models\License;

use App\Model\License\LicensePermission;
use App\Model\License\LicensePermissionPivot;
use App\Model\License\LicenseType;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class LicenseModelsTest extends TestCase
{
    // ───────────── LicensePermission ─────────────

    public function test_license_permission_table_name(): void
    {
        $this->assertSame('license_permissions', (new LicensePermission())->getTable());
    }

    public function test_license_permission_fillable(): void
    {
        $model = new LicensePermission();
        $this->assertContains('id', $model->getFillable());
        $this->assertContains('permissions', $model->getFillable());
    }

    public function test_license_permission_license_types_relation(): void
    {
        $this->assertInstanceOf(BelongsToMany::class, (new LicensePermission())->licenseTypes());
    }

    // ───────────── LicensePermissionPivot ─────────────

    public function test_license_permission_pivot_table_name(): void
    {
        $this->assertSame('license_license_permissions', (new LicensePermissionPivot())->getTable());
    }

    public function test_license_permission_pivot_fillable(): void
    {
        $model = new LicensePermissionPivot();
        $this->assertContains('license_type_id', $model->getFillable());
        $this->assertContains('license_permission_id', $model->getFillable());
        $this->assertContains('status', $model->getFillable());
    }

    public function test_license_permission_pivot_get_mappings(): void
    {
        $model = new LicensePermissionPivot();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('license_permission_id', $mappings);
    }

    // ───────────── LicenseType ─────────────

    public function test_license_type_table_name(): void
    {
        $this->assertSame('license_types', (new LicenseType())->getTable());
    }

    public function test_license_type_fillable(): void
    {
        $model = new LicenseType();
        $this->assertContains('id', $model->getFillable());
        $this->assertContains('name', $model->getFillable());
    }

    public function test_license_type_permissions_relation(): void
    {
        $this->assertInstanceOf(BelongsToMany::class, (new LicenseType())->permissions());
    }

    public function test_license_type_products_relation(): void
    {
        $this->assertInstanceOf(HasMany::class, (new LicenseType())->products());
    }

    public function test_license_type_get_mappings(): void
    {
        $model = new LicenseType();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('name', $mappings);
    }
}
