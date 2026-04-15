<?php

namespace Modules\Accounting\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Booking\Models\Booking;

class SalesInvoiceController extends Controller
{
    public function show(string $bookingId): Response
    {
        $booking = Booking::with(['doctor', 'creator'])->findOrFail($bookingId);

        return Inertia::render('accounting/SalesInvoice', [
            'booking' => $booking,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'booking_id' => 'required|string|exists:bookings,id',
            'amount_paid' => 'required|numeric|min:0',
            'pay_method' => 'required|in:cash,card,transfer,insurance',
            'ins_amount' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

        $booking = Booking::findOrFail($data['booking_id']);

        $totalPaid = (float) $data['amount_paid'];
        $netDue = $booking->price - ((float) ($data['discount'] ?? 0));

        $payStatus = $totalPaid >= $netDue ? 'paid' : ($totalPaid > 0 ? 'partial' : 'unpaid');

        $booking->update([
            'pay_status' => $payStatus,
            'pay_method' => $data['pay_method'],
            'ins_amount' => $data['ins_amount'] ?? 0,
            'discount' => $data['discount'] ?? 0,
        ]);

        return redirect("/booking/{$booking->id}/receipt")->with('success', 'تم إصدار الفاتورة بنجاح.');
    }
}
