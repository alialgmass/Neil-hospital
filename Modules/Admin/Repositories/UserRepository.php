<?php

namespace Modules\Admin\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Admin\Repositories\Contracts\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return User::with('roles')->latest()->paginate($perPage);
    }

    public function findById(int $id): User
    {
        return User::with('roles')->findOrFail($id);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function assignRole(User $user, string $role): void
    {
        $user->syncRoles([$role]);
    }
}
