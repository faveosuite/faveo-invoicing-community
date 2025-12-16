<?php

namespace Tests\Unit\Common;

use App\Model\Common\SocialMedia;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;
use Tests\TestCase;

class SocialMediaControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->getLoggedInUser('admin');

        $this->withoutMiddleware();
    }

    public function test_it_returns_social_media_list()
    {
        SocialMedia::create([
            'name' => 'facebook',
            'link' => 'https://facebook.com',
        ]);

        $response = $this->getJson('/social-media/list');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => __('message.social_media_fetched'),
            ])
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'link', 'action']
                    ]
                ]
            ]);
    }

    public function test_it_creates_social_media()
    {
        $payload = [
            'name' => 'twitter',
            'link' => 'https://twitter.com',
        ];

        $response = $this->postJson('/social-media/create', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => __('message.saved-successfully'),
            ]);

        $this->assertDatabaseHas('social_media', [
            'name' => 'twitter',
            'link' => 'https://twitter.com',
        ]);
    }

    public function test_it_returns_single_social_media()
    {
        $social = SocialMedia::create([
            'name' => 'linkedin',
            'link' => 'https://linkedin.com',
        ]);

        $response = $this->getJson("/social-media/show/{$social->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'linkedin',
                'link' => 'https://linkedin.com',
            ]);
    }

    public function test_it_returns_error_when_social_media_not_found()
    {
        $response = $this->getJson('/social-media/show/999');

        $response->assertStatus(404)
            ->assertJsonFragment([
                'success' => false,
                'message' => __('message.no-record'),
            ]);
    }

    public function test_it_updates_social_media()
    {
        $social = SocialMedia::create([
            'name' => 'instagram',
            'link' => 'https://instagram.com',
        ]);

        $payload = [
            'name' => 'instagram_updated',
            'link' => 'https://instagram.com/new',
        ];

        $response = $this->patchJson("/social-media/update/{$social->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => __('message.updated-successfully'),
            ]);

        $this->assertDatabaseHas('social_media', [
            'id' => $social->id,
            'name' => 'instagram_updated',
        ]);
    }

    public function test_it_returns_error_when_updating_non_existing_social_media()
    {
        $response = $this->patchJson('/social-media/update/999', [
            'name' => 'test',
            'link' => 'https://test.com',
        ]);

        $response->assertStatus(404)
            ->assertJsonFragment([
                'success' => false,
            ]);
    }

    public function test_it_deletes_selected_social_media()
    {
        $social1 = SocialMedia::create(['name' => 'fb', 'link' => 'x']);
        $social2 = SocialMedia::create(['name' => 'tw', 'link' => 'y']);

        $response = $this->deleteJson('/social-media/delete', [
            'select' => [$social1->id, $social2->id],
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => __('message.deleted-successfully'),
            ]);

        $this->assertDatabaseMissing('social_media', ['id' => $social1->id]);
        $this->assertDatabaseMissing('social_media', ['id' => $social2->id]);
    }

    public function test_it_returns_error_when_no_rows_selected_for_delete()
    {
        $response = $this->deleteJson('/social-media/delete', [
            'select' => [],
        ]);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'message' => __('message.select-a-row'),
            ]);
    }

    public function test_it_fails_validation_when_creating_social_media_without_required_fields()
    {
        $response = $this->postJson('/social-media/create', []);

        $response->assertStatus(422);
    }

    public function test_it_filters_social_media_list_by_search_query()
    {
        SocialMedia::create([
            'name' => 'facebook',
            'link' => 'https://facebook.com',
        ]);

        SocialMedia::create([
            'name' => 'twitter',
            'link' => 'https://twitter.com',
        ]);

        SocialMedia::create([
            'name' => 'linkedin',
            'link' => 'https://linkedin.com',
        ]);

        $response = $this->getJson('/social-media/list?search-query=twitter');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Twitter',
                'link' => 'https://twitter.com',
            ]);

        $response->assertJsonMissing([
            'name' => 'Facebook',
        ]);

        $response->assertJsonMissing([
            'name' => 'Linkedin',
        ]);
    }

}
