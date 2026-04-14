<?php

namespace Modules\Doctor\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Doctor\Models\Doctor;

class DoctorController extends Controller
{
    public function index(): Response
    {
        $search  = request('search');
        $doctors = Doctor::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(30);

        return Inertia::render('doctors/Index', [
            'doctors' => $doctors,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'specialty' => ['nullable', 'string', 'max:100'],
            'phone'     => ['nullable', 'string', 'max:30'],
            'fee_type'  => ['required', 'in:percentage,fixed,insurance'],
            'fee_value' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        Doctor::create($data);

        return back()->with('success', 'تم إضافة الطبيب بنجاح.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $doctor = Doctor::findOrFail($id);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'specialty' => ['nullable', 'string', 'max:100'],
            'phone'     => ['nullable', 'string', 'max:30'],
            'fee_type'  => ['required', 'in:percentage,fixed,insurance'],
            'fee_value' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $doctor->update($data);

        return back()->with('success', 'تم تعديل بيانات الطبيب بنجاح.');
    }
}
