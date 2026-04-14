<?php

namespace Modules\Labs\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Labs\Models\DiagnosticResult;

class LabsController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    public function index(): Response
    {
        $date    = request('date', today()->toDateString());
        $search  = request('search');

        $queue = \Modules\Booking\Models\Booking::query()
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
            'queue'   => $queue,
            'date'    => $date,
            'filters' => ['search' => $search],
        ]);
    }

    public function storeResult(Request $request, string $bookingId): RedirectResponse
    {
        $data = $request->validate([
            'test_name'    => ['required', 'string', 'max:150'],
            'eye'          => ['nullable', 'in:OD,OS,OU'],
            'result_text'  => ['nullable', 'string'],
            'result_values'=> ['nullable', 'array'],
            'doctor_notes' => ['nullable', 'string'],
        ]);

        $result = DiagnosticResult::create([
            ...$data,
            'booking_id'    => $bookingId,
            'technician_id' => auth()->id(),
            'recorded_at'   => now(),
        ]);

        $this->activityLog->log(
            action:      'result_recorded',
            module:      'labs',
            recordId:    $result->id,
            description: "تسجيل نتيجة: {$result->test_name} للحجز {$bookingId}",
        );

        return back()->with('success', 'تم تسجيل نتيجة الفحص بنجاح.');
    }
}
