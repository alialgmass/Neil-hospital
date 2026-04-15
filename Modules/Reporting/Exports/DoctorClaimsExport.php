<?php

namespace Modules\Reporting\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DoctorClaimsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly array $data) {}

    public function collection(): Collection
    {
        return collect($this->data['rows']);
    }

    public function headings(): array
    {
        return ['الطبيب', 'عدد الحالات', 'إجمالي الفواتير', 'تأمين', 'صافي', 'مستحق الطبيب', 'حصة المركز'];
    }

    public function map($row): array
    {
        return [
            $row->doctor_name,
            $row->cases,
            $row->total_billed,
            $row->ins_amount,
            $row->net_billed,
            $row->doctor_claim,
            $row->center_share,
        ];
    }
}
