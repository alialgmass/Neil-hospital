# Implementation Plan: Al-Nour Eye Hospital Management System

**Branch**: `master` | **Date**: 2026-04-14 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `specs/001-eye-hospital-hms/spec.md`

---

## Summary

Implement the complete Al-Nour Eye Hospital (مستشفى النور) management system as a
full-stack Laravel 13 + Vue 3 + Inertia.js web application. The HTML prototype
`eye_hospital_v10 (2).html` is the definitive UI reference. The system covers 28
functional modules organized into 12 nwidart/laravel-modules domains, serving 6
distinct user roles with Arabic-RTL interface.

The backend enforces Clean Architecture (Controllers → Actions → Services →
Repositories → Models) with SOLID principles. All modules share a consistent base
repository interface and DI binding pattern. The frontend is built with Vue 3 +
Inertia.js, reusing shared UI components from the HTML prototype.

---

## Technical Context

**Language/Version**: PHP 8.3 (backend) · TypeScript 5.x (frontend)
**Primary Dependencies**:
- Laravel 13 · nwidart/laravel-modules 13 · Laravel Fortify · Laravel Sanctum
- Laravel Telescope · spatie/laravel-permission · spatie/laravel-medialibrary
- spatie/laravel-query-builder · laravel/wayfinder
- Vue 3 · Inertia.js 3 · Vite · Pinia · Tailwind CSS (already configured)
- maatwebsite/laravel-excel (Excel import/export — to be added)
- PHPUnit 12 · Mockery · Laravel Pint · ESLint · Prettier

**Storage**: MySQL 8+ (production) / SQLite (development/CI)
**Testing**: PHPUnit 12 (feature + unit) · Vitest (Vue component tests)
**Target Platform**: Linux LEMP server · Desktop browser (Arabic RTL) · Print
**Project Type**: Full-stack web application (Inertia.js SPA-like)
**Performance Goals**: Page load < 2s · API response < 500ms · Excel export < 5s
**Constraints**: Arabic RTL · EGP currency · Single-branch hospital · Browser print only
· No Supabase (replaced by project's MySQL/SQLite) · No offline mode in v1
**Scale/Scope**: ~10 concurrent users · 6 roles · 28 screens · 12 Laravel modules

---

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-checked after Phase 1 design.*

| Principle | Status | Evidence |
|-----------|--------|---------|
| **SRP** | ✅ PASS | Each class has one job: Controllers handle HTTP only; Services orchestrate domain logic; Repositories own all queries; Actions encapsulate single use-cases |
| **OCP** | ✅ PASS | All Repositories implement interfaces; new payment methods/fee types extend via new implementations, not edits |
| **LSP** | ✅ PASS | All Repository implementations honour full interface contracts; no silent no-ops |
| **ISP** | ✅ PASS | Repository interfaces are per-module (BookingRepositoryInterface, DoctorRepositoryInterface, etc.); no fat interfaces |
| **DIP** | ✅ PASS | All Services receive Repository interfaces via constructor injection; service container binds concrete to interface in each module's ServiceProvider |
| **KISS** | ⚠ JUSTIFIED | 12 modules + full Clean Architecture layers — necessary because 28 distinct functional screens, 6 roles, and complex doctor fee formulas (5 different calculation modes per department) each have non-trivial, independent business rules |
| **DRY** | ✅ PASS | Shared base repository (`BaseRepository`), shared DTOs for cross-module data (PatientDTO, DoctorDTO), shared Vue components (DataTable, Modal, StatCard, Badge) |
| **Clean Architecture** | ✅ PASS | 4-layer model strictly applied in all 12 modules |
| **Test Discipline** | ✅ PASS | PHPUnit feature tests for all controllers; unit tests for all Services and Actions; `composer test` gate enforced |
| **Technology Stack** | ✅ PASS | All packages already in project; only `maatwebsite/laravel-excel` to be added |
| **Module Isolation** | ✅ PASS | Cross-module communication via Service interfaces only; no direct Model access across module boundaries |

**Complexity justification for 12 modules:**

| Complexity Item | Justified By |
|-----------------|-------------|
| 12 separate nwidart modules | Each domain has independent routes, permissions, migrations, and business rules; a single module would violate SRP for 28+ screens |
| Full Repository pattern in all modules | Doctor fee calculation formulas differ per department; Repositories isolate query logic from the 5 distinct fee calculation strategies in DoctorClaimsService |
| 5 doctor fee calculation strategies | Business requirement: clinic/labs/laser (service-defined share), surgery/lasik (supply cost deduction), insurance surgery (triple calculation) |
| Separate DTOs per operation | Booking creation vs update vs status change have different validation and data shapes |

---

## Project Structure

### Documentation (this feature)

```text
specs/001-eye-hospital-hms/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output
│   ├── booking.md
│   ├── clinic.md
│   ├── surgery-lasik-laser.md
│   ├── accounting.md
│   ├── inventory.md
│   ├── insurance.md
│   ├── doctor.md
│   └── reporting.md
├── checklists/
│   └── requirements.md
└── tasks.md             # Phase 2 output (/speckit.tasks command)
```

### Source Code (repository root)

```text
Modules/                                # nwidart/laravel-modules root
├── Booking/
│   ├── Actions/
│   │   ├── CreateBookingAction.php
│   │   ├── UpdateBookingAction.php
│   │   ├── UpdateBookingStatusAction.php
│   │   └── CancelBookingAction.php
│   ├── Controllers/
│   │   ├── BookingController.php
│   │   └── BookingStatusController.php
│   ├── DTOs/
│   │   ├── BookingData.php
│   │   └── BookingFilterData.php
│   ├── Http/Requests/
│   │   ├── StoreBookingRequest.php
│   │   └── UpdateBookingRequest.php
│   ├── Models/
│   │   └── Booking.php
│   ├── Repositories/
│   │   ├── Contracts/BookingRepositoryInterface.php
│   │   └── BookingRepository.php
│   ├── Services/
│   │   ├── BookingService.php
│   │   └── MrnGeneratorService.php
│   ├── Routes/web.php
│   └── Providers/BookingServiceProvider.php
│
├── Clinic/
│   ├── Actions/
│   │   ├── RecordClinicSheetAction.php
│   │   └── ReferPatientAction.php
│   ├── Controllers/
│   │   └── ClinicController.php
│   ├── DTOs/
│   │   └── ClinicSheetData.php
│   ├── Http/Requests/StoreClinicSheetRequest.php
│   ├── Models/ClinicSheet.php
│   ├── Repositories/
│   │   ├── Contracts/ClinicSheetRepositoryInterface.php
│   │   └── ClinicSheetRepository.php
│   ├── Services/ClinicService.php
│   ├── Routes/web.php
│   └── Providers/ClinicServiceProvider.php
│
├── Labs/
│   ├── Actions/RecordDiagnosticResultAction.php
│   ├── Controllers/LabsController.php
│   ├── DTOs/DiagnosticResultData.php
│   ├── Http/Requests/StoreDiagnosticResultRequest.php
│   ├── Models/DiagnosticResult.php
│   ├── Repositories/
│   │   ├── Contracts/DiagnosticResultRepositoryInterface.php
│   │   └── DiagnosticResultRepository.php
│   ├── Services/LabsService.php
│   ├── Routes/web.php
│   └── Providers/LabsServiceProvider.php
│
├── Surgery/
│   ├── Actions/
│   │   ├── ScheduleSurgeryAction.php
│   │   ├── RecordSurgeryReportAction.php
│   │   └── RecordSuppliesUsedAction.php
│   ├── Controllers/
│   │   ├── SurgeryController.php
│   │   └── OrRoomController.php
│   ├── DTOs/
│   │   ├── SurgeryData.php
│   │   └── SuppliesUsedData.php
│   ├── Http/Requests/
│   │   ├── StoreSurgeryRequest.php
│   │   └── RecordSuppliesRequest.php
│   ├── Models/
│   │   ├── Surgery.php
│   │   ├── OrRoom.php
│   │   └── OrBed.php
│   ├── Repositories/
│   │   ├── Contracts/SurgeryRepositoryInterface.php
│   │   └── SurgeryRepository.php
│   ├── Services/SurgeryService.php
│   ├── Routes/web.php
│   └── Providers/SurgeryServiceProvider.php
│
├── Lasik/                              # Same structure as Surgery
│   └── ...
│
├── Laser/                              # Same structure as Surgery
│   └── ...
│
├── Doctor/
│   ├── Actions/
│   │   ├── CreateDoctorAction.php
│   │   ├── RecordDoctorPaymentAction.php
│   │   └── OpenDoctorShiftAction.php
│   ├── Controllers/
│   │   ├── DoctorController.php
│   │   ├── DoctorClaimsController.php
│   │   ├── DoctorPaymentController.php
│   │   └── DoctorShiftController.php
│   ├── DTOs/
│   │   ├── DoctorData.php
│   │   └── DoctorClaimFilterData.php
│   ├── Http/Requests/
│   │   ├── StoreDoctorRequest.php
│   │   └── DoctorPaymentRequest.php
│   ├── Models/
│   │   ├── Doctor.php
│   │   ├── DoctorShift.php
│   │   └── DoctorPayment.php
│   ├── Repositories/
│   │   ├── Contracts/DoctorRepositoryInterface.php
│   │   └── DoctorRepository.php
│   ├── Services/
│   │   ├── DoctorService.php
│   │   └── DoctorClaimsService.php    # 5 fee calculation strategies
│   ├── Routes/web.php
│   └── Providers/DoctorServiceProvider.php
│
├── Accounting/
│   ├── Actions/
│   │   ├── PostJournalEntryAction.php
│   │   ├── RecordTreasuryEntryAction.php
│   │   └── AutoPostBookingPaymentAction.php
│   ├── Controllers/
│   │   ├── TreasuryController.php
│   │   ├── JournalController.php
│   │   ├── ChartOfAccountsController.php
│   │   ├── TrialBalanceController.php
│   │   ├── IncomeStatementController.php
│   │   └── AccountStatementController.php
│   ├── DTOs/
│   │   ├── JournalEntryData.php
│   │   └── TreasuryEntryData.php
│   ├── Http/Requests/
│   │   ├── StoreJournalEntryRequest.php
│   │   └── StoreTreasuryEntryRequest.php
│   ├── Models/
│   │   ├── Account.php
│   │   ├── JournalEntry.php
│   │   └── TreasuryEntry.php
│   ├── Repositories/
│   │   ├── Contracts/
│   │   │   ├── AccountRepositoryInterface.php
│   │   │   ├── JournalRepositoryInterface.php
│   │   │   └── TreasuryRepositoryInterface.php
│   │   ├── AccountRepository.php
│   │   ├── JournalRepository.php
│   │   └── TreasuryRepository.php
│   ├── Services/
│   │   ├── AccountingService.php
│   │   ├── TrialBalanceService.php
│   │   └── IncomeStatementService.php
│   ├── Routes/web.php
│   └── Providers/AccountingServiceProvider.php
│
├── Inventory/
│   ├── Actions/
│   │   ├── ReceivePurchaseInvoiceAction.php
│   │   ├── IssueStockPermitAction.php
│   │   ├── AddStockPermitAction.php
│   │   └── StockTakeAdjustmentAction.php
│   ├── Controllers/
│   │   ├── InventoryController.php
│   │   ├── SupplierController.php
│   │   ├── PurchaseInvoiceController.php
│   │   ├── StockPermitController.php
│   │   ├── StockTakeController.php
│   │   └── PurchaseReturnController.php
│   ├── DTOs/
│   │   ├── InventoryItemData.php
│   │   └── PurchaseInvoiceData.php
│   ├── Http/Requests/
│   │   ├── StoreInventoryItemRequest.php
│   │   └── StorePurchaseInvoiceRequest.php
│   ├── Models/
│   │   ├── InventoryItem.php
│   │   ├── Supplier.php
│   │   ├── PurchaseInvoice.php
│   │   ├── PurchaseInvoiceItem.php
│   │   ├── StockPermit.php
│   │   └── StockTake.php
│   ├── Repositories/
│   │   ├── Contracts/
│   │   │   ├── InventoryRepositoryInterface.php
│   │   │   └── SupplierRepositoryInterface.php
│   │   ├── InventoryRepository.php
│   │   └── SupplierRepository.php
│   ├── Services/
│   │   ├── InventoryService.php
│   │   ├── PurchaseInvoiceService.php
│   │   └── StockAlertService.php
│   ├── Routes/web.php
│   └── Providers/InventoryServiceProvider.php
│
├── Insurance/
│   ├── Actions/
│   │   ├── CreateInsuranceCompanyAction.php
│   │   └── ManagePriceListAction.php
│   ├── Controllers/
│   │   ├── InsuranceCompanyController.php
│   │   └── PriceListController.php
│   ├── DTOs/
│   │   ├── InsuranceCompanyData.php
│   │   └── PriceListData.php
│   ├── Http/Requests/
│   │   ├── StoreInsuranceCompanyRequest.php
│   │   └── StorePriceListRequest.php
│   ├── Models/
│   │   ├── InsuranceCompany.php
│   │   └── PriceList.php
│   ├── Repositories/
│   │   ├── Contracts/InsuranceRepositoryInterface.php
│   │   └── InsuranceRepository.php
│   ├── Services/InsuranceService.php
│   ├── Routes/web.php
│   └── Providers/InsuranceServiceProvider.php
│
├── Reporting/
│   ├── Controllers/
│   │   ├── DashboardController.php
│   │   ├── DeptRevenueReportController.php
│   │   ├── CasesReportController.php
│   │   ├── DoctorClaimsReportController.php
│   │   ├── DoctorPaymentsReportController.php
│   │   ├── InsuranceReportController.php
│   │   ├── InventoryMovementController.php
│   │   ├── PurchasePriceReportController.php
│   │   ├── ProfitLossController.php
│   │   ├── ExpenseAnalysisController.php
│   │   └── SystemLogController.php
│   ├── DTOs/ReportFilterData.php
│   ├── Services/
│   │   ├── ReportingService.php
│   │   └── ExcelExportService.php
│   ├── Routes/web.php
│   └── Providers/ReportingServiceProvider.php
│
└── Admin/
    ├── Actions/
    │   ├── CreateUserAction.php
    │   └── AssignRoleAction.php
    ├── Controllers/
    │   ├── UserController.php
    │   ├── RoleController.php
    │   ├── SettingsController.php
    │   ├── ArchiveController.php
    │   └── SystemLogController.php
    ├── DTOs/
    │   ├── UserData.php
    │   └── SettingsData.php
    ├── Http/Requests/
    │   ├── StoreUserRequest.php
    │   └── UpdateSettingsRequest.php
    ├── Models/
    │   ├── Setting.php
    │   └── ActivityLog.php
    ├── Repositories/
    │   ├── Contracts/UserRepositoryInterface.php
    │   └── UserRepository.php
    ├── Services/
    │   ├── UserManagementService.php
    │   └── ActivityLogService.php
    ├── Routes/web.php
    └── Providers/AdminServiceProvider.php

app/
├── Repositories/
│   └── BaseRepository.php              # Shared abstract base
├── DTOs/
│   ├── PatientDTO.php                  # Cross-module patient data
│   └── DoctorDTO.php                   # Cross-module doctor data
└── Providers/
    └── RepositoryServiceProvider.php   # Binds all interfaces

database/migrations/                    # All module migrations here
resources/js/
├── Pages/
│   ├── Dashboard/Index.vue
│   ├── Booking/
│   │   ├── Index.vue
│   │   └── Partials/
│   │       ├── BookingForm.vue
│   │       └── BookingStatusBadge.vue
│   ├── Clinic/Index.vue
│   ├── Labs/Index.vue
│   ├── Surgery/Index.vue
│   ├── Lasik/Index.vue
│   ├── Laser/Index.vue
│   ├── Doctor/
│   │   ├── Index.vue
│   │   ├── Claims.vue
│   │   ├── Payments.vue
│   │   └── Shifts.vue
│   ├── Accounting/
│   │   ├── Treasury.vue
│   │   ├── Journal.vue
│   │   ├── ChartOfAccounts.vue
│   │   ├── TrialBalance.vue
│   │   ├── IncomeStatement.vue
│   │   └── AccountStatement.vue
│   ├── Inventory/
│   │   ├── Index.vue
│   │   ├── Suppliers.vue
│   │   ├── PurchaseInvoices.vue
│   │   ├── StockPermit.vue
│   │   ├── StockTake.vue
│   │   └── PurchaseReturns.vue
│   ├── Insurance/
│   │   ├── Companies.vue
│   │   └── PriceLists.vue
│   ├── Reporting/
│   │   ├── Index.vue
│   │   ├── DeptRevenue.vue
│   │   ├── CasesReport.vue
│   │   ├── DoctorClaims.vue
│   │   ├── DoctorPayments.vue
│   │   ├── InsuranceClaims.vue
│   │   ├── InventoryMovement.vue
│   │   ├── PurchasePrices.vue
│   │   ├── ProfitLoss.vue
│   │   └── ExpenseAnalysis.vue
│   ├── Admin/
│   │   ├── Users.vue
│   │   ├── Roles.vue
│   │   ├── SystemLog.vue
│   │   ├── Archive.vue
│   │   └── Settings.vue
│   └── Auth/Login.vue
├── Components/
│   ├── Layout/
│   │   ├── AppLayout.vue               # Sidebar + Topbar wrapper
│   │   ├── Sidebar.vue
│   │   └── Topbar.vue
│   └── Shared/
│       ├── DataTable.vue
│       ├── Modal.vue
│       ├── ConfirmDialog.vue
│       ├── StatCard.vue
│       ├── Badge.vue
│       ├── SearchBar.vue
│       ├── DateFilter.vue
│       ├── ExportBar.vue
│       └── ProgressBar.vue
└── composables/
    ├── useNotifications.ts
    ├── useExport.ts
    └── usePrint.ts

tests/
├── Feature/
│   ├── Booking/
│   │   └── BookingControllerTest.php
│   ├── Clinic/
│   ├── Surgery/
│   ├── Accounting/
│   ├── Inventory/
│   └── Admin/
└── Unit/
    ├── Doctor/
    │   └── DoctorClaimsServiceTest.php  # All 5 fee formulas
    ├── Accounting/
    │   └── TrialBalanceServiceTest.php
    └── Inventory/
        └── StockAlertServiceTest.php
```

**Structure Decision**: Option 2 (Web Application) — Laravel backend with nwidart
modules as domain containers, Vue 3 + Inertia.js as the SPA-like frontend. All 12
modules follow identical internal layering (Action → Service → Repository → Model).

---

## Complexity Tracking

| Complexity Item | Why Needed | Simpler Alternative Rejected Because |
|-----------------|-----------|--------------------------------------|
| 12 nwidart modules | Each of 28 screens has independent routes, migrations, RBAC guards, and business rules | Single `app/` namespace would mix 6 domains in Controllers/, Services/, violating SRP and making permission scoping impossible |
| 5 fee calculation strategies in DoctorClaimsService | Business spec explicitly defines 5 distinct formulas: clinic/labs/laser (service share), surgery/lasik (supply deduction), insurance-surgery (triple calc) | A single formula would produce incorrect doctor entitlements for 3 of 5 department types |
| Repository pattern | Isolates Eloquent from business logic; enables testability with fakes | Direct Eloquent in Services would make DoctorClaimsService untestable without a real DB; fee formula unit tests require injecting test data |
| maatwebsite/laravel-excel addition | Excel import/export is a hard FR (FR-010); 3 modules need it (Services, Inventory, all reports) | SheetJS (frontend-only) can't handle server-side report generation across all records |
| Separate DTOs per operation | Booking creation vs status change vs cancellation have different fields and validation | One monolithic DTO would always carry optional fields and produce confusing validation errors |
