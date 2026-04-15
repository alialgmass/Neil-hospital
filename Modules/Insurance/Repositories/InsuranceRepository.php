<?php

namespace Modules\Insurance\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Insurance\Models\InsuranceCompany;
use Modules\Insurance\Models\PriceList;
use Modules\Insurance\Repositories\Contracts\InsuranceRepositoryInterface;

class InsuranceRepository implements InsuranceRepositoryInterface
{
    public function paginate(?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        return InsuranceCompany::query()
            ->when($search, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function allActive(): Collection
    {
        return InsuranceCompany::where('status', 'active')->orderBy('name')->get();
    }

    public function findById(string $id): InsuranceCompany
    {
        return InsuranceCompany::findOrFail($id);
    }

    public function create(array $data): InsuranceCompany
    {
        return InsuranceCompany::create($data);
    }

    public function update(string $id, array $data): InsuranceCompany
    {
        $company = $this->findById($id);
        $company->update($data);

        return $company->fresh();
    }

    public function priceLists(string $companyId): Collection
    {
        return PriceList::with('items.service')
            ->where('ins_company_id', $companyId)
            ->get();
    }

    public function createPriceList(array $data, array $items): PriceList
    {
        $priceList = PriceList::create($data);

        foreach ($items as $item) {
            $priceList->items()->create($item);
        }

        return $priceList->load('items.service');
    }
}
