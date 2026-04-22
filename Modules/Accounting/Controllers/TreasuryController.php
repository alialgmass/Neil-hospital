<?php

namespace Modules\Accounting\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Accounting\Http\Requests\StoreTreasuryRequest;
use Modules\Accounting\Models\TreasuryEntry;
use Modules\Accounting\Services\TreasuryService;

class TreasuryController extends Controller
{
    public function __construct(
        private readonly TreasuryService $treasuryService,
        private readonly ActivityLogService $activityLog,
    ) {}

    public function index(): Response
    {
        $filters = request()->only(['type', 'from', 'to']);

        $todayIn  = TreasuryEntry::where('type', 'in')->whereDate('date', today())->sum('amount');
        $todayOut = TreasuryEntry::where('type', 'out')->whereDate('date', today())->sum('amount');

        return Inertia::render('treasury/Index', [
            'entries'  => $this->treasuryService->list($filters, 30),
            'balance'  => $this->treasuryService->balance(),
            'todayNet' => (float) ($todayIn - $todayOut),
            'filters'  => $filters,
        ]);
    }

    public function store(StoreTreasuryRequest $request): RedirectResponse
    {
        $entry = $this->treasuryService->record($request->validated());

        $this->activityLog->log(
            action:      'treasury_entry',
            module:      'treasury',
            recordId:    $entry->id,
            description: "{$entry->type}: {$entry->description} — {$entry->amount} ج.م",
        );

        return back()->with('success', 'تم تسجيل حركة الخزنة بنجاح.');
    }
}
