<?php

namespace Modules\Doctor\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Repositories\Contracts\DoctorRepositoryInterface;

class DoctorRepository implements DoctorRepositoryInterface
{
    public function paginate(?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        return Doctor::query()
            ->when($search, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function allActive(): Collection
    {
        return Doctor::where('status', 'active')->orderBy('name')->get();
    }

    public function findById(string $id): Doctor
    {
        return Doctor::findOrFail($id);
    }

    public function create(array $data): Doctor
    {
        return Doctor::create($data);
    }

    public function update(string $id, array $data): Doctor
    {
        $doctor = $this->findById($id);
        $doctor->update($data);

        return $doctor->fresh();
    }
}
