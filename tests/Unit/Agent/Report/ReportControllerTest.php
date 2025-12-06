<?php

namespace Tests\Unit\Agent\Report;

use App\ExportDetail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Storage;
use Tests\DBTestCase;

class ReportControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->getLoggedInUser('admin');
    }


    public function test_it_returns_all_reports()
    {
        $detail = ExportDetail::create([
            'user_id' => $this->user->id,
            'file' => 'report1.xlsx',
            'file_path' => storage_path('app/reports/report1.xlsx'),
            'name' => 'sales',
        ]);

        $response = $this->getJson('/reports');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        [
                            'id',
                            'file',
                            'format',
                            'type',
                            'user',
                            'created_at'
                        ]
                    ]
                ]
            ]);
    }


    public function test_it_filters_reports_by_search()
    {
        ExportDetail::create([
            'user_id' => $this->user->id,
            'file' => 'invoice_export.xlsx',
        ]);

        $response = $this->getJson('/reports?search-query=invoice');

        $response->assertStatus(200);
        $this->assertStringContainsString('invoice_export.xlsx', $response->getContent());
    }


    public function test_it_deletes_bulk_reports_successfully()
    {
        $report = ExportDetail::create([
            'user_id' => $this->user->id,
            'file_path' => storage_path('app/reports/test.xlsx'),
        ]);

        Storage::put('reports/test.xlsx', 'dummy content');

        $response = $this->deleteJson('/reports', [
            'select' => [$report->id]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('export_details', [
            'id' => $report->id
        ]);

        Storage::disk('local')->assertMissing('reports/test.xlsx');
    }

    /** @test */
    public function it_returns_error_if_bulk_delete_has_no_ids()
    {
        $response = $this->deleteJson('/reports', [
            'select' => []
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function it_returns_report_settings()
    {
        ReportSetting::factory()->create([
            'records' => 100
        ]);

        $response = $this->getJson('/reports/setting');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['records']
            ]);
    }

    /** @test */
    public function it_updates_report_settings()
    {
        $setting = ReportSetting::factory()->create([
            'records' => 50
        ]);

        $response = $this->patchJson('/reports/setting', [
            'records' => 200
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('report_settings', [
            'id' => $setting->id,
            'records' => 200
        ]);
    }

    /** @test */
    public function it_fails_validation_when_records_invalid()
    {
        ReportSetting::factory()->create();

        $response = $this->patchJson('/reports/setting', [
            'records' => 5000
        ]);

        $response->assertStatus(422);
    }
}
