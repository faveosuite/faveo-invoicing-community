<?php

namespace Yajra\DataTables\Livewire;

use Illuminate\Bus\Batch;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @property Batch $exportBatch
 */
class ExportButtonComponent extends Component
{
    public string $class = 'btn btn-primary';

    public ?string $tableId = null;

    public ?string $emailTo = '';

    public string $type = 'xlsx';

    public string $filename = '';

    public string $buttonName = 'Export';

    public string $sheetName = 'Sheet1';

    public bool $autoDownload = false;

    public bool $downloaded = false;

    public bool $exporting = false;

    public bool $exportFinished = false;

    public bool $exportFailed = false;

    public ?string $batchJobId = null;

    public function export(string $batchJobId): void
    {
        $this->batchJobId = $batchJobId;
        $this->exportFinished = false;
        $this->exportFailed = false;
        $this->exporting = true;
        $this->downloaded = false;
    }

    public function getExportBatchProperty(): ?Batch
    {
        if (! $this->batchJobId) {
            return null;
        }

        return Bus::findBatch($this->batchJobId);
    }

    public function updateExportProgress(): ?StreamedResponse
    {
        $this->exportFinished = $this->exportBatch->finished();
        $this->exportFailed = $this->exportBatch->hasFailures();

        if ($this->exportFinished) {
            $this->exporting = false;
            if ($this->autoDownload and ! $this->downloaded) {
                $this->downloaded = true;

                return $this->downloadExport();
            }
        }

        return null;
    }

    public function downloadExport(): StreamedResponse
    {
        if ($this->getS3Disk()) {
            return Storage::disk($this->getS3Disk())
                ->download($this->batchJobId.'.'.$this->getType(), $this->getFilename());
        }

        return Storage::disk($this->getDisk())->download($this->batchJobId.'.'.$this->getType(), $this->getFilename());
    }

    protected function getType(): string
    {
        if (Str::endsWith($this->filename, ['csv', 'xlsx'])) {
            return pathinfo($this->filename, PATHINFO_EXTENSION);
        }

        return $this->type == 'csv' ? 'csv' : 'xlsx';
    }

    protected function getFilename(): string
    {
        if (Str::endsWith(Str::lower($this->filename), ['csv', 'xlsx'])) {
            return $this->filename;
        }

        return Str::random().'.'.$this->getType();
    }

    public function render(): Renderable
    {
        return view('datatables-export::export-button', [
            'fileType' => $this->getType(),
        ]);
    }

    protected function getDisk(): string
    {
        /** @var string $disk */
        $disk = config('datatables-export.disk', 'local');

        return $disk;
    }

    protected function getS3Disk(): string
    {
        /** @var string $disk */
        $disk = config('datatables-export.s3_disk', '');

        return $disk;
    }
}
