<?php

namespace Tests\Unit\Backend\Plugins\Mailchimp;

use App\Model\Common\Mailchimp\MailchimpField;
use App\Model\Common\Mailchimp\MailchimpFieldAgoraRelation;
use App\Model\Common\Mailchimp\MailchimpGroup;
use App\Model\Common\Mailchimp\MailchimpGroupAgoraRelation;
use App\Model\Common\Mailchimp\MailchimpSetting;
use App\Model\Common\StatusSetting;
use App\Plugins\Mailchimp\Exceptions\MailchimpApiException;
use App\Plugins\Mailchimp\Http\Client\MailchimpClient;
use App\Plugins\Mailchimp\Http\Controllers\SettingsController;
use App\Plugins\Mailchimp\Http\Requests\UpdateSettingsRequest;
use App\Plugins\Mailchimp\Services\ContactBuilder;
use App\Plugins\Mailchimp\Services\MailchimpService;
use App\User;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\DBTestCase;

class SettingsControllerTest extends DBTestCase
{
    private const string KEY = '0123456789abcdef0123456789abwxyz-us7';

    private SettingsController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        Http::swap(new Factory);
        Http::preventStrayRequests();

        MailchimpSetting::query()->delete();
        MailchimpSetting::create(['api_key' => self::KEY, 'list_id' => 'list1', 'subscribe_status' => 'subscribed']);
        MailchimpField::where('list_id', 'list1')->delete();
        MailchimpField::create(['list_id' => 'list1', 'merge_id' => 990001, 'tag' => 'FNAME', 'name' => 'First Name', 'type' => 'text', 'required' => 0]);
        MailchimpField::create(['list_id' => 'list1', 'merge_id' => 990002, 'tag' => 'COMPANY', 'name' => 'Company', 'type' => 'text', 'required' => 1]);

        $this->forgetServices();
        $this->controller = new SettingsController;
    }

    private function forgetServices(): void
    {
        app()->forgetInstance(MailchimpService::class);
        app()->forgetInstance(ContactBuilder::class);
    }

    public function test_settings_return_the_saved_api_key(): void
    {
        Http::fake([
            'us7.api.mailchimp.com/3.0/lists*' => Http::response(['lists' => [], 'total_items' => 0]),
            'us7.api.mailchimp.com/3.0/*' => Http::response(['account_name' => 'Faveo Test']),
        ]);

        $data = $this->controller->getSettings()->getData(true)['data'];

        $this->assertSame(self::KEY, $data['api_key']);
        $this->assertTrue($data['has_key']);
        $this->assertSame('wxyz', $data['key_hint']);
        $this->assertSame('us7', $data['data_center']);
        $this->assertSame('Faveo Test', $data['account_name']);
    }

    public function test_saving_a_blank_key_keeps_the_saved_one(): void
    {
        Http::fake([
            'us7.api.mailchimp.com/3.0/lists*' => Http::response(['lists' => [], 'total_items' => 0]),
            'us7.api.mailchimp.com/3.0/*' => Http::response(['account_id' => 'acc1']),
        ]);

        $request = UpdateSettingsRequest::create('/', 'POST', ['mailchimp_auth_key' => '', 'status' => 1]);
        $response = $this->controller->saveApiKey($request);

        $this->assertSame(200, $response->getStatusCode(), (string) $response->getContent());
        $this->assertSame(self::KEY, MailchimpSetting::value('api_key'));
    }

    public function test_saving_a_blank_key_without_a_saved_one_is_rejected(): void
    {
        MailchimpSetting::query()->update(['api_key' => null]);

        $response = $this->controller->saveApiKey(UpdateSettingsRequest::create('/', 'POST', ['mailchimp_auth_key' => '']));

        $this->assertSame(422, $response->getStatusCode());
        Http::assertNothingSent();
    }

    public function test_disconnect_forgets_key_and_audience_and_switches_off(): void
    {
        StatusSetting::where('id', 1)->update(['mailchimp_status' => 1]);

        $this->controller->disconnect();

        $setting = MailchimpSetting::firstOrFail();
        $this->assertNull($setting->api_key);
        $this->assertNull($setting->list_id);
        $this->assertSame(0, (int) StatusSetting::value('mailchimp_status'));
    }

    public function test_field_mapping_rejects_a_tag_from_another_audience(): void
    {
        $this->expectException(ValidationException::class);

        $this->controller->saveFieldMapping(new Request(['first_name' => 'MMERGE5', 'company' => 'COMPANY']));
    }

    public function test_field_mapping_requires_the_fields_mailchimp_requires(): void
    {
        app('translator')->addLines(['message.mailchimp_required_missing' => 'Map the fields Mailchimp requires: :fields'], 'en');
        $response = $this->controller->saveFieldMapping(new Request(['first_name' => 'FNAME']));

        $this->assertSame(422, $response->getStatusCode());
        $this->assertStringContainsString('Company', $response->getData()->message);
    }

    public function test_field_mapping_saves_and_clears_unmapped_fields(): void
    {
        MailchimpFieldAgoraRelation::firstOrNew()->fill(['first_name' => 'OLD', 'zip' => 'OLDZIP', 'is_paid_yes' => 'yes1'])->save();

        $response = $this->controller->saveFieldMapping(new Request(['country' => 'FNAME', 'company' => 'COMPANY']));

        $this->assertSame(200, $response->getStatusCode());
        $relation = MailchimpFieldAgoraRelation::firstOrFail();
        $this->assertSame('FNAME', $relation->country);
        $this->assertSame('COMPANY', $relation->company);
        $this->assertNull($relation->first_name);
        $this->assertNull($relation->zip);
        $this->assertSame('yes1', $relation->is_paid_yes, 'is-paid mapping is not part of field mapping');
    }

    public function test_send_test_needs_an_audience(): void
    {
        MailchimpSetting::query()->update(['list_id' => null]);
        $this->actingAs(User::factory()->create());

        $response = $this->controller->sendTest();

        $this->assertSame(400, $response->getStatusCode());
        Http::assertNothingSent();
    }

    public function test_send_test_says_so_when_nothing_is_mapped(): void
    {
        MailchimpFieldAgoraRelation::firstOrNew()->fill(array_fill_keys(['first_name', 'last_name', 'company', 'mobile', 'address', 'town', 'country', 'state', 'zip', 'active', 'role', 'source'], null))->save();
        $this->actingAs(User::factory()->create());

        $response = $this->controller->sendTest();

        $this->assertSame(400, $response->getStatusCode());
        Http::assertNothingSent();
    }

    public function test_send_test_shows_mailchimps_reason_when_it_fails(): void
    {
        app('translator')->addLines(['message.mailchimp_test_failed' => 'Mailchimp rejected the test: :reason'], 'en');
        MailchimpFieldAgoraRelation::firstOrNew()->fill(['first_name' => 'FNAME', 'company' => 'COMPANY'])->save();
        $this->actingAs(User::factory()->create(['first_name' => 'Ann', 'company' => 'Acme']));
        Http::fake(['us7.api.mailchimp.com/3.0/lists/list1/members' => Http::response(['detail' => 'Your merge fields were invalid.'], 400)]);

        $response = $this->controller->sendTest();

        $this->assertSame(400, $response->getStatusCode());
        $this->assertStringContainsString('Your merge fields were invalid.', $response->getData()->message);
    }

    public function test_send_test_subscribes_the_signed_in_admin_with_the_mapping(): void
    {
        MailchimpFieldAgoraRelation::firstOrNew()->fill(['first_name' => 'FNAME', 'company' => 'COMPANY'])->save();
        $admin = User::factory()->create(['first_name' => 'Ann', 'company' => 'Acme']);
        $this->actingAs($admin);
        Http::fake(['us7.api.mailchimp.com/3.0/lists/list1/members' => Http::response(['id' => 'm1'])]);

        $response = $this->controller->sendTest();

        $this->assertSame(200, $response->getStatusCode());
        Http::assertSent(fn ($request): bool => str_ends_with((string) $request->url(), '/lists/list1/members')
            && $request['email_address'] === $admin->email
            && $request['merge_fields']['FNAME'] === 'Ann'
            && $request['merge_fields']['COMPANY'] === 'Acme');
    }

    public function test_merge_fields_skip_tags_from_another_audience(): void
    {
        MailchimpFieldAgoraRelation::firstOrNew()->fill(['first_name' => 'FNAME', 'company' => 'MMERGE5'])->save();
        $user = User::factory()->make(['first_name' => 'Ann', 'company' => 'Acme']);

        $this->assertSame(['FNAME' => 'Ann'], resolve(ContactBuilder::class)->mergeFields($user));
    }

    public function test_purchase_interests_skip_groups_from_another_audience(): void
    {
        StatusSetting::where('id', 1)->update(['mailchimp_product_status' => 1, 'mailchimp_ispaid_status' => 1]);
        MailchimpGroup::where('list_id', 'list1')->delete();
        MailchimpGroup::create(['list_id' => 'list1', 'category_id' => 'cat', 'category_option_id' => 'good', 'category_name' => 'Pro']);
        MailchimpGroup::create(['list_id' => 'list1', 'category_id' => 'paid', 'category_option_id' => 'yes1', 'category_name' => 'YES']);
        MailchimpFieldAgoraRelation::firstOrNew()->fill(['is_paid_yes' => 'yes1', 'is_paid_no' => 'staleNo'])->save();
        MailchimpGroupAgoraRelation::query()->delete();
        MailchimpGroupAgoraRelation::create(['agora_product_id' => 99, 'mailchimp_group_cat_id' => 'stale']);
        MailchimpGroupAgoraRelation::create(['agora_product_id' => 99, 'mailchimp_group_cat_id' => 'good']);

        $this->assertSame(['yes1' => true, 'good' => true], resolve(ContactBuilder::class)->purchaseInterests(99, true));
    }

    public function test_mailchimp_errors_name_the_invalid_fields(): void
    {
        Http::fake(['us7.api.mailchimp.com/*' => Http::response([
            'detail' => "The resource submitted could not be validated. For field-specific details, see the 'errors' array.",
            'errors' => [['field' => 'MMERGE5', 'message' => 'Merge field does not exist']],
        ], 400)]);

        try {
            (new MailchimpClient(self::KEY))->post('lists/list1/members');
            $this->fail('Expected a MailchimpApiException.');
        } catch (MailchimpApiException $e) {
            $this->assertStringContainsString('MMERGE5: Merge field does not exist', $e->getMessage());
        }
    }

    private function relation(): MailchimpFieldAgoraRelation
    {
        return MailchimpFieldAgoraRelation::first() ?? MailchimpFieldAgoraRelation::create([]);
    }

    public function test_sync_fields_removes_mappings_to_fields_the_audience_no_longer_has(): void
    {
        $this->relation()->update(['first_name' => 'FNAME', 'company' => 'MMERGE5', 'is_paid_yes' => 'int1']);
        Http::fake(['us7.api.mailchimp.com/3.0/lists/list1/merge-fields*' => Http::response(['merge_fields' => [
            ['merge_id' => 990001, 'tag' => 'FNAME', 'name' => 'First Name', 'type' => 'text', 'required' => false, 'list_id' => 'list1'],
        ]])]);

        $response = $this->controller->syncMergeFields();

        $relation = $this->relation()->refresh();
        $this->assertSame('FNAME', $relation->first_name);
        $this->assertNull($relation->company);
        $this->assertSame('int1', $relation->is_paid_yes, 'interest ids are not merge tags and must be left alone');
        $this->assertStringContainsString('Company', (string) $response->getData()->message);
    }

    public function test_sync_groups_removes_product_mappings_to_groups_the_audience_no_longer_has(): void
    {
        $this->relation()->update(['is_paid_yes' => 'int1', 'is_paid_no' => 'gone1']);
        MailchimpGroupAgoraRelation::query()->delete();
        MailchimpGroupAgoraRelation::create(['agora_product_id' => 990101, 'mailchimp_group_cat_id' => 'int1']);
        MailchimpGroupAgoraRelation::create(['agora_product_id' => 990102, 'mailchimp_group_cat_id' => 'old-audience-group']);
        Http::fake([
            'us7.api.mailchimp.com/3.0/lists/list1/interest-categories/cat1/interests*' => Http::response(['interests' => [
                ['id' => 'int1', 'category_id' => 'cat1', 'list_id' => 'list1', 'name' => 'Gold'],
            ]]),
            'us7.api.mailchimp.com/3.0/lists/list1/interest-categories*' => Http::response(['categories' => [['id' => 'cat1', 'title' => 'Plans']]]),
        ]);

        $this->controller->syncInterestGroups();

        $this->assertSame(['int1'], MailchimpGroupAgoraRelation::pluck('mailchimp_group_cat_id')->all());
        $relation = $this->relation()->refresh();
        $this->assertSame('int1', $relation->is_paid_yes);
        $this->assertNull($relation->is_paid_no);
    }

    public function test_sync_groups_removes_stored_options_mailchimp_no_longer_has(): void
    {
        MailchimpGroup::create(['category_option_id' => 'gone-opt', 'category_id' => 'deleted-cat', 'list_id' => 'list1', 'category_name' => 'YES']);
        MailchimpGroup::create(['category_option_id' => 'other-list-opt', 'category_id' => 'x', 'list_id' => 'list2', 'category_name' => 'Other']);
        Http::fake([
            'us7.api.mailchimp.com/3.0/lists/list1/interest-categories/cat1/interests*' => Http::response(['interests' => [
                ['id' => 'int1', 'category_id' => 'cat1', 'list_id' => 'list1', 'name' => 'Gold'],
            ]]),
            'us7.api.mailchimp.com/3.0/lists/list1/interest-categories*' => Http::response(['categories' => [['id' => 'cat1', 'title' => 'Plans']]]),
        ]);

        $this->controller->syncInterestGroups();

        $this->assertSame(['int1'], MailchimpGroup::where('list_id', 'list1')->pluck('category_option_id')->all());
        $this->assertTrue(MailchimpGroup::where('category_option_id', 'other-list-opt')->exists(), 'another audience is left alone');
    }

    public function test_sync_without_an_audience_leaves_every_mapping_alone(): void
    {
        MailchimpSetting::query()->update(['list_id' => null]);
        $this->forgetServices();
        $this->relation()->update(['company' => 'MMERGE5']);
        MailchimpGroupAgoraRelation::query()->delete();
        MailchimpGroupAgoraRelation::create(['agora_product_id' => 990101, 'mailchimp_group_cat_id' => 'int1']);

        $service = resolve(MailchimpService::class);

        $this->assertSame([], $service->syncMergeFields());
        $this->assertSame([], $service->syncInterestGroups());
        $this->assertSame('MMERGE5', $this->relation()->refresh()->company);
        $this->assertSame(1, MailchimpGroupAgoraRelation::count());
    }
}
