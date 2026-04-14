<!--
SYNC IMPACT REPORT
==================
Version change: N/A (initial) → 1.0.0
Added sections:
  - Core Principles (I. SOLID, II. KISS, III. DRY, IV. Clean Architecture, V. Test Discipline)
  - Technology Stack & Constraints
  - Development Workflow & Quality Gates
  - Governance
Removed sections: N/A — initial ratification
Modified principles: N/A — initial ratification
Templates reviewed:
  ✅ .specify/templates/plan-template.md — Constitution Check section aligns with
     SOLID/Clean Architecture gates; no structural changes needed
  ✅ .specify/templates/spec-template.md — FR/SC structure compatible with clean
     architecture requirements; no changes needed
  ✅ .specify/templates/tasks-template.md — Phase structure compatible with
     Models → Repositories → Services → Actions → Controllers layering; no changes needed
  ⚠ .specify/templates/commands/ — No command files found; skip
Follow-up TODOs: None — all placeholders resolved
-->

# Neil Hospital Constitution

## Core Principles

### I. SOLID Principles (NON-NEGOTIABLE)

Every class, module, and service MUST adhere to all five SOLID principles:

- **Single Responsibility (SRP)**: A class MUST have exactly one reason to change.
  Controllers handle HTTP; Services handle business logic; Repositories handle
  persistence; Actions encapsulate single use-case operations. No mixing of concerns.
- **Open/Closed (OCP)**: Classes MUST be open for extension and closed for modification.
  Use interfaces, abstract classes, and Laravel's service container bindings to extend
  behavior without touching existing code.
- **Liskov Substitution (LSP)**: Subtypes MUST be substitutable for their base types.
  Any class implementing an interface MUST honour its full contract with no surprising
  side effects or silent no-ops.
- **Interface Segregation (ISP)**: Interfaces MUST be narrow and role-specific. No class
  SHOULD be forced to implement methods it does not use. Prefer many small interfaces
  over one fat interface.
- **Dependency Inversion (DIP)**: High-level modules MUST NOT depend on low-level modules.
  Both MUST depend on abstractions (interfaces). Inject dependencies through Laravel's
  IoC container; never instantiate concrete dependencies inside a class with `new`.

**Rationale**: SOLID violations are the root cause of the majority of maintenance bugs and
scalability failures. Enforcing them from day one prevents architectural debt.

### II. KISS — Keep It Simple, Stupid

The simplest solution that correctly satisfies the requirement MUST be chosen.

- MUST NOT introduce design patterns, abstractions, or layers that are not yet warranted
  by a concrete, present need (YAGNI — You Ain't Gonna Need It).
- Every added abstraction MUST be justified in code review with a documented reason in
  the relevant plan.md.
- Complexity tracking in plan.md MUST be updated whenever a non-trivial pattern is
  introduced.
- Prefer explicit, readable code over terse, "clever" code. Code is read far more often
  than it is written.

**Rationale**: Premature complexity is the single greatest source of wasted engineering
effort and on-boarding friction in hospital-domain software where requirements shift with
clinical needs.

### III. DRY — Don't Repeat Yourself

Every piece of knowledge or logic MUST have a single, authoritative representation.

- Business rules MUST live in a Service or Action, never duplicated across Controllers,
  Jobs, or Console Commands.
- Database query logic MUST live in a Repository; raw query duplication across Services
  is PROHIBITED.
- Shared validation rules MUST be extracted into Form Request classes or Concern traits.
- Shared UI logic (Vue) MUST be extracted into composables or shared components before
  a third usage.
- Configuration values MUST be read from `config/` files; hard-coded environment keys
  scattered through code are PROHIBITED.

**Rationale**: Duplication ensures that every bug must be fixed in multiple places and
that behavioral drift between copies causes patient-safety-critical inconsistencies in
a hospital domain.

### IV. Clean Architecture Layering (NON-NEGOTIABLE)

The codebase MUST be structured in discrete, dependency-directed layers.
Dependencies MUST only point inward (toward the domain core).

```
HTTP Layer        → Controllers, Form Requests, API Resources
Application Layer → Actions (single use-case), Jobs, Listeners, Services
Domain Layer      → Models (Eloquent), DTOs, Domain Exceptions, Enums
Infrastructure    → Repositories, External API Adapters, Mail/Notification classes
```

Rules:
- **Controllers**: MUST only resolve a request, delegate to an Action or Service,
  and return a response. Controllers MUST NOT contain business logic or DB queries.
- **Services**: MUST orchestrate multiple domain operations for a bounded use case.
  A Service MUST NOT directly access the database; it MUST call a Repository.
- **Actions**: MUST encapsulate a single, atomic use-case operation (e.g.,
  `CreatePatientAdmissionAction`). Actions MUST be thin wrappers that call
  Service/Repository methods.
- **Repositories**: MUST own all Eloquent/DB query logic for a given Model.
  Repositories MUST implement a corresponding interface bound in the service container.
- **DTOs (Data Transfer Objects)**: MUST be used to transfer structured data between
  layers. DTOs MUST be immutable value objects (readonly properties preferred).
- **Form Requests**: MUST handle all HTTP input validation. Validation MUST NOT be
  performed inside Controllers or Services.
- **API Resources**: MUST transform Model/DTO data for API responses. Response shaping
  MUST NOT occur inside Controllers.
- **Laravel Modules** (nwidart/laravel-modules): Each feature domain (e.g., Patient,
  Admission, Billing) MUST be encapsulated in its own module following the same layering.

**Rationale**: Hospital systems grow large and require parallel team development. Clean
layers enforce boundaries that allow modules to be developed, tested, and replaced
independently without cascading breakage.

### V. Test Discipline

Automated tests MUST accompany every feature and bug fix.

- **Unit tests**: Every Service, Action, and Repository method MUST have unit tests.
  Business logic MUST be unit-tested in isolation using mocks/fakes for dependencies.
- **Feature tests**: Every Controller endpoint MUST have feature/integration tests that
  exercise the full HTTP → DB stack (Laravel's `RefreshDatabase` trait).
- **Test-First encouraged**: TDD (Red-Green-Refactor) is the recommended approach;
  writing tests after implementation is permitted only when the design is already clear
  and tests are committed in the same PR as the implementation.
- **No mocking the database** in feature tests: Use SQLite in-memory or a dedicated
  test database. Mocked DB tests that pass while the real migration fails are PROHIBITED.
- Test coverage for critical clinical paths (patient registration, admissions, medication
  dispensing) MUST be maintained at ≥ 80% line coverage.
- `composer test` MUST pass (lint + PHPUnit) before any PR is mergeable.

**Rationale**: Clinical software errors have direct patient-safety implications.
Automated tests are the first line of defense against regressions.

## Technology Stack & Constraints

**Backend**: PHP 8.3 · Laravel 13 · Laravel Fortify (auth) · Laravel Sanctum (API tokens)
· Laravel Telescope (dev observability) · nwidart/laravel-modules (modular domain structure)
· spatie/laravel-permission (RBAC) · spatie/laravel-medialibrary (file attachments)
· spatie/laravel-query-builder (filterable API queries)

**Frontend**: Vue 3 · Inertia.js · TypeScript · Vite

**Tooling**: PHPUnit 12 · Laravel Pint (PHP linting) · ESLint · Prettier
· Mockery (PHP mocking)

**Constraints**:
- PHP version MUST remain ≥ 8.3; no 8.2-only features (named arguments, readonly
  classes, etc.) MUST be avoided if they reduce 8.3 compatibility.
- All new modules MUST be registered under `nwidart/laravel-modules`; standalone
  `app/` additions are PROHIBITED for domain features — `app/` is reserved for
  framework-level infrastructure (Providers, Middleware, global Models).
- All inter-module communication MUST go through defined Service interfaces; direct
  cross-module Model queries are PROHIBITED.
- External API integrations MUST be wrapped in an Adapter class implementing an
  interface; the application layer MUST never call Guzzle/HTTP clients directly.
- Environment secrets MUST be stored in `.env` files only; they MUST NOT be
  committed to version control.

## Development Workflow & Quality Gates

**Branching**: Feature branches named `###-short-description`; PRs target `main`.

**Quality Gate** (MUST pass before merge):
1. `composer test` — PHP lint (Pint) + PHPUnit suite passes.
2. `npm run lint:check` — ESLint passes with zero errors.
3. `npm run format:check` — Prettier formatting check passes.
4. `npm run types:check` — TypeScript compilation passes with no errors.
5. Constitution Check (see plan.md gate) — reviewer confirms no SOLID/Clean Architecture
   violations introduced.

**Code Review Requirements**:
- Every PR MUST be reviewed by at least one other developer.
- Reviewer MUST verify: layer boundaries respected, no DRY violations, no direct DB
  access in Controllers or Actions.
- Complexity introductions (new patterns, additional abstractions) MUST be documented
  in the PR description with justification referencing the Complexity Tracking table
  in plan.md.

**Commit discipline**: Commits MUST be atomic and scoped to a single logical change.
Commit messages MUST follow Conventional Commits (`feat:`, `fix:`, `refactor:`, `test:`,
`docs:`, `chore:`).

## Governance

This constitution supersedes all other development practices and conventions.
No practice that conflicts with a Core Principle is valid without a formal amendment.

**Amendment procedure**:
1. Propose amendment via PR targeting `.specify/memory/constitution.md`.
2. Amendment MUST document: motivation, impact on existing code, migration plan (if any).
3. Amendment MUST be approved by at least two senior developers.
4. Version MUST be bumped per semantic versioning rules (see below).
5. Dependent templates (plan-template.md, spec-template.md, tasks-template.md) MUST be
   reviewed and updated in the same PR if the amendment introduces new mandatory sections
   or removes existing ones.

**Versioning policy**:
- MAJOR: Backward-incompatible governance change, principle removal, or redefinition.
- MINOR: New principle or section added, or materially expanded guidance.
- PATCH: Clarifications, wording improvements, typo fixes, non-semantic refinements.

**Compliance review**: Constitution compliance MUST be verified at every PR review.
Deviations MUST be documented and justified in the plan.md Complexity Tracking table.
Unjustified deviations are grounds for blocking a merge.

**Runtime development guidance**: Use `.specify/memory/` for agent runtime context and
`.specify/templates/` for spec/plan/task scaffolding. The constitution is the root
authority; any conflict between templates and the constitution resolves in favour of
the constitution.

**Version**: 1.0.0 | **Ratified**: 2026-04-14 | **Last Amended**: 2026-04-14
