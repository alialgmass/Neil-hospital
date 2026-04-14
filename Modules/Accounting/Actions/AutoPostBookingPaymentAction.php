<?php

namespace Modules\Accounting\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Accounting\Services\JournalService;
use Modules\Accounting\Services\TreasuryService;
use Modules\Booking\Models\Booking;

class AutoPostBookingPaymentAction
{
    public function __construct(
        private readonly TreasuryService $treasuryService,
        private readonly JournalService $journalService,
    ) {}

    public function execute(Booking $booking): void
    {
        if ((float) $booking->price <= 0) {
            return;
        }

        // 1. Create treasury entry
        $this->treasuryService->record([
            'type'        => 'in',
            'description' => "دفعة حجز: {$booking->file_no} — {$booking->patient_name}",
            'amount'      => $booking->price,
            'date'        => $booking->date,
            'source'      => 'booking',
            'booking_id'  => $booking->id,
        ]);

        // 2. Auto-post journal entry: Debit Cash / Credit Revenue
        $cashAccount    = $this->findAccount($booking->pay_method === 'card' ? '1100' : '1000'); // Bank or Cash
        $revenueAccount = $this->findRevenueAccount($booking->dept);

        if ($cashAccount && $revenueAccount) {
            $this->journalService->record([
                'date'              => $booking->date,
                'description'       => "إيراد حجز: {$booking->file_no} — {$booking->service}",
                'debit_account_id'  => $cashAccount,
                'credit_account_id' => $revenueAccount,
                'amount'            => $booking->price,
                'source'            => 'auto_booking',
                'reference'         => $booking->file_no,
            ]);
        }
    }

    private function findAccount(string $code): ?string
    {
        return DB::table('accounts')->where('code', $code)->value('id');
    }

    private function findRevenueAccount(string $dept): ?string
    {
        $codeMap = [
            'clinic'  => '2000',
            'labs'    => '2100',
            'surgery' => '2200',
            'lasik'   => '2300',
            'laser'   => '2400',
        ];

        $code = $codeMap[$dept] ?? '2000';

        return $this->findAccount($code);
    }
}
