<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\Queue;

use App\Http\Requests\Queue\QueueRequest;
use Tests\TestCase;

class QueueRequestTest extends TestCase
{
    public function test_authorize_returns_true(): void
    {
        $this->assertTrue((new QueueRequest())->authorize());
    }

    public function test_set_rule_returns_input_required_when_empty(): void
    {
        $rules = (new QueueRequest())->setRule([]);

        $this->assertSame(['input' => 'required'], $rules);
    }

    public function test_set_rule_maps_each_key_to_required(): void
    {
        $rules = (new QueueRequest())->setRule(['driver' => 'database', 'queue' => 'default']);

        $this->assertArrayHasKey('driver', $rules);
        $this->assertArrayHasKey('queue', $rules);
        $this->assertArrayNotHasKey('input', $rules);
        $this->assertSame('required', $rules['driver']);
    }

    public function test_rules_calls_set_rule_with_request_data(): void
    {
        $request = new QueueRequest();
        $request->merge(['driver' => 'database']);
        $rules = $request->rules();

        $this->assertArrayHasKey('driver', $rules);
    }
}
