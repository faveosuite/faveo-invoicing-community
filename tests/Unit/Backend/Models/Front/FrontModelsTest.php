<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Models\Front;

use App\Model\Front\FrontendPage;
use App\Model\Front\Widgets;
use Tests\TestCase;

class FrontModelsTest extends TestCase
{
    // ───────────── Widgets ─────────────

    public function test_widgets_table_name(): void
    {
        $this->assertSame('widgets', (new Widgets())->getTable());
    }

    public function test_widgets_fillable(): void
    {
        $model = new Widgets();
        $this->assertContains('name', $model->getFillable());
        $this->assertContains('type', $model->getFillable());
        $this->assertContains('publish', $model->getFillable());
        $this->assertContains('content', $model->getFillable());
        $this->assertContains('allow_tweets', $model->getFillable());
        $this->assertContains('allow_mailchimp', $model->getFillable());
        $this->assertContains('allow_social_media', $model->getFillable());
    }

    public function test_widgets_get_mappings(): void
    {
        $model = new Widgets();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('name', $mappings);
        $this->assertArrayHasKey('publish', $mappings);
        $this->assertArrayHasKey('allow_tweets', $mappings);
    }

    public function test_widgets_mapping_active_inactive_callbacks(): void
    {
        $model = new Widgets();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $active = $mappings['publish'][1](1);
        $this->assertNotNull($active);
        $inactive = $mappings['publish'][1](0);
        $this->assertNotNull($inactive);
    }

    // ───────────── FrontendPage ─────────────

    public function test_frontend_page_table_name(): void
    {
        $this->assertSame('frontend_pages', (new FrontendPage())->getTable());
    }

    public function test_frontend_page_fillable(): void
    {
        $model = new FrontendPage();
        $this->assertContains('parent_page_id', $model->getFillable());
        $this->assertContains('slug', $model->getFillable());
        $this->assertContains('name', $model->getFillable());
        $this->assertContains('content', $model->getFillable());
        $this->assertContains('url', $model->getFillable());
        $this->assertContains('publish', $model->getFillable());
        $this->assertContains('type', $model->getFillable());
    }

    public function test_frontend_page_get_mappings(): void
    {
        $model = new FrontendPage();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('slug', $mappings);
        $this->assertArrayHasKey('name', $mappings);
        $this->assertArrayHasKey('publish', $mappings);
    }

    public function test_frontend_page_slug_mutator_strips_spaces(): void
    {
        $model = new FrontendPage();
        $model->slug = 'hello world';
        $this->assertSame('helloworld', $model->getAttributes()['slug']);
    }

    public function test_frontend_page_mapping_publish_callbacks(): void
    {
        $model = new FrontendPage();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $active = $mappings['publish'][1](1);
        $this->assertNotNull($active);
        $inactive = $mappings['publish'][1](0);
        $this->assertNotNull($inactive);
    }
}
