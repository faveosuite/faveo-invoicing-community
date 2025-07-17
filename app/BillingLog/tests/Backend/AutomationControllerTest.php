<?php

namespace App\BillingLog\tests\Backend;

use App\BillingLog\Controllers\AutomationController;
use App\BillingLog\Model\CronLog;
use App\BillingLog\Model\ExceptionLog;
use App\BillingLog\Model\LogCategory;
use App\BillingLog\Model\MailLog;
use App\Http\Controllers\Common\PhpMailController;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mail;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AutomationControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
    }

    #[Test]
    #[Group('validation')]
    public function test_validation_fails_when_required_fields_missing()
    {
        $response = $this->getJson('/log-category-list');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['date', 'log_type']);
    }

    #[Test]
    #[Group('validation')]
    public function test_validation_fails_when_invalid_log_type()
    {
        $response = $this->getJson('/log-category-list?date=2023-01-01&log_type=invalid');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['log_type']);
    }

    #[Test]
    #[Group('cron')]
    public function test_get_cron_commands_success()
    {
        $date = '2023-01-01';
        $command = 'TestCommand';

        CronLog::create([
            'command' => $command,
            'status' => 'success',
            'created_at' => Carbon::parse($date)->startOfDay(),
        ]);

        $response = $this->getJson("/log-category-list?date={$date}&log_type=cron");

        $response->assertStatus(200);
        $data = $response->json('data')[0];

        $this->assertEquals($command, $data['command']);
        $this->assertArrayHasKey('success', $data);
    }

    #[Test]
    #[Group('mail')]
    public function test_get_mail_category_log_success()
    {
        $date = '2023-01-01';

        $category = LogCategory::create(['name' => 'test_category']);

        $type = new TemplateType();
        $type->name = $category->name;
        $type->save();

        Template::create([
            'name' => 'Test Template',
            'type' => TemplateType::where('name', $category->name)->value('id'),
        ]);

        MailLog::create([
            'log_category_id' => $category->id,
            'status' => 'sent',
            'created_at' => Carbon::parse($date)->startOfDay(),
        ]);

        $response = $this->getJson("/log-category-list?date={$date}&log_type=mail");

        $response->assertStatus(200);
        $data = $response->json('data')[0];

        $this->assertEquals($category->id, $data['id']);
        $this->assertEquals('Test Template', $data['name']); // From Template
        $this->assertArrayHasKey('sent', $data);
    }

    #[Test]
    #[Group('exception')]
    public function test_get_exception_category_log_success()
    {
        $date = '2023-01-01';

        $category = LogCategory::create(['name' => 'exception_category']);

        ExceptionLog::create([
            'log_category_id' => $category->id,
            'created_at' => Carbon::parse($date)->startOfDay(),
        ]);

        $response = $this->getJson("/log-category-list?date={$date}&log_type=exception");

        $response->assertStatus(200);
        $data = $response->json('data')[0];

        $this->assertEquals($category->id, $data['id']);
        $this->assertEquals('exception_category', $data['name']);
        $this->assertEquals(1, $data['count']);
    }

    #[Test]
    #[Group('mail-dispatch')]
    public function test_dispatch_payload_success()
    {
        Mail::fake();

        $controller = new PhpMailController();
        $controller->sendEmail(
            from: 'test@example.com',
            to: 'user@example.com',
            template_data: 'Sample data',
            template_name: 'welcome_email',
            categoryName: 'test_category'
        );

        $mailLog = MailLog::latest()->first();
        $response = $this->getJson("retry/mail-log/{$mailLog->id}");

        // Assert response structure
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
        ]);

        // Assert values
        $this->assertTrue($response['success']);
        $this->assertEquals(
            trans('log::lang.queued_dispatch_successfully'),
            $response['message']
        );

        // Assert database record exists and payload is not null
        $this->assertNotNull($mailLog);
        $this->assertNotNull($mailLog->job_payload);
        $this->assertEquals('test_category', $mailLog->category->name);
    }

    #[Test]
    #[Group('mail-dispatch')]
    public function test_dispatch_payload_not_found()
    {
        $response = $this->getJson('retry/mail-log/999999'); // Non-existent ID

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('No query results', $response['message']);
    }

    #[Test]
    #[Group('controller-methods')]
    public function test_attempts_returns_default_value()
    {
        $controller = new AutomationController();
        $this->assertEquals(5, $controller->attempts());
    }

    #[Test]
    #[Group('controller-methods')]
    public function test_get_job_id_returns_null()
    {
        $controller = new AutomationController();
        $this->assertNull($controller->getJobId());
    }

    #[Test]
    #[Group('controller-methods')]
    public function test_get_raw_body_returns_set_value()
    {
        $controller = new AutomationController();
        $controller->rawBody = 'test raw body';
        $this->assertEquals('test raw body', $controller->getRawBody());
    }
}
