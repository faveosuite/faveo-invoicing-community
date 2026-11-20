<?php

namespace App\License\tests\Backend\Services;

use App\License\Helpers\LicenseValidator;
use App\License\Models\Installation;
use App\License\Models\License;
use App\License\Models\LicensePlugin;
use App\License\tests\Backend\LicenseTestCase;
use App\Model\Product\Product;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * Faveo v9.4.3.9.B.2 merged each plugin's "Pro" and non-Pro products (e.g. Elea 263 -> 264).
 * Driven through LicenseValidator::findLicense, the entry point all licensing callbacks use.
 */
class ProductMergeServiceTest extends LicenseTestCase
{
    private Product $old;

    private Product $new;

    protected function setUp(): void
    {
        parent::setUp();

        $this->old = $this->createProduct();
        $this->new = $this->createProduct();
        config(['merged_products.products' => [$this->new->id => $this->old->id]]);
    }

    private function find(License $license, int $productId, ?int $oldProductId = null): ?License
    {
        return new LicenseValidator()->findLicense($license->license_code, null, $productId, $oldProductId);
    }

    #[Test]
    #[Group('license-callbacks')]
    public function old_faveo_is_served_for_the_retired_product_without_migrating(): void
    {
        $license = $this->createLicense(['product_id' => $this->old->id]);

        $found = $this->find($license, $this->old->id);

        $this->assertSame($license->id, $found?->id);
        $license->refresh();
        $this->assertSame($this->old->id, $license->product_id);
        $this->assertNull($license->migrated_from_product_id);
    }

    #[Test]
    #[Group('license-callbacks')]
    public function new_faveo_moves_an_add_on_license_once_and_marks_it(): void
    {
        $license = $this->createLicense(['product_id' => $this->old->id]);
        $this->createInstallation(['license' => $license, 'product_id' => $this->old->id]);

        $this->assertSame($license->id, $this->find($license, $this->new->id, $this->old->id)?->id);

        $license->refresh();
        $this->assertSame($this->new->id, $license->product_id);
        $this->assertSame($this->old->id, $license->migrated_from_product_id);
        $this->assertNotNull($license->product_migrated_at);
        $migratedAt = $license->product_migrated_at;

        // Installations are copied, not moved: old sites keep theirs, updated ones need no reinstall.
        $this->assertSame(1, Installation::where('license_code', $license->license_code)->where('product_id', $this->old->id)->count());
        $this->assertSame(1, Installation::where('license_code', $license->license_code)->where('product_id', $this->new->id)->count());

        // A later request finds it directly: nothing is moved or copied again.
        $this->travel(1)->days();
        $this->assertSame($license->id, $this->find($license, $this->new->id, $this->old->id)?->id);
        $this->assertEquals($migratedAt, $license->fresh()->product_migrated_at);
        $this->assertSame(1, Installation::where('license_code', $license->license_code)->where('product_id', $this->new->id)->count());
    }

    #[Test]
    #[Group('license-callbacks')]
    public function new_faveo_moves_a_bundled_plugin_link_and_marks_it(): void
    {
        $core = $this->createProduct();
        $license = $this->createLicense(['product_id' => $core->id]);
        LicensePlugin::create(['license_id' => $license->id, 'product_id' => $this->old->id]);

        $found = $this->find($license, $this->new->id, $this->old->id);

        $this->assertSame($license->id, $found?->id);
        $this->assertSame($this->new->id, $found->product_id);
        $this->assertSame($core->id, $license->fresh()->product_id, 'the core license itself stays on its product');

        $link = LicensePlugin::where('license_id', $license->id)->sole();
        $this->assertSame($this->new->id, (int) $link->product_id);
        $this->assertSame($this->old->id, $link->migrated_from_product_id);
        $this->assertNotNull($link->product_migrated_at);
    }

    #[Test]
    #[Group('license-callbacks')]
    public function old_faveo_is_still_served_after_the_license_was_moved(): void
    {
        $addOn = $this->createLicense(['product_id' => $this->old->id]);
        $core = $this->createLicense(['product_id' => $this->createProduct()->id]);
        LicensePlugin::create(['license_id' => $core->id, 'product_id' => $this->old->id]);
        $this->find($addOn, $this->new->id, $this->old->id);
        $this->find($core, $this->new->id, $this->old->id);

        foreach ([$addOn, $core] as $license) {
            $found = $this->find($license, $this->old->id);

            $this->assertSame($license->id, $found?->id);
            $this->assertSame($this->old->id, $found->product_id, 'answered for the product that was asked for');
        }
    }

    #[Test]
    #[Group('license-callbacks')]
    public function a_pair_that_is_not_a_merged_product_moves_nothing(): void
    {
        $other = $this->createProduct();
        $license = $this->createLicense(['product_id' => $this->old->id]);

        $this->assertNull($this->find($license, $other->id, $this->old->id));

        $license->refresh();
        $this->assertSame($this->old->id, $license->product_id);
        $this->assertNull($license->migrated_from_product_id);
    }

    #[Test]
    #[Group('license-callbacks')]
    public function a_license_of_neither_product_is_not_found(): void
    {
        $license = $this->createLicense(['product_id' => $this->createProduct()->id]);

        $this->assertNull($this->find($license, $this->new->id, $this->old->id));
        $this->assertNull($this->find($license, $this->old->id));
    }

    #[Test]
    #[Group('license-callbacks')]
    public function a_license_already_linked_to_both_products_is_left_alone(): void
    {
        $license = $this->createLicense(['product_id' => $this->createProduct()->id]);
        LicensePlugin::create(['license_id' => $license->id, 'product_id' => $this->old->id]);
        LicensePlugin::create(['license_id' => $license->id, 'product_id' => $this->new->id]);

        $this->assertSame($license->id, $this->find($license, $this->new->id, $this->old->id)?->id);

        $this->assertSame(2, LicensePlugin::where('license_id', $license->id)->count());
        $this->assertSame(0, LicensePlugin::where('license_id', $license->id)->whereNotNull('migrated_from_product_id')->count());
    }

    #[Test]
    #[Group('license-callbacks')]
    public function an_installation_already_on_the_new_product_is_not_duplicated(): void
    {
        $license = $this->createLicense(['product_id' => $this->old->id]);
        $this->createInstallation(['license' => $license, 'product_id' => $this->old->id]);
        $this->createInstallation(['license' => $license, 'product_id' => $this->new->id]);

        $this->find($license, $this->new->id, $this->old->id);

        $this->assertSame(1, Installation::where('license_code', $license->license_code)->where('product_id', $this->new->id)->count());
    }
}
