# Findings Detail: Confirmed Security Deficiencies

This document details the independently verified security vulnerabilities rated Medium, High, or Critical identified during the full security audit of CUCI.IN (commit `ec5bb88159c91ab860205fabe38fb8b32d0dc328`).

---

## 1. CUCIIN-GUEST-PAYMENT-PROOF-UNTHROTTLED-BRUTE-FORCE

- **Title**: Unthrottled Guest Payment Proof Endpoint Allows Phone Verification Brute Force and Spurious Payment Injection
- **Overall Severity**: High (Likelihood: High, Impact: High)
- **Confidence**: High
- **CWE**: CWE-307 (Improper Restriction of Excessive Authentication Attempts), CWE-20 (Improper Input Validation)

### A. Vulnerability Description and Root Cause
The public transaction lookup route allows unauthenticated guests who hold a transaction code (e.g. `TRX-0001` or `TRK-20261025-046`) to submit proof of payment via bank transfer or QRIS (`POST /lookup/payment-proof`). The endpoint attempts to authenticate that the submitter is the legitimate customer by requiring the last 4 digits of the customer's phone number (`phone_last_4`).

However:
1. `POST /lookup/payment-proof` in `routes/web.php` has zero rate-limiting middleware (`throttle`).
2. There are only 10,000 possible 4-digit combinations (0000 to 9999).
3. `TransactionLookupController::storePaymentProof` throws a standard validation exception on mismatch without recording attempt counts or triggering a lockout.
4. Because automated HTTP clients can execute hundreds of requests per second against the local or remote server, an attacker can brute force the 4 digits in seconds to minutes.

### B. Observed Security Impact
Once the 4-digit phone suffix is matched:
1. `SubmitPaymentProof::execute` uploads and saves the attacker-supplied image to `storage/app/private/payments/proofs/`.
2. A new `Payment` record is created with `verification_status = 'Menunggu Verifikasi'` and `amount = <attacker_value>`.
3. The transaction's `submittable_amount` is reduced by the submitted amount. If the attacker submits the full remaining balance, `submittable_amount` drops to 0, completely preventing the legitimate customer from uploading their real proof of payment.
4. Spurious files are injected into the staff verification queue (`GET /staff/payments`).

### C. Source Trace and Evidence
- **Entrypoint**: `routes/web.php:25`
  ```php
  Route::post('/lookup/payment-proof', [TransactionLookupController::class, 'storePaymentProof'])
      ->name('transactions.lookup.payment-proof');
  ```
- **Propagation**: `app/Http/Controllers/TransactionLookupController.php:100-108`
  ```php
  // 1. Verifikasi 4 digit terakhir nomor telepon customer untuk keamanan
  $customerPhone = preg_replace('/\D/', '', (string) ($transaction->customer?->phone ?? ''));
  $last4 = substr($customerPhone, -4);

  if ($customerPhone === '' || $last4 !== $validated['phone_last_4']) {
      throw ValidationException::withMessages([
          'phone_last_4' => ['4 digit terakhir nomor telepon tidak cocok dengan nomor yang terdaftar pada nota.'],
      ]);
  }
  ```
- **Sink**: `app/Services/SubmitPaymentProof.php:75-86`
  ```php
  $payment = Payment::create([
      'transaction_id' => $lockedTransaction->id,
      'amount' => $amount,
      'payment_type' => RecordPayment::determinePaymentType($lockedTransaction, $amount),
      'method' => $method,
      'proof_file' => $proofPath,
      'verification_status' => Payment::PENDING_STATUS,
      'paid_at' => now(),
      'created_by' => $userId,
  ]);
  ```

### D. Target-Neutral Local Reproduction
1. Seed a transaction with `total_amount = 50000`, `status = 'Diterima'`, linked to a customer with phone `081234567890` (last 4: `7890`).
2. Run a loop sending multipart form submissions with varying `phone_last_4` from `0000` upwards to `POST /lookup/payment-proof`:
   ```bash
   curl -s -X POST http://localhost:8000/lookup/payment-proof \
     -F "transaction_code=TRX-0001" \
     -F "phone_last_4=0001" \
     -F "method=Transfer" \
     -F "amount=50000" \
     -F "proof_file=@test.jpg"
   ```
3. Observe HTTP 302 redirect back with validation errors until `phone_last_4=7890` is sent.
4. On `7890`, observe HTTP 302 redirect with session flash `payment_proof_success`. Querying `payments` table shows the injected record.

### E. Remediation
1. Attach `throttle:5,1` middleware to `POST /lookup/payment-proof` in `routes/web.php`.
2. Add rate limiter keys in `TransactionLookupController::storePaymentProof` tracking failed attempts per IP and per `transaction_code`.

```diff
--- a/routes/web.php
+++ b/routes/web.php
@@ -23,5 +23,6 @@ Route::get('/lookup', [TransactionLookupController::class, 'index'])
     ->name('transactions.lookup');
 Route::post('/lookup/payment-proof', [TransactionLookupController::class, 'storePaymentProof'])
+    ->middleware('throttle:5,1')
     ->name('transactions.lookup.payment-proof');
```

---

## 2. CUCIIN-REPORT-CONTROLLER-BROKEN-ACCESS-CONTROL

- **Title**: Broken Access Control Allows Operational Staff to Access Executive Financial Reports and Customer PII
- **Overall Severity**: Medium (Likelihood: High, Impact: Medium)
- **Confidence**: High
- **CWE**: CWE-284 (Improper Access Control), CWE-269 (Improper Privilege Management)

### A. Vulnerability Description and Root Cause
In `routes/web.php`, lines 125-293 group all operational and administrative routes under a single middleware declaration:
```php
Route::middleware('role:admin,staff')->group(function () {
    // Staff routes...
    Route::prefix('admin')->name('admin.')->group(function () {
        // Customer & Transaksi routes...
        // Master Services...
        // Reports:
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
        Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
        Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');
    });
});
```
While `ServiceController` explicitly checks `$this->user()->role === 'admin'` inside its form requests and methods, `ReportController` has no internal authorization checks. Furthermore, the `admin/reports` route group inherits `role:admin,staff`.

Because `EnsureUserHasRole` checks `in_array($user->role, ['admin', 'staff'], true)`, any authenticated staff cashier can access `GET /admin/reports` and all export routes.

### B. Observed Security Impact
An operational staff member can view:
1. Executive business turnover, gross revenue, net cash collected, and outstanding receivables.
2. Full transaction history across all cashiers and outlets.
3. Complete customer PII database (names, phone numbers, addresses, spending totals) exported via CSV or Excel.

### C. Source Trace and Evidence
- **Entrypoint**: `routes/web.php:125`
  ```php
  Route::middleware('role:admin,staff')->group(function () {
  ```
- **Propagation**: `routes/web.php:281-292`
  ```php
  Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
  Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
  Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
  Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');
  ```
- **Sink**: `app/Http/Controllers/ReportController.php:22-47`
  ```php
  public function index(Request $request): View
  {
      $filters = $this->resolveDateFilters($request);
      $query = $this->buildReportQuery($filters);
      $summary = $this->calculateSummary($filters);
      ...
      return view('admin.reports.index', ...);
  }
  ```

### D. Target-Neutral Local Reproduction
1. Create a user with `role = 'staff'`, `is_active = true`.
2. Authenticate as the staff user via `actingAs($staff)`.
3. Perform a GET request to `route('admin.reports.index')` or `route('admin.reports.export.csv')`.
4. Observe HTTP 200 OK response with full KPI summary and transaction details, rather than HTTP 403 Forbidden.

### E. Remediation
Move the `admin/reports` routes out of the shared `role:admin,staff` group into a dedicated `role:admin` group:

```diff
--- a/routes/web.php
+++ b/routes/web.php
@@ -107,4 +107,17 @@ Route::middleware(['auth', 'active'])->group(function () {
             Route::resource('users', AdminUserController::class)
                 ->except(['show']);
+
+            // Laporan Operasional & Keuangan Khusus Administrator/Owner
+            Route::get('/reports', [ReportController::class, 'index'])
+                ->name('reports.index');
+            Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])
+                ->name('reports.export.pdf');
+            Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])
+                ->name('reports.export.excel');
+            Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])
+                ->name('reports.export.csv');
         });
```

---

## 3. CUCIIN-REPORT-CSV-FORMULA-INJECTION

- **Title**: CSV Formula Injection via Unsanitized Customer Names in Operational Transaction Export
- **Overall Severity**: Medium (Likelihood: Medium, Impact: Medium)
- **Confidence**: High
- **CWE**: CWE-1236 (Improper Neutralization of Formula Elements in a CSV File)

### A. Vulnerability Description and Root Cause
When an administrator exports transactions via `GET /admin/reports/export/csv`, `ReportController::exportCsv` streams fields directly into `fputcsv`:
```php
fputcsv($handle, [
    $transaction->transaction_code,
    $transaction->created_at->format('d/m/Y H:i'),
    $transaction->customer?->name ?? 'Pelanggan Umum',
    $transaction->customer?->phone ?? '-',
    $servicesSummary ?: 'Kiloan Reguler',
    ...
], ';');
```
Any customer registering via `POST /register` can provide a `name` up to 100 characters containing formula trigger characters (`=`, `+`, `-`, `@`, `\t`, `\r`), such as `=SUM(1+1)` or `=cmd|' /C calc'!A0`. Because `fputcsv` does not escape leading trigger characters, spreadsheet software (such as Microsoft Excel) treats the cell as executable formula syntax upon opening.

### B. Observed Security Impact
If an administrator or store owner exports the CSV file and opens it in Microsoft Excel without Protected View, formula execution or Dynamic Data Exchange (DDE) execution can occur on the administrator's workstation.

### C. Source Trace and Evidence
- **Entrypoint**: `app/Http/Requests/Auth/RegisterRequest.php:27`
  ```php
  'name' => ['required', 'string', 'max:100'],
  ```
- **Propagation**: `app/Http/Controllers/ReportController.php:125-162`
  ```php
  $query->with(['customer', 'details.service', 'payments', 'creator'])->lazyById(100)
  ```
- **Sink**: `app/Http/Controllers/ReportController.php:180`
  ```php
  $transaction->customer?->name ?? 'Pelanggan Umum',
  ```

### D. Target-Neutral Local Reproduction
1. Register a customer with name `=1+2`.
2. Create a transaction for that customer.
3. Request `GET /admin/reports/export/csv` as an administrator.
4. Capture output stream:
   ```text
   TRX-XXXX;08/11/2026 05:00;=1+2;08123456789;...
   ```
5. Confirm that the third column contains raw unescaped `=1+2`.

### E. Remediation
Sanitize cell values prior to writing to CSV by prepending an apostrophe (`'`) if the value begins with formula characters:

```diff
--- a/app/Http/Controllers/ReportController.php
+++ b/app/Http/Controllers/ReportController.php
@@ -174,10 +174,14 @@ class ReportController extends Controller
                         ? $transaction->status->value
                         : (string) $transaction->status;

+                    $rawCustomerName = (string) ($transaction->customer?->name ?? 'Pelanggan Umum');
+                    $sanitizedName = preg_replace('/^[=+\-@\t\r]/', "'$0", $rawCustomerName);
+
                     fputcsv($handle, [
                         $transaction->transaction_code,
                         $transaction->created_at->format('d/m/Y H:i'),
-                        $transaction->customer?->name ?? 'Pelanggan Umum',
+                        $sanitizedName,
                         $transaction->customer?->phone ?? '-',
```

---

## 4. CUCIIN-TRUST-PROXIES-WILDCARD-IP-SPOOFING

- **Title**: Overly Permissive Proxy Trust Configuration Enables Complete Bypass of IP-Based Rate Limiting
- **Overall Severity**: Medium (Likelihood: High, Impact: Medium)
- **Confidence**: High
- **CWE**: CWE-348 (Use of Less Trusted Input in a Privileged Position), CWE-290 (Authentication Bypass by Spoofing)

### A. Vulnerability Description and Root Cause
In `bootstrap/app.php` line 19:
```php
$middleware->trustProxies(at: '*');
```
Setting `trustProxies(at: '*')` configures Symfony's `Request` to trust reverse proxy headers (`X-Forwarded-For`, `X-Forwarded-Host`, `X-Forwarded-Proto`) from any IP address. 

In `LoginRequest::throttleKey()`, the throttle bucket key is generated as:
```php
return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
```
When an attacker sends requests with custom `X-Forwarded-For: <random-ip>`, `Request::ip()` returns the spoofed IP. Each request is treated as originating from a new IP address, completely resetting the lockout counter.

### B. Observed Security Impact
1. Full bypass of login credential brute-force protection.
2. Inaccurate client IP logging across audit and activity trails.
3. Potential cache poisoning or redirection manipulation if untrusted `X-Forwarded-Host` or `X-Forwarded-Proto` are processed.

### C. Source Trace and Evidence
- **Entrypoint**: `bootstrap/app.php:19`
  ```php
  $middleware->trustProxies(at: '*');
  ```
- **Propagation**: `app/Http/Requests/Auth/LoginRequest.php:113`
  ```php
  return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
  ```
- **Sink**: `app/Http/Requests/Auth/LoginRequest.php:92`
  ```php
  if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
  ```

### D. Target-Neutral Local Reproduction
1. Issue 5 failed login attempts for `admin@cuci.in` with `X-Forwarded-For: 10.0.0.1`.
2. The 6th attempt with `X-Forwarded-For: 10.0.0.1` is rejected with `auth.throttle` lockout message.
3. Issue a 7th attempt for `admin@cuci.in` with `X-Forwarded-For: 10.0.0.2`.
4. Observe that the request is NOT locked out; the credentials are authenticated against database/bcrypt, bypassing brute-force lockout.

### E. Remediation
Replace wildcard `*` with the exact CIDR blocks of Cloudflare (or the known internal load balancer IP):

```diff
--- a/bootstrap/app.php
+++ b/bootstrap/app.php
@@ -16,7 +16,11 @@ return Application::configure(basePath: dirname(__DIR__))
         health: '/up',
     )
     ->withMiddleware(function (Middleware $middleware): void {
-        $middleware->trustProxies(at: '*');
+        $middleware->trustProxies(at: [
+            '173.245.48.0/20',
+            '103.21.244.0/22',
+            '103.22.200.0/22',
+            '103.31.4.0/22',
+            '141.101.64.0/18',
+            '108.162.192.0/18',
+            '190.93.240.0/20',
+            '188.114.96.0/20',
+            '197.234.240.0/22',
+            '198.41.128.0/17',
+            '162.158.0.0/15',
+            '104.16.0.0/13',
+            '104.24.0.0/14',
+            '172.64.0.0/13',
+            '131.0.72.0/22',
+        ]);
```

