<?php

namespace App\License\tests\Backend\Controllers;

use App\License\Controllers\WhitelistIpsController;
use App\License\Models\LicenseBannedHost;
use App\License\Models\LicenseWhitelistIp;
use App\License\Requests\whitelistIpsRequest;
use App\License\tests\Backend\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class WhitelistIpsControllerTest extends LicenseTestCase
{
    private WhitelistIpsController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new WhitelistIpsController();
    }

    #[Test]
    #[Group('license-admin')]
    public function whitelist_add_creates_record(): void
    {
        $request = whitelistIpsRequest::create('/api/admin/whitelist/updateOrCreate', 'POST', [
            'whitelist_host_ip' => '172.16.0.10',
            'whitelist_host_comments' => 'Allowed by test',
        ]);

        $response = $this->controller->whitelistAdd($request);
        $json = $this->assertSuccessfulJson($response, 201);

        $this->assertSame('172.16.0.10', $json['data']['whitelist_host_ip']);
        $this->assertDatabaseHas('license_whitelist_ips', [
            'whitelist_host_ip' => '172.16.0.10',
        ]);
    }

    #[Test]
    #[Group('license-admin')]
    public function whitelist_add_rejects_banned_ip(): void
    {
        LicenseBannedHost::create([
            'banned_host_ip' => '172.16.0.11',
            'comments' => 'Blocked',
        ]);
        $request = whitelistIpsRequest::create('/api/admin/whitelist/updateOrCreate', 'POST', [
            'whitelist_host_ip' => '172.16.0.11',
            'whitelist_host_comments' => 'Should fail',
        ]);

        $response = $this->controller->whitelistAdd($request);

        $this->assertErrorJson($response, 500);
        $this->assertDatabaseMissing('license_whitelist_ips', ['whitelist_host_ip' => '172.16.0.11']);
    }

    #[Test]
    #[Group('license-admin')]
    public function whitelist_add_updates_existing_record(): void
    {
        $host = LicenseWhitelistIp::create([
            'whitelist_host_ip' => '172.16.0.12',
            'whitelist_host_comments' => 'Old',
        ]);
        $request = whitelistIpsRequest::create('/api/admin/whitelist/updateOrCreate', 'POST', [
            'id' => $host->id,
            'whitelist_host_ip' => '172.16.0.13',
            'whitelist_host_comments' => 'New',
        ]);

        $response = $this->controller->whitelistAdd($request);

        $this->assertSuccessfulJson($response);
        $this->assertDatabaseHas('license_whitelist_ips', [
            'id' => $host->id,
            'whitelist_host_ip' => '172.16.0.13',
            'whitelist_host_comments' => 'New',
        ]);
    }

    #[Test]
    #[Group('license-admin')]
    public function delete_whitelist_ip_removes_record(): void
    {
        $host = LicenseWhitelistIp::create([
            'whitelist_host_ip' => '172.16.0.14',
            'whitelist_host_comments' => 'Delete',
        ]);

        $response = $this->controller->deleteWhitelistIp($this->moduleRequest(['id' => $host->id], 'POST'));

        $this->assertSuccessfulJson($response);
        $this->assertDatabaseMissing('license_whitelist_ips', ['id' => $host->id]);
    }

    #[Test]
    #[Group('license-admin')]
    public function edit_returns_single_whitelist_record(): void
    {
        $host = LicenseWhitelistIp::create([
            'whitelist_host_ip' => '172.16.0.15',
            'whitelist_host_comments' => 'Edit',
        ]);

        $response = $this->controller->edit($host->id);
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame($host->id, $json['data']['host_data']['id']);
    }

    #[Test]
    #[Group('license-admin')]
    public function view_filters_whitelist_records(): void
    {
        $host = LicenseWhitelistIp::create([
            'whitelist_host_ip' => '172.16.0.16',
            'whitelist_host_comments' => 'Search whitelist',
        ]);

        $response = $this->controller->view($this->moduleRequest([
            'search_query' => 'Search whitelist',
            'perPage' => 5,
        ]));
        $json = $this->assertSuccessfulJson($response, 201);

        $this->assertSame($host->id, $json['data']['data'][0]['id']);
        $this->assertSame('172.16.0.16', $json['data']['data'][0]['whitelist_host_ip']);
    }
}
