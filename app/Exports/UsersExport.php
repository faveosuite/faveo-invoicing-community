<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class UsersExport implements FromCollection, WithHeadings, WithTitle
{
    use Exportable;

    public function __construct(protected $selectedColumns, protected $usersData, protected $sheetIndex)
    {
    }

    public function collection()
    {
        return collect($this->usersData);
    }

    public function headings(): array
    {
        $headingsMap = [
            'name' => 'Name',
            'email' => 'Email',
            'mobile' => 'Mobile',
            'country' => 'Country',
            'created_at' => 'Registered on',
            'active' => 'Email status',
            'mobile_verified' => 'Mobile status',
            'is_2fa_enabled' => '2FA status',
        ];

        return array_map(fn ($column) => $headingsMap[$column] ?? $column, $this->selectedColumns);
    }

    public function title(): string
    {
        return 'Sheet '.$this->sheetIndex;
    }
}
