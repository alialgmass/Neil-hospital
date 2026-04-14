# Contract: Doctor Module

**Module**: `Modules/Doctor`
**Routes**: Inertia.js web routes
**Permission prefix**: `doctors.*`, `drpayments.*`

---

## Routes

### GET /doctors
**Controller**: `DoctorController@index` | **Permission**: `doctors.view`
**Props**: `{ doctors: [...], can: { write: bool } }`

### POST /doctors
**Controller**: `DoctorController@store` | **Permission**: `doctors.write`
**Action**: `CreateDoctorAction`
**Request**:
```json
{
  "name": "required|string|max:150",
  "specialty": "nullable|string|max:100",
  "phone": "nullable|string|max:20",
  "fee_type": "required|in:percentage,fixed,insurance_zero",
  "fee_value": "numeric|min:0|max:100",
  "user_id": "nullable|exists:users,id",
  "notes": "nullable|string"
}
```

### GET /doctors/claims
**Controller**: `DoctorClaimsController@index` | **Permission**: `drpayments.view`
**Query params**: `from_date`, `to_date`, `doctor_id`
**Props**:
```json
{
  "claims": [
    {
      "doctor": { "id": "...", "name": "دكتور احمد عبده", "fee_type": "percentage", "fee_value": 40 },
      "bookings": [
        {
          "date": "2026-04-10",
          "file_no": "MRN-2026-00012",
          "patient_name": "...",
          "dept": "surgery",
          "service_name": "كتاراكت فاكو",
          "paid": 5000.00,
          "supply_total": 1000.00,
          "dr_share": 4000.00,
          "center_share": 1000.00
        }
      ],
      "totals": {
        "total_paid": 5000.00,
        "total_supplies": 1000.00,
        "dr_total": 4000.00,
        "center_total": 1000.00,
        "payments_made": 2000.00,
        "balance_due": 2000.00
      }
    }
  ]
}
```

### POST /doctors/payments
**Controller**: `DoctorPaymentController@store` | **Permission**: `drpayments.write`
**Action**: `RecordDoctorPaymentAction`
**Request**:
```json
{
  "doctor_id": "required|exists:doctors,id",
  "amount": "required|numeric|min:0.01",
  "payment_date": "required|date",
  "from_date": "required|date",
  "to_date": "required|date|after_or_equal:from_date",
  "pay_method": "required|in:cash,transfer",
  "notes": "nullable|string"
}
```

---

## Doctor Fee Calculation Service

`DoctorClaimsService` applies the correct strategy per booking dept:

```
dept=clinic|labs|laser:
  dr_share = booking.price - booking.discount - service.center_share
  center_share = service computed share

dept=surgery|lasik:
  dr_share = booking.price - booking.discount - surgery.supply_total
  center_share = surgery.supply_total

dept=surgery + pay_method=insurance:
  center_share = booking.price - surgery.supply_total - dr_share
  (dr_share uses doctor.fee_value as fixed amount from contract)

fee_type=insurance_zero:
  dr_share = 0 (insurance pays doctor directly, not through hospital)
```
