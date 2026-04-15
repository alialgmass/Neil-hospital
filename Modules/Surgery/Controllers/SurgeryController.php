<?php

namespace Modules\Surgery\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Doctor\Models\Doctor;
use Modules\Surgery\Actions\RecordSuppliesUsedAction;
use Modules\Surgery\Actions\RecordSurgeryReportAction;
use Modules\Surgery\Actions\ScheduleSurgeryAction;
use Modules\Surgery\DTOs\SuppliesUsedData;
use Modules\Surgery\DTOs\SurgeryData;
use Modules\Surgery\Http\Requests\RecordSuppliesRequest;
use Modules\Surgery\Http\Requests\StoreSurgeryRequest;
use Modules\Surgery\Services\SurgeryService;

class SurgeryController extends Controller
{
    public function __construct(
        private readonly SurgeryService $surgeryService,
        private readonly ScheduleSurgeryAction $scheduleAction,
        private readonly RecordSurgeryReportAction $reportAction,
        private readonly RecordSuppliesUsedAction $suppliesAction,
    ) {}

    public function index(): Response
    {
        $dept = request()->segment(1, 'surgery'); // derive dept from URL segment
        $status = request('status');

        $page = match ($dept) {
            'lasik' => 'lasik/Index',
            'laser' => 'laser/Index',
            default => 'surgery/Index',
        };

        $settings = DB::table('settings')->whereIn('key', ['surgery_beds', 'lasik_beds'])->pluck('value', 'key');
        $totalBeds = (int) ($dept === 'lasik'
            ? ($settings['lasik_beds'] ?? 20)
            : ($settings['surgery_beds'] ?? 30));

        $surgeries = $this->surgeryService->list($dept, $status, 200); // load more to fill bed map

        return Inertia::render($page, [
            'surgeries' => $surgeries,
            'totalBeds' => $totalBeds,
            'doctors' => Doctor::select('id', 'name')->orderBy('name')->get(),
            'dept' => $dept,
            'filters' => ['status' => $status],
        ]);
    }

    public function store(StoreSurgeryRequest $request): RedirectResponse
    {
        $data = SurgeryData::fromArray($request->validated());
        $this->scheduleAction->execute($data);

        return back()->with('success', 'تم جدولة الإجراء بنجاح.');
    }

    public function report(string $id): RedirectResponse
    {
        $this->reportAction->execute($id, request()->only(['op_report', 'post_op_notes', 'complications']));

        return back()->with('success', 'تم تسجيل تقرير العملية.');
    }

    public function supplies(RecordSuppliesRequest $request): RedirectResponse
    {
        $data = SuppliesUsedData::fromArray($request->validated());
        $this->suppliesAction->execute($data);

        return back()->with('success', 'تم تسجيل المستلزمات المستخدمة.');
    }
}
