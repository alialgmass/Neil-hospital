# Contract: Reporting Module

**Module**: `Modules/Reporting`
**Permission prefix**: `reports.financial`, `reports.clinical`

All report routes accept `from_date` and `to_date` query params (default: current month).
All report pages include an `ExportBar` component offering Excel download and Print.

---

## Dashboard

### GET /dashboard
**Controller**: `DashboardController@index`
**Permission**: `dashboard` (all roles)
**Props**:
```json
{
  "stats": {
    "bookings_today": 12,
    "revenue_today": 8500.00,
    "surgeries_today": 3,
    "lab_exams_today": 5,
    "low_stock_count": 2
  },
  "recent_bookings": [...last 10...],
  "dept_revenue_today": {
    "clinic": 2000.00,
    "labs": 1500.00,
    "surgery": 4000.00,
    "lasik": 0,
    "laser": 1000.00
  },
  "pending_bookings": [...waiting/confirmed for today...]
}
```

---

## Report Routes

### GET /reports/dept-revenue
**Permission**: `reports.financial`
**Props**:
```json
{
  "data": [
    {
      "dept": "surgery",
      "dept_label": "العمليات",
      "doctor_name": "دكتور احمد عبده",
      "cases": 8,
      "total_revenue": 40000.00,
      "center_share": 8000.00,
      "dr_share": 32000.00
    }
  ],
  "totals": { "cases": 35, "total": 95000.00 },
  "filters": { "from_date": "...", "to_date": "..." }
}
```

### GET /reports/cases
**Permission**: `reports.clinical`
**Props**: Full booking list with patient details, dept, doctor, price, status

### GET /reports/doctor-claims
**Permission**: `drpayments.view`
**Props**: Per-doctor claim breakdown (matches DoctorClaimsController structure)

### GET /reports/doctor-payments
**Permission**: `drpayments.view`
**Props**: Payment history per doctor with period covered and method

### GET /reports/insurance-claims
**Permission**: `reports.financial`
**Props**: Bookings with `pay_method=insurance`, grouped by insurance company

### GET /reports/inventory-movement
**Permission**: `inventory.view`
**Props**: Purchase invoices + stock permit transactions per item, with running balance

### GET /reports/purchase-prices
**Permission**: `inventory.view`
**Props**: Purchase invoice items with supplier, date, qty, unit_cost, total

### GET /reports/profit-loss
**Permission**: `reports.financial`
**Props**: Same as income statement but with department breakdown

### GET /reports/expense-analysis
**Permission**: `reports.financial`
**Props**: Expenses grouped by account, with % of total

### GET /reports/system-log
**Permission**: `users.manage`
**Props**: Activity log entries with user, action, module, timestamp

---

## Excel Export

All reports support `GET /reports/{type}/export?from_date=...&to_date=...`
**Action**: `ExcelExportService::export(string $reportType, ReportFilterData $filter)`
**Returns**: File download response (XLSX)
**Implementation**: `maatwebsite/laravel-excel` with `FromQuery` + `WithHeadings` concerns
