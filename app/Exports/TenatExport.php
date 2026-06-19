<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TenatExport implements FromCollection, WithHeadings, WithTitle
{
    use Exportable;

    public function __construct(protected mixed $selectedColumns, protected mixed $tenantsData, protected mixed $sheetIndex)
    {
    }

    /**
     * @return Collection<int|string, mixed>
     */
    public function collection()
    {
        return collect((array) $this->tenantsData);
    }

    /**
     * @return array<mixed>
     */
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

        return array_map(fn ($column) => $headingsMap[$column] ?? $column, $this->selectedColumns);
    }

    public function title(): string
    {
        return 'Sheet '.$this->sheetIndex;
    }
}
