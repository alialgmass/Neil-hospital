# Feature Specification: Al-Nour Eye Hospital Management System

**Feature Branch**: `001-eye-hospital-hms`
**Created**: 2026-04-14
**Status**: Draft
**Input**: Implement eye_hospital_v10 (2).html as a full-stack web application using
Vue + nwidart/laravel-modules following SOLID, KISS, DRY, and clean architecture principles.

---

## Overview

Al-Nour Hospital (مستشفى النور) is an integrated ophthalmology and eye surgery hospital
management system. The system manages the complete patient journey — from appointment
booking through clinical consultation, surgery scheduling, billing, pharmacy/inventory,
and financial reporting — across six user roles in a bilingual (Arabic-primary, RTL)
interface.

The system MUST be implemented as a modular Laravel application where each functional
domain (Booking, Clinic, Surgery, Accounting, Inventory, etc.) is an independent
nwidart/laravel-modules module. All modules MUST share a clean architecture with
Controllers, Services, Repositories, DTOs, and Actions.

---

## User Roles & Actors

| Role | Arabic | Capabilities |
|------|--------|--------------|
| System Admin | مدير النظام | Full access to all modules |
| Doctor | طبيب | Clinic, patient file, own shift/schedule |
| Reception | استقبال | Booking, clinic queue, patient registration |
| Accountant | محاسب | All financial and billing modules |
| Nurse | ممرض/ة | Clinic operations, patient notes |
| Store Keeper | مخزن | Inventory, suppliers, purchase invoices |

---

## User Scenarios & Testing

### User Story 1 — Patient Registration & Appointment Booking (Priority: P1)

A receptionist registers a new patient and books an appointment in one of the hospital's
clinical departments (Clinic, Labs/Examinations, Surgery, LASIK, or Laser).

**Why this priority**: Every patient interaction begins here. Without booking, no other
clinical or financial workflow can proceed.

**Independent Test**: A receptionist can register a new patient, select a department and
doctor, enter appointment date/time, and confirm the booking. The booking appears in the
department queue immediately.

**Acceptance Scenarios**:

1. **Given** the receptionist is on the Booking module, **When** they complete the new
   patient form (name, date of birth, phone, national ID, insurance info if applicable)
   and select a department and appointment slot, **Then** the booking is saved and a
   booking confirmation is displayed with a unique booking number.

2. **Given** the patient already exists, **When** the receptionist searches by name or
   national ID, **Then** the system retrieves the existing patient record and pre-fills
   the form for a follow-up booking.

3. **Given** a booking exists, **When** the receptionist changes the status (Waiting →
   In Examination → Completed / Cancelled), **Then** the queue updates in real time.

4. **Given** the patient is covered by insurance, **When** creating the booking,
   **Then** the system captures the insurance company, policy number, and coverage type
   for billing purposes.

---

### User Story 2 — Clinical Consultation & Patient Medical File (Priority: P1)

A doctor performs a clinical examination, records findings, prescribes treatment, and
updates the patient's medical file.

**Why this priority**: This is the core clinical workflow and produces the medical
records that drive downstream billing and surgical scheduling.

**Independent Test**: A doctor can open a patient's queue entry, enter examination
findings (visual acuity, diagnosis, prescription), save the consultation record, and
view the complete patient medical history.

**Acceptance Scenarios**:

1. **Given** a patient is in "Waiting" status in the clinic queue, **When** the doctor
   selects the patient, **Then** the patient's full medical history is displayed
   alongside a blank consultation form.

2. **Given** the doctor completes a consultation form (diagnosis, examination notes,
   prescribed glasses/medication, follow-up date), **When** they save, **Then** the
   record is appended to the patient's medical file and the booking status changes to
   "Completed".

3. **Given** a patient has prior visits, **When** the doctor opens their file,
   **Then** all previous consultations, surgeries, and test results are visible in
   chronological order.

4. **Given** the doctor refers the patient to surgery, **When** they mark the
   referral, **Then** a surgery scheduling request is created automatically.

---

### User Story 3 — Surgery & Procedure Scheduling (Priority: P2)

Staff manage operating theatre scheduling for eye surgeries, LASIK, and laser
procedures, including pre-op and post-op tracking.

**Why this priority**: Surgical scheduling is the highest-revenue activity in an
eye hospital and requires tight coordination between clinical, nursing, and billing.

**Independent Test**: A receptionist or nurse can schedule a surgery for a referred
patient, assign an operating doctor, record the procedure type, and mark the surgery
as completed with outcomes.

**Acceptance Scenarios**:

1. **Given** a patient has a surgical referral, **When** the scheduler opens the
   Surgery / LASIK / Laser module, **Then** they can create a procedure booking with
   date, time, operating doctor, anaesthesia type, and procedure code.

2. **Given** a surgery is scheduled, **When** the procedure is completed, **Then**
   the nurse/doctor records the outcome, complications (if any), and post-op
   instructions, and the status changes to "Completed".

3. **Given** the Surgery module is filtered by date, **When** a specific date is
   selected, **Then** the operating theatre schedule is displayed with all booked
   procedures and their statuses.

4. **Given** a surgery is completed, **When** billing is triggered, **Then** the
   surgery fee, anaesthesia fee, and consumables are automatically added to the
   patient invoice.

---

### User Story 4 — Billing, Invoicing & Treasury (Priority: P2)

An accountant generates sales invoices, processes payments (cash/insurance), and
manages daily treasury movements.

**Why this priority**: Revenue collection is mission-critical; errors in billing
directly impact hospital financial health.

**Independent Test**: An accountant can generate a patient invoice from a completed
visit, apply insurance discounts, record payment, and see the treasury balance update.

**Acceptance Scenarios**:

1. **Given** a patient visit is completed, **When** the accountant opens Sales Invoice
   and selects the patient's booking, **Then** all services rendered are pre-populated
   with their prices from the hospital price list.

2. **Given** the invoice is reviewed, **When** the accountant selects payment method
   (cash, card, insurance, mixed), **Then** the system records the payment, generates
   a receipt, and posts the journal entry to the accounting ledger.

3. **Given** the patient is insured, **When** processing the invoice, **Then** the
   system applies the insurance coverage percentage, calculates the patient co-pay,
   and generates an insurance claim record.

4. **Given** the accountant opens Treasury Movement, **When** they view the current
   shift, **Then** all cash-in and cash-out transactions are listed with a running
   balance and a shift summary.

5. **Given** a receptionist or accountant applies a discount, **When** the discount
   requires justification above a configured threshold, **Then** the system flags it
   for supervisor approval.

---

### User Story 5 — Inventory, Suppliers & Purchase Management (Priority: P3)

The store keeper manages pharmaceutical and consumable inventory, creates purchase
invoices from suppliers, and issues stock to departments.

**Why this priority**: Inventory shortages directly affect clinical operations;
purchase cost control affects profitability.

**Independent Test**: A store keeper can add a new item to inventory, create a
purchase invoice from a supplier, and issue stock to a department via a dispensing
permit. Low-stock alerts appear in the notification bell.

**Acceptance Scenarios**:

1. **Given** a supplier delivers goods, **When** the store keeper creates a purchase
   invoice with supplier, items, quantities, and unit prices, **Then** the stock
   quantities are updated and a corresponding accounting entry is posted.

2. **Given** a department requests stock, **When** the store keeper creates a
   Dispensing Permit (إذن صرف), **Then** the stock quantity is reduced and the
   transaction is logged in the inventory movement report.

3. **Given** an item's quantity falls below its minimum threshold, **When** any user
   views the notification bell, **Then** a low-stock alert is shown for that item.

4. **Given** the store keeper performs a physical count (stocktaking), **When** they
   enter actual quantities, **Then** the system calculates variances and posts an
   inventory adjustment entry.

---

### User Story 6 — Financial Accounting & Reporting (Priority: P3)

An accountant manages the chart of accounts, posts journal entries, and generates
financial statements (trial balance, income statement, profit & loss).

**Why this priority**: Financial reporting is mandatory for hospital management
decisions and regulatory compliance.

**Independent Test**: An accountant can view the trial balance, income statement, and
profit & loss report for a selected date range, with all figures correctly reflecting
posted transactions.

**Acceptance Scenarios**:

1. **Given** the accountant opens Trial Balance, **When** they select a date range,
   **Then** all accounts with their debit and credit totals are displayed, with
   balanced totals at the bottom.

2. **Given** the accountant opens the Income Statement, **When** a period is selected,
   **Then** revenue and expense categories are grouped with subtotals and a net
   income/loss figure.

3. **Given** a manual journal entry is needed, **When** the accountant enters the
   Daily Journal (قيود اليومية), **Then** they can create debit/credit entries against
   any account in the chart of accounts, with mandatory balance validation before save.

4. **Given** the accountant opens the Ledger (دفتر الأستاذ), **When** they select
   an account, **Then** all transactions affecting that account are listed
   chronologically with running balance.

---

### User Story 7 — Doctor Management, Shifts & Entitlements (Priority: P3)

Administrators manage doctor profiles, working shifts, and calculate doctor fees/
entitlements from completed procedures.

**Why this priority**: Doctor entitlements are a major expense line and must be
calculated accurately from procedure records.

**Independent Test**: An administrator can view a doctor's shift schedule, see all
procedures performed in that shift, and generate a doctor entitlement report for a
date range that can be exported.

**Acceptance Scenarios**:

1. **Given** the admin opens Doctor Management, **When** they create a doctor profile
   with specialty, fee structure (fixed/percentage per procedure type), and shift
   schedule, **Then** the doctor appears in department booking dropdowns.

2. **Given** a completed procedure is linked to a doctor, **When** the admin opens
   Doctor Claims (مستحقات الأطباء) for a date range, **Then** the system calculates
   entitlements per procedure type and shows a summary per doctor.

3. **Given** entitlements are approved, **When** payment is processed, **Then** the
   Doctor Payments Statement records the payout and the doctor's outstanding balance
   decreases.

4. **Given** a shift is ending, **When** the nurse/receptionist opens Shift Handover,
   **Then** they can document the shift summary (patient count, revenue collected,
   pending cases) and hand over to the next shift.

---

### User Story 8 — User Management, Roles & Permissions (Priority: P4)

System administrators manage user accounts and configure role-based access control
to ensure each role sees only its authorized modules and actions.

**Why this priority**: Security and access control are non-negotiable in a healthcare
system handling patient data.

**Independent Test**: An admin can create a user, assign a role, and confirm that the
user can only access the modules and actions granted to their role after login.

**Acceptance Scenarios**:

1. **Given** the admin creates a new user account with a role (Doctor, Reception,
   Accountant, Nurse, Store Keeper), **When** that user logs in, **Then** only the
   navigation items authorized for their role are visible and accessible.

2. **Given** an unauthorized user attempts to access a restricted module via URL,
   **When** the system processes the request, **Then** the user is redirected with a
   "Not Authorized" response — no data is exposed.

3. **Given** the admin opens Roles & Permissions, **When** they modify a role's
   permissions, **Then** the change takes effect for all users with that role on their
   next page load.

---

### User Story 9 — Comprehensive Reporting & Data Export (Priority: P4)

Management and department heads access cross-cutting reports with date-range filters
and export capability (Excel / print).

**Why this priority**: Reports drive operational and financial decisions at hospital
management level.

**Independent Test**: A manager can open Department Revenue Report, filter by date
range, see revenue broken down by department and doctor, and export the result to Excel.

**Acceptance Scenarios**:

1. **Given** any report page with a date-range filter, **When** the user selects a
   range and applies it, **Then** only data within that range is displayed and all
   totals recalculate accordingly.

2. **Given** a report is displayed, **When** the user clicks Export to Excel,
   **Then** an .xlsx file is downloaded containing the same data as shown on screen.

3. **Given** the user clicks Print on any report, **When** the browser print dialog
   opens, **Then** the sidebar, action buttons, and non-report elements are hidden
   and the report is formatted for A4 paper.

4. **Given** Insurance Claims report is opened, **When** filtered by insurance
   company and date, **Then** all pending and processed claims are listed with
   amounts, status, and patient details.

---

### Edge Cases

- What happens when two receptionists book the same appointment slot simultaneously?
- How does the system handle a surgery cancelled after the invoice has been paid?
- What happens when inventory stock falls to zero and a dispensing permit is requested?
- How does the system behave when a user's session expires mid-form?
- What happens if a journal entry does not balance (debits ≠ credits)?
- How are purchase returns to suppliers handled in both inventory and accounting?
- What happens when an insurance company rejects a claim?

---

## Requirements

### Functional Requirements

- **FR-001**: System MUST support six distinct user roles with independent permission
  sets: System Admin, Doctor, Reception, Accountant, Nurse, Store Keeper.
- **FR-002**: System MUST provide an internal booking module supporting appointments
  across five departments: Clinic, Examinations/Labs, Surgery, LASIK, and Laser.
- **FR-003**: System MUST maintain a complete, chronological patient medical file
  including consultations, diagnoses, prescriptions, and surgical history.
- **FR-004**: System MUST manage surgical procedure scheduling with theatre allocation,
  operating doctor assignment, and pre/post-op status tracking.
- **FR-005**: System MUST generate sales invoices that auto-populate services from
  completed bookings and apply insurance coverage calculations.
- **FR-006**: System MUST maintain a double-entry accounting ledger with chart of
  accounts, daily journal entries, trial balance, and income statement.
- **FR-007**: System MUST manage inventory (medications, consumables, lenses) with
  purchase invoices, dispensing permits, stocktaking, and low-stock alerts.
- **FR-008**: System MUST calculate and report doctor entitlements based on procedure
  type and configured fee structures.
- **FR-009**: System MUST generate a minimum of 10 distinct reports including:
  department revenue, cases data, doctor claims, doctor payments, insurance claims,
  inventory movement, purchase prices, daily journal, profit & loss, expense analysis.
- **FR-010**: System MUST support Excel export and print-optimized output for all
  reports.
- **FR-011**: System MUST enforce role-based access control at both route and data
  levels — no unauthorized data exposure via direct URL access.
- **FR-012**: System MUST display the interface in Arabic (RTL layout) as the primary
  language.
- **FR-013**: System MUST display real-time low-stock notifications in the notification
  bell visible to authorized users.
- **FR-014**: System MUST support shift management including doctor shifts, shift
  handover documentation, and shift-based treasury reconciliation.
- **FR-015**: System MUST support insurance company management including company
  profiles, price lists per company, and claim tracking.
- **FR-016**: System MUST allow configuration of hospital settings including hospital
  name, department setup, service pricing, and price lists.
- **FR-017**: System MUST implement an activity/audit log recording all data-modifying
  actions with timestamp and user attribution.
- **FR-018**: System MUST support purchase returns to suppliers with corresponding
  inventory and accounting adjustments.
- **FR-019**: System MUST be structured as independent Laravel modules (one per
  functional domain) with clean architecture layers in each module.
- **FR-020**: System MUST support archive functionality for old/inactive patient and
  financial records.

### Key Entities

- **Patient**: Demographics (name, DOB, gender, phone, national ID), insurance
  information, medical record number; linked to Bookings and Medical File.
- **Booking**: Department, doctor, date/time, status (Waiting/In Examination/
  Completed/Cancelled), booking number, payment status.
- **MedicalRecord**: Patient consultation entries — diagnosis, findings, prescription,
  referrals, linked files; linked to Patient and Doctor.
- **Procedure**: Surgical or laser procedure with type, date, theatre, operating
  doctor, anaesthesia, outcome, fee structure.
- **Invoice**: Patient invoice with line items (services rendered), discount, insurance
  coverage, payment method(s), status; generates journal entries.
- **JournalEntry**: Double-entry accounting record with debit/credit lines, account
  codes, date, reference, and posting status.
- **Account**: Chart of accounts entry with code, name, type (asset/liability/equity/
  revenue/expense), and parent account for hierarchy.
- **InventoryItem**: Item name, category, unit, minimum quantity, current quantity,
  reorder level, unit cost; linked to purchase and dispensing transactions.
- **Supplier**: Supplier profile with name, contact, and linked purchase invoices.
- **Doctor**: Profile with specialty, fee structure (fixed or percentage per procedure
  type), active shift schedule.
- **InsuranceCompany**: Company profile, coverage percentage, price list, and linked
  claims.
- **User**: Authentication credentials, assigned role, status; linked to activity log.

---

## Success Criteria

### Measurable Outcomes

- **SC-001**: A receptionist can complete a new patient registration and appointment
  booking in under 3 minutes from a fresh form.
- **SC-002**: A patient's full medical history (all prior visits, surgeries, and
  examinations) loads in under 2 seconds after selection.
- **SC-003**: All financial reports (trial balance, income statement, P&L) display
  accurate, balanced figures for any selected date range.
- **SC-004**: The system correctly enforces role-based access for all six roles —
  zero unauthorized data accesses occur across any standard test suite run.
- **SC-005**: Excel export produces a correctly formatted, complete .xlsx file for
  every report within 5 seconds for up to 12 months of data.
- **SC-006**: Low-stock alerts appear in the notification bell within one page
  load cycle after an item's quantity drops below its minimum threshold.
- **SC-007**: Doctor entitlement calculations match manual calculations with 100%
  accuracy across all procedure types and fee structures.
- **SC-008**: The system supports at least 10 concurrent users (one per role) without
  functional degradation during normal hospital operating hours.
- **SC-009**: All data entry forms (booking, invoice, purchase invoice, consultation)
  validate inputs and display user-friendly Arabic error messages before save.
- **SC-010**: The system logs 100% of data-modifying actions in the audit log with
  correct user attribution.

---

## Assumptions

- The hospital operates as a single physical location (no multi-branch support in v1).
- The primary currency is Egyptian Pound (ج.م / EGP); no multi-currency support required.
- Arabic is the only UI language required; English field names in the database are
  acceptable.
- The system will be deployed on a standard LAMP/LEMP server; no cloud-native
  infrastructure is required in v1.
- Authentication uses the existing Laravel Fortify setup in the project; no external
  SSO is required.
- The existing `spatie/laravel-permission` package handles all role-based access
  control; custom permission tables are not needed.
- Print formatting targets A4 paper in RTL layout; PDF generation is out of scope
  (browser print is sufficient).
- All existing nwidart/laravel-modules configuration in the project is used as-is;
  no changes to the module loader configuration are needed.
- The HTML prototype (`eye_hospital_v10 (2).html`) is the definitive UI reference
  for layout, color scheme, component design, and Arabic labels.
- Supabase (referenced in the HTML prototype) is NOT used; all data is stored in
  the project's configured relational database (MySQL/PostgreSQL/SQLite).
- Excel export uses the `maatwebsite/laravel-excel` package or equivalent; XLSX
  format only.
- Each nwidart module maps to one functional domain: Booking, Clinic, Labs,
  Surgery, LASIK, Laser, Accounting, Inventory, Treasury, Reporting, Admin.
