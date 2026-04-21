<?php

namespace Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Admin\Actions\CreateServiceAction;
use Modules\Admin\Actions\UpdateServiceAction;
use Modules\Admin\Http\Requests\StoreServiceRequest;
use Modules\Admin\Http\Requests\UpdateServiceRequest;
use Modules\Booking\Models\Service;

class ServicesController extends Controller
{
    public function __construct(
        private readonly CreateServiceAction $createAction,
        private readonly UpdateServiceAction $updateAction,
    ) {}

    public function index(): Response
    {
        $dept = request('dept');
        $search = request('search');

        $services = Service::query()
            ->when($dept, fn ($q) => $q->where('dept', $dept))
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('dept')
            ->orderBy('name')
            ->paginate(40);

        return Inertia::render('admin/Services', [
            'services' => $services,
            'filters' => compact('dept', 'search'),
        ]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $this->createAction->execute($request->validated());

        return back()->with('success', 'تم إضافة الخدمة بنجاح.');
    }

    public function update(UpdateServiceRequest $request, string $id): RedirectResponse
    {
        $service = Service::findOrFail($id);
        $this->updateAction->execute($service, $request->validated());

        return back()->with('success', 'تم تعديل الخدمة بنجاح.');
    }
}
