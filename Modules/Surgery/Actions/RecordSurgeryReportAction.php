<?php

namespace Modules\Surgery\Actions;

use App\Services\ActivityLogService;
use Modules\Surgery\Models\Surgery;
use Modules\Surgery\Repositories\Contracts\SurgeryRepositoryInterface;

class RecordSurgeryReportAction
{
    public function __construct(
        private readonly SurgeryRepositoryInterface $surgeryRepository,
        private readonly ActivityLogService $activityLog,
    ) {}

    public function execute(string $surgeryId, array $report): Surgery
    {
        $surgery = $this->surgeryRepository->update($surgeryId, [
            'op_report'      => $report['op_report'] ?? null,
            'post_op_notes'  => $report['post_op_notes'] ?? null,
            'complications'  => $report['complications'] ?? null,
            'status'         => 'completed',
            'ended_at'       => now(),
        ]);

        $this->activityLog->log(
            action:      'report_recorded',
            module:      $surgery->dept,
            recordId:    $surgeryId,
            description: "تسجيل تقرير العملية: {$surgeryId}",
        );

        return $surgery;
    }
}
