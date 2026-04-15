<?php

namespace Modules\Insurance\Actions;

use Modules\Admin\Services\ActivityLogService;
use Modules\Insurance\Models\PriceList;
use Modules\Insurance\Services\InsuranceService;

class ManagePriceListAction
{
    public function __construct(
        private readonly InsuranceService $insuranceService,
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function create(array $data, array $items): PriceList
    {
        $priceList = $this->insuranceService->createPriceList($data, $items);

        $this->activityLogService->log(
            action: 'create',
            module: 'insurance',
            recordId: $priceList->id,
            description: "إنشاء قائمة أسعار: {$priceList->name}",
        );

        return $priceList;
    }
}
