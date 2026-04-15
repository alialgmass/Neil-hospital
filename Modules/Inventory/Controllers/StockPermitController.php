<?php

namespace Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Inventory\Actions\AddStockPermitAction;
use Modules\Inventory\Actions\IssueStockPermitAction;
use Modules\Inventory\Http\Requests\StoreStockPermitRequest;
use Modules\Inventory\Models\InventoryItem;
use Modules\Inventory\Models\StockPermit;

class StockPermitController extends Controller
{
    public function __construct(
        private readonly IssueStockPermitAction $issueAction,
        private readonly AddStockPermitAction $addAction,
    ) {}

    public function index(): Response
    {
        $permits = StockPermit::query()
            ->with(['items', 'creator'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('inventory/StockPermit', [
            'permits' => $permits,
            'items' => InventoryItem::orderBy('name')->get(['id', 'name', 'unit', 'quantity', 'unit_cost']),
        ]);
    }

    public function issue(StoreStockPermitRequest $request): RedirectResponse
    {
        $this->issueAction->execute(
            $request->only(['department', 'reason', 'notes']),
            $request->input('items', []),
        );

        return back()->with('success', 'تم إصدار إذن الصرف بنجاح.');
    }

    public function add(StoreStockPermitRequest $request): RedirectResponse
    {
        $this->addAction->execute(
            $request->only(['department', 'reason', 'notes']),
            $request->input('items', []),
        );

        return back()->with('success', 'تم إضافة إذن الإضافة بنجاح.');
    }
}
