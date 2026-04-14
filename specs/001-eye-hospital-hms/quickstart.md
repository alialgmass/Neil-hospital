# Quickstart: Al-Nour Eye Hospital Management System

**Feature**: 001-eye-hospital-hms
**Date**: 2026-04-14

This document describes how to set up the development environment, create the 12
nwidart modules, and validate the system end-to-end after implementation.

---

## Prerequisites

- PHP 8.3+ with extensions: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- Composer 2.x
- Node.js 20+ with npm
- MySQL 8+ (production) or SQLite (development)
- Git

---

## 1. Install New Dependency

```bash
# Add Excel package (not yet in composer.json)
composer require maatwebsite/laravel-excel
```

---

## 2. Create the 12 Laravel Modules

```bash
# Generate all modules (run from project root)
php artisan module:make Booking
php artisan module:make Clinic
php artisan module:make Labs
php artisan module:make Surgery
php artisan module:make Lasik
php artisan module:make Laser
php artisan module:make Doctor
php artisan module:make Accounting
php artisan module:make Inventory
php artisan module:make Insurance
php artisan module:make Reporting
php artisan module:make Admin
```

Each module is created under `Modules/{Name}/` with the standard nwidart structure.
Add the clean-architecture folders (Actions/, DTOs/, Repositories/Contracts/) manually
or via the scaffold commands below.

---

## 3. Register Module Providers

Each module's `ServiceProvider` MUST bind its Repository interface to its concrete:

```php
// Example: Modules/Booking/Providers/BookingServiceProvider.php
public function register(): void
{
    $this->app->bind(
        BookingRepositoryInterface::class,
        BookingRepository::class
    );
}
```

---

## 4. Seed Default Data

```bash
# Run after migrations
php artisan db:seed --class=AccountsSeeder         # 13 default GL accounts
php artisan db:seed --class=DoctorsSeeder           # 5 doctors from spec §3
php artisan db:seed --class=RolesPermissionsSeeder  # 6 roles + 30+ permissions
php artisan db:seed --class=SettingsSeeder          # Hospital name, MRN format, etc.
php artisan db:seed --class=AdminUserSeeder         # Default admin user
```

---

## 5. Run Development Server

```bash
composer run dev
# Starts: php artisan serve + queue:listen + pail + npm run dev
```

---

## 6. Validation Checklist (Golden Path)

After implementation, verify each flow manually:

### Booking Flow
- [ ] Login as `استقبال` → only Booking and Clinic navigation visible
- [ ] Click "حجز جديد" → form opens with department/service/doctor selectors
- [ ] Select "عيادة" dept → only clinic services appear
- [ ] Select a service → price auto-fills
- [ ] Submit → booking appears in table with status "انتظار" and unique MRN
- [ ] Second booking for same day → MRN increments correctly (MRN-2026-00002)

### Clinical Flow
- [ ] Login as `طبيب` → Clinic queue shows today's bookings
- [ ] Open patient → clinic sheet form appears with prior history (if any)
- [ ] Complete sheet → status changes to "مكتمل"

### Surgery Flow
- [ ] Create a surgery booking → assign OR bed
- [ ] Record supplies used → supply_total calculates
- [ ] Doctor claims report shows correct `dr_share = paid - supply_total`

### Accounting Flow
- [ ] Mark booking as paid → treasury entry auto-created
- [ ] Manual journal entry with debit ≠ credit → saves correctly
- [ ] Manual journal entry with same debit and credit → returns validation error
- [ ] Trial Balance → totals balance (debit = credit)
- [ ] Income Statement → net income = revenues - expenses

### Inventory Flow
- [ ] Add item with `min_quantity = 5` and `quantity = 3`
- [ ] Notification bell shows low-stock badge with count
- [ ] Create dispensing permit (صرف) for 2 units → quantity becomes 1 (still low)
- [ ] Create dispensing permit for 1 unit → quantity = 0 (prevents going negative)

### Excel Export
- [ ] Open any report → click Export → .xlsx file downloads with correct headers
- [ ] Import services CSV → shows "X جديدة، Y محدّثة، Z متجاهلة"

### RBAC
- [ ] Login as `محاسب` → Booking visible, System Log NOT visible
- [ ] Direct URL access to `/admin/system-log` → redirected with 403

---

## 7. Run Test Suite

```bash
composer test
# Runs: config:clear → pint --test → php artisan test
```

Key test classes to verify:
- `Tests/Unit/Doctor/DoctorClaimsServiceTest` — all 5 fee formulas
- `Tests/Unit/Accounting/TrialBalanceServiceTest` — balance assertion
- `Tests/Feature/Booking/BookingControllerTest` — CRUD + permission guards
- `Tests/Feature/Admin/RbacTest` — all 6 roles × unauthorized routes

---

## 8. CSS Design Tokens → Tailwind

The HTML prototype defines CSS variables. Map them to `tailwind.config.js`:

```js
// tailwind.config.js theme extension
extend: {
  colors: {
    primary: { DEFAULT: '#0A4FA6', light: '#1A6ED8', pale: '#E8F1FB', dark: '#072E63' },
    accent:  { DEFAULT: '#00B5A4', pale: '#E0F7F5' },
    danger:  { DEFAULT: '#D63B3B', pale: '#FDEAEA' },
    warning: { DEFAULT: '#E07C10', pale: '#FEF0E0' },
    success: { DEFAULT: '#1A8C5B', pale: '#E2F5EC' },
    border:  '#DDE4EF',
    surface: { DEFAULT: '#FFFFFF', 2: '#F8FAFD' },
    text:    { DEFAULT: '#0D1F3C', 2: '#4A5878', 3: '#8A96AE' },
  },
  borderRadius: { card: '14px' },
}
```

---

## 9. Arabic RTL Setup

In `resources/views/app.blade.php`:
```html
<html lang="ar" dir="rtl">
```

In Tailwind config:
```js
// No special RTL plugin needed; use logical properties (ms-*, me-*, ps-*, pe-*)
// or rely on dir="rtl" CSS cascade from the prototype.
```
