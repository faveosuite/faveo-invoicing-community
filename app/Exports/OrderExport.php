<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class OrderExport implements FromCollection, WithHeadings, WithTitle
{
    use Exportable;

    public function __construct(protected mixed $selectedColumns, protected mixed $ordersData, protected mixed $sheetIndex)
    {
    }

    /**
     * @return \Illuminate\Support\Collection<int|string, mixed>
     */
    public function collection()
    {
        return collect((array) $this->ordersData);
    }

    /**
     * @return array<mixed>
     */
    public function headings(): array
    {
        $headingsMap = [
            'client' => 'User',
            'email' => 'Email',
            'mobile' => 'Mobile',
            'number' => 'Order No',
            'product_name' => 'Product',
            'plan_name' => 'Plan',
            'version' => 'Version',
            'agents' => 'Agents',
            'order_status' => 'Status',
            'status' => 'Order Status',
            'order_date' => 'Order Date',
            'update_ends_at' => 'Expiry',
        ];

        return array_map(fn ($column) => $headingsMap[$column] ?? $column, $this->selectedColumns);
    }

    public function title(): string
    {
        return 'Orders';
    }
}
