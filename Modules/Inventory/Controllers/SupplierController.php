<?php

namespace Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Inventory\Models\Supplier;

class SupplierController extends Controller
{
    public function index(): Response
    {
        $search    = request('search');
        $suppliers = Supplier::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(30);

        return Inertia::render('suppliers/Index', [
            'suppliers' => $suppliers,
            'filters'   => ['search' => $search],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:150'],
            'phone'   => ['nullable', 'string', 'max:30'],
            'email'   => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string'],
            'tax_no'  => ['nullable', 'string', 'max:50'],
        ]);

        Supplier::create($data);

        return back()->with('success', 'تم إضافة المورد بنجاح.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $supplier = Supplier::findOrFail($id);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'phone'     => ['nullable', 'string', 'max:30'],
            'email'     => ['nullable', 'email', 'max:100'],
            'address'   => ['nullable', 'string'],
            'tax_no'    => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ]);

        $supplier->update($data);

        return back()->with('success', 'تم تعديل المورد بنجاح.');
    }
}
