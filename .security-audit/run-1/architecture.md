# Architecture and Threat Model: CUCI.IN (Milestone 2)

## 1. Product, Principals, and Normal Authority
CUCI.IN is a laundry operations and management Multi-Page Application (MPA) built for commercial coin/drop-off laundry businesses. It defines four primary trust principals:
- **Guest / Unauthenticated User**: Accesses public landing pages, tracking lookup (`GET /lookup`), public tracking API (`GET /api/v1/track/{transaction_code}`), guest payment proof submission (`POST /lookup/payment-proof`), and auth endpoints (`/login`, `/register`, `/forgot-password`).
- **Customer (`role:customer`)**: Authenticated customer with linked `Customer` record. Can view personal order history (`/customer/transactions`), submit complaints (`/customer/complaints`), upload payment transfer proofs for personal orders, and view private payment proof images.
- **Staff / Cashier (`role:staff`)**: Authenticated store personnel. Operates the POS terminal (`/staff/transactions/create`), records payments, verifies non-cash payment proofs (`/staff/payments`), manages customer records, and responds to complaints.
- **Administrator / Owner (`role:admin`)**: Elevated operator. Manages user accounts and roles (`/admin/users`), configures service catalog and pricing (`/admin/services`), accesses aggregated business analytics and financial reports, and performs CSV/Excel/PDF exports (`/admin/reports`).

Protected resources include:
- Financial transaction records (`transactions`, `transaction_details`, `payments`, `idempotency_keys`).
- Customer PII (names, phone numbers, addresses).
- Private payment receipts (bank transfer screenshots, QRIS transaction images stored on private storage disk).
- Master service pricing configurations and operational turnover analytics.

## 2. Comparable Software Baseline
Comparable systems include open-source POS and laundry management suites (e.g. OpenBoxes, Invoice Ninja, Akaunting). In such systems, order tracking is commonly exposed via tokenized links or nonces rather than sequential incremental keys. Trade-offs observed:
- Public order status lookup requires masking customer PII to prevent wholesale harvesting.
- Guest payment proof submission must be bounded by IP and rate limiters to prevent ledger pollution or harassment of pending verification queues.
- Financial reporting and data export functions must strictly require administrative authorization due to corporate data leakage risks.

## 3. Tech Stack and Deployment Visibility
- **Framework & Runtime**: Laravel 12 on PHP 8.4 (MPA), Alpine.js, Tailwind CSS, Vite.
- **Data Stores**: PostgreSQL 16 in production (`cuci_in`), SQLite in-memory for automated PHPUnit test suites.
- **Storage Driver**: Laravel Local Filesystem. Private files (`payments/proofs`) reside at `storage/app/private/`. Public assets (`complaints/photos`) reside at `storage/app/public/`.
- **Infrastructure (docs/sistem.md)**: AWS EC2 `t3.micro` (Ubuntu/Nginx 1.28+, PHP 8.5 FPM), reverse-proxied through Cloudflare (Flexible SSL).
- **Execution Limits**: Offline testing runs via `vendor/bin/phpunit` using local SQLite without external dependencies. No external network access is needed for security assertions.

## 4. Entry Surfaces and Data Sinks
- **HTTP Web Surface**:
  - `routes/web.php` defines 91 routes across guest, customer, staff, and admin prefixes.
  - Sinks: Database Eloquent models (`Transaction`, `Payment`, `Complaint`, `User`, `Customer`), filesystem writes (`Payment::PROOF_DIRECTORY`), PDF generation (`Barryvdh\DomPDF\Facade\Pdf`), CSV streaming (`php://output`).
- **HTTP API Surface**:
  - `routes/api.php` exposes `GET /api/v1/track/{transaction_code}`.
  - Sinks: JSON response returning status and ISO 8601 timestamps.
- **Storage Surface**:
  - Dynamic file serving routes `GET staff/payments/{payment}/proof` and `GET customer/payments/{payment}/proof` streaming from private storage.
  - Public symlink `/storage/` serving `storage/app/public/`.

## 5. Trust Boundaries and Primary Controls
- **TB-1: Guest to Authentication/Session**: Controlled by `guest` middleware, `LoginRequest` (email+IP rate limiter check), `RegisterRequest` (honeypot + timestamp velocity check), and CSRF token verification.
- **TB-2: Guest to Public Tracking**: Controlled by `TransactionLookupController::index` and `TrackingController::show`. Customer names are masked with initial + asterisks.
- **TB-3: Guest to Transaction Ledger**: `POST /lookup/payment-proof` requires `transaction_code` and `phone_last_4` verification against customer phone.
- **TB-4: Customer to Customer Isolation (Anti-IDOR)**: Enforced via `ComplaintPolicy` (`Gate::authorize`), `StorePaymentProofRequest::authorize()`, and scoped controller queries (`$customer->transactions()`).
- **TB-5: Staff to Administrative Boundary**: Enforced via `role:admin` middleware for user management, but weakened in `admin/reports` route grouping.
- **TB-6: Client Input to Spreadsheet Software**: Untrusted customer names flow from registration into `ReportController::exportCsv` without leading character sanitization.

## 6. Repository-Relative Starting Paths
- Routes: `routes/web.php`, `routes/api.php`
- Middleware & Bootstrap: `bootstrap/app.php`, `app/Http/Middleware/`
- Controllers: `app/Http/Controllers/`
- Requests: `app/Http/Requests/`
- Services & Actions: `app/Services/`
- Policies: `app/Policies/`
- Infrastructure Docs: `docs/sistem.md`, `docs/rules_milestone_2.md`

## 7. Prior Coverage and Initial State
This is the baseline full security audit (run-1) on commit `ec5bb88159c91ab860205fabe38fb8b32d0dc328`. No prior compatible coverage ledgers or findings files exist in the repository.

## 8. Companion Selection Summary
Derived from ATTACK-CLASSES.md:
- **WEB-PROTOCOL-AND-AUTH.md**: Selected for multi-role session auth, Sanctum API tracking surface, proxy trust settings, and public lookup endpoints.
- **RESOURCE-EXHAUSTION-AND-AVAILABILITY.md**: Selected for unthrottled guest upload, unthrottled tracking API, and background image optimization queue jobs.
- **DATA-ISOLATION-AND-LIFECYCLE.md**: Selected for customer-to-customer data segregation, private vs public proof storage, and CSV/Excel/PDF export boundaries.
- **CLIENT-SIDE.md**: Selected for Blade/Alpine.js DOM rendering, formula injection risk in exported spreadsheets, and CSRF protection.

