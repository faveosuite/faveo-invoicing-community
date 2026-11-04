<?php

namespace Tests\Unit\Backend\Models;

use App\Model\User\AccountActivate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\DBTestCase;

class AccountActivateTest extends DBTestCase
{
    use DatabaseTransactions;

    public function test_codes_are_stored_hashed_and_checked_with_matches(): void
    {
        $email = 'code-'.uniqid().'@example.com';
        AccountActivate::create(['email' => $email, 'token' => 482913]);

        $this->assertNotSame('482913', DB::table('account_activates')->where('email', $email)->value('token'));

        $record = AccountActivate::where('email', $email)->first();
        $this->assertTrue($record->matches('482913'));
        $this->assertFalse($record->matches('000000'));
    }

    public function test_a_code_saved_before_hashing_never_matches_and_does_not_throw(): void
    {
        $email = 'legacy-'.uniqid().'@example.com';
        DB::table('account_activates')->insert(['email' => $email, 'token' => '482913', 'created_at' => now(), 'updated_at' => now()]);

        $this->assertFalse(AccountActivate::where('email', $email)->first()->matches('482913'));
    }
}
