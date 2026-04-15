<?php

namespace Modules\Reporting\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DeptRevenueExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly array $data) {}

    public function collection(): Collection
    {
        return collect($this->data['rows']);
    }

    public function headings(): array
    {
        return ['القسم', 'الطبيب', 'عدد الحالات', 'الإيراد', 'تأمين', 'المريض'];
    }

    public function map($row): array
    {
        return [
            $row->dept,
            $row->doctor_name ?? '—',
            $row->cases,
            $row->revenue,
            $row->ins_amount,
            $row->patient_amount,
        ];
    }
}
