# Contract: Accounting Module

**Module**: `Modules/Accounting`
**Permission prefix**: `treasury.*`, `journal.*`

---

## Treasury Routes

### GET /treasury
**Controller**: `TreasuryController@index` | **Permission**: `treasury.view`
**Props**:
```json
{
  "entries": { "data": [...], "meta": {...} },
  "summary": { "total_in": 45000.00, "total_out": 12000.00, "balance": 33000.00 },
  "filters": { "date": "2026-04-14", "type": null }
}
```

### POST /treasury
**Controller**: `TreasuryController@store` | **Permission**: `treasury.write`
**Action**: `RecordTreasuryEntryAction`
**Request**:
```json
{
  "type": "required|in:in,out",
  "description": "required|string|max:300",
  "amount": "required|numeric|min:0.01",
  "date": "required|date",
  "reference_no": "nullable|string|max:50",
  "beneficiary": "nullable|string|max:150",
  "account_id": "nullable|exists:accounts,id"
}
```

---

## Journal Routes

### GET /journal
**Controller**: `JournalController@index` | **Permission**: `journal.view`
**Props**: `{ entries: [...paginated...], accounts: [...all active accounts...] }`

### POST /journal
**Controller**: `JournalController@store` | **Permission**: `journal.write`
**Action**: `PostJournalEntryAction`
**Request**:
```json
{
  "date": "required|date",
  "description": "required|string|max:300",
  "debit_account_id": "required|exists:accounts,id",
  "credit_account_id": "required|exists:accounts,id|different:debit_account_id",
  "amount": "required|numeric|min:0.01",
  "reference": "nullable|string|max:80"
}
```
**Business rule**: `debit_account_id` MUST differ from `credit_account_id` — enforced in both Request and Action.

---

## Chart of Accounts Routes

### GET /accounts
**Controller**: `ChartOfAccountsController@index` | **Permission**: `journal.view`
**Returns**: Tree-structured accounts grouped by `group` (assets/liabilities/equity/revenues/expenses)

### POST /accounts
**Controller**: `ChartOfAccountsController@store` | **Permission**: `journal.write`
**Request**:
```json
{
  "code": "required|string|unique:accounts,code",
  "name": "required|string|max:150",
  "group": "required|in:assets,liabilities,equity,revenues,expenses",
  "nature": "required|in:debit,credit",
  "parent_id": "nullable|exists:accounts,id"
}
```

---

## Reports Routes (Accounting)

### GET /trial-balance
**Controller**: `TrialBalanceController@index` | **Permission**: `reports.financial`
**Query**: `from_date`, `to_date`
**Props**:
```json
{
  "accounts": [
    {
      "code": "1000",
      "name": "الصندوق النقدي",
      "group": "assets",
      "total_debit": 50000.00,
      "total_credit": 12000.00,
      "balance": 38000.00
    }
  ],
  "totals": { "total_debit": 150000.00, "total_credit": 150000.00 }
}
```
**Business rule**: `total_debit` MUST equal `total_credit` (enforced by TrialBalanceService).

### GET /income-statement
**Controller**: `IncomeStatementController@index` | **Permission**: `reports.financial`
**Query**: `from_date`, `to_date`
**Props**:
```json
{
  "revenues": [
    { "name": "إيرادات العيادة", "amount": 25000.00 }
  ],
  "expenses": [
    { "name": "مستحقات الأطباء", "amount": 8000.00 }
  ],
  "total_revenues": 70000.00,
  "total_expenses": 20000.00,
  "net_income": 50000.00
}
```

---

## Auto-posting Rules

When a booking payment is confirmed (`pay_status → paid`), `AutoPostBookingPaymentAction` fires:
1. Treasury entry: `type=in, amount=price-discount, source=booking`
2. Journal entry: `debit=Cash(1000), credit=Revenue(dept-account), amount=center_share`
3. Journal entry: `debit=DrEntitlements(3100), credit=Cash(1000), amount=dr_share` (deferred)
