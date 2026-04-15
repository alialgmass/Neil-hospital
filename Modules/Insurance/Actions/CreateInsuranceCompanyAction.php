<?php

namespace Modules\Insurance\Actions;

use Modules\Admin\Services\ActivityLogService;
use Modules\Insurance\Models\InsuranceCompany;
use Modules\Insurance\Services\InsuranceService;

class CreateInsuranceCompanyAction
{
    public function __construct(
        private readonly InsuranceService $insuranceService,
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function execute(array $data): InsuranceCompany
    {
        $company = $this->insuranceService->create($data);

        $this->activityLogService->log(
            action: 'create',
            module: 'insurance',
            recordId: $company->id,
            description: "إضافة شركة تأمين: {$company->name}",
        );

        return $company;
    }
}
