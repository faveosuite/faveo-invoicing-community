<?php

namespace Tests\Unit\Client\Account;

use App\User;
use Hash;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Storage;
use Tests\DBTestCase;

class ProfileTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        self::withoutMiddleware();
        self::getLoggedInUser();
    }

    public function test_postProfile_successful_update()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->image('profile.jpg');

        $response = $this->patchJson('/my-profile', [
            'profile_pic' => $file,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'company' => 'Test Company',
            'mobile' => '1234567890',
            'address' => '123 Street',
            'country' => 'In',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'first_name' => 'John',
        ]);
    }

    public function test_postProfile_validation_failure()
    {
        $response = $this->patchJson('/my-profile', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors([
            'first_name',
            'last_name',
            'mobile',
            'email',
            'company',
            'address',
            'country',
        ]);

    }

    public function test_postPassword_successful_update()
    {
        $this->user->update(['password' => Hash::make('oldpassword')]);
        $response = $this->patchJson('/my-password', [
            'old_password' => 'oldpassword',
            'new_password' => 'Newpassword@123',
            'confirm_password' => 'Newpassword@123',
        ]);

        $response->assertStatus(200);
        $this->assertTrue(Hash::check('Newpassword@123', $this->user->fresh()->password));
    }

    public function test_postPassword_incorrect_old_password()
    {
        $response = $this->patchJson('/my-password', [
            'old_password' => 'wrongpassword',
            'new_password' => 'Newpassword@123',
            'confirm_password' => 'Newpassword@123',
        ]);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Incorrect old password']);
    }

    public function test_postPassword_with_short_new_password()
    {
        $response = $this->patchJson('/my-password', [
            'old_password' => 'oldpassword',
            'new_password' => '123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors([
            'new_password',
            'confirm_password',
        ]);
    }


    public function test_my_profile_successful_update(){
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->call('PATCH', 'my-profile', [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email'=>'santhanu@gmail.com',
            'company' => $user->company,
            'country' => $user->country,
            'mobile' => $user->mobile,
            'address' => $user->address,
        ]);
        $response->assertContent('{"success":true,"message":"Updated Successfully"}');
        $response->assertJsonStructure([
            'success','message']);

    }

    public function test_when_mandatory_field_not_filled(){
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->call('PATCH', 'my-profile', [
            'last_name' => $user->last_name,
            'email'=>'santhanu@gmail.com',
            'company' => $user->company,
            'country' => $user->country,
            'mobile' => $user->mobile,
            'address' => $user->address,
        ]);
        $response->assertSessionHasErrors(['first_name'=>"The first name field is required."]);
    }

    public function test_when_email_already_present(){
        $user1 = User::factory()->create(['email'=>'santhanu@gmail.com']);
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->call('PATCH', 'my-profile', [
            'first_name' => $user1->first_name,
            'last_name' => $user->last_name,
            'email'=>'santhanu@gmail.com',
            'company' => $user->company,
            'country' => $user->country,
            'mobile' => $user->mobile,
            'address' => $user->address,
        ]);
        $response->assertSessionHasErrors(['email'=>"The email address has already been taken. Please choose a different email."]);
    }

    public function test_when_2fa_update_recovery_code(){
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->call('POST', '2fa-recovery-code');
        $content=$response->json();
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success','message']);
        $this->assertEquals(true, $content['success']);
        $this->assertTrue(20==strlen($content['message']['code']));
    }

    public function test_when_2fa_verify_password(){
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->call('GET', 'verify-password',['user_password'=>'password','login_type'=>'login']);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success','message']);
    }

    public function test_when_password_is_wrong(){
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->call('GET', 'verify-password',['user_password'=>'passwor','login_type'=>'login']);
        $content=$response->json();
        $response->assertStatus(400);
        $this->assertEquals('password_incorrect',$content['message']);
    }


}
