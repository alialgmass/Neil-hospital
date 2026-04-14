<?php

namespace Modules\Inventory\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Inventory\Models\Supplier;
use Modules\Inventory\Repositories\Contracts\SupplierRepositoryInterface;

class SupplierRepository implements SupplierRepositoryInterface
{
    public function all(): Collection
    {
        return Supplier::orderBy('name')->get();
    }

    public function findById(string $id): Supplier
    {
        return Supplier::findOrFail($id);
    }

    public function create(array $data): Supplier
    {
        return Supplier::create($data);
    }

    public function update(string $id, array $data): Supplier
    {
        $supplier = $this->findById($id);
        $supplier->update($data);

        return $supplier->fresh();
    }
}
