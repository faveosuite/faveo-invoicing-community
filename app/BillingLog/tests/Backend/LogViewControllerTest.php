<?php

namespace App\BillingLog\tests\Backend;

use App\BillingLog\Controllers\LogWriteController;
use App\BillingLog\Model\CronLog;
use App\BillingLog\Model\ExceptionLog;
use App\BillingLog\Model\LogCategory;
use App\BillingLog\Model\MailLog;
use App\Model\Order\Order;
use App\Payment_log;
use App\User;
use DB;
use Exception;
use Illuminate\Support\Facades\Date;
use Logger;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Activitylog\Models\Activity;
use Str;
use Tests\DBTestCase;

class LogViewControllerTest extends DBTestCase
{
    protected int $defaultCategoryId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        $this->defaultCategoryId = LogCategory::firstOrCreate(['name' => 'default'])->id;
    }

    /** ----------------------- Exception Logs ----------------------- */
    #[Test]
    #[Group('exception-logs')]
    public function test_exceptionLogs_withoutFilters(): void
    {
        Logger::exception(new Exception('test_exception_1'));
        Logger::exception(new Exception('test_exception_2'));

        $response = $this->postJson('/logs/exception', $this->defaultExceptionPayload());

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.data')
            ->assertJsonFragment(['message' => 'test_exception_1'])
            ->assertJsonFragment(['message' => 'test_exception_2']);
    }

    #[Test]
    #[Group('exception-logs')]
    public function test_exceptionLogs_withSearchQuery(): void
    {
        Logger::exception(new Exception('test_exception_1'));
        Logger::exception(new Exception('test_exception_2'));

        $payload = $this->defaultExceptionPayload(['search-query' => 'test_exception_1']);

        $response = $this->postJson('/logs/exception', $payload);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonFragment(['message' => 'test_exception_1']);
    }

    #[Test]
    #[Group('exception-logs')]
    public function test_exceptionLogs_withLimit(): void
    {
        foreach (range(1, 5) as $i) {
            Logger::exception(new Exception('test_exception_'.$i));
        }

        $payload = $this->defaultExceptionPayload(['limit' => 3]);

        $response = $this->postJson('/logs/exception', $payload);

        $response->assertStatus(200)->assertJsonCount(3, 'data.data');
    }

    #[Test]
    #[Group('exception-logs')]
    public function test_exceptionLogs_withFutureDateSearch(): void
    {
        Logger::exception(new Exception('test_exception_1'));

        $payload = $this->defaultExceptionPayload(['search-query' => '3000-11-27']);

        $response = $this->postJson('/logs/exception', $payload);

        $response->assertStatus(200)->assertJsonCount(0, 'data.data');
    }

    #[Test]
    #[Group('exception-logs')]
    public function test_exceptionLogs_withCategoryFilter(): void
    {
        $cat1 = LogCategory::create(['name' => 'test_category_1']);
        $cat2 = LogCategory::create(['name' => 'test_category_2']);

        Logger::exception(new Exception('exception_one'), $cat1->name);
        Logger::exception(new Exception('exception_two'), $cat1->name);
        Logger::exception(new Exception('exception_two'), $cat2->name);

        $payload = $this->defaultExceptionPayload(['category' => $cat1->id]);

        $response = $this->postJson('/logs/exception', $payload);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.data')
            ->assertJsonFragment(['message' => 'exception_one'])
            ->assertJsonFragment(['message' => 'exception_two']);
    }

    /** ----------------------- Cron Logs ----------------------- */
    #[Test]
    #[Group('cron-logs')]
    public function test_cronLogs_withCategoryAndStatus(): void
    {
        LogCategory::create(['name' => 'database:sync']);
        $cronLog = Logger::cron('database:sync', 'Update DB to latest version');
        Logger::cron('testing-setup', 'Create an testing environment');
        Logger::cronCompleted($cronLog->id);

        $payload = $this->defaultCronPayload(['category' => 'database:sync', 'status' => 'completed']);

        $response = $this->postJson('/logs/cron', $payload);

        $response->assertStatus(200)->assertJsonCount(1, 'data.data');
    }

    #[Test]
    #[Group('cron-logs')]
    public function test_cronLogs_withLimit(): void
    {
        $log1 = Logger::cron('database:sync', 'Update DB to latest version');
        $log2 = Logger::cron('database:sync', 'Update DB to latest version');

        Logger::cronCompleted($log1->id);
        Logger::cronCompleted($log2->id);

        $payload = $this->defaultCronPayload(['limit' => 1]);

        $response = $this->postJson('/logs/cron', $payload);

        $response->assertStatus(200)->assertJsonCount(1, 'data.data');
    }

    #[Test]
    #[Group('cron-logs')]
    public function test_cronLogs_withCreatedAtFilter(): void
    {
        $log1 = Logger::cron('database:sync', 'Update DB to latest version');
        $log2 = Logger::cron('database:sync', 'Update DB to latest version');

        CronLog::where('id', $log1->id)->update(['created_at' => Date::now()->subDay()]);

        Logger::cronCompleted($log1->id);
        Logger::cronCompleted($log2->id);

        $response = $this->postJson('/logs/cron', $this->defaultCronPayload());

        $response->assertStatus(200)->assertJsonCount(1, 'data.data');
    }

    /** ----------------------- Mail Logs ----------------------- */
    #[Test]
    #[Group('mail-logs')]
    public function test_mailLogs_withoutFilters(): void
    {
        $log = $this->logMailByCategory();

        $payload = $this->defaultMailPayload(['category' => $log->log_category_id]);

        $response = $this->postJson('/logs/mail', $payload);

        $response->assertStatus(200)->assertJsonCount(1, 'data.data');
    }

    #[Test]
    #[Group('mail-logs')]
    public function test_mailLogs_withSearchQuery(): void
    {
        $log = $this->logMailByCategory('', '', [], [], 'First Subject');
        $categoryName = LogCategory::find($log->log_category_id)->name;

        $this->logMailByCategory('', 'test1@gmail.com', [], [], 'Second Subject', $categoryName);
        $this->logMailByCategory('', 'test2@gmail.com', [], [], 'Third Subject', $categoryName);

        $payload = $this->defaultMailPayload([
            'category' => $log->log_category_id,
            'search-query' => 'test1@gmail.com',
        ]);

        $response = $this->postJson('/logs/mail', $payload);

        $response->assertStatus(200)->assertJsonCount(1, 'data.data');
    }

    #[Test]
    #[Group('mail-logs')]
    public function test_mailLogs_withLimit(): void
    {
        $log = $this->logMailByCategory('', '', [], [], 'First Subject');
        $categoryName = LogCategory::find($log->log_category_id)->name;

        foreach (['a@gmail.com', 'b@gmail.com', 'c@gmail.com', 'd@gmail.com'] as $mail) {
            $this->logMailByCategory('', $mail, [], [], 'Some Subject', $categoryName);
        }

        $payload = $this->defaultMailPayload([
            'category' => $log->log_category_id,
            'limit' => 3,
        ]);

        $response = $this->postJson('/logs/mail', $payload);

        $response->assertStatus(200)->assertJsonCount(3, 'data.data');
    }

    /** ----------------------- Helpers ----------------------- */
    /**
     * @param array<mixed> $overrides
     * @return array<mixed>
     */
    private function defaultExceptionPayload(array $overrides = []): array
    {
        return array_merge([
            'draw' => 1,
            'columns' => $this->defaultColumns(['file', 'line', 'message', 'trace', 'created_at']),
            'order' => [['column' => 0, 'dir' => 'asc']],
            'start' => 0,
            'length' => 10,
            'search' => ['value' => '', 'regex' => false],
            'date' => Date::now()->toDateString(),
            'category' => $this->defaultCategoryId,
            'log_type' => 'exception',
        ], $overrides);
    }

    /**
     * @param array<mixed> $overrides
     * @return array<mixed>
     */
    private function defaultCronPayload(array $overrides = []): array
    {
        return array_merge([
            'start' => 0,
            'length' => 10,
            'date' => Date::now()->toDateString(),
            'category' => 'database:sync',
            'status' => 'completed',
        ], $overrides);
    }

    /**
     * @param array<mixed> $overrides
     * @return array<mixed>
     */
    private function defaultMailPayload(array $overrides = []): array
    {
        return array_merge([
            'draw' => 1,
            'columns' => $this->defaultColumns(['sender_mail', 'receiver_mail', 'carbon_copy', 'blind_carbon_copy', 'subject', 'created_at', 'updated_at', 'status']),
            'order' => [['column' => 0, 'dir' => 'asc']],
            'start' => 0,
            'length' => 10,
            'search' => ['value' => '', 'regex' => false],
            'date' => Date::now()->toDateString(),
            'category' => $this->defaultCategoryId,
            'log_type' => 'mail',
            'status' => 'queued',
        ], $overrides);
    }

    /**
     * @param array<mixed> $fields
     * @return array<mixed>
     */
    private function defaultColumns(array $fields): array
    {
        return collect($fields)->map(fn ($f): array => [
            'data' => $f,
            'searchable' => true,
            'orderable' => true,
            'search' => ['value' => '', 'regex' => false],
        ])->all();
    }

    /**
     * @param array<mixed> $bcc
     * @param array<mixed> $cc
     */
    private function logMailByCategory(
        string $senderMail = 'test@sender.com',
        string $receiverMail = 'receiver@example.com',
        array $cc = [],
        array $bcc = [],
        string $subject = 'Test Subject',
        ?string $categoryName = 'test_category'
    ): ?\Illuminate\Database\Eloquent\Model {
        return new LogWriteController()->logMailByCategory(
            $senderMail,
            $receiverMail,
            $cc,
            $bcc,
            $subject,
            'This is a test email body.',
            $categoryName
        );
    }

    /*
     * Delete Exception Log Test Cases
    */
    #[Test]
    public function it_deletes_exception_logs(): void
    {
        // Older log → should be deleted
        ExceptionLog::create([
            'log_category_id' => 1,
            'file' => 'test.php',
            'line' => 10,
            'trace' => 'trace1',
            'message' => 'Old Log',
            'created_at' => now()->subDays(5),
        ]);

        // Recent log → should stay
        ExceptionLog::create([
            'log_category_id' => 1,
            'file' => 'test.php',
            'line' => 20,
            'trace' => 'trace2',
            'message' => 'Recent Log',
            'created_at' => now(),
        ]);

        $payload = [
            'log_types' => ['exception'],
            'to_date' => now()->subDays(2)->toDateString(),
        ];

        $response = $this->deleteJson('/logs/delete', $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Logs deleted successfully']);
    }

    /*
     * Delete Mail Log Test Cases
     */

    #[Test]
    public function it_deletes_mail_logs(): void
    {
        $category = LogCategory::create([
            'name' => 'default',
        ]);

        MailLog::create([
            'log_category_id' => $category->id,
            'sender_mail' => 'a@test.com',
            'receiver_mail' => 'b@test.com',
            'status' => 'sent',
            'carbon_copy' => null,
            'created_at' => now()->subDays(5),
        ]);

        MailLog::create([
            'log_category_id' => $category->id,
            'sender_mail' => 'c@test.com',
            'receiver_mail' => 'd@test.com',
            'status' => 'sent',
            'carbon_copy' => null,
            'created_at' => now(),
        ]);

        $payload = [
            'log_types' => ['mail'],
            'to_date' => now()->subDays(2)->toDateString(),
        ];

        $response = $this->deleteJson('/logs/delete', $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Logs deleted successfully']);
    }

    /*
     * Activity Log Test Cases
    */
    public function test_get_activity_returns_activity_logs(): void
    {
        $this->createActivity([
            'log_name' => 'User',
            'event' => 'updated',
            'description' => 'Profile updated',
        ]);

        $response = $this->getJson('/get-activity');

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Activity logs fetched successfully']);
    }

    public function test_get_activity_search_filter_works(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Jon',
            'user_name' => 'jon123',
            'email' => 'jon@example.com',
        ]);

        $activity = $this->createActivity([
            'log_name' => 'Billing',
            'event' => 'created',
            'causer_id' => $user->id,
            'description' => 'Invoice created',
            'created_at' => now(),
        ]);

        // Search by log name
        $this->getJson('/get-activity?search-query='.$activity->log_name)
             ->assertStatus(200)
             ->assertJsonFragment(['module' => 'Billing']);

        // Search by description
        $this->getJson('/get-activity?search-query='.$activity->description)
             ->assertStatus(200)
             ->assertJsonFragment(['description' => 'Invoice created']);

        // Search by first name
        $this->getJson('/get-activity?search-query='.$user->first_name)
             ->assertStatus(200);
        // Search by user_name
        $this->getJson('/get-activity?search-query='.$user->user_name)
             ->assertStatus(200);

        // Search by email
        $this->getJson('/get-activity?search-query='.$user->email)
             ->assertStatus(200);
    }

    /**
     * Create activity record with ability to override any field.
     * @param array<mixed> $overrides
     */
    protected function createActivity(array $overrides = []): mixed
    {
        $defaults = [
            'log_name' => 'Billing',
            'event' => 'created',
            'causer_id' => User::factory()->create()->id,
            'causer_type' => User::class,
            'description' => 'Invoice created',
            'properties' => [],
            'created_at' => now(),
        ];

        return Activity::create(array_merge($defaults, $overrides));
    }

    public function test_filter_activity_logs_by_module(): void
    {
        // Filter by Module
        $this->createActivity(['log_name' => 'Billing']);
        $this->createActivity(['log_name' => 'Support']);

        $response = $this->getJson('/get-activity?module[]=Billing');

        $response->assertStatus(200)
                 ->assertJsonFragment(['module' => 'Billing'])
                 ->assertJsonMissing(['module' => 'Support']);

        // Filter by Event
        $this->createActivity(['event' => 'created']);
        $this->createActivity(['event' => 'deleted']);

        $response = $this->getJson('/get-activity?event[]=deleted');

        $response->assertStatus(200)
                 ->assertJsonFragment(['event' => 'Deleted'])
                 ->assertJsonMissing(['event' => 'Created']);

        // Filter by Performed By User
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->createActivity(['causer_id' => $user1->id]);
        $this->createActivity(['causer_id' => $user2->id]);

        $response = $this->getJson('/get-activity?performed_by[]='.$user1->id);

        $response->assertStatus(200);
    }

    public function test_get_activity_applies_all_filters_together(): void
    {
        $targetUser = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'user_name' => 'john_d',
            'email' => 'john@example.com',
            'role' => 'admin',
        ]);

        $otherUser = User::factory()->create();

        // Activity that MUST MATCH the filters
        $this->createActivity([
            'log_name' => 'Billing',
            'event' => 'created',
            'causer_id' => $targetUser->id,
            'description' => 'Invoice created successfully',
            'created_at' => now()->subDay(),
        ]);

        // Activity that MUST BE EXCLUDED
        $this->createActivity([
            'log_name' => 'Support',
            'event' => 'deleted',
            'causer_id' => $otherUser->id,
            'description' => 'Ticket deleted',
            'created_at' => now()->subDays(10),
        ]);

        // Build query string for ALL filters
        $queryString = http_build_query([
            'module' => ['Billing'],
            'event' => ['created'],
            'performed_by' => [$targetUser->id],
            'log_from' => now()->subDays(2)->toDateString(),
            'log_till' => now()->toDateString(),
            'search-query' => 'Invoice',
        ]);

        $response = $this->getJson('/get-activity?'.$queryString);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'module' => 'Billing',
                     'event' => 'Created',
                     'description' => 'Invoice created successfully',
                 ])
                ->assertJsonMissing(['module' => 'Support'])
                ->assertJsonMissing(['event' => 'Deleted'])
                ->assertJsonMissing(['description' => 'Ticket deleted']);
    }

    /*
     * Delete Activity Log Test Cases
    */
    public function test_it_deletes_activity_logs(): void
    {
        // Old log (should be deleted)
        $this->createActivity([
            'description' => 'Old system log',
            'created_at' => now()->subDays(10),
        ]);

        // New log (should not be deleted)
        $this->createActivity([
            'description' => 'Recent system log',
            'created_at' => now(),
        ]);

        $payload = [
            'log_types' => ['systemLogs'],
            'to_date' => now()->subDays(2)->toDateString(),
        ];

        $response = $this->deleteJson('/logs/delete', $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => __('message.logs_deleted_successfully')]);
    }

    /*
     * Delete Cron Log Test Cases
    */
    public function test_it_deletes_cron_logs(): void
    {
        LogCategory::create(['name' => 'database:sync']);

        //Create OLD cron log — should be deleted
        $oldCronLog = Logger::cron('database:sync', 'Old cron execution');
        DB::table('cron_logs')->where('id', $oldCronLog->id)->update([
            'created_at' => now()->subDays(10),
        ]);

        // Create NEW cron log — should remain
        $newCronLog = Logger::cron('database:sync', 'Recent cron execution');
        DB::table('cron_logs')->where('id', $newCronLog->id)->update([
            'created_at' => now(),
        ]);

        $payload = [
            'log_types' => ['cron'],
            'to_date' => now()->toDateString(),
        ];

        $response = $this->deleteJson('/logs/delete', $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => __('message.logs_deleted_successfully')]);
    }

    /*
     * Delete Failed Job Log Test Cases
    */
    public function test_it_deletes_failed_job_logs(): void
    {
        // Create OLD failed job — should be deleted
        $oldFailedId = DB::table('failed_jobs')->insertGetId([
            'uuid' => Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['job' => 'ProcessOld']),
            'exception' => 'Old job exception',
            'failed_at' => now()->subDays(10),
        ]);

        // Create NEW failed job — should remain
        $newFailedId = DB::table('failed_jobs')->insertGetId([
            'uuid' => Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['job' => 'ProcessNew']),
            'exception' => 'New job exception',
            'failed_at' => now(),
        ]);

        $payload = [
            'log_types' => ['failed_jobs'],
            'to_date' => now()->subDays(2)->toDateString(),
        ];

        $response = $this->deleteJson('/logs/delete', $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => __('message.logs_deleted_successfully')]);

        // Ensure only one record remains — the new failed job
        $this->assertDatabaseCount('failed_jobs', 1);

        // New job must exist
        $this->assertDatabaseHas('failed_jobs', [
            'id' => $newFailedId,
            'exception' => 'New job exception',
        ]);

        // Old job must be deleted
        $this->assertDatabaseMissing('failed_jobs', [
            'id' => $oldFailedId,
            'exception' => 'Old job exception',
        ]);
    }

    /*
     * Test Cases for Payment Logs
    */

    /**
     * @param array<mixed> $overrides
     */
    protected function createPaymentLog(array $overrides = []): \App\Payment_log
    {
        $defaults = [
            'status' => 'success',
            'payment_type' => 'invoice',
            'payment_method' => 'razorpay',
            'amount' => 100,
            'date' => now(),
        ];

        $log = new Payment_log();
        $log->forceFill(array_merge($defaults, $overrides));
        $log->save();

        return $log;
    }

    public function test_get_payment_logs(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create();

        $this->createPaymentLog([
            'order' => $order->number,
            'from' => $user->email,
        ]);

        $response = $this->getJson('/get-paymentlog');

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => __('message.payment_logs_retrieved')])
                 ->assertJsonFragment(['status' => 'Success']);
    }

    public function test_get_payment_logs_search_filter(): void
    {
        $user = User::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
        $order = Order::factory()->create();

        $paymentLog = $this->createPaymentLog([
            'order' => $order->number,
            'from' => $user->email,
        ]);

        // Search by email
        $response1 = $this->getJson('/get-paymentlog?search-query='.$user->email);
        $response1->assertStatus(200)
                  ->assertJsonFragment(['payment_email' => $user->email]);

        // Search by user name (first_name + last_name)
        $fullName = sprintf('%s %s', $user->first_name, $user->last_name);
        $response2 = $this->getJson('/get-paymentlog?search-query='.$fullName);
        $response2->assertStatus(200)
                  ->assertJsonFragment(['user_name' => $fullName]);

        // Search by status
        $response3 = $this->getJson('/get-paymentlog?search-query='.$paymentLog->status);
        $response3->assertStatus(200)
                  ->assertJsonFragment(['status' => ucfirst((string) $paymentLog->status)]);

        // Search by order number
        $response4 = $this->getJson('/get-paymentlog?search-query='.$paymentLog->order);
        $response4->assertStatus(200);

        // Search by from email
        $response5 = $this->getJson('/get-paymentlog?search-query='.$paymentLog->from);
        $response5->assertStatus(200)
                  ->assertJsonFragment(['payment_email' => $paymentLog->from]);

        // Search by payment type
        $response6 = $this->getJson('/get-paymentlog?search-query='.$paymentLog->payment_type);
        $response6->assertStatus(200)
                  ->assertJsonFragment(['description' => ucfirst((string) $paymentLog->payment_type)]);

        // Search by payment method
        $response7 = $this->getJson('/get-paymentlog?search-query='.$paymentLog->payment_method);
        $response7->assertStatus(200)
                  ->assertJsonFragment(['payment_method' => ucfirst((string) $paymentLog->payment_method)]);
    }

    public function test_payment_log_applies_all_filters_together(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create();

        $paymentLog1 = $this->createPaymentLog([
            'order' => $order->number,
            'from' => $user->email,
            'status' => 'success',
            'payment_method' => 'stripe',
            'payment_type' => 'subscription',
            'created_at' => now()->subDay(),
        ]);
        $paymentLog1->forceFill([
            'created_at' => Date::create(2025, 7, 12)->startOfDay(),
        ])->saveQuietly();

        $paymentLog2 = $this->createPaymentLog([
            'order' => $order->number,
            'from' => $user->email,
            'status' => 'success',
            'payment_method' => 'stripe',
            'payment_type' => 'subscription',
            'created_at' => now()->subDay(),
        ]);
        $paymentLog2->forceFill([
            'created_at' => Date::create(2025, 9, 12)->startOfDay(),
        ])->saveQuietly();

        $paymentLog3 = $this->createPaymentLog([
            'order' => $order->number,
            'from' => $user->email,
            'status' => 'success',
            'payment_method' => 'stripe',
            'payment_type' => 'subscription',
            'created_at' => now()->subDay(),
        ]);
        $paymentLog3->forceFill([
            'created_at' => Date::create(2025, 10, 12)->startOfDay(),
        ])->saveQuietly();

        // filter by same date (from and till)
        $response1 = $this->getJson('/get-paymentlog?from=2025-07-12&till=2025-07-12');
        $response1->assertStatus(200)
            ->assertJsonCount(1, 'data.logs.data');

        // filter by different  date range (from and till)
        $response2 = $this->getJson('/get-paymentlog?from=2025-07-12&till=2025-10-12');
        $response2->assertStatus(200)
            ->assertJsonCount(3, 'data.logs.data');

        //filter by without till date
        $response3 = $this->getJson('/get-paymentlog?from=2025-09-12');
        $response3->assertStatus(200)
            ->assertJsonCount(2, 'data.logs.data');
    }

    /*
    * Delete Payment Log Test Cases
    */
    public function test_destroy_payment_returns_error_when_no_ids_sent(): void
    {
        $payload = ['select' => []];

        $response = $this->deleteJson('paymentlog-delete', $payload);

        $response->assertStatus(400)
                 ->assertJsonFragment(['message' => __('message.select-a-row')]);
    }

    /*
    * Delete Payment Log Test Cases
    */
    public function test_destroy_payment_deletes_selected_records(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create();

        $this->createPaymentLog([
            'order' => $order->number,
            'from' => $user->email,
        ]);

        // Logs to DELETE — store references
        $log1 = $this->createPaymentLog([
            'status' => 'failed',
        ]);

        $log2 = $this->createPaymentLog([
            'payment_method' => 'razorpay',
        ]);

        // Delete request with selected IDs
        $payload = ['select' => [$log1->id, $log2->id]];

        $response = $this->deleteJson('paymentlog-delete', $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => __('message.deleted-successfully')]);

        $this->assertDatabaseMissing('payment_logs', ['id' => $log1->id]);
        $this->assertDatabaseMissing('payment_logs', ['id' => $log2->id]);
        $this->assertDatabaseCount('payment_logs', 1);
    }
}
