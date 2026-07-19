<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\Front;

use App\Http\Requests\Front\PageRequest;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PageRequestTest extends TestCase
{
    private function rules(string $method = 'POST'): array
    {
        $request = new PageRequest();
        $request->setMethod($method);

        return $request->rules();
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue((new PageRequest())->authorize());
    }

    public function test_name_slug_and_content_are_required_on_create(): void
    {
        $v = validator([], $this->rules('POST'));
        $errors = $v->errors()->toArray();

        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('slug', $errors);
        $this->assertArrayHasKey('content', $errors);
    }

    public function test_name_slug_and_content_are_optional_on_update(): void
    {
        $v = validator([], $this->rules('PUT'));
        $errors = $v->errors()->toArray();

        $this->assertArrayNotHasKey('name', $errors);
        $this->assertArrayNotHasKey('slug', $errors);
        $this->assertArrayNotHasKey('content', $errors);
    }

    public function test_seo_meta_fields_are_optional_on_create(): void
    {
        $v = validator([
            'name' => 'About',
            'slug' => 'about',
            'content' => 'Body',
        ], $this->rules('POST'));
        $errors = $v->errors()->toArray();

        $this->assertArrayNotHasKey('meta_title', $errors);
        $this->assertArrayNotHasKey('meta_description', $errors);
        $this->assertArrayNotHasKey('og_title', $errors);
        $this->assertArrayNotHasKey('og_description', $errors);
        $this->assertArrayNotHasKey('og_same_as_meta', $errors);
    }

    public function test_og_same_as_meta_must_be_boolean_when_present(): void
    {
        $v = validator([
            'name' => 'About',
            'slug' => 'about',
            'content' => 'Body',
            'og_same_as_meta' => 'yes',
        ], $this->rules('POST'));

        $this->assertArrayHasKey('og_same_as_meta', $v->errors()->toArray());
    }

    public function test_og_image_rejects_a_disallowed_mime_type(): void
    {
        $file = UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf');

        $v = validator([
            'name' => 'About',
            'slug' => 'about',
            'content' => 'Body',
            'og_image' => $file,
        ], $this->rules('POST'));

        $this->assertArrayHasKey('og_image', $v->errors()->toArray());
    }

    public function test_og_image_accepts_an_allowed_image_type(): void
    {
        $file = UploadedFile::fake()->image('cover.jpg');

        $v = validator([
            'name' => 'About',
            'slug' => 'about',
            'content' => 'Body',
            'og_image' => $file,
        ], $this->rules('POST'));

        $this->assertArrayNotHasKey('og_image', $v->errors()->toArray());
    }
}
