<?php

namespace Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Inventory\Models\InventoryItem;
use Modules\Inventory\Models\PurchaseInvoice;
use Modules\Inventory\Models\Supplier;

class PurchaseReturnController extends Controller
{
    public function index(): Response
    {
        $returns = DB::table('purchase_invoices')
            ->leftJoin('suppliers', 'purchase_invoices.supplier_id', '=', 'suppliers.id')
            ->where('purchase_invoices.status', 'cancelled')
            ->select('purchase_invoices.*', 'suppliers.name as supplier_name')
            ->orderByDesc('purchase_invoices.created_at')
            ->paginate(20);

        $invoices = PurchaseInvoice::with(['supplier', 'items'])
            ->where('status', 'posted')
            ->orderByDesc('invoice_date')
            ->get(['id', 'invoice_no', 'invoice_date', 'total', 'supplier_id']);

        return Inertia::render('inventory/PurchaseReturns', [
            'returns' => $returns,
            'invoices' => $invoices,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'invoice_id' => 'required|string|exists:purchase_invoices,id',
            'reason' => 'nullable|string|max:300',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'nullable|string|exists:inventory,id',
            'items.*.item_name' => 'required|string|max:200',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data) {
            $invoice = PurchaseInvoice::findOrFail($data['invoice_id']);

            foreach ($data['items'] as $item) {
                if (! empty($item['item_id'])) {
                    // Deduct returned qty from inventory
                    InventoryItem::where('id', $item['item_id'])
                        ->decrement('quantity', $item['qty']);
                }
            }

            // Reduce supplier balance by return value
            $returnTotal = collect($data['items'])->sum(fn ($i) => $i['qty'] * $i['unit_cost']);

            if ($invoice->supplier_id) {
                Supplier::where('id', $invoice->supplier_id)->decrement('balance', $returnTotal);
            }
        });

        return back()->with('success', 'تم تسجيل المرتجع بنجاح وتعديل المخزون.');
    }
}
