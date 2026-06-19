<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Requests\Email\EmailSettingRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class EmailSettingsTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        $this->assertTrue(condition: true);
    }

    public function test_smtp_driver_correct_fields(): void
    {
        $data = ['driver' => 'smtp', 'password' => 'password'];
        $request = new EmailSettingRequest;
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
    }
}
