<?php

namespace Modules\Accounting\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Accounting\Models\TreasuryEntry;
use Modules\Accounting\Repositories\Contracts\TreasuryRepositoryInterface;

class TreasuryService
{
    public function __construct(private readonly TreasuryRepositoryInterface $treasuryRepository) {}

    public function list(array $filters = [], int $perPage = 30): LengthAwarePaginator
    {
        return $this->treasuryRepository->paginate($filters, $perPage);
    }

    public function record(array $data): TreasuryEntry
    {
        return $this->treasuryRepository->create([
            ...$data,
            'created_by' => auth()->id(),
        ]);
    }

    public function balance(?string $upToDate = null): array
    {
        return $this->treasuryRepository->balance($upToDate);
    }
}
