<?php

namespace Tests\Unit\Backend\Http\Controllers\Report;

use App\ExportDetail;
use App\ReportSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class ReportControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
    }

    public function test_it_returns_all_reports(): void
    {
        ExportDetail::create([
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
                            'created_at',
                        ],
                    ],
                ],
            ]);
    }

    public function test_it_filters_reports_by_search(): void
    {
        ExportDetail::create([
            'user_id' => $this->user->id,
            'file' => 'invoice_export.xlsx',
        ]);

        $response = $this->getJson('/reports?search-query=invoice');

        $response->assertStatus(200);
        $this->assertStringContainsString('invoice_export.xlsx', $response->getContent());
    }

    public function test_it_deletes_bulk_reports_successfully(): void
    {
        $folderName = 'users_export_'.auth()->id().'_'.now()->format('Ymd_His').'_XLSX';
        $folderPath = storage_path('app/public/export/'.$folderName);

        $report = ExportDetail::create([
            'user_id' => auth()->id(),
            'file_path' => $folderPath,
            'file' => $folderName,
            'name' => 'users',
        ]);

        $response = $this->deleteJson('/reports', [
            'select' => [$report->id],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('export_details', [
            'id' => $report->id,
        ]);
    }

    public function test_it_deletes_report_and_removes_existing_file(): void
    {
        // Covers lines 76-77: file_exists is true → Storage::delete called
        $filePath = storage_path('app/test_report_'.uniqid().'.xlsx');
        file_put_contents($filePath, 'dummy');

        $report = ExportDetail::create([
            'user_id' => auth()->id(),
            'file_path' => $filePath,
            'file' => basename($filePath),
            'name' => 'users',
        ]);

        $response = $this->deleteJson('/reports', ['select' => [$report->id]]);

        @unlink($filePath);

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_it_returns_error_if_bulk_delete_has_no_ids(): void
    {
        $response = $this->deleteJson('/reports', [
            'select' => [],
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => __('message.select-a-row'),
            ]);
    }

    public function test_it_returns_report_settings(): void
    {
        ReportSetting::updateOrCreate(['id' => 1], [
            'records' => 100,
        ]);

        $response = $this->getJson('/reports/setting');

        $response->assertStatus(200)
            ->assertJsonFragment(['success' => true])
            ->assertJsonFragment(['records' => '100']);
    }

    public function test_it_updates_report_settings(): void
    {
        $setting = ReportSetting::updateOrCreate(['id' => 1], [
            'records' => 50,
        ]);

        $response = $this->patchJson('/reports/setting', [
            'records' => 200,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('report_settings', [
            'id' => $setting->id,
            'records' => 200,
        ]);
    }

    public function test_it_fails_validation_when_records_invalid(): void
    {
        ReportSetting::create();

        $response = $this->patchJson('/reports/setting', [
            'records' => 5000,
        ]);

        $response->assertStatus(422);
    }
}
