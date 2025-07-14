<?php

namespace Tests\Unit\Common;

use App\User;
use Tests\TestCase;

class SocialMediaControllerTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_validation_when_given_invalid_link()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $response = $this->post('/social-media', [
            'name' => 'youtube',
            'link' => 'https://youtub',
        ]);
        $errors = session('errors');
        $response->assertStatus(302);
    }

    public function test_validation_when_given_name_empty()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $response = $this->post('/social-media', [
            'name' => '',
            'link' => 'https://youtub.com',
        ]);
        $errors = session('errors');
        $response->assertStatus(302);
    }
}
