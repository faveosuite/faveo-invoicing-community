<?php

namespace Yajra\DataTables;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Yajra\DataTables\Jobs\DataTableExportJob;

/**
 * @mixin \Yajra\DataTables\Services\DataTable
 */
trait WithExportQueue
{
    /**
     * Process dataTables needed render output.
     *
     * @return mixed
     *
     * @throws \Throwable
     */
    public function render(?string $view = null, array $data = [], array $mergeData = [])
    {
        if (request()->ajax() && request('action') == 'exportQueue') {
            return $this->exportQueue();
        }

        return parent::render($view, $data, $mergeData);
    }

    /**
     * Create and run batch job.
     *
     *
     * @throws \Throwable
     */
    public function exportQueue(): string
    {
        $job = new DataTableExportJob(
            [get_class($this), $this->attributes],
            request()->all(),
            Auth::id() ?? 0,
            $this->sheetName(),
        );

        $batch = Bus::batch([$job])->name('datatables-export')->dispatch();

        return $batch->id;
    }

    /**
     * Default sheet name.
     * Character limit 31.
     */
    protected function sheetName(): string
    {
        return request('sheetName', 'Sheet1');
    }
}
