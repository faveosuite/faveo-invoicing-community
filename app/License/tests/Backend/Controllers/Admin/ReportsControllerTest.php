<?php

namespace App\License\tests\Backend\Controllers\Admin;

use App\License\Controllers\Admin\ReportsController;
use App\License\Models\LicenseReport;
use App\License\tests\Backend\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ReportsControllerTest extends LicenseTestCase
{
    private ReportsController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new ReportsController();
    }

    #[Test]
    #[Group('license-admin')]
    public function report_array_system_returns_system_reports(): void
    {
        $user = $this->createUser(['email' => 'system-report-'.uniqid().'@example.test']);
        $report = LicenseReport::create([
            'user_id' => $user->id,
            'license_code' => null,
            'report_text' => 'system report searchable',
            'report_date_time' => now(),
            'report_system' => 1,
            'report_status' => 1,
        ]);

        $response = $this->controller->reportArraySystem($this->moduleRequest([
            'search_query' => 'system report searchable',
            'perPage' => 5,
        ]));
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame($report->id, $json['data']['data'][0]['id']);
        $this->assertSame($user->email, $json['data']['data'][0]['user_formatted']);
    }

    #[Test]
    #[Group('license-admin')]
    public function report_array_cracking_returns_unassigned_non_upgrade_reports(): void
    {
        $report = LicenseReport::create([
            'report_text' => 'cracking report searchable',
            'report_date_time' => now(),
            'report_system' => 0,
            'report_status' => 1,
        ]);

        $response = $this->controller->reportArrayCracking($this->moduleRequest([
            'search_query' => 'cracking report searchable',
            'perPage' => 5,
        ]));
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame($report->id, $json['data']['data'][0]['id']);
    }

    #[Test]
    #[Group('license-admin')]
    public function report_array_license_returns_license_reports_with_license_id(): void
    {
        $license = $this->createLicense();
        $report = LicenseReport::create([
            'user_id' => $license->user_id,
            'license_code' => $license->license_code,
            'report_text' => 'license report searchable',
            'report_date_time' => now(),
            'report_system' => 0,
            'report_status' => 1,
        ]);

        $response = $this->controller->reportArrayLicense($this->moduleRequest([
            'search_query' => 'license report searchable',
            'perPage' => 5,
        ]));
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame($report->id, $json['data']['data'][0]['id']);
        $this->assertSame($license->id, $json['data']['data'][0]['license_id']);
    }

    #[Test]
    #[Group('license-admin')]
    public function report_array_update_returns_upgrade_reports(): void
    {
        $product = $this->createProduct(['name' => 'Report Product']);
        $report = LicenseReport::create([
            'product_id' => $product->id,
            'report_text' => 'upgrade report searchable',
            'report_date_time' => now(),
            'report_system' => 0,
            'report_status' => 1,
        ]);

        $response = $this->controller->reportArrayUpdate($this->moduleRequest([
            'search_query' => 'upgrade report searchable',
            'perPage' => 5,
        ]));
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame($report->id, $json['data']['data'][0]['id']);
        $this->assertSame($product->name, $json['data']['data'][0]['product_title']);
    }

    #[Test]
    #[Group('license-admin')]
    public function reports_deletes_selected_reports(): void
    {
        $report = LicenseReport::create([
            'report_text' => 'delete report',
            'report_date_time' => now(),
            'report_system' => 0,
            'report_status' => 1,
        ]);

        $response = $this->controller->reports($this->moduleRequest([
            'arr' => [$report->id],
            'which_report' => 'test',
        ], 'POST'));
        $json = $this->jsonContent($response);

        $this->assertArrayHasKey('message', $json);
        $this->assertDatabaseMissing('license_reports', ['id' => $report->id]);
    }
}
