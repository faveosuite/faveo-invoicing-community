<?php

namespace Tests\Unit\Common;

use App\Model\Front\Widgets;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class WidgetControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getLoggedInUser('admin');

        $this->withoutMiddleware();
    }

    public function test_fetches_widget_list(): void
    {
        Widgets::create([
            'name' => 'footer',
            'type' => 'footer',
            'publish' => 1,
            'content' => 'Footer content',
        ]);

        $response = $this->getJson('/widgets/list');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => __('message.widget_fetched'),
            ])
            ->assertJsonStructure([
                'data' => [
                    'pages' => [
                        'data' => [
                            '*' => ['id', 'name', 'type', 'created_at', 'content', 'action'],
                        ],
                    ],
                    'total',
                ],
            ]);
    }

    public function test_filters_widgets_by_search_query(): void
    {
        Widgets::create([
            'name' => 'Footer Widget',
            'type' => 'footer',
            'publish' => 1,
            'content' => 'Footer content',
        ]);

        Widgets::create([
            'name' => 'Header Widget',
            'type' => 'header',
            'publish' => 1,
            'content' => 'Header content',
        ]);

        $response = $this->getJson('/widgets/list?search-query=footer');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Footer Widget',
                'type' => 'footer',
            ]);
    }

    public function test_fetches_single_widget(): void
    {
        $widget = Widgets::create([
            'name' => 'footer',
            'type' => 'footer',
            'publish' => 1,
            'content' => 'Footer content',
        ]);

        $response = $this->getJson('/widgets/show/'.$widget->id);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $widget->id,
                'name' => 'footer',
            ]);
    }

    public function test_returns_404_when_widget_not_found(): void
    {
        $response = $this->getJson('/widgets/show/999');

        $response->assertStatus(404)
            ->assertJsonFragment([
                'message' => __('message.no-record'),
            ]);
    }

    public function test_creates_widget_successfully(): void
    {
        $payload = [
            'name' => 'Header Widget',
            'type' => 'header',
            'publish' => 1,
            'content' => 'Header Content',
        ];

        $response = $this->postJson('/widgets/create', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'message' => __('message.saved-successfully'),
            ]);

        $this->assertDatabaseHas('widgets', [
            'type' => 'header',
        ]);
    }

    public function test_fails_when_duplicate_widget_type_is_used(): void
    {
        Widgets::create([
            'name' => 'Footer',
            'type' => 'footer',
            'publish' => 1,
        ]);

        $response = $this->postJson('/widgets/create', [
            'name' => 'Another Footer',
            'type' => 'footer',
            'publish' => 1,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['type']);
    }

    public function test_updates_widget_successfully(): void
    {
        $widget = Widgets::create([
            'name' => 'Footer',
            'type' => 'footer',
            'publish' => 1,
        ]);

        $response = $this->putJson('/widgets/update/'.$widget->id, [
            'name' => 'Updated Footer',
            'type' => 'footer',
            'publish' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => __('message.updated-successfully'),
            ]);

        $this->assertDatabaseHas('widgets', [
            'id' => $widget->id,
            'name' => 'Updated Footer',
        ]);
    }

    public function test_deletes_selected_widgets(): void
    {
        $widget1 = Widgets::create([
            'name' => 'Footer',
            'type' => 'footer',
            'publish' => 1,
        ]);

        $widget2 = Widgets::create([
            'name' => 'Header',
            'type' => 'header',
            'publish' => 1,
        ]);

        $response = $this->deleteJson('/widgets/delete', [
            'select' => [$widget1->id, $widget2->id],
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => __('message.deleted-successfully'),
            ]);

        $this->assertDatabaseMissing('widgets', ['id' => $widget1->id]);
        $this->assertDatabaseMissing('widgets', ['id' => $widget2->id]);
    }

    public function test_returns_error_when_delete_called_without_ids(): void
    {
        $response = $this->deleteJson('/widgets/delete', [
            'select' => [],
        ]);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'message' => __('message.select-a-row'),
            ]);
    }
}
