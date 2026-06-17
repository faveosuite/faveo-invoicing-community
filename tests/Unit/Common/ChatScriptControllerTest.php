<?php

namespace Tests\Unit\Common;

use App\Model\Common\ChatScript;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class ChatScriptControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getLoggedInUser('admin');

        $this->withoutMiddleware();
    }

    public function test_it_returns_chat_script_list(): void
    {
        ChatScript::create([
            'name' => 'Live Chat',
            'script' => '<script></script>',
            'on_every_page' => 1,
        ]);

        $response = $this->getJson('/chat/list');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => __('message.scripts_fetched'),
            ])
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'checkbox', 'action'],
                    ],
                ],
            ]);
    }

    public function test_it_filters_chat_scripts_by_search_query(): void
    {
        ChatScript::create([
            'name' => 'Facebook Chat',
            'script' => 'fb',
        ]);

        ChatScript::create([
            'name' => 'Whatsapp Chat',
            'script' => 'wa',
        ]);

        $response = $this->getJson('/chat/list?search-query=Facebook');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Facebook Chat',
            ])
            ->assertJsonMissing([
                'name' => 'Whatsapp Chat',
            ]);
    }

    public function test_it_creates_chat_script(): void
    {
        $payload = [
            'name' => 'Test Script',
            'script' => '<script>test</script>',
            'google_analytics' => 0,
            'on_registration' => 0,
        ];

        $response = $this->postJson('/chat/create', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => __('message.saved-successfully'),
            ]);

        $this->assertDatabaseHas('chat_scripts', [
            'name' => 'Test Script',
        ]);
    }

    public function test_it_fails_validation_when_required_fields_missing(): void
    {
        $response = $this->postJson('/chat/create', []);
        $response->assertStatus(422);
    }

    public function test_it_returns_single_chat_script(): void
    {
        $script = ChatScript::create([
            'name' => 'My Script',
            'script' => 'code',
        ]);

        $response = $this->getJson('/chat/show/'.$script->id);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'My Script',
            ]);
    }

    public function test_it_returns_error_when_chat_script_not_found(): void
    {
        $response = $this->getJson('/chat/show/999');

        $response->assertStatus(404)
            ->assertJsonFragment([
                'message' => __('message.no-record'),
            ]);
    }

    public function test_it_updates_chat_script(): void
    {
        $script = ChatScript::create([
            'name' => 'Old Script',
            'script' => 'old',
        ]);

        $payload = [
            'name' => 'Updated Script',
            'script' => 'new',
            'on_registration' => 1,
        ];

        $response = $this->putJson('/chat/update/'.$script->id, $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => __('message.updated-successfully'),
            ]);

        $this->assertDatabaseHas('chat_scripts', [
            'id' => $script->id,
            'name' => 'Updated Script',
        ]);
    }

    public function test_it_returns_error_when_updating_non_existing_script(): void
    {
        $response = $this->putJson('/chat/update/999', [
            'name' => 'test',
            'script' => 'code',
        ]);

        $response->assertStatus(404)
            ->assertJsonFragment([
                'message' => __('message.record_not_found'),
            ]);
    }

    public function test_it_deletes_selected_chat_scripts(): void
    {
        $script1 = ChatScript::create(['name' => 'A', 'script' => 'a']);
        $script2 = ChatScript::create(['name' => 'B', 'script' => 'b']);

        $response = $this->deleteJson('/chat/delete', [
            'select' => [$script1->id, $script2->id],
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => __('message.deleted-successfully'),
            ]);

        $this->assertDatabaseMissing('chat_scripts', ['id' => $script1->id]);
        $this->assertDatabaseMissing('chat_scripts', ['id' => $script2->id]);
    }

    public function test_it_returns_error_when_no_script_selected_for_delete(): void
    {
        $response = $this->deleteJson('/chat/delete', [
            'select' => [],
        ]);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'message' => __('message.select-a-row'),
            ]);
    }
}
