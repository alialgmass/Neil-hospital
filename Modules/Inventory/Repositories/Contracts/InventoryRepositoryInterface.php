<?php

namespace Modules\Inventory\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Inventory\Models\InventoryItem;

interface InventoryRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 30): LengthAwarePaginator;

    public function findById(string $id): InventoryItem;

    public function create(array $data): InventoryItem;

    public function update(string $id, array $data): InventoryItem;

    public function lowStockCount(): int;

    public function adjustQuantity(string $id, float $delta): void;
}
