<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TenatExport implements FromCollection, WithHeadings, WithTitle
{
    use Exportable;

    protected $selectedColumns;
    protected $tenantsData;
    protected $sheetIndex;

    public function __construct($selectedColumns, $tenantsData, $sheetIndex)
    {
        $this->selectedColumns = $selectedColumns;
        $this->tenantsData = $tenantsData;
        $this->sheetIndex = $sheetIndex;
    }

    public function collection()
    {
        return collect($this->tenantsData);
    }

    public function headings(): array
    {
        $headingsMap = [
            'name' => 'User',
            'email' => 'Email',
            'mobile' => 'Mobile',
            'Order' => 'Order',
            'Expiry day' => 'Expiry Day',
            'Deletion day' => 'Deletion Day',
            'plan' => 'Plan',
            'tenats' => 'Tenats',
            'domain' => 'Domain',
            'status' => 'Order Status',
            'db_name' => 'Database Name',
            'db_username' => 'Database Username',
        ];

        return array_map(function ($column) use ($headingsMap) {
            return $headingsMap[$column] ?? $column;
        }, $this->selectedColumns);
    }

    public function title(): string
    {
        return 'Sheet '.$this->sheetIndex;
    }
}
