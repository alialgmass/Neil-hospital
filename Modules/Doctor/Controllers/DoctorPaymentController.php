<?php

namespace Modules\Doctor\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Doctor\Services\DoctorService;

class DoctorPaymentController extends Controller
{
    public function __construct(private readonly DoctorService $doctorService) {}

    public function index(): Response
    {
        return Inertia::render('doctors/Payments', [
            'payments' => $this->doctorService->payments(request()->only(['doctor_id', 'from', 'to'])),
            'doctors' => $this->doctorService->allActive(),
            'filters' => request()->only(['doctor_id', 'from', 'to']),
        ]);
    }
}
