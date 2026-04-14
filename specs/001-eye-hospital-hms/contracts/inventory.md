# Contract: Inventory Module

**Module**: `Modules/Inventory`
**Permission prefix**: `inventory.*`, `services.*`

---

## Services & Pricing Routes

### GET /services
**Controller**: ServiceController routes | **Permission**: `services.view`
**Props**: `{ services: [...paginated...], depts: ['clinic','labs','surgery','lasik','laser'] }`

### POST /services
**Permission**: `services.write`
**Request**:
```json
{
  "name": "required|string|max:200",
  "dept": "required|in:clinic,labs,surgery,lasik,laser",
  "price": "required|numeric|min:0",
  "ins_price": "numeric|min:0",
  "center_type": "required|in:pct,fixed",
  "center_val": "required|numeric|min:0",
  "duration_mins": "integer|min:1"
}
```

### POST /services/import
**Permission**: `services.write`
**Action**: Import from XLSX/CSV via `maatwebsite/laravel-excel`
**Request**: `{ file: required|file|mimes:xlsx,csv }` (multipart)
**Response**: `{ added: N, updated: M, skipped: K }`

---

## Inventory Routes

### GET /inventory
**Permission**: `inventory.view`
**Props**:
```json
{
  "items": { "data": [...], "meta": {...} },
  "low_stock_count": 3,
  "filters": { "category": null, "search": null }
}
```
Each item includes `is_low_stock: boolean` (quantity <= min_quantity).

### POST /inventory/items/import
**Permission**: `inventory.write`
**Request**: `{ file: required|file|mimes:xlsx,csv }`

---

## Purchase Invoice Routes

### POST /inventory/purchases
**Action**: `ReceivePurchaseInvoiceAction`
**Workflow**:
1. Create `purchase_invoice` record
2. For each line item: create `purchase_invoice_item`, add qty to `inventory_items.quantity`, update `unit_cost`
3. Post accounting entry: `debit=Inventory asset, credit=Supplier payable`

---

## Stock Permit Routes

### POST /inventory/permits
**Action**: `IssueStockPermitAction` (type=out) or `AddStockPermitAction` (type=in)
**Request**:
```json
{
  "type": "required|in:in,out",
  "date": "required|date",
  "dept": "nullable|string",
  "items": "required|array|min:1",
  "items.*.inventory_item_id": "required|exists:inventory_items,id",
  "items.*.qty": "required|numeric|min:0.01"
}
```
**Business rule (type=out)**: Each item qty MUST NOT exceed `inventory_items.quantity`. Returns 422 if insufficient stock.

---

## Low-Stock Alert

`StockAlertService::getLowStockItems()` returns items where `quantity <= min_quantity`.
Called from:
- `DashboardController` to populate the notification badge count
- `InventoryController@index` to flag rows
