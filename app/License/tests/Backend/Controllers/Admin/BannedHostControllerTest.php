<?php

namespace App\License\tests\Backend\Controllers\Admin;

use App\License\Controllers\Admin\BannedHostController;
use App\License\Models\LicenseBannedHost;
use App\License\Models\LicenseWhitelistIp;
use App\License\Requests\BannedHostRequest;
use App\License\tests\Backend\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class BannedHostControllerTest extends LicenseTestCase
{
    private BannedHostController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new BannedHostController($this->moduleRequest());
    }

    #[Test]
    #[Group('license-admin')]
    public function banned_host_add_creates_record(): void
    {
        $ip = '10.10.10.10';
        $request = BannedHostRequest::create('/api/admin/bannedHosts/add', 'POST', [
            'banned_host_ip' => $ip,
            'comments' => 'Blocked by test',
        ]);

        $response = $this->controller->bannedHostAdd($request);
        $json = $this->assertSuccessfulJson($response, 201);

        $this->assertSame($ip, $json['data']['banned_host_ip']);
        $this->assertDatabaseHas('license_banned_hosts', [
            'banned_host_ip' => $ip,
            'comments' => 'Blocked by test',
        ]);
    }

    #[Test]
    #[Group('license-admin')]
    public function banned_host_add_rejects_whitelisted_ip(): void
    {
        LicenseWhitelistIp::create([
            'whitelist_host_ip' => '10.10.10.11',
            'whitelist_host_comments' => 'Allowed',
        ]);
        $request = BannedHostRequest::create('/api/admin/bannedHosts/add', 'POST', [
            'banned_host_ip' => '10.10.10.11',
        ]);

        $response = $this->controller->bannedHostAdd($request);

        $this->assertErrorJson($response, 400);
        $this->assertDatabaseMissing('license_banned_hosts', ['banned_host_ip' => '10.10.10.11']);
    }

    #[Test]
    #[Group('license-admin')]
    public function banned_host_update_changes_existing_record(): void
    {
        $host = LicenseBannedHost::create([
            'banned_host_ip' => '10.10.10.12',
            'comments' => 'Old',
        ]);

        $response = $this->controller->bannedHostUpdate($this->moduleRequest([
            'id' => $host->id,
            'banned_host_ip' => '10.10.10.13',
            'comments' => 'New',
        ], 'POST'));

        $this->assertSuccessfulJson($response, 201);
        $this->assertDatabaseHas('license_banned_hosts', [
            'id' => $host->id,
            'banned_host_ip' => '10.10.10.13',
            'comments' => 'New',
        ]);
    }

    #[Test]
    #[Group('license-admin')]
    public function delete_banned_host_removes_record(): void
    {
        $host = LicenseBannedHost::create([
            'banned_host_ip' => '10.10.10.14',
            'comments' => 'Delete',
        ]);

        $response = $this->controller->deleteBannedHost($this->moduleRequest(['id' => $host->id], 'POST'));
        $json = $this->assertSuccessfulJson($response, 201);

        $this->assertSame(1, $json['data']);
        $this->assertDatabaseMissing('license_banned_hosts', ['id' => $host->id]);
    }

    #[Test]
    #[Group('license-admin')]
    public function show_filters_banned_hosts(): void
    {
        $host = LicenseBannedHost::create([
            'banned_host_ip' => '10.10.10.15',
            'comments' => 'Searchable banned host',
        ]);

        $response = $this->controller->show($this->moduleRequest([
            'search_query' => 'Searchable banned host',
            'perPage' => 5,
        ]));
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame($host->id, $json['data']['data'][0]['id']);
        $this->assertSame('10.10.10.15', $json['data']['data'][0]['banned_host_ip']);
        $this->assertArrayHasKey('banned_host_date', $json['data']['data'][0]);
    }

    #[Test]
    #[Group('license-admin')]
    public function view_returns_single_banned_host(): void
    {
        $host = LicenseBannedHost::create([
            'banned_host_ip' => '10.10.10.16',
            'comments' => 'View',
        ]);

        $response = $this->controller->view($host->id);
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame($host->id, $json['data']['banned_host_data']['id']);
    }
}
