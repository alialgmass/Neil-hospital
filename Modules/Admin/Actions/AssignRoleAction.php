<?php

namespace Modules\Admin\Actions;

use Modules\Admin\Services\ActivityLogService;
use Modules\Admin\Services\UserManagementService;

class AssignRoleAction
{
    public function __construct(
        private readonly UserManagementService $userManagementService,
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function execute(int $userId, string $role): void
    {
        $this->userManagementService->assignRole($userId, $role);

        $this->activityLogService->log(
            action: 'updated',
            module: 'users',
            recordId: (string) $userId,
            description: "تم تغيير دور المستخدم إلى: {$role}",
            newValues: ['role' => $role],
        );
    }
}
