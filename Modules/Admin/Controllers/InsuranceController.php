<?php

namespace Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Booking\Models\InsuranceCompany;

class InsuranceController extends Controller
{
    public function index(): Response
    {
        $companies = InsuranceCompany::orderBy('name')->paginate(30);

        return Inertia::render('admin/Insurance', [
            'companies' => $companies,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'code'           => ['nullable', 'string', 'max:20'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'coverage_pct'   => ['nullable', 'numeric', 'min:0', 'max:100'],
            'disc_pct'       => ['nullable', 'numeric', 'min:0', 'max:100'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'email'          => ['nullable', 'email', 'max:100'],
            'contract_no'    => ['nullable', 'string', 'max:50'],
        ]);

        InsuranceCompany::create($data);

        return back()->with('success', 'تم إضافة شركة التأمين بنجاح.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $company = InsuranceCompany::findOrFail($id);

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'coverage_pct'   => ['nullable', 'numeric', 'min:0', 'max:100'],
            'disc_pct'       => ['nullable', 'numeric', 'min:0', 'max:100'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'email'          => ['nullable', 'email', 'max:100'],
            'status'         => ['nullable', 'in:active,inactive'],
        ]);

        $company->update($data);

        return back()->with('success', 'تم تعديل بيانات شركة التأمين بنجاح.');
    }
}
