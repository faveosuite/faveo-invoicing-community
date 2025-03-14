<?php

namespace Tests\Unit;

use App\Http\Controllers\FreeTrailController;
use App\Model\Product\Product;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\DBTestCase;

class FreeTrialControllerTest extends DBTestCase
{
    public function test_firstLoginAttempt_return_exception_when_not_first_time_register_users()
    {
        $this->expectException(\Exception::class);
        $user = User::factory()->create(['role' => 'user', 'country' => 'IN']);
        Product::factory()->create();
        $auth = Auth::loginUsingId($user->id);
        $this->actingAs($user);
        $response = (new FreeTrailController())->firstLoginAttempt(new Request(['id' => $user->id, 'first_time_login' => 1]));
        $this->expectExceptionMessage('Can not Generate Freetrial Cloud instance');
        $response = $response->getOriginalContent();
        $this->assertFalse(auth()->check());
    }
}
