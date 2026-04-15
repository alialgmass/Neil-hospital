<?php

namespace Modules\Inventory\Actions;

use Modules\Admin\Services\ActivityLogService;
use Modules\Inventory\Models\PurchaseInvoice;
use Modules\Inventory\Services\PurchaseInvoiceService;

class ReceivePurchaseInvoiceAction
{
    public function __construct(
        private readonly PurchaseInvoiceService $purchaseInvoiceService,
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function execute(array $data, array $items): PurchaseInvoice
    {
        $invoice = $this->purchaseInvoiceService->create($data, $items);

        $this->activityLogService->log(
            action: 'create',
            module: 'inventory',
            recordId: $invoice->id,
            description: "استلام فاتورة شراء رقم {$invoice->invoice_no} بمبلغ {$invoice->total} ج",
        );

        return $invoice;
    }
}
