<?php

namespace Modules\Surgery\DTOs;

readonly class SuppliesUsedData
{
    /**
     * @param  array<int, array{item_id: string, name: string, qty: float, unit_cost: float, total: float}>  $items
     */
    public function __construct(
        public string $surgeryId,
        public array $items,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            surgeryId: $data['surgery_id'],
            items:     $data['items'] ?? [],
        );
    }

    public function total(): float
    {
        return array_sum(array_map(fn ($item) => (float) ($item['total'] ?? 0), $this->items));
    }
}
