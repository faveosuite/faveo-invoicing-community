<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class InvoiceExport implements FromCollection, WithHeadings, WithTitle
{
    use Exportable;

    public function __construct(protected mixed $selectedColumns, protected mixed $invoicesData, protected mixed $sheetIndex)
    {
    }

    /**
     * @return \Illuminate\Support\Collection<int|string, mixed>
     */
    public function collection()
    {
        return collect((array) $this->invoicesData);
    }

    /**
     * @return array<mixed>
     */
    public function headings(): array
    {
        $headingsMap = [
            'user_id' => 'User',
            'email' => 'Email',
            'mobile' => 'Mobile',
            'country' => 'Country',
            'grand_total' => 'Total',
            'number' => 'InvoiceNo',
            'date' => 'Date',
            'status' => 'Status',
        ];

        return array_map(fn ($column) => $headingsMap[$column] ?? $column, $this->selectedColumns);
    }

    public function title(): string
    {
        return 'Sheet '.$this->sheetIndex;
    }
}
