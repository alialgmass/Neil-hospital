<?php

namespace Modules\Admin\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Modules\Admin\Models\ActivityLog;

class ActivityLogService
{
    /**
     * Record an activity event.
     */
    public function log(
        string $action,
        string $module,
        ?string $recordId = null,
        ?string $description = null,
        array $oldValues = [],
        array $newValues = [],
    ): void {
        $user = Auth::user();

        ActivityLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId,
            'description' => $description,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => Request::ip(),
        ]);
    }
}
