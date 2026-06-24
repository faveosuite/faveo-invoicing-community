<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Models;

use App\BaseModel;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BaseModelTest extends TestCase
{
    public function test_set_attribute_creates_purifier_dir_when_missing(): void
    {
        // Simulate missing serializer directory to hit the makeDirectory branch
        File::shouldReceive('exists')->once()->andReturn(false);
        File::shouldReceive('makeDirectory')->once()->andReturn(true);

        $model = new class extends BaseModel {
            protected $table = 'users';
        };

        // setAttribute with a plain string value (no HTML) — purifier returns same value
        $model->setAttribute('name', 'plain text');

        $this->assertSame('plain text', $model->getAttributes()['name']);
    }

    public function test_set_attribute_purifies_html_value(): void
    {
        $model = new class extends BaseModel {
            protected $table = 'users';
        };

        $model->setAttribute('name', '<b>bold</b>');

        // HTMLPurifier strips <b> tag by default config
        $this->assertStringNotContainsString('<script', $model->getAttributes()['name']);
    }

    public function test_set_attribute_skips_purify_for_excluded_properties(): void
    {
        $model = new class extends BaseModel {
            protected $table = 'products';
        };

        $html = '<p>Some <b>description</b></p>';
        $model->setAttribute('description', $html);

        $this->assertSame($html, $model->getAttributes()['description']);
    }
}
