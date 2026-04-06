<?php

namespace App\License\tests\Backend\Controllers\Admin;

use App\License\Controllers\Admin\NotificationsController;
use App\License\Models\LicenseNotification;
use App\License\Models\VersionNotification;
use App\License\tests\Backend\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class NotificationsControllerTest extends LicenseTestCase
{
    private NotificationsController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new NotificationsController();
    }

    #[Test]
    #[Group('license-admin')]
    public function update_license_notifications_creates_and_show_returns_first_record(): void
    {
        $payload = array_fill_keys((new LicenseNotification())->getFillable(), 'license notification text');

        $response = $this->controller->updateLicenseNotifications($this->moduleRequest($payload, 'POST'), 999999);
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame('license notification text', $json['data']['notification_license_ok']);

        $show = $this->controller->showLicenseNotifications();
        $showJson = $this->assertSuccessfulJson($show);
        $this->assertSame($json['data']['id'], $showJson['data']['id']);
    }

    #[Test]
    #[Group('license-admin')]
    public function update_update_notifications_creates_and_show_returns_first_record(): void
    {
        $payload = array_fill_keys((new VersionNotification())->getFillable(), 'version notification text');

        $response = $this->controller->updateUpdateNotifications($this->moduleRequest($payload, 'POST'), 999999);
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame('version notification text', $json['data']['notification_operation_ok']);

        $show = $this->controller->showUpdateNotifications();
        $showJson = $this->assertSuccessfulJson($show);
        $this->assertSame($json['data']['id'], $showJson['data']['id']);
    }
}
