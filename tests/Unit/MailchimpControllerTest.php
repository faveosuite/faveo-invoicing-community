<?php

namespace Tests\Unit;

use Tests\TestCase;

class MailchimpControllerTest extends TestCase
{
    public function test_post_mailchimp_settings_success()
    {
        $payload = [
            'list_id' => 'new-list-id',
            'subscribe_status' => 1,
        ];

        $response = $this->post('/settings/mailchimp', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'success',
            'list_id' => 'new-list-id',
        ]);
    }
}
