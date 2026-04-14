<?php

namespace Modules\Accounting\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Accounting\Models\Account;

class ChartOfAccountsController extends Controller
{
    public function index(): Response
    {
        $accounts = Account::orderBy('code')->get();

        return Inertia::render('accounting/ChartOfAccounts', [
            'accounts' => $accounts,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code'      => ['required', 'string', 'max:20', 'unique:accounts,code'],
            'name'      => ['required', 'string', 'max:150'],
            'group'     => ['required', 'in:assets,liabilities,equity,revenues,expenses'],
            'nature'    => ['required', 'in:debit,credit'],
            'parent_id' => ['nullable', 'exists:accounts,id'],
        ]);

        Account::create($data);

        return back()->with('success', 'تم إضافة الحساب بنجاح.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $account = Account::findOrFail($id);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'group'     => ['required', 'in:assets,liabilities,equity,revenues,expenses'],
            'nature'    => ['required', 'in:debit,credit'],
            'parent_id' => ['nullable', 'exists:accounts,id'],
            'is_active' => ['boolean'],
        ]);

        $account->update($data);

        return back()->with('success', 'تم تعديل الحساب بنجاح.');
    }
}
