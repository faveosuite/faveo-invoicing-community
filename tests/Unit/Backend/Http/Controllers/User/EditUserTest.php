<?php

namespace Tests\Unit\Backend\Http\Controllers\User;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EditUserTest extends TestCase
{
    use DatabaseTransactions;

    #[Group('clientController')]
    public function test_client_controller_when_user_updates_details(): void
    {
        $user = User::factory()->create();
        $this->withoutMiddleware();
        $this->call('PATCH', 'clients/'.$user->id, ['first_name' => $user->first_name, 'last_name' => $user->last_name, 'email' => $user->email, 'user_name' => $user->user_name, 'company' => $user->company, 'bussiness' => $user->bussiness, 'active' => $user->active, 'mobile_verified' => $user->mobile_verified, 'role' => $user->role, 'position' => '', 'company_type' => $user->company_type, 'company_size' => $user->company_size, 'address' => $user->address, 'town' => $user->town, 'country' => 'IN', 'state' => $user->state, 'zip' => $user->zip, 'currency' => 'USD', 'mobile_code' => '91', 'mobile' => $user->mobile, 'skype' => '',
            'manager' => 'Ashutosh', 'timezone_id' => $user->timezone_id, ]);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
