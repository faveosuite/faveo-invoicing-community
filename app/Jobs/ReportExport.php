<?php

namespace App\Jobs;

use App\Http\Controllers\Report\ConcreteExportHandleController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ReportExport implements ShouldQueue
{
    use Queueable;

    protected $exportHandleController;

    public $tries = 5;

    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $reportType, protected $selectedColumns, protected $searchParams, protected $email)
    {
        $exportHandleController = new ConcreteExportHandleController($this->reportType, $this->selectedColumns, $this->searchParams, $this->email);
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
