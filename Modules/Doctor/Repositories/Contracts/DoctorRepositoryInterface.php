<?php

namespace Modules\Doctor\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Doctor\Models\Doctor;

interface DoctorRepositoryInterface
{
    public function paginate(?string $search = null, int $perPage = 20): LengthAwarePaginator;

    public function allActive(): Collection;

    public function findById(string $id): Doctor;

    public function create(array $data): Doctor;

    public function update(string $id, array $data): Doctor;
}
