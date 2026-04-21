<?php

namespace Modules\Labs\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Booking\Models\Booking;
use Modules\Labs\Actions\StoreLabResultAction;
use Modules\Labs\Http\Requests\StoreLabResultRequest;

class LabsController extends Controller
{
    public function __construct(
        private readonly StoreLabResultAction $storeResultAction,
    ) {}

    public function index(): Response
    {
        $date = request('date', today()->toDateString());
        $search = request('search');

        $queue = Booking::query()
            ->with(['doctor', 'diagnosticResults'])
            ->where('dept', 'labs')
            ->whereDate('date', $date)
            ->when($search, fn ($q) => $q->where(function ($q2) use ($search) {
                $q2->where('patient_name', 'like', "%{$search}%")
                    ->orWhere('file_no', 'like', "%{$search}%");
            }))
            ->orderBy('time')
            ->paginate(25);

        return Inertia::render('labs/Index', [
            'queue' => $queue,
            'date' => $date,
            'filters' => ['search' => $search],
        ]);
    }

    public function storeResult(StoreLabResultRequest $request, string $bookingId): RedirectResponse
    {
        $this->storeResultAction->execute($bookingId, $request->validated(), $request->user()->id);

        return back()->with('success', 'تم تسجيل نتيجة الفحص بنجاح.');
    }
}
