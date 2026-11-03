<?php

namespace Tests\Unit\Backend\Http\Controllers\Common;

use App\Facades\Attach;
use App\Http\Controllers\Common\AuthBrandController;
use App\Model\Common\CommonSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\DBTestCase;

class AuthBrandControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
        CommonSettings::where('option_name', 'auth_brand')->delete();
    }

    public function test_defaults_when_nothing_is_saved(): void
    {
        // Empty text + null image = the client uses the translated defaults and built-in illustration.
        $this->assertSame(
            ['show' => true, 'title' => '', 'description' => '', 'tagline' => '', 'image' => null],
            AuthBrandController::settings()
        );
        $this->getJson('/auth-brand')->assertOk()->assertJsonPath('data.show', true);
    }

    public function test_update_saves_text_and_visibility(): void
    {
        $this->post('/auth-brand', [
            'show' => 0, 'title' => '  Hello  ', 'description' => 'Desc', 'tagline' => '', 'use_default_image' => 1,
        ])->assertOk()->assertJson(['success' => true]);

        $settings = AuthBrandController::settings();
        $this->assertFalse($settings['show']);
        $this->assertSame('Hello', $settings['title']); // trimmed
        $this->assertSame('Desc', $settings['description']);
        $this->assertSame('', $settings['tagline']);
        $this->assertNull($settings['image']);
    }

    public function test_uploaded_image_is_stored_and_resolved_to_a_url(): void
    {
        Attach::shouldReceive('put')->once()->andReturn('images/brand-abc.png');
        Attach::shouldReceive('getUrlPath')->with('images/brand-abc.png')->andReturn('https://cdn.test/brand-abc.png');

        $this->post('/auth-brand', ['show' => 1, 'image' => UploadedFile::fake()->image('brand.png')])->assertOk();

        $this->assertSame('https://cdn.test/brand-abc.png', AuthBrandController::settings()['image']);
    }

    public function test_use_default_image_clears_a_saved_image(): void
    {
        CommonSettings::upsert([['option_name' => 'auth_brand', 'optional_field' => 'image', 'option_value' => 'old.png', 'status' => '']], ['option_name', 'optional_field'], ['option_value']);

        $this->post('/auth-brand', ['show' => 1, 'use_default_image' => 1])->assertOk();

        $this->assertNull(AuthBrandController::settings()['image']);
    }

    public function test_svg_upload_is_rejected(): void
    {
        $this->withHeaders(['Accept' => 'application/json'])
            ->post('/auth-brand', ['show' => 1, 'image' => UploadedFile::fake()->create('x.svg', 1, 'image/svg+xml')])
            ->assertStatus(412); // this app reports validation failures as 412
    }

    public function test_non_admin_cannot_read_or_update(): void
    {
        $this->withMiddleware();
        $this->getLoggedInUser('user');

        $this->assertNotSame(200, $this->getJson('/auth-brand')->status());
        $this->assertNotSame(200, $this->postJson('/auth-brand', ['show' => 0])->status());
    }
}
