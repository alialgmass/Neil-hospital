# Research: Al-Nour Eye Hospital Management System

**Feature**: 001-eye-hospital-hms
**Phase**: 0 — Research & Decision Log
**Date**: 2026-04-14

All decisions below are derived from the HTML prototype (`eye_hospital_v10 (2).html`),
the project's existing `composer.json`, the constitution, and standard Laravel/Vue
best practices. No external research queries were required — all information was
derivable from available artifacts.

---

## Decision 1: Database Backend

**Decision**: MySQL 8+ (production) with SQLite (development / CI)
**Rationale**: Prototype used Supabase (PostgreSQL); per spec assumption, Supabase is
NOT used. The existing Laravel project is configured for MySQL/SQLite via `config/database.php`.
The data model uses ULIDs, JSON columns, and ENUM types — all supported by both.
**Alternatives considered**:
- PostgreSQL: Compatible but requires additional server setup; MySQL already configured.
- Supabase: Explicitly excluded per spec assumption.

---

## Decision 2: Authentication

**Decision**: Laravel Fortify (already installed) with session-based authentication.
Role selection at login uses `spatie/laravel-permission` role assignment on the
authenticated User model.
**Rationale**: Fortify is already installed and configured in the project. The HTML
prototype shows a role-selector dropdown at login — this is implemented by checking
the user's assigned Spatie role after Fortify authenticates, not by passing role as
a login parameter (security: roles are server-assigned, not client-chosen).
**Alternatives considered**:
- Sanctum token auth: Unnecessary for Inertia.js SPA (session cookies suffice).
- Custom auth: Already solved by Fortify.

**Login UI detail**: The role dropdown in the HTML is for UX simulation only.
In the real system, the user logs in with username+password; the system reads their
Spatie role and renders the appropriate navigation items.

---

## Decision 3: Module Boundaries

**Decision**: 12 nwidart/laravel-modules, grouped as follows:

| Module | Screens Covered |
|--------|----------------|
| `Booking` | Internal Booking (حجز داخلي), Patient File |
| `Clinic` | Clinic Queue (قسم العيادة), Clinic Sheet |
| `Labs` | Examinations (قسم الفحوصات) |
| `Surgery` | Surgery OR (قسم العمليات) |
| `Lasik` | LASIK (قسم الليزك) |
| `Laser` | Laser (قسم الليزر) |
| `Doctor` | Doctor Mgmt, Claims, Payments, Shifts, Shift Handover |
| `Accounting` | Treasury, Journal, Chart of Accounts, Trial Balance, Income Statement, Account Statement, Ledger, Sales Invoice |
| `Inventory` | Suppliers, Inventory, Purchase Invoices, Stock Permits, Stocktaking, Purchase Returns, Services & Prices |
| `Insurance` | Insurance Companies, Price Lists |
| `Reporting` | Dashboard, All Reports (10+ report types) |
| `Admin` | Users, Roles, Settings, Archive, System Log |

**Rationale**: Each domain has distinct migrations, permissions, and business rules.
Merging Surgery + LASIK + Laser into one module was considered but rejected: each
has different procedure types, fee formulas (supply-based vs service-share), and
distinct permission guards (`surgery.write` vs `lasik.write`).
**Alternatives considered**: 8 modules (merged Lasik+Laser into Surgery) — rejected
because LASIK fee formula differs from Surgery formula and the HTML treats them as
independent navigation sections with different data.

---

## Decision 4: Doctor Fee Calculation Strategies

**Decision**: Strategy pattern implemented in `DoctorClaimsService` with 5 concrete
strategies injected via interface:

```
ClinicFeeStrategy       → dr_share = paid - service.center_share
LabsFeeStrategy         → dr_share = paid - service.center_share
LaserFeeStrategy        → dr_share = paid - service.center_share
SurgeryFeeStrategy      → dr_share = paid - supplies_used_total
LasikFeeStrategy        → dr_share = paid - supplies_used_total
InsuranceSurgeryStrategy→ dr_share = paid - supplies; center = total - supplies - dr_share
```

**Rationale**: The spec explicitly documents 5 distinct formulas. A single if-else
chain in one service would violate OCP (adding a new department type requires editing
the service). Strategy pattern allows each formula to be unit-tested independently.
**Alternatives considered**: Switch statement in service — rejected; violates OCP and
makes unit testing the 5 formulas impossible in isolation.

---

## Decision 5: Excel Import/Export Package

**Decision**: Add `maatwebsite/laravel-excel` (^3.1) for:
- Import: Services pricing (CSV/XLSX) and Inventory items
- Export: All 10+ reports as .xlsx

**Rationale**: Reports span potentially thousands of rows (full year of bookings);
server-side generation is required. Frontend SheetJS cannot access server-side DB data.
`maatwebsite/laravel-excel` is the de-facto standard for Laravel Excel handling.
**Alternatives considered**: PhpSpreadsheet directly — more verbose, no Laravel integration.

---

## Decision 6: Frontend State Management

**Decision**: Inertia.js shared data (via `HandleInertiaRequests` middleware) for
auth user, permissions, and global settings. Pinia for client-side UI state (modals,
filters, notifications). No API layer — all data via Inertia page props.
**Rationale**: Project already uses Inertia.js 3. Mixing Axios API calls with Inertia
would create two data-fetching patterns (DRY violation). Inertia's server-driven model
fits the Arabic RTL hospital screens which are primarily data tables + forms.
**Alternatives considered**: Pinia for all state — rejected; would require duplicating
all server data in client store (DRY violation).

---

## Decision 7: ULID vs UUID vs Auto-increment

**Decision**: Use Laravel's `HasUlids` trait (or string ULIDs) for all primary keys,
matching the prototype's schema definition.
**Rationale**: ULIDs are sortable (unlike UUID v4), shorter than UUIDs, and the spec
explicitly uses ULID notation in all table definitions.
**Alternatives considered**: Auto-increment integer PKs — simpler but expose record counts;
not suitable for patient medical record numbers (MRN is a separate formatted field anyway).

---

## Decision 8: RTL / Arabic Rendering

**Decision**: Use `dir="rtl"` on the `<html>` element (set in Blade layout). All CSS
from the HTML prototype is extracted into Tailwind utility classes or scoped Vue component
styles. The HTML prototype's CSS variables (--p, --a, --s, etc.) are mapped to Tailwind
config theme extensions.
**Rationale**: The prototype already has a complete, tested RTL CSS system. Faithfully
porting it avoids re-inventing the design.
**Alternatives considered**: A separate RTL CSS library — unnecessary overhead; prototype
CSS is self-contained.

---

## Decision 9: MRN Generation

**Decision**: `MrnGeneratorService` generates `MRN-YYYY-XXXXX` format using a
`SELECT MAX(sequence)` query on the `bookings` table, padded to 5 digits.
Protected by a database-level UNIQUE constraint on `file_no`.
**Rationale**: Atomic sequence generation via DB MAX() + UNIQUE constraint prevents
duplicates under concurrent booking creation.
**Alternatives considered**: Laravel's `unique()` validation only — not race-condition safe.

---

## Decision 10: Activity Logging

**Decision**: `ActivityLogService` in the Admin module records all data-modifying events.
Called from Actions (not Controllers), so every create/update/delete/cancel goes through
the log. Uses a dedicated `activity_logs` table (not spatie/laravel-activitylog package
to keep KISS).
**Rationale**: The spec requires 100% attribution of mutations. Recording in Actions
ensures the log is written regardless of the calling context (web request, CLI command,
queue job).
**Alternatives considered**: spatie/laravel-activitylog — adds another package dependency
for functionality that is straightforward to implement directly; KISS principle favors
the simpler custom implementation.

---

## Resolved Clarifications

All spec clarifications were resolved via assumptions documented in `spec.md`:

| Topic | Resolution |
|-------|-----------|
| Database | MySQL/SQLite — Supabase excluded |
| Currency | EGP (Egyptian Pound) only |
| Language | Arabic primary; no i18n library needed |
| Print/PDF | Browser `window.print()` with CSS `@media print` |
| Offline | Out of scope for v1 |
| Multi-branch | Out of scope; single hospital only |
| Excel export package | `maatwebsite/laravel-excel` added |
