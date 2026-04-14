<?php

namespace Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ArchiveController extends Controller
{
    public function index(): Response
    {
        $search = request('search');
        $dept = request('dept');
        $from = request('from');
        $to = request('to');

        $bookings = DB::table('bookings')
            ->leftJoin('doctors', 'bookings.doctor_id', '=', 'doctors.id')
            ->select(
                'bookings.id',
                'bookings.file_no',
                'bookings.patient_name',
                'bookings.patient_phone',
                'bookings.dept',
                'bookings.visit_date',
                'bookings.status',
                'bookings.pay_status',
                'bookings.price',
                'doctors.name as doctor_name',
            )
            ->whereIn('bookings.status', ['completed', 'cancelled'])
            ->when($search, fn ($q) => $q->where(function ($q2) use ($search) {
                $q2->where('bookings.patient_name', 'like', "%{$search}%")
                    ->orWhere('bookings.file_no', 'like', "%{$search}%");
            }))
            ->when($dept, fn ($q) => $q->where('bookings.dept', $dept))
            ->when($from, fn ($q) => $q->whereDate('bookings.visit_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('bookings.visit_date', '<=', $to))
            ->orderByDesc('bookings.visit_date')
            ->paginate(30);

        return Inertia::render('admin/Archive', [
            'bookings' => $bookings,
            'filters' => compact('search', 'dept', 'from', 'to'),
        ]);
    }
}
