<?php

namespace App\License\tests\Controllers\Admin;

use App\License\Controllers\Admin\LanguageController;
use App\License\tests\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class LanguageControllerTest extends LicenseTestCase
{
    #[Test]
    #[Group('license-admin')]
    public function get_language_file_returns_javascript_translator_payload(): void
    {
        app()->setLocale('en');

        $response = (new LanguageController())->getLanguageFile();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/javascript', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('translator = ', $response->getContent());
        $this->assertStringEndsWith(';', $response->getContent());
    }
}
