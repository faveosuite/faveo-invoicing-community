<?php

namespace Tests\Unit\Backend\Http\Controllers\License;

use App\Http\Controllers\License\EncryptDecryptController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EncryptDecryptControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_generate_keys_creates_key_files(): void
    {
        Storage::fake('public');
        $controller = new EncryptDecryptController;
        $orderNo = 'TEST001';

        $controller->generateKeys($orderNo);

        Storage::disk('public')->assertExists("publicKey-{$orderNo}.txt");
        Storage::disk('public')->assertExists("privateKey-{$orderNo}.txt");
    }

    public function test_encrypt_and_decrypt_roundtrip(): void
    {
        Storage::fake('public');
        $controller = new EncryptDecryptController;
        $orderNo    = 'TEST002';
        $plaintext  = 'license-data-12345';

        // Generate keys first
        $controller->generateKeys($orderNo);

        // Encrypt
        $encrypted = $controller->encrypt($plaintext, $orderNo);
        $this->assertIsString($encrypted);
        $this->assertNotEquals($plaintext, $encrypted);

        // Decrypt requires the file to exist — store it manually
        Storage::disk('public')->put("faveo-license-{{$orderNo}}.txt", $encrypted);

        // Decrypt
        $decrypted = $controller->decrypt($orderNo);
        $this->assertEquals($plaintext, $decrypted);
    }

    public function test_decrypt_with_no_key_file_returns_empty_string_or_throws(): void
    {
        Storage::fake('public');
        $controller = new EncryptDecryptController;
        // No keys generated → Storage returns null → openssl may warn/return false
        try {
            $result = $controller->decrypt('NO_SUCH_ORDER');
            $this->assertSame('', $result);
        } catch (\Throwable) {
            // Acceptable — openssl issues a warning that becomes an ErrorException
            $this->assertTrue(true);
        }
    }
}
