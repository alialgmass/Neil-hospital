<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\TelescopeServiceProvider::class,

    // Hospital modules
    Modules\Booking\Providers\BookingServiceProvider::class,
    Modules\Clinic\Providers\ClinicServiceProvider::class,
    Modules\Surgery\Providers\SurgeryServiceProvider::class,
    Modules\Accounting\Providers\AccountingServiceProvider::class,
    Modules\Inventory\Providers\InventoryServiceProvider::class,
    Modules\Doctor\Providers\DoctorServiceProvider::class,
    Modules\Admin\Providers\AdminServiceProvider::class,
    Modules\Reporting\Providers\ReportingServiceProvider::class,
    Modules\Labs\Providers\LabsServiceProvider::class,
];
