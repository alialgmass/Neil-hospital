<?php

namespace Modules\Inventory\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Inventory\Models\Supplier;

interface SupplierRepositoryInterface
{
    public function all(): Collection;

    public function findById(string $id): Supplier;

    public function create(array $data): Supplier;

    public function update(string $id, array $data): Supplier;
}
