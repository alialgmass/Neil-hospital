<?php

namespace Modules\Doctor\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Doctor\Actions\CreateDoctorAction;
use Modules\Doctor\Actions\UpdateDoctorAction;
use Modules\Doctor\Http\Requests\StoreDoctorRequest;
use Modules\Doctor\Http\Requests\UpdateDoctorRequest;
use Modules\Doctor\Models\Doctor;

class DoctorController extends Controller
{
    public function __construct(
        private readonly CreateDoctorAction $createAction,
        private readonly UpdateDoctorAction $updateAction,
    ) {}

    public function index(): Response
    {
        $search = request('search');
        $doctors = Doctor::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(30);

        return Inertia::render('doctors/Index', [
            'doctors' => $doctors,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(StoreDoctorRequest $request): RedirectResponse
    {
        $this->createAction->execute($request->validated());

        return back()->with('success', 'تم إضافة الطبيب بنجاح.');
    }

    public function update(UpdateDoctorRequest $request, string $id): RedirectResponse
    {
        $doctor = Doctor::findOrFail($id);
        $this->updateAction->execute($doctor, $request->validated());

        return back()->with('success', 'تم تعديل بيانات الطبيب بنجاح.');
    }
}
