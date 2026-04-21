<?php

namespace Modules\Insurance\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Insurance\Actions\CreateInsuranceCompanyAction;
use Modules\Insurance\Actions\UpdateInsuranceCompanyAction;
use Modules\Insurance\Http\Requests\StoreInsuranceCompanyRequest;
use Modules\Insurance\Http\Requests\UpdateInsuranceCompanyRequest;
use Modules\Insurance\Models\InsuranceClaim;
use Modules\Insurance\Services\InsuranceService;

class InsuranceCompanyController extends Controller
{
    public function __construct(
        private readonly InsuranceService $insuranceService,
        private readonly CreateInsuranceCompanyAction $createAction,
        private readonly UpdateInsuranceCompanyAction $updateAction,
    ) {}

    public function index(): Response
    {
        $search = request('search');
        $companyFilter = request('company_id');
        $companies = $this->insuranceService->list($search, 20);

        $claimsQuery = InsuranceClaim::with(['company:id,name', 'service:id,name'])
            ->when($companyFilter, fn ($q, $v) => $q->where('insurance_company_id', $v))
            ->orderByDesc('claim_date')
            ->orderByDesc('created_at');

        $thisMonth = now()->startOfMonth();
        $monthlyClaimsCount = InsuranceClaim::where('claim_date', '>=', $thisMonth)->count();
        $monthlyClaimsTotal = InsuranceClaim::where('claim_date', '>=', $thisMonth)->sum('insurance_share');

        return Inertia::render('insurance/Companies', [
            'companies' => $companies,
            'filters' => ['search' => $search, 'company_id' => $companyFilter],
            'claims' => $claimsQuery->paginate(30)->withQueryString(),
            'stats' => [
                'monthly_claims_count' => $monthlyClaimsCount,
                'monthly_claims_total' => round((float) $monthlyClaimsTotal, 2),
            ],
        ]);
    }

    public function store(StoreInsuranceCompanyRequest $request): RedirectResponse
    {
        $this->createAction->execute($request->validated());

        return back()->with('success', 'تم إضافة شركة التأمين بنجاح.');
    }

    public function update(UpdateInsuranceCompanyRequest $request, string $id): RedirectResponse
    {
        $this->updateAction->execute($id, $request->validated());

        return back()->with('success', 'تم تعديل شركة التأمين بنجاح.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $company = $this->insuranceService->findById($id);
        $company->delete();

        return back()->with('success', 'تم حذف شركة التأمين.');
    }
}
