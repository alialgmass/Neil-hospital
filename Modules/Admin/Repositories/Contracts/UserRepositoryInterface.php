<?php

namespace Modules\Admin\Repositories\Contracts;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function paginate(int $perPage = 20): LengthAwarePaginator;

    public function findById(int $id): User;

    public function create(array $data): User;

    public function assignRole(User $user, string $role): void;
}
