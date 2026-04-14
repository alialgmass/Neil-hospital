<?php

namespace Modules\Admin\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Modules\Admin\Repositories\Contracts\UserRepositoryInterface;

class UserManagementService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function listUsers(): LengthAwarePaginator
    {
        return $this->userRepository->paginate(25);
    }

    public function createUser(array $data): User
    {
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (! empty($data['role'])) {
            $this->userRepository->assignRole($user, $data['role']);
        }

        return $user;
    }

    public function assignRole(int $userId, string $role): void
    {
        $user = $this->userRepository->findById($userId);
        $this->userRepository->assignRole($user, $role);
    }
}
