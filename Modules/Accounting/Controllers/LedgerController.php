<?php

namespace Modules\Accounting\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Accounting\Services\JournalService;
use Modules\Accounting\Services\LedgerService;

class LedgerController extends Controller
{
    public function __construct(
        private readonly LedgerService $ledgerService,
        private readonly JournalService $journalService,
    ) {}

    public function trialBalance(): Response
    {
        $from = request('from');
        $to   = request('to');

        return Inertia::render('ledger/TrialBalance', [
            'rows'    => $this->ledgerService->trialBalance($from, $to),
            'filters' => compact('from', 'to'),
        ]);
    }

    public function accountStatement(): Response
    {
        $accountId = request('account_id');
        $from      = request('from');
        $to        = request('to');

        return Inertia::render('ledger/AccountStatement', [
            'statement' => $accountId
                ? $this->ledgerService->accountStatement($accountId, $from, $to)
                : null,
            'accounts'  => $this->journalService->accounts(),
            'filters'   => compact('account_id', 'from', 'to'),
        ]);
    }
}
