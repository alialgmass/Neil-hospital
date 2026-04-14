<?php

namespace Modules\Doctor\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Models\DoctorPayment;
use Modules\Doctor\Models\DoctorShift;

class DoctorService
{
    public function list(?string $search = null): LengthAwarePaginator
    {
        return Doctor::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(30);
    }

    public function allActive(): \Illuminate\Database\Eloquent\Collection
    {
        return Doctor::where('is_active', true)->orderBy('name')->get();
    }

    public function getActiveForDept(string $dept): \Illuminate\Database\Eloquent\Collection
    {
        return Doctor::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function payments(array $filters = []): LengthAwarePaginator
    {
        return DoctorPayment::query()
            ->with('doctor:id,name')
            ->when($filters['doctor_id'] ?? null, fn ($q, $v) => $q->where('doctor_id', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('paid_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('paid_at', '<=', $v))
            ->orderByDesc('paid_at')
            ->paginate(30);
    }

    public function shifts(array $filters = []): LengthAwarePaginator
    {
        return DoctorShift::query()
            ->with('doctor:id,name')
            ->when($filters['doctor_id'] ?? null, fn ($q, $v) => $q->where('doctor_id', $v))
            ->when($filters['date'] ?? null, fn ($q, $v) => $q->whereDate('shift_date', $v))
            ->orderByDesc('shift_date')
            ->paginate(30);
    }

    public function openShift(string $doctorId, string $shiftDate, ?string $notes = null): DoctorShift
    {
        return DoctorShift::create([
            'doctor_id'  => $doctorId,
            'shift_date' => $shiftDate,
            'status'     => 'open',
            'started_at' => now(),
            'notes'      => $notes,
        ]);
    }

    public function closeShift(string $id): void
    {
        $shift = DoctorShift::findOrFail($id);
        $shift->update([
            'status'   => 'closed',
            'ended_at' => now(),
        ]);
    }

    public function handoverShift(string $id, array $data): void
    {
        $shift = DoctorShift::findOrFail($id);
        $shift->update([
            'status'   => 'handed_over',
            'ended_at' => now(),
            'notes'    => ($shift->notes ? $shift->notes . "\n" : '') . ($data['notes'] ?? ''),
        ]);
    }

    public function shiftSummary(string $id): array
    {
        $shift   = DoctorShift::with('doctor:id,name')->findOrFail($id);
        $revenue = \Modules\Booking\Models\Booking::query()
            ->where('doctor_id', $shift->doctor_id)
            ->whereDate('visit_date', $shift->shift_date)
            ->where('pay_status', 'paid')
            ->sum('price');

        $bookingsCount = \Modules\Booking\Models\Booking::query()
            ->where('doctor_id', $shift->doctor_id)
            ->whereDate('visit_date', $shift->shift_date)
            ->count();

        $pendingCount = \Modules\Booking\Models\Booking::query()
            ->where('doctor_id', $shift->doctor_id)
            ->whereDate('visit_date', $shift->shift_date)
            ->whereIn('status', ['confirmed', 'waiting', 'in_progress'])
            ->count();

        return [
            'shift'          => $shift,
            'bookings_count' => $bookingsCount,
            'revenue'        => (float) $revenue,
            'pending_count'  => $pendingCount,
        ];
    }
}
