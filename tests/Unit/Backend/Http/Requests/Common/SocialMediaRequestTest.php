<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\Common;

use App\Http\Requests\Common\SocialMediaRequest;
use Tests\TestCase;

class SocialMediaRequestTest extends TestCase
{
    public function test_rules_returns_empty_array_for_get_method(): void
    {
        $request = new SocialMediaRequest();
        // Default method on a blank FormRequest is GET — hits the fallback return []
        $this->assertSame([], $request->rules());
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue((new SocialMediaRequest())->authorize());
    }
}
