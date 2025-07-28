<div class="d-flex align-items-center" x-data>
    <form class="mr-2"
          x-on:submit.prevent="
                $refs.exportBtn.disabled = true;
                var oTable = LaravelDataTables['{{ $tableId }}'];
                var baseUrl = oTable.ajax.url() === '' ? window.location.toString() : oTable.ajax.url();

                var url = new URL(baseUrl);
                var searchParams = new URLSearchParams(url.search);
                searchParams.set('action', 'exportQueue');
                searchParams.set('exportType', '{{$fileType}}');
                searchParams.set('sheetName', '{{$sheetName}}');
                searchParams.set('buttonName', '{{$buttonName}}');
                searchParams.set('emailTo', '{{urlencode($emailTo)}}');

                var tableParams = $.param(oTable.ajax.params());
                if (tableParams) {
                    var tableSearchParams = new URLSearchParams(tableParams);
                    tableSearchParams.forEach((value, key) => {
                        searchParams.append(key, value);
                    });
                }

                url.search = searchParams.toString();

                $.get(url.toString()).then(function(exportId) {
                    $wire.export(exportId);
                }).catch(function(error) {
                    $wire.exportFinished = true;
                    $wire.exporting = false;
                    $wire.exportFailed = true;
                });
              "
    >
        <button type="submit"
                x-ref="exportBtn"
                :disabled="$wire.exporting"
                class="{{ $class }}"
        >{{$buttonName}}
        </button>
    </form>

    @if($exporting && $emailTo)
        <div class="d-inline">Export will be emailed to {{ $emailTo }}.</div>
    @endif

    @if($exporting && !$exportFinished)
        <div class="d-inline" wire:poll="updateExportProgress">Exporting...please wait.</div>
    @endif

    @if($exportFinished && !$exportFailed && !$autoDownload)
        <span>Done. Download file <a href="#" class="text-primary" wire:click.prevent="downloadExport">here</a></span>
    @endif

    @if($exportFinished && !$exportFailed && $autoDownload && $downloaded)
        <span>Done. File has been downloaded.</span>
    @endif

    @if($exportFailed)
        <span>Export failed, please try again later.</span>
    @endif
</div>
