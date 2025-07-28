<?php

namespace App\Jobs;

use App\Http\Controllers\Report\ConcreteExportHandleController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReportExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reportType;
    protected $selectedColumns;
    protected $searchParams;
    protected $email;
    protected $exportHandleController;

    public $tries = 5;
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct($reportType, $selectedColumns, $searchParams, $email)
    {
        $this->reportType = $reportType;
        $this->selectedColumns = $selectedColumns;
        $this->searchParams = $searchParams;
        $this->email = $email;

        $exportHandleController = new ConcreteExportHandleController($reportType, $selectedColumns, $searchParams, $email);
        $this->exportHandleController = $exportHandleController;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        switch ($this->reportType) {
            case 'users':
                $exportJob = $this->exportHandleController->userExports($this->selectedColumns, $this->searchParams, $this->email);
                break;
            case 'invoices':
                $exportJob = $this->exportHandleController->invoiceExports($this->selectedColumns, $this->searchParams, $this->email);
                break;
            case 'orders':
                $exportJob = $this->exportHandleController->orderExports($this->selectedColumns, $this->searchParams, $this->email);
                break;
            case 'tenats':
                $exportJob = $this->exportHandleController->tenantExports($this->selectedColumns, $this->searchParams, $this->email);
                break;
            default:
                return;
        }
    }
}
