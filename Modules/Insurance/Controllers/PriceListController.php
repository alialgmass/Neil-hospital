<?php

namespace Modules\Insurance\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Insurance\Actions\ManagePriceListAction;
use Modules\Insurance\Services\InsuranceService;
use Modules\Inventory\Models\Service;

class PriceListController extends Controller
{
    public function __construct(
        private readonly InsuranceService $insuranceService,
        private readonly ManagePriceListAction $managePriceListAction,
    ) {}

    public function index(): Response
    {
        return Inertia::render('insurance/PriceLists', [
            'priceLists' => $this->insuranceService->allPriceLists(),
            'companies' => $this->insuranceService->allActive(),
            'services' => Service::active()->orderBy('dept')->orderBy('name')->get(['id', 'name', 'dept', 'price', 'ins_price']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'type' => 'required|in:cash,insurance,vip,special',
            'ins_company_id' => 'nullable|string|exists:insurance_companies,id',
            'ins_coverage' => 'nullable|numeric|min:0|max:100',
            'discount_pct' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.service_id' => 'required|string|exists:services,id',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $items = $data['items'] ?? [];
        unset($data['items']);

        $this->managePriceListAction->create($data, $items);

        return back()->with('success', 'تم إنشاء قائمة الأسعار بنجاح.');
    }
}
