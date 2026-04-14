<?php

namespace Modules\Accounting\Services;

use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\Account;

class IncomeStatementService
{
    /**
     * Build income statement (P&L) grouped by revenue and expense accounts.
     */
    public function get(?string $from = null, ?string $to = null): array
    {
        $accounts = Account::where('is_active', true)
            ->whereIn('group', ['revenues', 'expenses'])
            ->orderBy('code')
            ->get();

        $revenues = [];
        $expenses = [];
        $totalRevenue = 0.0;
        $totalExpense = 0.0;

        foreach ($accounts as $account) {
            $debits = (float) DB::table('journal_entries')
                ->where('debit_account_id', $account->id)
                ->when($from, fn ($q) => $q->whereDate('date', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('date', '<=', $to))
                ->sum('amount');

            $credits = (float) DB::table('journal_entries')
                ->where('credit_account_id', $account->id)
                ->when($from, fn ($q) => $q->whereDate('date', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('date', '<=', $to))
                ->sum('amount');

            // Revenue accounts: credit-natured — balance = credits - debits
            // Expense accounts: debit-natured  — balance = debits  - credits
            $balance = $account->group === 'revenues'
                ? $credits - $debits
                : $debits - $credits;

            $row = [
                'code' => $account->code,
                'name' => $account->name,
                'balance' => $balance,
            ];

            if ($account->group === 'revenues') {
                $revenues[] = $row;
                $totalRevenue += $balance;
            } else {
                $expenses[] = $row;
                $totalExpense += $balance;
            }
        }

        return [
            'revenues' => $revenues,
            'expenses' => $expenses,
            'totalRevenue' => $totalRevenue,
            'totalExpense' => $totalExpense,
            'netIncome' => $totalRevenue - $totalExpense,
        ];
    }
}
