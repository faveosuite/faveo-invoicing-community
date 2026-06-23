<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Services\Payment;

use App\Model\Product\CloudProducts;
use App\Services\Payment\FreeTrialService;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\DBTestCase;

class FreeTrialServiceTest extends DBTestCase
{
    private FreeTrialService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FreeTrialService();
    }

    // --- checkEligibility() ---

    public function test_check_eligibility_passes_for_user_with_no_prior_trial(): void
    {
        $this->getLoggedInUser('user');

        $cloudProduct = new CloudProducts();
        $cloudProduct->cloud_product = 99999; // product_id that has no free_trial_allowed row

        // Should not throw
        $this->service->checkEligibility($this->user, $cloudProduct);

        $this->assertTrue(true); // Reached without exception
    }

    public function test_check_eligibility_throws_for_user_who_already_used_trial(): void
    {
        $this->getLoggedInUser('user');

        $cloudProduct = new CloudProducts();
        $cloudProduct->cloud_product = 99999;

        // Insert a free_trial_allowed row within the transaction (rolled back after test).
        DB::table('free_trial_allowed')->insert([
            'user_id'    => $this->user->id,
            'product_id' => $cloudProduct->cloud_product,
            'domain'     => 'used-trial.example.com',
        ]);

        $this->expectException(RuntimeException::class);

        $this->service->checkEligibility($this->user, $cloudProduct);
    }

    public function test_check_eligibility_passes_for_different_product(): void
    {
        $this->getLoggedInUser('user');

        // User used trial for product 99998 but NOT 99999.
        DB::table('free_trial_allowed')->insert([
            'user_id'    => $this->user->id,
            'product_id' => 99998,
            'domain'     => 'other-product.example.com',
        ]);

        $cloudProduct = new CloudProducts();
        $cloudProduct->cloud_product = 99999; // different product

        // Should NOT throw — trial used for a different product, not this one.
        $this->service->checkEligibility($this->user, $cloudProduct);

        $this->assertTrue(true);
    }

    public function test_check_eligibility_passes_for_different_user(): void
    {
        $this->getLoggedInUser('user');

        $otherUser = \App\User::factory()->create();

        $cloudProduct = new CloudProducts();
        $cloudProduct->cloud_product = 99999;

        // Other user used trial — not this user.
        DB::table('free_trial_allowed')->insert([
            'user_id'    => $otherUser->id,
            'product_id' => $cloudProduct->cloud_product,
            'domain'     => 'other-user.example.com',
        ]);

        // Should NOT throw for this user.
        $this->service->checkEligibility($this->user, $cloudProduct);

        $this->assertTrue(true);
    }

    // --- provision() ---
    // provision() orchestrates OrderController + TenantController (external HTTP) +
    // Invoice creation, making it unsuitable for unit testing. Integration tests
    // covering the full cloud provisioning flow belong in Feature tests.

    
}
