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

    /**
     * Post a payment for a booking to the treasury and journal.
     *
     * @param  float|null  $amount  Payment amount; defaults to booking's paid_amount.
     */
    public function execute(Booking $booking, ?float $amount = null): void
    {
        $amount ??= (float) $booking->paid_amount;

        if ($amount <= 0) {
            return;
        }

        $date = $booking->visit_date->toDateString();

        // 1. Create treasury entry
        $this->treasuryService->record([
            'type' => 'in',
            'description' => "دفعة حجز: {$booking->file_no} — {$booking->patient_name}",
            'amount' => $amount,
            'date' => $date,
            'source' => 'booking',
            'booking_id' => $booking->id,
        ]);

        // 2. Auto-post journal entry: Debit Cash / Credit Revenue
        $cashAccount = $this->findAccount($booking->pay_method === 'card' ? '1100' : '1000');
        $revenueAccount = $this->findRevenueAccount($booking->dept);

        if ($cashAccount && $revenueAccount) {
            $this->journalService->record([
                'date' => $date,
                'description' => "إيراد حجز: {$booking->file_no} — {$booking->service_name}",
                'debit_account_id' => $cashAccount,
                'credit_account_id' => $revenueAccount,
                'amount' => $amount,
                'source' => 'auto_booking',
                'reference' => $booking->file_no,
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
            'clinic' => '2000',
            'labs' => '2100',
            'surgery' => '2200',
            'lasik' => '2300',
            'laser' => '2400',
        ];

        return $this->findAccount($codeMap[$dept] ?? '2000');
    }
}
