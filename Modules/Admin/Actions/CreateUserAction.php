<?php

namespace Modules\Admin\Actions;

use App\Models\User;
use Modules\Admin\Services\ActivityLogService;
use Modules\Admin\Services\UserManagementService;

class CreateUserAction
{
    public function __construct(
        private readonly UserManagementService $userManagementService,
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function execute(array $data): User
    {
        $user = $this->userManagementService->createUser($data);

        $this->activityLogService->log(
            action: 'created',
            module: 'users',
            recordId: (string) $user->id,
            description: "تم إنشاء مستخدم: {$user->name}",
            newValues: ['name' => $user->name, 'email' => $user->email, 'role' => $data['role'] ?? null],
        );

        return $user;
    }
}
