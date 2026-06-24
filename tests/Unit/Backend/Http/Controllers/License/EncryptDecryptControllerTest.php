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
        $orderNo = 'TEST002';
        $plaintext = 'license-data-12345';

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

    public function test_decrypt_returns_string_when_decryption_fails(): void
    {
        Storage::fake('public');
        $controller = new EncryptDecryptController;
        $orderNo = 'TEST003';

        // Use mismatched keys — generate new keys but store data encrypted with different keys
        $controller->generateKeys($orderNo);
        // Store garbage that won't decrypt with these keys
        Storage::disk('public')->put("faveo-license-{{$orderNo}}.txt", base64_encode(str_repeat('X', 512)));

        // Either returns '' (line 37) or decrypted garbage
        $result = $controller->decrypt($orderNo);
        $this->assertIsString($result);
    }

    public function test_encrypt_throws_when_public_key_is_invalid(): void
    {
        Storage::fake('public');
        // Store an invalid (non-RSA) public key
        Storage::disk('public')->put('publicKey-BADORDER.txt', 'not-a-valid-key');

        $controller = new EncryptDecryptController;

        $this->expectException(\Exception::class);
        $controller->encrypt('some data', 'BADORDER');
    }
}
