<?php

namespace Modules\Inventory\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\Inventory\Models\InventoryItem;

class InventoryImport implements ToCollection, WithHeadingRow
{
    public int $created = 0;

    public int $updated = 0;

    public int $skipped = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? $row['الاسم'] ?? ''));

            if (empty($name)) {
                $this->skipped++;

                continue;
            }

            $existing = InventoryItem::where('name', $name)->first();

            $data = [
                'name' => $name,
                'code' => $row['code'] ?? $row['الكود'] ?? null,
                'category' => $row['category'] ?? $row['التصنيف'] ?? null,
                'unit' => $row['unit'] ?? $row['الوحدة'] ?? null,
                'unit_cost' => (float) ($row['unit_cost'] ?? $row['التكلفة'] ?? 0),
                'sell_price' => (float) ($row['sell_price'] ?? $row['سعر_البيع'] ?? 0),
                'min_quantity' => (float) ($row['min_quantity'] ?? $row['الحد_الأدنى'] ?? 0),
            ];

            if ($existing) {
                $existing->update($data);
                $this->updated++;
            } else {
                InventoryItem::create([...$data, 'quantity' => 0]);
                $this->created++;
            }
        }
    }
}
