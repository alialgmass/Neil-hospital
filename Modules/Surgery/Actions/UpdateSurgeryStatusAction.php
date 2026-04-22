<?php

namespace Modules\Surgery\Actions;

use App\Services\ActivityLogService;
use Modules\Surgery\Models\OrBed;
use Modules\Surgery\Models\Surgery;

class UpdateSurgeryStatusAction
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    public function execute(string $id, string $status): Surgery
    {
        $surgery = Surgery::findOrFail($id);
        $surgery->update(['status' => $status]);

        if (in_array($status, ['completed', 'cancelled']) && $surgery->or_bed_id) {
            OrBed::where('id', $surgery->or_bed_id)->update(['status' => 'available']);
        }

        $this->activityLog->log(
            action: 'status_updated',
            module: $surgery->dept->value ?? 'surgery',
            recordId: $id,
            description: "تغيير حالة الإجراء إلى: {$status}",
        );

        return $surgery->fresh();
    }
}
