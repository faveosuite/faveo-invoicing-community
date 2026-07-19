<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\Product;

use App\Http\Requests\Product\GroupRequest;
use Tests\TestCase;

class GroupRequestTest extends TestCase
{
    private function rules(): array
    {
        return (new GroupRequest())->rules();
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue((new GroupRequest())->authorize());
    }

    public function test_name_is_required(): void
    {
        $v = validator(['pricing_templates_id' => 1], $this->rules());
        $this->assertArrayHasKey('name', $v->errors()->toArray());
    }

    public function test_pricing_templates_id_is_required(): void
    {
        $v = validator(['name' => 'Support'], $this->rules());
        $this->assertArrayHasKey('pricing_templates_id', $v->errors()->toArray());
    }

    public function test_headline_is_optional(): void
    {
        $v = validator(['name' => 'Support', 'pricing_templates_id' => 1], $this->rules());
        $this->assertArrayNotHasKey('headline', $v->errors()->toArray());
    }

    public function test_status_must_be_boolean_when_present(): void
    {
        $v = validator(['name' => 'S', 'pricing_templates_id' => 1, 'status' => 'yes'], $this->rules());
        $this->assertArrayHasKey('status', $v->errors()->toArray());
    }

    public function test_seo_meta_fields_are_optional(): void
    {
        $v = validator(['name' => 'Support', 'pricing_templates_id' => 1], $this->rules());
        $errors = $v->errors()->toArray();

        $this->assertArrayNotHasKey('meta_title', $errors);
        $this->assertArrayNotHasKey('meta_description', $errors);
        $this->assertArrayNotHasKey('og_title', $errors);
        $this->assertArrayNotHasKey('og_description', $errors);
        $this->assertArrayNotHasKey('og_same_as_meta', $errors);
    }

    public function test_og_same_as_meta_must_be_boolean_when_present(): void
    {
        $v = validator(['name' => 'S', 'pricing_templates_id' => 1, 'og_same_as_meta' => 'yes'], $this->rules());
        $this->assertArrayHasKey('og_same_as_meta', $v->errors()->toArray());
    }

    public function test_og_image_rejects_a_disallowed_mime_type(): void
    {
        $file = \Illuminate\Http\UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf');

        $v = validator(['name' => 'S', 'pricing_templates_id' => 1, 'og_image' => $file], $this->rules());

        $this->assertArrayHasKey('og_image', $v->errors()->toArray());
    }

    public function test_og_image_accepts_an_allowed_image_type(): void
    {
        $file = \Illuminate\Http\UploadedFile::fake()->image('cover.jpg');

        $v = validator(['name' => 'S', 'pricing_templates_id' => 1, 'og_image' => $file], $this->rules());

        $this->assertArrayNotHasKey('og_image', $v->errors()->toArray());
    }
}
