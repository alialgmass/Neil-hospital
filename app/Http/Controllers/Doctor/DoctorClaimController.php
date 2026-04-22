<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Doctor\Actions\ProcessDoctorPayment;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Services\ClaimCalculator;

class DoctorClaimController extends Controller
{
    public function __construct(
        private readonly ClaimCalculator $calculator,
        private readonly ProcessDoctorPayment $paymentAction
    ) {}

    public function index(Request $request)
    {
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to   = $request->query('to', now()->endOfMonth()->toDateString());

        $doctors = Doctor::where('is_active', true)->get()->map(function ($doctor) use ($from, $to) {
            $calc = $this->calculator->calculate($doctor, \Carbon\Carbon::parse($from), \Carbon\Carbon::parse($to));
            return array_merge($doctor->toArray(), ['claim' => $calc['stats']['total_claim']]);
        });

        return Inertia::render('doctor/Claims', [
            'doctors' => $doctors,
            'period'  => ['from' => $from, 'to' => $to],
        ]);
    }

    public function calculate(Doctor $doctor, Request $request)
    {
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to   = $request->query('to', now()->endOfMonth()->toDateString());

        $details = $this->calculator->calculate($doctor, \Carbon\Carbon::parse($from), \Carbon\Carbon::parse($to));

        return response()->json($details);
    }

    public function pay(Doctor $doctor, Request $request)
    {
        $data = $request->validate([
            'amount'      => 'required|numeric|min:0',
            'period_from' => 'required|date',
            'period_to'   => 'required|date',
            'method'      => 'required|string',
            'notes'       => 'nullable|string',
        ]);

        $this->paymentAction->execute($doctor, $data);

        return back()->with('success', 'تم صرف مستحقات الطبيب وتسجيل القيود المحاسبية بنجاح');
    }
}
