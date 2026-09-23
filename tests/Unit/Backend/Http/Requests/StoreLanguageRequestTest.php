<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests;

use App\Http\Requests\StoreLanguageRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreLanguageRequestTest extends TestCase
{
    public function test_invalid_language_fails_validation(): void
    {
        $rules = (new StoreLanguageRequest())->rules();
        $validator = Validator::make(['language' => 'nonexistent_lang_xyz'], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('language', $validator->errors()->toArray());
    }

    public function test_language_required(): void
    {
        $rules = (new StoreLanguageRequest())->rules();
        $validator = Validator::make([], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('language', $validator->errors()->toArray());
    }

    public function test_messages_returns_array(): void
    {
        $messages = (new StoreLanguageRequest())->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('language.required', $messages);
    }
}
