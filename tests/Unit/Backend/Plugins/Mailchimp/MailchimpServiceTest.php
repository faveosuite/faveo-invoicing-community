<?php

namespace Tests\Unit\Backend\Plugins\Mailchimp;

use App\Model\Common\Mailchimp\MailchimpSetting;
use App\Plugins\Mailchimp\Http\Client\MailchimpClient;
use App\Plugins\Mailchimp\Services\ContactBuilder;
use App\Plugins\Mailchimp\Services\MailchimpService;
use App\User;
use Mockery;
use Tests\TestCase;

class MailchimpServiceTest extends TestCase
{
    public function test_purchase_upserts_the_buyer_so_a_missing_contact_is_added_back(): void
    {
        $user = new User(['email' => 'Buyer@Example.com', 'first_name' => 'B', 'last_name' => 'Y']);

        $builder = Mockery::mock(ContactBuilder::class);
        $builder->shouldReceive('mergeFields')->andReturn(['FNAME' => 'B']);
        $builder->shouldReceive('purchaseInterests')->with(7, true)->andReturn(['yes' => true]);

        $client = Mockery::mock(MailchimpClient::class);
        $client->shouldNotReceive('patch');
        $client->shouldReceive('put')->once()->with(
            'lists/list1/members/'.md5('buyer@example.com'),
            [
                'email_address' => 'Buyer@Example.com',
                'status_if_new' => 'subscribed',
                'merge_fields' => ['FNAME' => 'B'],
                'interests' => ['yes' => true],
            ]
        );

        $setting = new MailchimpSetting(['list_id' => 'list1']);
        $setting->subscribe_status = 'subscribed';

        (new MailchimpService($client, $builder, $setting))->updatePurchaseInterests($user, 7, true);
    }
}
