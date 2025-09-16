<?php

namespace App\BillingLog\tests\Backend;

use App\BillingLog\Controllers\LogWriteController;
use App\BillingLog\Model\CronLog;
use App\BillingLog\Model\LogCategory;
use Carbon\Carbon;
use Exception;
use Tests\DBTestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;

class LogViewControllerTest extends DBTestCase
{
    protected $defaultCategoryId;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        $this->defaultCategoryId = LogCategory::firstOrCreate(['name' => 'default'])->id;
    }

    /** ----------------------- Exception Logs ----------------------- */

    #[Test]
    #[Group('exception-logs')]
    public function test_exceptionLogs_withoutFilters()
    {
        \Logger::exception(new Exception('test_exception_1'));
        \Logger::exception(new Exception('test_exception_2'));

        $response = $this->postJson('/logs/exception', $this->defaultExceptionPayload());

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['message' => 'test_exception_1'])
            ->assertJsonFragment(['message' => 'test_exception_2']);
    }

    #[Test]
    #[Group('exception-logs')]
    public function test_exceptionLogs_withSearchQuery()
    {
        \Logger::exception(new Exception('test_exception_1'));
        \Logger::exception(new Exception('test_exception_2'));

        $payload = $this->defaultExceptionPayload(['search' => ['value' => 'test_exception_1']]);

        $response = $this->postJson('/logs/exception', $payload);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['message' => 'test_exception_1']);
    }

    #[Test]
    #[Group('exception-logs')]
    public function test_exceptionLogs_withLimit()
    {
        foreach (range(1, 5) as $i) {
            \Logger::exception(new Exception("test_exception_$i"));
        }

        $payload = $this->defaultExceptionPayload(['length' => 3]);

        $response = $this->postJson('/logs/exception', $payload);

        $response->assertStatus(200)->assertJsonCount(3, 'data');
    }

    #[Test]
    #[Group('exception-logs')]
    public function test_exceptionLogs_withFutureDateSearch()
    {
        \Logger::exception(new Exception('test_exception_1'));

        $payload = $this->defaultExceptionPayload(['search' => ['value' => '3000-11-27']]);

        $response = $this->postJson('/logs/exception', $payload);

        $response->assertStatus(200)->assertJsonCount(0, 'data');
    }

    #[Test]
    #[Group('exception-logs')]
    public function test_exceptionLogs_withCategoryFilter()
    {
        $cat1 = LogCategory::create(['name' => 'test_category_1']);
        $cat2 = LogCategory::create(['name' => 'test_category_2']);

        \Logger::exception(new Exception('exception_one'), $cat1->name);
        \Logger::exception(new Exception('exception_two'), $cat1->name);
        \Logger::exception(new Exception('exception_two'), $cat2->name);

        $payload = $this->defaultExceptionPayload(['category' => $cat1->id]);

        $response = $this->postJson('/logs/exception', $payload);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['message' => 'exception_one'])
            ->assertJsonFragment(['message' => 'exception_two']);
    }

    /** ----------------------- Cron Logs ----------------------- */

    #[Test]
    #[Group('cron-logs')]
    public function test_cronLogs_withCategoryAndStatus()
    {
        LogCategory::create(['name' => 'database:sync']);
        $cronLog = \Logger::cron("database:sync", "Update DB to latest version");
        \Logger::cron("testing-setup", "Create an testing environment");
        \Logger::cronCompleted($cronLog->id);

        $payload = $this->defaultCronPayload(['category' => 'database:sync', 'status' => 'completed']);

        $response = $this->postJson('/logs/cron', $payload);

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }

    #[Test]
    #[Group('cron-logs')]
    public function test_cronLogs_withLimit()
    {
        $log1 = \Logger::cron("database:sync", "Update DB to latest version");
        $log2 = \Logger::cron("database:sync", "Update DB to latest version");

        \Logger::cronCompleted($log1->id);
        \Logger::cronCompleted($log2->id);

        $payload = $this->defaultCronPayload(['length' => 1]);

        $response = $this->postJson('/logs/cron', $payload);

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }

    #[Test]
    #[Group('cron-logs')]
    public function test_cronLogs_withCreatedAtFilter()
    {
        $log1 = \Logger::cron("database:sync", "Update DB to latest version");
        $log2 = \Logger::cron("database:sync", "Update DB to latest version");

        CronLog::where('id', $log1->id)->update(['created_at' => Carbon::now()->subDay()]);

        \Logger::cronCompleted($log1->id);
        \Logger::cronCompleted($log2->id);

        $response = $this->postJson('/logs/cron', $this->defaultCronPayload());

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }

    /** ----------------------- Mail Logs ----------------------- */

    #[Test]
    #[Group('mail-logs')]
    public function test_mailLogs_withoutFilters()
    {
        $log = $this->logMailByCategory();

        $payload = $this->defaultMailPayload(['category' => $log->log_category_id]);

        $response = $this->postJson('/logs/mail', $payload);

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }

    #[Test]
    #[Group('mail-logs')]
    public function test_mailLogs_withSearchQuery()
    {
        $log = $this->logMailByCategory('', '', [], [], 'First Subject');
        $categoryName = LogCategory::find($log->log_category_id)->name;

        $this->logMailByCategory('', 'test1@gmail.com', [], [], 'Second Subject', 'queued', $categoryName);
        $this->logMailByCategory('', 'test2@gmail.com', [], [], 'Third Subject', 'queued', $categoryName);

        $payload = $this->defaultMailPayload([
            'category' => $log->log_category_id,
            'search'   => ['value' => 'test1@gmail.com'],
        ]);

        $response = $this->postJson('/logs/mail', $payload);

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }

    #[Test]
    #[Group('mail-logs')]
    public function test_mailLogs_withLimit()
    {
        $log = $this->logMailByCategory('', '', [], [], 'First Subject');
        $categoryName = LogCategory::find($log->log_category_id)->name;

        foreach (['a@gmail.com', 'b@gmail.com', 'c@gmail.com', 'd@gmail.com'] as $mail) {
            $this->logMailByCategory('', $mail, [], [], 'Some Subject', 'queued', $categoryName);
        }

        $payload = $this->defaultMailPayload([
            'category' => $log->log_category_id,
            'length'   => 3,
        ]);

        $response = $this->postJson('/logs/mail', $payload);

        $response->assertStatus(200)->assertJsonCount(3, 'data');
    }

    /** ----------------------- Helpers ----------------------- */

    private function defaultExceptionPayload(array $overrides = []): array
    {
        return array_merge([
            'draw' => 1,
            'columns' => $this->defaultColumns(['file', 'line', 'message', 'trace', 'created_at']),
            'order' => [['column' => 0, 'dir' => 'asc']],
            'start' => 0,
            'length' => 10,
            'search' => ['value' => '', 'regex' => false],
            'date' => Carbon::now()->toDateString(),
            'category' => $this->defaultCategoryId,
            'log_type' => 'exception',
        ], $overrides);
    }

    private function defaultCronPayload(array $overrides = []): array
    {
        return array_merge([
            'start' => 0,
            'length' => 10,
            'date' => Carbon::now()->toDateString(),
            'category' => 'database:sync',
            'status' => 'completed'
        ], $overrides);
    }

    private function defaultMailPayload(array $overrides = []): array
    {
        return array_merge([
            'draw' => 1,
            'columns' => $this->defaultColumns(['sender_mail', 'receiver_mail', 'carbon_copy', 'blind_carbon_copy', 'subject', 'created_at', 'updated_at', 'status']),
            'order' => [['column' => 0, 'dir' => 'asc']],
            'start' => 0,
            'length' => 10,
            'search' => ['value' => '', 'regex' => false],
            'date' => Carbon::now()->toDateString(),
            'category' => $this->defaultCategoryId,
            'log_type' => 'mail',
            'status' => 'queued',
        ], $overrides);
    }

    private function defaultColumns(array $fields): array
    {
        return collect($fields)->map(fn($f) => [
            'data' => $f,
            'searchable' => true,
            'orderable' => true,
            'search' => ['value' => '', 'regex' => false]
        ])->toArray();
    }

    private function logMailByCategory(
        $senderMail = 'test@sender.com',
        $receiverMail = 'receiver@example.com',
        $cc = [],
        $bcc = [],
        $subject = 'Test Subject',
        $status = 'queued',
        $categoryName = 'test_category'
    ) {
        return (new LogWriteController())->logMailByCategory(
            $senderMail,
            $receiverMail,
            $cc,
            $bcc,
            $subject,
            'This is a test email body.',
            $categoryName,
            $status
        );
    }
}