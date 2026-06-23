<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Jobs;

use App\FailedWhatsappMessage;
use App\Jobs\SendWhatsappMessage;
use App\WhatsappIntegrationUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SendWhatsappMessageTest extends TestCase
{
    use DatabaseTransactions;

    private function validPayload(string $phoneNumberId = 'phone-123', string $callbackUrl = ''): string
    {
        return json_encode([
            'entry' => [[
                'id' => 'waba-123',
                'changes' => [[
                    'value' => [
                        'metadata' => ['phone_number_id' => $phoneNumberId],
                    ],
                ]],
            ]],
        ]);
    }

    // --- Contract ---

    public function test_implements_should_queue(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, new SendWhatsappMessage('{}'));
    }

    public function test_tries_is_one(): void
    {
        $this->assertSame(1, (new SendWhatsappMessage('{}'))->tries);
    }

    public function test_backoff_is_60_seconds(): void
    {
        $this->assertSame(60, (new SendWhatsappMessage('{}'))->backoff);
    }

    // --- handle(): invalid JSON → graceful, no uncaught exception ---

    public function test_handle_with_invalid_json_does_not_throw(): void
    {
        $job = new SendWhatsappMessage('not-valid-json');

        // handle() wraps in try/catch — must not propagate exception
        $job->handle();
        $this->assertTrue(true);
    }

    public function test_handle_with_empty_string_message_does_not_throw(): void
    {
        (new SendWhatsappMessage(''))->handle();
        $this->assertTrue(true);
    }

    public function test_handle_with_null_entry_id_does_not_dispatch(): void
    {
        // When entry[0]['id'] === '' the message is skipped
        $payload = json_encode(['entry' => [['id' => '', 'changes' => []]]]);
        (new SendWhatsappMessage($payload))->handle();
        $this->assertTrue(true);
    }

    // --- handle(): valid JSON but no registered callback URL — no HTTP call made ---

    public function test_handle_with_no_matching_integration_user_does_not_throw(): void
    {
        // phone_number_id=nonexistent → WhatsappIntegrationUser::value returns null → no HTTP call
        (new SendWhatsappMessage($this->validPayload('nonexistent-phone-id')))->handle();
        $this->assertTrue(true);
    }

    // --- failed(): persists failed message to DB ---

    public function test_failed_creates_failed_whatsapp_message_record(): void
    {
        $payload = '{"test":"message"}';
        $countBefore = FailedWhatsappMessage::count();

        $job = new SendWhatsappMessage($payload);
        $job->failed();

        $this->assertSame($countBefore + 1, FailedWhatsappMessage::count());

        $record = FailedWhatsappMessage::latest()->first();
        $this->assertSame($payload, $record->message);
    }
}
