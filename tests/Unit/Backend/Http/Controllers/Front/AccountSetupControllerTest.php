<?php

namespace Tests\Unit\Backend\Http\Controllers\Front;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Middleware\Install;
use App\Model\Common\State;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class AccountSetupControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // The test DB isn't "installed"; skip only the installer redirect, keep auth.
        $this->withoutMiddleware(Install::class);
    }

    private function newClient(array $attrs = []): User
    {
        return User::factory()->create($attrs + ['role' => 'user', 'company' => '', 'address' => '', 'country' => 'IN']);
    }

    public function test_needs_setup_only_for_clients_missing_company_or_address(): void
    {
        $this->assertTrue($this->newClient()->needsAccountSetup());
        $this->assertTrue($this->newClient(['company' => 'Acme'])->needsAccountSetup());
        $this->assertFalse($this->newClient(['company' => 'Acme', 'address' => '1 Main St'])->needsAccountSetup());
        $this->assertFalse(User::factory()->create(['role' => 'admin', 'company' => '', 'address' => ''])->needsAccountSetup());
    }

    public function test_login_sends_an_incomplete_client_to_setup_and_keeps_the_destination(): void
    {
        $this->actingAs($this->newClient());
        session(['url.intended' => url('/my-orders')]);

        $this->assertSame(url('account-setup'), (new LoginController)->redirectPath());
        $this->assertSame(url('/my-orders'), session('url.intended')); // where setup continues to
    }

    public function test_store_saves_details_and_continues_to_the_original_destination(): void
    {
        [$country, $state] = $this->countryWithStates();
        $user = $this->newClient();
        $this->actingAs($user);
        session(['url.intended' => url('/my-orders')]);

        $this->postJson('/account-setup', [
            'company' => 'Acme', 'address' => '1 Main St', 'town' => 'Chennai', 'zip' => '600001',
            'country' => $country, 'state' => $state,
        ])->assertOk()->assertJsonPath('data.redirect', url('/my-orders'));

        $user->refresh();
        $this->assertSame('Acme', $user->company);
        $this->assertSame('1 Main St', $user->address);
        $this->assertSame($country, $user->country);
        $this->assertFalse($user->needsAccountSetup());
    }

    public function test_store_requires_company_address_country_and_state_when_the_country_has_states(): void
    {
        [$country] = $this->countryWithStates();
        $this->actingAs($this->newClient());

        $this->postJson('/account-setup', ['country' => $country])->assertStatus(412); // this app's validation status
        $this->assertTrue(auth()->user()->fresh()->needsAccountSetup());
    }

    public function test_show_returns_current_details(): void
    {
        $this->actingAs($this->newClient(['town' => 'Pune']));

        $this->getJson('/account-setup/details')->assertOk()
            ->assertJsonPath('data.country', 'IN')
            ->assertJsonPath('data.town', 'Pune')
            ->assertJsonPath('data.company', '');
    }

    public function test_guest_cannot_use_setup(): void
    {
        $this->assertNotSame(200, $this->postJson('/account-setup', [])->status());
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function countryWithStates(): array
    {
        $state = State::query()->whereNotNull('country_code')->first();
        if (! $state) {
            $this->markTestSkipped('No states seeded in the test database.');
        }

        return [(string) $state->country_code, (string) $state->iso2];
    }
}
