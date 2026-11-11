# Security Audit Report: CUCI.IN (Milestone 2)

## 1. Executive Summary & Audit Metadata
- **Target Repository**: `c:\laragon\www\cuci-in`
- **Reviewed Source Ref**: `ec5bb88159c91ab860205fabe38fb8b32d0dc328` (branch: `develop`, clean worktree)
- **Run Profile**: `standard`
- **Execution Policy**: Sandboxed source inspection and offline local verification only. No external networks, shared infrastructure, or production endpoints were contacted.
- **Prior Run Reference**: None (baseline initial run).
- **Audit Completion**: All 14 planned coverage units reviewed; 7 findings recorded in `findings.json` (5 confirmed, 1 needs_validation, 1 rejected).

### Security Posture Summary
The CUCI.IN application exhibits strong baseline defense-in-depth in multiple critical areas:
- Financial transactions utilize database-level pessimistic locking (`lockForUpdate`), atomic DB transactions, idempotency headers, and strict server-side calculation (`TransactionCalculator`) that completely ignores client-supplied pricing.
- Image uploads are restricted to safe image formats, size-bounded to 5MB, renamed with high-entropy cryptographic hashes, and re-encoded using PHP GD in background queue jobs, stripping EXIF metadata.
- Payment proof files are segregated on a private non-web-accessible disk and served via authorized streaming controllers.
- Access control between customers is enforced via recently introduced Laravel Policies (`ComplaintPolicy`, `TransactionPolicy`) and scoped model relationships.

However, three primary architectural vulnerabilities exist:
1. **Unthrottled Public Payment Proof Upload**: Public guests can brute-force the 4-digit customer phone suffix on `POST /lookup/payment-proof` to inject spurious pending payment records and exhaust the submittable balance of legitimate orders.
2. **Broken Access Control on Financial Reports**: The `/admin/reports` routes inherit the `role:admin,staff` middleware group, permitting operational cashiers to view executive revenue analytics and export customer databases.
3. **CSV Formula Injection**: `ReportController::exportCsv` writes raw customer names to CSV files without prepending an apostrophe to escape formula trigger characters (`=`, `+`, `-`, `@`).

---

## 2. Confirmed Findings Table

| Severity | Fingerprint | Title | Affected Boundary | Observed Result |
| :--- | :--- | :--- | :--- | :--- |
| **HIGH** | `CUCIIN-GUEST-PAYMENT-PROOF-UNTHROTTLED-BRUTE-FORCE` | Unthrottled Guest Payment Proof Endpoint Allows Phone Verification Brute Force and Spurious Payment Injection | Guest -> Ledger & Storage | Brute force of 4 digits allows arbitrary file upload and unverified payment injection. |
| **MEDIUM** | `CUCIIN-REPORT-CONTROLLER-BROKEN-ACCESS-CONTROL` | Broken Access Control Allows Operational Staff to Access Executive Financial Reports and Customer PII | Staff -> Admin Boundary | Staff users can view executive turnover figures and export customer PII. |
| **MEDIUM** | `CUCIIN-REPORT-CSV-FORMULA-INJECTION` | CSV Formula Injection via Unsanitized Customer Names in Operational Transaction Export | Customer Input -> Admin Spreadsheet | Raw customer names starting with formula characters execute upon spreadsheet open. |
| **MEDIUM** | `CUCIIN-TRUST-PROXIES-WILDCARD-IP-SPOOFING` | Overly Permissive Proxy Trust Configuration Enables Complete Bypass of IP-Based Rate Limiting | External -> Trust Layer | Wildcard proxy trust permits arbitrary X-Forwarded-For spoofing and rate limit bypass. |
| **LOW** | `CUCIIN-AUTH-AND-API-UNTHROTTLED-SURFACE` | Absence of Route-Level Throttle Middleware on Auth and Public API Endpoints | Guest -> Auth & API | Unconstrained request volume on login, register, and public tracking API. |

---

## 3. Confirmed Vulnerability Details

### Finding 1: Unthrottled Guest Payment Proof Brute Force
- **Fingerprint**: `CUCIIN-GUEST-PAYMENT-PROOF-UNTHROTTLED-BRUTE-FORCE`
- **Severity**: HIGH (Likelihood: High, Impact: High)
- **Source Location**: `routes/web.php:25-26`, `app/Http/Controllers/TransactionLookupController.php:87-132`
- **Lower-Trust Principal**: Unauthenticated remote guest.
- **Bound Reproduction**: Sending repeated POST requests to `/lookup/payment-proof` with candidate 4-digit phone numbers (0000-9999). When correct, an image is stored in `storage/app/private/payments/proofs/` and a pending payment is created.
- **Conditions**: Target transaction has an outstanding balance and linked customer phone.
- **Impact**: Alteration of transaction financial status, denial of legitimate customer payment upload, disk storage consumption.
- **Smallest Effective Fix**:
  ```diff
  --- a/routes/web.php
  +++ b/routes/web.php
  @@ -24,4 +24,5 @@ Route::get('/lookup', [TransactionLookupController::class, 'index'])
   Route::post('/lookup/payment-proof', [TransactionLookupController::class, 'storePaymentProof'])
  +    ->middleware('throttle:5,1')
       ->name('transactions.lookup.payment-proof');
  ```

### Finding 2: Broken Access Control on Financial Reports
- **Fingerprint**: `CUCIIN-REPORT-CONTROLLER-BROKEN-ACCESS-CONTROL`
- **Severity**: MEDIUM (Likelihood: High, Impact: Medium)
- **Source Location**: `routes/web.php:125-293`, `app/Http/Controllers/ReportController.php:22-199`
- **Lower-Trust Principal**: Authenticated cashier (`role:staff`).
- **Bound Reproduction**: Authenticate as a staff user and request `GET /admin/reports` or `GET /admin/reports/export/csv`. Response returns HTTP 200 with full financial statistics and export payload.
- **Conditions**: User account possesses active `staff` role.
- **Impact**: Confidential financial turnover, profit metrics, and full customer database disclosed to non-managerial staff.
- **Smallest Effective Fix**: Move `/admin/reports*` routes to a dedicated `Route::middleware('role:admin')` group.

### Finding 3: CSV Formula Injection in Transaction Export
- **Fingerprint**: `CUCIIN-REPORT-CSV-FORMULA-INJECTION`
- **Severity**: MEDIUM (Likelihood: Medium, Impact: Medium)
- **Source Location**: `app/Http/Controllers/ReportController.php:177-192`, `app/Http/Requests/Auth/RegisterRequest.php:27`
- **Lower-Trust Principal**: Untrusted customer registering via `/register`.
- **Bound Reproduction**: Register customer with name `=SUM(1+1)`. As admin, download `/admin/reports/export/csv`. Inspect generated file to verify column 3 contains unescaped `=SUM(1+1)`.
- **Conditions**: Administrator opens downloaded CSV in Microsoft Excel without Protected View.
- **Impact**: Formula execution or DDE command execution on administrator desktop.
- **Smallest Effective Fix**: Prepend an apostrophe (`'`) to any CSV field value beginning with `=`, `+`, `-`, `@`, `\t`, or `\r`.

### Finding 4: Wildcard Proxy Trust Enables Rate Limiting Bypass
- **Fingerprint**: `CUCIIN-TRUST-PROXIES-WILDCARD-IP-SPOOFING`
- **Severity**: MEDIUM (Likelihood: High, Impact: Medium)
- **Source Location**: `bootstrap/app.php:19`, `app/Http/Requests/Auth/LoginRequest.php:111-114`
- **Lower-Trust Principal**: External web client.
- **Bound Reproduction**: Send failed login attempts to `/login` rotating `X-Forwarded-For` on each request. The login lockout counter does not increment across attempts, bypassing brute-force lockout.
- **Conditions**: Application reached directly or through proxy passing raw headers.
- **Impact**: Complete bypass of IP-based rate limiting and brute force protection.
- **Smallest Effective Fix**: Specify exact trusted proxy CIDR ranges (e.g. Cloudflare IP blocks) instead of wildcard `'*'`.

### Finding 5: Lack of Route-Level Throttling on Auth and API Routes
- **Fingerprint**: `CUCIIN-AUTH-AND-API-UNTHROTTLED-SURFACE`
- **Severity**: LOW (Likelihood: High, Impact: Low)
- **Source Location**: `routes/web.php:54-73`, `routes/api.php:8-12`
- **Lower-Trust Principal**: Unauthenticated remote client.
- **Bound Reproduction**: Rapid automated GET requests to `/api/v1/track/{transaction_code}` or POST requests to `/login` are processed without HTTP 429 status code returns.
- **Conditions**: Network reachability to web/API endpoints.
- **Impact**: Denial of service risk, CPU/memory consumption, user enumeration.
- **Smallest Effective Fix**: Attach `throttle:5,1` to `/login`, `/register`, `/forgot-password`, and `throttle:60,1` to `/api/v1/track/{transaction_code}`.

---

## 4. Needs Validation Leads

| Fingerprint | Title | Blocker | Validation Plan |
| :--- | :--- | :--- | :--- |
| `CUCIIN-INFRA-EC2-DIRECT-PORT-EXPOSURE` | Direct Access to EC2 Ports 80/443 Bypassing Cloudflare | Security Group and Nginx live firewall settings are not present in git repository. | Verify AWS Security Group inbound rules; restrict ports 80/443 to Cloudflare IP blocks. |

---

## 5. Rejected Candidate Dispositions

- **`CUCIIN-REJECTED-SQL-INJECTION-LOOKUP`**:
  - **Hypothesis**: Raw input in `whereRaw('UPPER(transaction_code) = ?', [mb_strtoupper($code)])` could lead to SQL injection.
  - **Disposition**: Rejected. Source inspection confirms PDO positional parameter binding (`?`) is used consistently across all lookups. No string concatenation exists.

---

## 6. Hardening Notes and Positive Security Practices

1. **Pessimistic Locking on Financial Ledger**: `RecordPayment::execute` and `SubmitPaymentProof::execute` utilize `lockForUpdate()` within explicit database transactions (`DB::transaction`), eliminating race conditions and double-submits.
2. **Idempotency Guard**: `EnsureIdempotency` middleware protects critical transaction creation routes (`POST /staff/transactions`), hashing request payloads and rejecting in-flight duplicate requests with HTTP 409 Conflict.
3. **Safe Image Processing**: `ImageOptimizer::optimize` re-encodes uploaded JPEG, PNG, and WebP images using PHP GD, destroying any polyglot shell scripts, EXIF tracking data, or embedded comments.
4. **Anti-IDOR Policy Authorization**: Customer complaint inspection and management enforces `Gate::authorize('view', $complaint)` and `Gate::authorize('create', Complaint::class)` using dedicated policies.

---

## 7. Coverage Ledger Summary
- **Total Units Planned & Evaluated**: 14
- **Candidate Units (Defects Found)**: 7
- **Covered Clean Units**: 7
- **Blocked / Deferred Units**: 0
- **Out of Scope Units**: 0
- **Validator Status**: PASS (`validate-findings.cjs` and `validate-coverage-ledger.cjs` report 0 errors).

