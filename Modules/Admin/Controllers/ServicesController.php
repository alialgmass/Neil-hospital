<?php

namespace Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Booking\Models\Service;

class ServicesController extends Controller
{
    public function index(): Response
    {
        $dept   = request('dept');
        $search = request('search');

        $services = Service::query()
            ->when($dept,   fn ($q) => $q->where('dept', $dept))
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('dept')
            ->orderBy('name')
            ->paginate(40);

        return Inertia::render('admin/Services', [
            'services' => $services,
            'filters'  => compact('dept', 'search'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:200'],
            'dept'        => ['required', 'in:clinic,labs,surgery,lasik,laser'],
            'price'       => ['nullable', 'numeric', 'min:0'],
            'ins_price'   => ['nullable', 'numeric', 'min:0'],
            'center_type' => ['required', 'in:pct,fixed'],
            'center_val'  => ['nullable', 'numeric', 'min:0'],
            'duration_mins' => ['nullable', 'integer', 'min:1'],
            'status'      => ['nullable', 'in:active,inactive'],
        ]);

        $service = Service::create($data);
        $this->computeShares($service);

        return back()->with('success', 'تم إضافة الخدمة بنجاح.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $service = Service::findOrFail($id);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:200'],
            'price'       => ['nullable', 'numeric', 'min:0'],
            'ins_price'   => ['nullable', 'numeric', 'min:0'],
            'center_type' => ['required', 'in:pct,fixed'],
            'center_val'  => ['nullable', 'numeric', 'min:0'],
            'duration_mins' => ['nullable', 'integer', 'min:1'],
            'status'      => ['nullable', 'in:active,inactive'],
        ]);

        $service->update($data);
        $this->computeShares($service->fresh());

        return back()->with('success', 'تم تعديل الخدمة بنجاح.');
    }

    private function computeShares(Service $service): void
    {
        $price       = (float) $service->price;
        $centerShare = $service->center_type === 'pct'
            ? round($price * ($service->center_val / 100), 2)
            : (float) $service->center_val;

        $service->update([
            'center_share' => $centerShare,
            'dr_share'     => max(0, $price - $centerShare),
        ]);
    }
}
