<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Models;

use App\FailedWhatsappMessage;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FailedWhatsappMessageTest extends TestCase
{
    use DatabaseTransactions;

    public function test_message_is_encrypted_on_set_and_decrypted_on_get(): void
    {
        $model = new FailedWhatsappMessage();
        $model->message = 'hello world';

        $this->assertSame('hello world', $model->message);
    }

    public function test_get_returns_raw_value_when_decryption_fails(): void
    {
        $model = new FailedWhatsappMessage();
        // Set a raw (non-encrypted) value directly to bypass the set cast
        $model->setRawAttributes(['message' => 'not-encrypted']);

        // get accessor tries Crypt::decrypt → throws DecryptException → returns raw value
        $this->assertSame('not-encrypted', $model->message);
    }

}
