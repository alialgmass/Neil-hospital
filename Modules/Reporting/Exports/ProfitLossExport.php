<?php

namespace Modules\Reporting\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProfitLossExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly array $data) {}

    public function collection(): Collection
    {
        $rows = [];

        foreach ($this->data['revenues'] as $r) {
            $rows[] = ['إيرادات', $r->name, $r->amount];
        }
        $rows[] = ['إجمالي الإيرادات', '', $this->data['totalRevenue']];
        $rows[] = ['', '', ''];

        foreach ($this->data['expenses'] as $e) {
            $rows[] = ['مصروفات', $e->name, $e->amount];
        }
        $rows[] = ['إجمالي المصروفات', '', $this->data['totalExpense']];
        $rows[] = ['صافي الدخل', '', $this->data['netIncome']];

        return collect($rows);
    }

    public function headings(): array
    {
        return ['النوع', 'البند', 'المبلغ'];
    }
}
