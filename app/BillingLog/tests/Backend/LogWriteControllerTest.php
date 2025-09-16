<?php

namespace App\BillingLog\tests\Backend;

use App\BillingLog\Controllers\LogWriteController;
use App\BillingLog\Model\CronLog;
use App\BillingLog\Model\ExceptionLog;
use App\BillingLog\Model\MailLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class LogWriteControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected LogWriteController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new LogWriteController();
    }

    /** @test */
    public function test_logs_a_cron_start()
    {
        $log = $this->controller->cron('my:command', 'Test description');
        $this->assertInstanceOf(CronLog::class, $log);
        $this->assertDatabaseHas('cron_logs', [
            'command' => 'my:command',
            'status' => 'running',
        ]);
    }

    /** @test */
    public function test_marks_a_cron_as_completed()
    {
        $log = CronLog::create([
            'command' => 'my:command',
            'description' => 'test',
            'status' => 'running',
        ]);

        Carbon::setTestNow(now()->addSeconds(5));
        $this->controller->cronCompleted($log->id);

        $this->assertDatabaseHas('cron_logs', [
            'id' => $log->id,
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function test_marks_a_cron_as_failed_with_exception()
    {
        $log = CronLog::create([
            'command' => 'my:command',
            'description' => 'test',
            'status' => 'running',
        ]);

        Carbon::setTestNow(now()->addSeconds(10));
        $this->controller->cronFailed($log->id, new Exception('Cron failed'));

        $this->assertDatabaseHas('cron_logs', [
            'id' => $log->id,
            'status' => 'failed',
        ]);
        $this->assertDatabaseHas('exception_logs', [
            'message' => 'Cron failed',
        ]);
    }

    /** @test */
    public function test_logs_exceptions_under_category()
    {
        $exception = new Exception('Something went wrong');
        $log = $this->controller->exception($exception, 'custom-category');

        $this->assertInstanceOf(ExceptionLog::class, $log);
        $this->assertDatabaseHas('exception_logs', [
            'message' => 'Something went wrong',
        ]);
        $this->assertDatabaseHas('log_categories', [
            'name' => 'custom-category',
        ]);
    }

    /** @test */
    public function test_logs_mail_successfully()
    {
        $log = $this->controller->logMailByCategory(
            'sender@example.com',
            'receiver@example.com',
            ['cc@example.com'],
            ['bcc@example.com'],
            'Test Subject',
            'Test Body',
            'mail-category'
        );

        $this->assertInstanceOf(MailLog::class, $log);
        $this->assertDatabaseHas('mail_logs', [
            'sender_mail' => 'sender@example.com',
            'receiver_mail' => 'receiver@example.com',
            'subject' => 'Test Subject',
            'status' => 'queued',
        ]);
    }

    /** @test */
    public function test_formats_mail_addresses_correctly()
    {
        $addresses = [
            ['address' => 'cc1@example.com'],
            ['name' => 'John Doe', 'address' => 'cc2@example.com'],
        ];
        $formatted = $this->invokeMethod($this->controller, 'formatAddresses', [$addresses]);
        $this->assertEquals('cc1@example.com, John Doe <cc2@example.com>', $formatted);
    }

    /** @test */
    public function test_marks_outgoing_mail_as_sent()
    {
        $log = MailLog::create(['status' => 'queued']);
        $this->controller->outgoingMailSent($log->id);
        $this->assertDatabaseHas('mail_logs', [
            'id' => $log->id,
            'status' => 'sent',
        ]);
    }

    /** @test */
    public function test_marks_outgoing_mail_as_failed_and_logs_exception()
    {
        $log = MailLog::create(['status' => 'queued']);
        $this->controller->outgoingMailFailed($log->id, new Exception('Mail failed'));

        $this->assertDatabaseHas('mail_logs', [
            'id' => $log->id,
            'status' => 'failed',
        ]);
        $this->assertDatabaseHas('exception_logs', [
            'message' => 'Mail failed',
        ]);
    }

    /** @test */
    public function test_deletes_logs_by_type_and_date_range()
    {
        $cronLog = CronLog::create();
        $exceptionLog = ExceptionLog::create();
        $mailLog = MailLog::create();

        $request = new Request([
            'from_date' => now()->subDay()->toDateString(),
            'to_date' => now()->addDay()->toDateString(),
            'log_types' => ['cron', 'exception', 'mail'],
        ]);

        $response = $this->controller->deleteLogs($request);
        $this->assertEquals('Logs deleted successfully', $response->getData()->message);

        $this->assertDatabaseMissing('cron_logs', ['id' => $cronLog->id]);
        $this->assertDatabaseMissing('exception_logs', ['id' => $exceptionLog->id]);
        $this->assertDatabaseMissing('mail_logs', ['id' => $mailLog->id]);
    }

    /** @test */
    public function delete_logs_validates_request()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $request = new Request([
            'log_types' => ['invalid-type'],
        ]);
        $this->controller->deleteLogs($request);
    }

    /**
     * Helper to call protected/private methods.
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
