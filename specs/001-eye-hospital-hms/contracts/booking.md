# Contract: Booking Module

**Module**: `Modules/Booking`
**Routes**: Inertia.js (web routes, session auth)
**Permission prefix**: `booking.*`

---

## Inertia Routes & Controller Actions

All routes are prefixed with `/booking` and protected by `auth` + Spatie permission middleware.

### GET /booking
**Controller**: `BookingController@index`
**Permission**: `booking.view`
**Props returned**:
```json
{
  "bookings": {
    "data": [
      {
        "id": "01HWQZ...",
        "file_no": "MRN-2026-00001",
        "patient_name": "أحمد محمد",
        "patient_phone": "0100...",
        "dept": "clinic",
        "service_name": "كشف أول",
        "doctor": { "id": "...", "name": "دكتور احمد عبده" },
        "date": "2026-04-14",
        "price": 150.00,
        "pay_status": "unpaid",
        "status": "waiting"
      }
    ],
    "meta": { "total": 120, "per_page": 20, "current_page": 1 }
  },
  "doctors": [{ "id": "...", "name": "..." }],
  "services": [{ "id": "...", "name": "...", "dept": "clinic", "price": 150 }],
  "filters": { "date": "2026-04-14", "dept": null, "status": null, "search": null }
}
```

### POST /booking
**Controller**: `BookingController@store`
**Permission**: `booking.create`
**Action**: `CreateBookingAction`
**Request** (`StoreBookingRequest`):
```json
{
  "patient_name": "string|required|max:150",
  "patient_phone": "string|nullable|max:20",
  "patient_age": "integer|nullable|min:0|max:120",
  "national_id": "string|nullable|max:20",
  "gender": "in:male,female|nullable",
  "dept": "required|in:clinic,labs,surgery,lasik,laser",
  "service_id": "required|exists:services,id",
  "doctor_id": "required|exists:doctors,id",
  "date": "required|date",
  "time": "nullable|date_format:H:i",
  "price": "numeric|min:0",
  "discount": "numeric|min:0",
  "ins_amount": "numeric|min:0",
  "ins_company_id": "nullable|exists:insurance_companies,id",
  "pay_method": "required|in:cash,card,transfer,insurance",
  "pay_status": "required|in:unpaid,partial,paid",
  "visit_note": "nullable|string"
}
```
**Response**: Redirect to `/booking` with flash success. `file_no` auto-generated.

### PUT /booking/{id}
**Controller**: `BookingController@update`
**Permission**: `booking.edit`
**Action**: `UpdateBookingAction`
**Request**: Same as store, all fields optional except `dept`.
**Business rule**: Cannot change `pay_status` from `paid` to `unpaid`.

### PATCH /booking/{id}/status
**Controller**: `BookingStatusController@update`
**Permission**: `booking.edit`
**Action**: `UpdateBookingStatusAction`
**Request**:
```json
{
  "status": "required|in:confirmed,in_progress,completed,cancelled,waiting",
  "cancel_reason": "required_if:status,cancelled|string"
}
```

### DELETE /booking/{id}
**Controller**: `BookingController@destroy`
**Permission**: `booking.delete`
**Business rule**: Returns 422 if `pay_status = paid`. Must use cancel flow instead.

### GET /booking/{id}/receipt
**Controller**: `BookingController@receipt`
**Permission**: `booking.view`
**Returns**: Inertia page for print receipt (no sidebar, print-optimized).

---

## DTO: `BookingData`
```php
readonly class BookingData {
    public function __construct(
        public string $patient_name,
        public string $dept,
        public string $service_id,
        public string $doctor_id,
        public Carbon $date,
        public float $price,
        public string $pay_method,
        public string $pay_status,
        public ?string $patient_phone = null,
        public ?int $patient_age = null,
        public ?string $national_id = null,
        public ?string $gender = null,
        public ?string $time = null,
        public float $discount = 0,
        public float $ins_amount = 0,
        public ?string $ins_company_id = null,
        public ?string $visit_note = null,
    ) {}
}
```

## Repository Interface: `BookingRepositoryInterface`
```php
interface BookingRepositoryInterface {
    public function paginate(BookingFilterData $filter): LengthAwarePaginator;
    public function findOrFail(string $id): Booking;
    public function create(BookingData $data, string $fileNo): Booking;
    public function update(string $id, BookingData $data): Booking;
    public function updateStatus(string $id, string $status, ?string $cancelReason): Booking;
    public function delete(string $id): void;
    public function getTodayStats(): array;
    public function getNextSequence(int $year): int;
}
```
