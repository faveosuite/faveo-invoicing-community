<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\User;

use App\Http\Requests\UpdateStoragePathRequest;
use Tests\TestCase;

class UpdateStoragePathRequestTest extends TestCase
{
    private function rules(): array
    {
        return (new UpdateStoragePathRequest())->rules();
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue((new UpdateStoragePathRequest())->authorize());
    }

    public function test_disk_is_required(): void
    {
        $v = validator([], $this->rules());
        $this->assertArrayHasKey('disk', $v->errors()->toArray());
    }

    public function test_validation_passes_with_disk_only(): void
    {
        $v = validator(['disk' => 'local'], $this->rules());
        $this->assertFalse($v->fails());
    }

    public function test_path_is_optional(): void
    {
        $v = validator(['disk' => 'local'], $this->rules());
        $this->assertArrayNotHasKey('path', $v->errors()->toArray());
    }

    public function test_messages_returns_non_empty_array(): void
    {
        $this->assertNotEmpty((new UpdateStoragePathRequest())->messages());
    }
}
