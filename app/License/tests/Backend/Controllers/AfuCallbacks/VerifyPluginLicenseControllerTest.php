<?php

namespace App\License\tests\Backend\Controllers\AfuCallbacks;

use App\License\Models\License;
use App\License\tests\Backend\LicenseTestCase;
use App\Model\Configure\PluginCompatibleWithProducts;
use App\Model\Product\Product;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class VerifyPluginLicenseControllerTest extends LicenseTestCase
{
    private Product $product;

    private Product $addon;

    private License $mainLicense;

    private License $addonLicense;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = $this->createProduct(['product_type' => 'product']);
        $this->addon = $this->createProduct(['product_type' => 'addon', 'product_key' => 'ADDONKEY']);
        PluginCompatibleWithProducts::create(['product_id' => $this->product->id, 'plugin_id' => $this->addon->id]);

        $this->mainLicense = $this->createLicense(['product_id' => $this->product->id, 'license_status' => 1]);
        $this->addonLicense = $this->createLicense(['product_id' => $this->addon->id, 'license_status' => 1]);
    }

    private function verify(array $overrides = []): mixed
    {
        return $this->post('/api/plugin/verifyLicense', array_merge([
            'license' => $this->mainLicense->license_code,
            'product_id' => $this->product->id,
            'plugin_id' => $this->addon->id,
            'plugin_license' => $this->addonLicense->license_code,
        ], $overrides));
    }

    #[Test]
    #[Group('license-callbacks')]
    public function a_valid_code_for_a_compatible_add_on_returns_its_product_key(): void
    {
        $this->verify()->assertOk()->assertJson(['success' => true, 'data' => ['product_key' => 'ADDONKEY']]);
    }

    #[Test]
    #[Group('license-callbacks')]
    public function the_helpdesk_licence_code_is_not_accepted_as_the_add_on_code(): void
    {
        $this->verify(['plugin_license' => $this->mainLicense->license_code])
            ->assertStatus(422)
            ->assertJson(['success' => false, 'message' => __('message.plugin_license_code_invalid')])
            ->assertJsonMissingPath('data.product_key');
    }

    #[Test]
    #[Group('license-callbacks')]
    public function a_code_of_another_add_on_is_not_accepted(): void
    {
        $other = $this->createProduct(['product_type' => 'addon']);
        PluginCompatibleWithProducts::create(['product_id' => $this->product->id, 'plugin_id' => $other->id]);
        $otherLicense = $this->createLicense(['product_id' => $other->id, 'license_status' => 1]);

        $this->verify(['plugin_license' => $otherLicense->license_code])
            ->assertStatus(422)
            ->assertJson(['message' => __('message.plugin_license_code_invalid')]);
    }

    #[Test]
    #[Group('license-callbacks')]
    public function an_add_on_that_is_not_compatible_with_the_product_is_refused(): void
    {
        $notCompatible = $this->createProduct(['product_type' => 'addon']);
        $license = $this->createLicense(['product_id' => $notCompatible->id, 'license_status' => 1]);

        $this->verify(['plugin_id' => $notCompatible->id, 'plugin_license' => $license->license_code])
            ->assertStatus(422)
            ->assertJson(['message' => __('message.plugin_not_compatible_with_product')]);
    }

    #[Test]
    #[Group('license-callbacks')]
    public function an_expired_add_on_licence_is_refused(): void
    {
        $this->addonLicense->update(['license_expire_date' => now()->subDay()]);

        $this->verify()->assertStatus(422)->assertJson(['message' => __('message.plugin_license_code_expired')]);
    }

    #[Test]
    #[Group('license-callbacks')]
    public function a_helpdesk_licence_of_another_product_is_refused(): void
    {
        $otherMain = $this->createLicense(['license_status' => 1]);

        $this->verify(['license' => $otherMain->license_code])
            ->assertStatus(422)
            ->assertJson(['message' => __('message.plugin_main_license_invalid')]);
    }

    #[Test]
    #[Group('license-callbacks')]
    public function missing_fields_return_a_json_error_not_a_redirect(): void
    {
        $this->post('/api/plugin/verifyLicense', [])->assertStatus(422)->assertJson(['success' => false]);
    }
}
