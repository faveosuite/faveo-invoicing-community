<?php

namespace PragmaRX\Google2FAQRCode\Tests;

use PragmaRX\Google2FAQRCode\QRCode\Bacon;
use PragmaRX\Google2FAQRCode\QRCode\Chillerlan;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\Png;
use PHPUnit\Framework\TestCase;
use PragmaRX\Google2FAQRCode\Google2FA;
use Zxing\QrReader;
use PragmaRX\Google2FAQRCode\Exceptions\MissingQrCodeServiceException;

class Google2FATest extends TestCase
{
    const EMAIL = 'acr+pragmarx@antoniocarlosribeiro.com';

    const OTP_URL = 'otpauth://totp/PragmaRX:acr+pragmarx@antoniocarlosribeiro.com?secret=ADUMJO5634NPDEKW&issuer=PragmaRX&algorithm=SHA1&digits=6&period=30';

    public function setUp(): void
    {
        $this->google2fa = new Google2FA();
    }

    public function readQRCode($data)
    {
        [, $data] = explode(';', $data);

        [, $data] = explode(',', $data);

        return rawurldecode(
            (new QrReader(
                base64_decode($data),
                QrReader::SOURCE_TYPE_BLOB
            ))->text()
        );
    }

    public function testQrcodeServiceMissing()
    {
        $this->expectException(MissingQrCodeServiceException::class);

        $this->google2fa->setQrcodeService(null);

        $this->getQrCode();
    }

    public function testQrcodeInlineBacon()
    {
        if (!(new Bacon())->imagickIsAvailable()) {
            $this->assertTrue(true);

            return;
        }

        $this->google2fa->setQrcodeService(new Bacon());

        $this->assertEquals(
            static::OTP_URL,
            $this->readQRCode($this->getQRCode())
        );

        $google2fa = new Google2FA(null, new Bacon(new \BaconQrCode\Renderer\Image\SvgImageBackEnd()));

        $this->assertEquals(
            static::OTP_URL,
            $this->readQRCode($this->getQRCode())
        );
    }

    public function testQrcodeInlineChillerlan()
    {
        $this->google2fa->setQrcodeService(new Chillerlan());

        $this->assertStringStartsWith(
            'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMj',
            $this->getQRCode()
        );
    }

    public function getQrCode()
    {
        return $this->google2fa->getQRCodeInline(
            'PragmaRX',
            static::EMAIL,
            Constants::SECRET
        );
    }
}
