<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.4. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>

=== cuci-in team & milestone 2 guidelines ===

# CUCI.IN — Alur Kerja Tim & Panduan Pengembang IDE AGY (Milestone 2)

Sistem ini dikembangkan oleh tim mahasiswa D4 Sistem Informasi Bisnis (SIB) menggunakan Full Laravel Multi-Page Application (MPA), Tailwind CSS, dan Alpine.js. 

Panduan ini wajib dipatuhi oleh seluruh asisten AI di Antigravity IDE (AGY) saat mendampingi anggota tim dalam pengerjaan Milestone 2 (Minggu 9–12).

---

## 1. Identifikasi Anggota Tim & Pembagian Modul Fitur

Asisten AI di IDE AGY HARUS mengenali tugas masing-masing anggota dan langsung mengarahkan bantuan teknis ke modul yang telah dialokasikan:

### A. Daysyani Sophi Masayu (`daysyani` | `syaniday@gmail.com`)
- **Fokus Modul:** Modul Tiket Pengaduan Pelanggan (*Complaint Handling*), Dashboard Customer, dan Skenario Uji Fungsional Komplain.
- **Tugas P10 PWL:** **Laravel Policies & Sentralisasi Otorisasi Anti-IDOR** (`ComplaintPolicy` & `TransactionPolicy`).
- **Cabang Git Utama:** `feature/complaint-handling` dan `feature/policy-authorization`
- **Spesifikasi Tugas:**
  - Formulir pengajuan pengaduan pelanggan terhubung nomor nota transaksi (`TRX-XXXX`).
  - Fitur unggah foto kendala/noda pakaian dan pelacakan status tiket (*Menunggu* $\rightarrow$ *Diproses* $\rightarrow$ *Selesai*).
  - Tampilan pemantauan status cucian mandiri dan riwayat komplain pada Dashboard Customer.
  - Implementasi `app/Policies/ComplaintPolicy.php` dan `TransactionPolicy.php` menggantikan `abort(403)` manual via `Gate::authorize()`.
  - Penulisan skenario dan eksekusi pengujian di `tests/Feature/CustomerComplaintTest.php` (termasuk uji 403 Forbidden).

### B. Gargarina Nanda Iswati / Ayin (`ayinanda` | `ayiniswati22@gmail.com`)
- **Fokus Modul:** Pengelolaan Katalog Layanan Lanjutan, Mesin Ekspor Laporan (PDF & Excel), dan Diagram Rekayasa Sistem.
- **Tugas P10 PWL:** **Eloquent API Resource (`TransactionResource`) & Kontrak OpenAPI Spec**.
- **Cabang Git Utama:** `feature/advanced-service-catalog`, `feature/export-reports-pdf-excel`, dan `feature/transaction-api-resource`
- **Spesifikasi Tugas:**
  - Pengaturan tarif layanan dinamis, estimasi durasi selesai dinamis, dan integrasi paket kilat ke katalog kasir.
  - Implementasi mesin unduh/ekspor laporan operasional laundry ke dokumen siap cetak (PDF & Excel).
  - Implementasi `app/Http/Resources/TransactionResource.php` dan `TransactionDetailResource.php` dengan proteksi N+1 via `whenLoaded()`.
  - Penyusunan berkas spesifikasi OpenAPI di `docs/openapi.yaml` serta pemodelan visual *Activity Diagram* (AD) dan *Sequence Diagram* (SD).

### C. Nayla Annora Nobel Widyonarko / Nora (`nayla annora nobel widyonarko` / `annora1304` | `naylaannora@gmail.com`)
- **Fokus Modul:** Modul Upload Bukti Pembayaran Digital, Panel Verifikasi Kasir, dan Dataset Seeder Pengujian.
- **Tugas P10 PWL:** **Pemasangan Laravel Sanctum & API Endpoint Pelacakan Nota Publik**.
- **Cabang Git Utama:** `feature/payment-proof-upload`, `feature/cashier-payment-verification`, dan `feature/sanctum-tracking-api`
- **Spesifikasi Tugas:**
  - Komponen upload bukti transfer bank dan struk QRIS oleh pelanggan beserta validasi berkas gambar (format MIME & batas ukuran file).
  - Panel verifikasi bukti bayar bagi kasir/admin untuk menyetujui (*approve*) atau menolak (*reject*) pembayaran digital.
  - Inisialisasi API (`php artisan install:api`), pasang trait `HasApiTokens` di `User.php`, buat endpoint `GET /api/v1/track/{transaction_code}`.
  - Penyiapan dataset seeder transaksi dan pengujian modul pembayaran & tracking API di `tests/Feature/Api/TrackingApiTest.php`.

### D. Maulana Ahmad Bukhori (`Maulanahb` | `maulanahmad2287@gmail.com`)
- **Fokus:** Project Leader, Manajemen Peran Pengguna (*Role Authorization* Admin/Kasir/Owner), Optimasi Logika Kasir POS & Thermal Receipt, dan Koordinasi Tim.
- **Tugas P10 PWL:** **Idempotency Key & Anti Double-Submit pada Transaksi POS & Pembayaran**.
- **Cabang Git:** `feature/user-role-management`, `feature/pos-idempotency-guard`, `develop`, `main`
- **Spesifikasi Tugas:**
  - Migration tabel `idempotency_keys` dan middleware `EnsureIdempotency` untuk cegah transaksi kasir dobel akibat timeout/retry.
  - Header `Idempotency-Key` (UUID) dan state `:disabled="loading"` pada tombol POS di `create.blade.php`.
  - Penulisan pengujian di `tests/Feature/IdempotencyTransactionTest.php`.

### E. Muh. Zaky Dawamul Busro (`Zakydawamul` | `zakydb091005@gmail.com`)
- **Fokus:** Backend Architecture, Mesin Kueri Agregasi Pelaporan Keuangan/Operasional, Optimasi Indeks Basis Data, dan Resolusi Defek Backend (*Bug Log*).
- **Tugas P10 PWL:** **Rate Limiting Login (`throttle:5,1`) & Konfigurasi Throttle Global**.
- **Cabang Git:** `feature/reporting-aggregation-engine`, `feature/auth-rate-limiting`, `fix/backend-bug-log-resolution`
- **Spesifikasi Tugas:**
  - Pemasangan `throttle:5,1` pada rute `POST /login` di `routes/web.php` untuk mencegah brute force.
  - Konfigurasi named rate limiter di `bootstrap/app.php` (`RateLimiter::for('api', ...)`).
  - Penulisan pengujian di `tests/Feature/AuthRateLimitTest.php` (memastikan status 429 dan header `Retry-After`).

---

## 2. Standar Koding & Prosedur Pengerjaan (SOP Antigravity Agent)

Setiap agen AI di IDE AGY wajib memandu pengerjaan dengan alur terstandar berikut:

1. **Cabang Git Terpisah (*Feature Branching*):**
   - Sebelum mulai mengoding, buat cabang fitur baru dari `develop`: `git checkout -b feature/<nama-fitur>`.
   - Hindari komit langsung ke cabang `main` atau `develop`.

2. **Arsitektur & Konvensi Tampilan:**
   - Wajib menggunakan arsitektur **Laravel Multi-Page Application (Blade)**, **Tailwind CSS**, dan **Alpine.js**.
   - DILARANG menggunakan Vue.js, React, atau framework SPA tanpa persetujuan tim.
   - Gunakan layout resmi yang sudah ada (`resources/views/layouts/app.blade.php`, `resources/views/layouts/staff.blade.php`, dll).
   - Gunakan komponen Blade yang sudah ada di `resources/views/components/` untuk konsistensi UI.

3. **Komit Git Bertahap & Bermakna (*Commit Often*):**
   - Pandu pengembang untuk membuat komit teratur dan modular (setiap selesai satu sub-fitur atau komponen) agar riwayat kontribusi tercatat rapi di Git.
   - Format pesan komit: `feat(...)`, `fix(...)`, `test(...)`, `refactor(...)`, `docs(...)`.
   - Pastikan konfigurasi git author sesuai dengan nama dan email anggota yang bertugas.

4. **Kualitas & Verifikasi Kode:**
   - Setiap fitur baru harus dilengkapi dengan feature test PHPUnit di `tests/Feature/`.
   - Pastikan seluruh test lulus dengan menjalankan: `php artisan test`.
   - Jalankan pemformat kode otomatis sebelum selesai: `vendor/bin/pint --format agent`.

---

## 3. Standar Arsitektur Pengolahan Data, Ekspor/Impor & Queue (Materi P9)

Setiap agen AI di AGY wajib menerapkan standar berikut saat membantu modul terkait:
1. **Laporan & Kueri (Zaky & Maulana):** Wajib cegah jebakan tanggal terpotong dengan `$request->date('to')->endOfDay()`, hitung agregasi di database (`selectRaw('SUM(...)')`), dan gunakan indeks komposit tabel `transactions` & `payments`.
2. **Ekspor Berkas (Ayin):** Batasi DomPDF hanya untuk cetak nota/dokumen ringkas (1–2 halaman). Untuk dataset transaksi besar, wajib gunakan pola aliran (*streaming*) `response()->streamDownload()` + `lazyById()` agar RAM server tetap stabil.
3. **Queue Job (Maulana, Nora, Daysyani):** Tugas lambat/berat (kompresi foto bukti transfer, kompresi foto komplain noda, notifikasi status cucian) wajib menggunakan Job (`make:job`) dengan `$tries = 3`, `$backoff = [10, 60]`, `$timeout = 60`, hanya membawa ID model/path, dan dispatch dengan `->afterCommit()`.
4. Rujukan detail aturan pengerjaan tim dan arsitektur ada pada [rules_milestone_2.md](file:///c:/laragon/www/cuci-in/docs/rules_milestone_2.md) dan [prd.md](file:///c:/laragon/www/cuci-in/docs/prd.md) (Seksi 24).

---

## 4. Standar Arsitektur REST API, Keamanan & Deteksi Otomatis Tugas P10

Asisten AI di IDE AGY **wajib mendeteksi secara otomatis** anggota tim yang sedang aktif ketika mereka menyebutkan nama atau fitur yang dikerjakan, lalu langsung memandu ke cabang Git dan file target yang relevan:

* **Deteksi Maulana (`Maulanahb`) / Idempotency POS:**
  * Cabang: `feature/pos-idempotency-guard`
  * Fokus: Migration `idempotency_keys`, middleware `EnsureIdempotency`, Alpine.js UUID header, disable checkout submit.
* **Deteksi Zaky (`Zakydawamul`) / Rate Limiting Login:**
  * Cabang: `feature/auth-rate-limiting`
  * Fokus: `throttle:5,1` di `routes/web.php`, `RateLimiter::for('api')`, status code 429, `Retry-After`.
* **Deteksi Daysyani (`daysyani`) / Laravel Policies:**
  * Cabang: `feature/policy-authorization`
  * Fokus: `ComplaintPolicy`, `TransactionPolicy`, refactor `abort(403)` ke `Gate::authorize()`.
* **Deteksi Nora (`annora1304`) / Sanctum & Tracking API:**
  * Cabang: `feature/sanctum-tracking-api`
  * Fokus: `install:api`, trait `HasApiTokens`, `routes/api.php`, endpoint `GET /api/v1/track/{trx}`.
* **Deteksi Ayin (`ayinanda`) / API Resource & OpenAPI:**
  * Cabang: `feature/transaction-api-resource`
  * Fokus: `TransactionResource`, `whenLoaded()`, format integer uang & ISO 8601, berkas `docs/openapi.yaml`.

---

## 5. Infrastruktur Server Production & Panduan Deployment Cloud (sistem.md)

Aplikasi CUCI.IN telah ter-deploy langsung pada lingkungan cloud publik dan terintegrasi dengan domain resmi. Rujukan lengkap infrastruktur tercatat di [sistem.md](file:///c:/laragon/www/cuci-in/docs/sistem.md):

1. **Domain & Jaringan:**
   - URL Utama: `https://portal-cuciin.web.id`
   - DNS & SSL: Dikelola via Cloudflare (SSL Mode: *Full / Full (strict)* + Origin CA SSL di Nginx EC2 Port 443; HTTP Port 80 redirect `301` ke HTTPS).
   - Server Host: AWS EC2 Instance `t3.micro` (`ap-southeast-2`), IP Publik `3.25.167.8`.
   - Security Group Inbound: Port 22 (SSH) untuk admin; Port 80/443 dibatasi untuk trafik terproksi Cloudflare (akses langsung IP origin diblokir firewall).

2. **Web Stack Server:**
   - Web Server: Nginx 1.28+ (`/etc/nginx/sites-available/cuci-in` -> `/var/www/cuci-in/public`).
   - Runtime: PHP 8.5+ FastCGI (`/run/php/php-fpm.sock`), Composer 2.11+, Node.js 20+ LTS.
   - Database: PostgreSQL 16+ (`cuci_in`), Port 5432, User `postgres`.
   - Swap: 2 GB (`/swapfile`) untuk stabilitas memori RAM 1 GB.

3. **Alur Deployment (Pembaruan Kode ke Server):**
   - Setiap kali cabang fitur selesai diuji dan di-*merge* ke cabang `develop`, pembaruan ke server live dilakukan via SSH/AWS Instance Connect dengan perintah:
     ```bash
     cd /var/www/cuci-in && git pull origin develop && composer install --no-dev --optimize-autoloader && php artisan migrate --force && npm install && npm run build && php artisan optimize && sudo chown -R www-data:www-data storage bootstrap/cache
     ```
   - Worker background job (`cuciin-queue.service`) dan scheduler harian (`model:prune` di `routes/console.php`) berjalan sebagai `www-data` di server:
     ```bash
     sudo systemctl status cuciin-queue --no-pager
     # Cron aktif di /etc/cron.d/cuciin-scheduler:
     # * * * * * www-data cd /var/www/cuci-in && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
     ```

---

## 6. Deteksi Otomatis Sprint Remediasi Audit Keamanan & Bug Log

Sebagai lanjutan evaluasi keamanan aplikasi, asisten AI di IDE AGY juga **wajib mengenali tugas perbaikan bug keamanan** masing-masing anggota tim (detail lokasi baris, target uji, dan status mitigasi `Lead 1–5` yang sudah terintegrasi tercatat di [rules_milestone_2.md](file:///c:/laragon/www/cuci-in/docs/rules_milestone_2.md) Seksi 6):

* **Deteksi Ayin (`ayinanda`) / Bug Keamanan API Resource & Ekspor:**
  * Cabang: `fix/api-resource-and-export-sanitization`
  * Fokus: Masking `customer.name` & hapus/masking penuh `customer.phone` di `app/Http/Resources/TransactionResource.php` (sinkron dengan `docs/openapi.yaml` & `TransactionResourceTest.php`), serta sanitasi spreadsheet formula injection (`=`, `+`, `-`, `@`, `\t`, `\r`) di `ReportController::exportCsv` & `resources/views/admin/reports/excel.blade.php`.
* **Deteksi Nora (`annora1304`) / Bug Keamanan Lookup & SRI QR Scanner:**
  * Cabang: `fix/tracking-lookup-security`
  * Fokus: Tambah `throttle:20,1` pada `GET /lookup` dan `throttle:10,1` pada `POST /lookup/payment-proof` di `routes/web.php`, selaraskan `TrackingApiTest.php`, serta tambahkan SRI (`integrity` + `crossorigin`) atau Vite bundling untuk `html5-qrcode@2.3.8` di `resources/views/lookup/_content.blade.php`.
* **Deteksi Daysyani (`daysyani`) / Bug Keamanan Foto Komplain & Validation Oracle:**
  * Cabang: `fix/complaint-photo-privacy`
  * Fokus: Pindahkan foto komplain dari disk `'public'` ke disk privat `'local'` di `ComplaintController::store` & `CompressComplaintPhoto`, sajikan lewat endpoint controller ber-`Gate::authorize('view', $complaint)`, update `Complaint::getPhotoUrlAttribute()`, serta pasang `'bail'` & filter `customer_id` di query pertama `StoreComplaintRequest`.
* **Deteksi Zaky (`Zakydawamul`) / Bug Keamanan TrustProxies, Auth Throttle, & Sesi Reset:**
  * Cabang: `fix/proxy-trust-and-auth-hardening`
  * Fokus: Ganti `$middleware->trustProxies(at: '*')` di `bootstrap/app.php` agar membatasi proxy tepercaya (`TRUSTED_PROXIES`) & mengecualikan `HEADER_X_FORWARDED_HOST`, paksa `URL::forceRootUrl`/`forceScheme('https')` di `AppServiceProvider`, pasang regex telepon di `RegisterRequest`/`CustomerRequest`, hapus `sessions` di `ResetPasswordController`, dan tambah `throttle:6,1` pada auth routes tamu di `routes/web.php`.
* **Deteksi Maulana (`Maulanahb`) / Bug Idempotency Scoping, RBAC Laporan, & POS:**
  * Cabang: `fix/idempotency-scoping-and-role-reports`
  * Fokus: Migrasi composite `unique(['user_id', 'route', 'key'])` pada `idempotency_keys`, filter lookup & `computePayloadHash()` (`$request->path()`) di `EnsureIdempotency`, pindahkan `/admin/reports*` ke grup `role:admin` (sinkron dengan `docs/prd.md` & `ReportExportTest.php`), serta ganti `.innerHTML` menjadi `.textContent` di `create.blade.php`. *(Cek & hardening EC2 Lead 1–5 sudah selesai; tersisa cek UI opsional Cloudflare `Full (strict)` + HSTS).*

---

## 7. Deteksi Otomatis Sprint Stabilisasi & Remediasi Bug Minggu 13 (docs/qa_bug_tracker_minggu_13.md)

Memasuki **Minggu 13 (Fase QA, Stabilisasi, & Hardening Pasca-Milestone 2)**, asisten AI di IDE AGY **wajib mengenali secara otomatis** anggota tim yang sedang aktif ketika mereka menyebutkan nama, panggilan, atau kata kunci tugasnya, lalu langsung memandu ke cabang Git, konfigurasi `git config user.*`, dan berkas target sesuai dokumen [qa_bug_tracker_minggu_13.md](file:///c:/laragon/www/cuci-in/docs/qa_bug_tracker_minggu_13.md):

* **Deteksi Nora (`annora1304` | `nayla annora nobel widyonarko` | `naylaannora@gmail.com` | "Nora" | "saya nora"):**
  * **Tugas M13:** 
    1. **BUG-09 — Sinkronisasi Parameter Kode Nota Landing Page ke /lookup** (Ubah `name="transaction_code"` jadi `name="code"` di hero form landing page & pasang fallback parameter di controller agar hasil pencarian langsung muncul tanpa ketik ulang). Cabang: `fix/tracking-lookup-security`. Berkas: [resources/views/welcome.blade.php](file:///c:/laragon/www/cuci-in/resources/views/welcome.blade.php), [app/Http/Controllers/TransactionLookupController.php](file:///c:/laragon/www/cuci-in/app/Http/Controllers/TransactionLookupController.php).
    2. **BUG-10 — Duplikasi Ikon Mata Input Password di Edge/Chromium** (Reset CSS `input[type="password"]::-ms-reveal, ::-ms-clear { display: none; }` agar tidak bentrok dengan tombol kustom Alpine.js). Cabang: `fix/password-reveal-edge` atau `fix/tracking-lookup-security`. Berkas: [resources/css/app.css](file:///c:/laragon/www/cuci-in/resources/css/app.css).
    3. **BUG-11 — Alur Persetujuan Verifikasi Pembayaran Kasir POS** (Sediakan opsi verifikasi mutasi non-tunai di POS kasir agar bukti masuk antrean 'Menunggu Verifikasi' dan memunculkan tombol 'Setujui / Approve' di `/admin/payments`). Cabang: `fix/cashier-payment-verification-flow`. Berkas: [app/Services/RecordPayment.php](file:///c:/laragon/www/cuci-in/app/Services/RecordPayment.php), [resources/views/staff/transactions/create.blade.php](file:///c:/laragon/www/cuci-in/resources/views/staff/transactions/create.blade.php), [resources/views/staff/payments/index.blade.php](file:///c:/laragon/www/cuci-in/resources/views/staff/payments/index.blade.php).
    4. **BUG-12 — Tarif Kalkulator Landing Page Hardcoded Statis** (Sinkronkan data paket & tarif kalkulator `#estimasi` dari model `Service::where('is_active', true)->get()` pada `routes/web.php` dan binding dinamis `@json($services)` di `welcome.blade.php`). Cabang: `fix/landing-calculator-pricing-sync`. Berkas: [routes/web.php](file:///c:/laragon/www/cuci-in/routes/web.php), [resources/views/welcome.blade.php](file:///c:/laragon/www/cuci-in/resources/views/welcome.blade.php).
  * **Setup Git:** `git config user.name "annora1304" && git config user.email "naylaannora@gmail.com"`

* **Deteksi Dawamul / Zaky (`Zakydawamul` | `muh. zaky dawamul busro` | `zakydb091005@gmail.com` | "Dawamul" | "Zaky" | "saya dawamul" | "saya zaky"):**
  * **Tugas M13:** 
    1. **BUG-05 — Alur Status Pengerjaan Cucian & Koreksi Tombol Aksi Header** (Prioritaskan status pengerjaan 'Mulai Cuci' & 'Selesai Cuci' sebelum 'Tandai Diambil' pada transaksi lunas).
    2. **BUG-08 — Kalkulasi Ambang Batas Minimum Order POS & Engine** (Terapkan `max($weight, $minimumOrder)` agar pesanan di bawah batas tidak lolos tanpa batas minimum).
  * **Cabang Git:** `fix/workflow-status-and-minimum-order`
  * **Setup Git:** `git config user.name "Zakydawamul" && git config user.email "zakydb091005@gmail.com"`
  * **Berkas Target:** [resources/views/staff/transactions/show.blade.php](file:///c:/laragon/www/cuci-in/resources/views/staff/transactions/show.blade.php), [app/Http/Controllers/Staff/TransactionController.php](file:///c:/laragon/www/cuci-in/app/Http/Controllers/Staff/TransactionController.php), [app/Services/TransactionCalculator.php](file:///c:/laragon/www/cuci-in/app/Services/TransactionCalculator.php), [resources/views/staff/transactions/create.blade.php](file:///c:/laragon/www/cuci-in/resources/views/staff/transactions/create.blade.php), dan `tests/Feature/TransactionCalculatorTest.php`.

* **Deteksi Ayin (`ayinanda` | `gargarina nanda iswati` | `ayiniswati22@gmail.com` | "Ayin" | "saya ayin"):**
  * **Tugas M13:** **BUG-07 — Perbaikan Step Mismatch & Validasi Input Modal Edit Layanan** (Koreksi `min` & `step` HTML5 agar angka nominal bulat seperti Rp 10.000 dan order minimum bulat 26 tidak ditolak browser native validation).
  * **Cabang Git:** `fix/service-modal-step-validation`
  * **Setup Git:** `git config user.name "ayinanda" && git config user.email "ayiniswati22@gmail.com"`
  * **Berkas Target:** [resources/views/services/index.blade.php](file:///c:/laragon/www/cuci-in/resources/views/services/index.blade.php), [app/Http/Requests/ServiceRequest.php](file:///c:/laragon/www/cuci-in/app/Http/Requests/ServiceRequest.php).

* **Deteksi Esha (`Esha` | `Esa` | "Esha" | "Esa" | "saya esha"):**
  * **Tugas M13:** 
    1. **BUG-04 — Hamburger Menu Mobile View Landing Page** (*Status: Telah Selesai / Fixed*).
    2. **BUG-06 — Validasi Panjang & Format Nomor Telepon Pelanggan** (Nomor seluler min 10–13 digit regex Indonesia, cegah nomor pendek 4 digit seperti `0681`). Cabang: `fix/customer-phone-validation`. Berkas: [app/Http/Requests/Auth/RegisterRequest.php](file:///c:/laragon/www/cuci-in/app/Http/Requests/Auth/RegisterRequest.php), [app/Http/Requests/CustomerRequest.php](file:///c:/laragon/www/cuci-in/app/Http/Requests/CustomerRequest.php), [resources/views/auth/register.blade.php](file:///c:/laragon/www/cuci-in/resources/views/auth/register.blade.php), dan `tests/Feature/Auth/RegistrationTest.php`.
    3. **Pengujian Responsif Mobile:** Pengujian tampilan mobile peramban seluler (viewport 360px–430px) dan pelaporan temuan defect baru ke [docs/qa_bug_tracker_minggu_13.md](file:///c:/laragon/www/cuci-in/docs/qa_bug_tracker_minggu_13.md).

* **Deteksi Daysyani (`daysyani` | `daysyani sophi masayu` | `syaniday@gmail.com` | "Daysyani" | "saya daysyani"):**
  * **Tugas M13:** Lead QA Regression Testing, validasi pengujian alur tiket komplain anti-IDOR, dan verifikasi status kelulusan bug fix yang diajukan anggota tim di [docs/qa_bug_tracker_minggu_13.md](file:///c:/laragon/www/cuci-in/docs/qa_bug_tracker_minggu_13.md) & Notion QA.
  * **Setup Git:** `git config user.name "daysyani" && git config user.email "syaniday@gmail.com"`

* **Deteksi Maulana (`Maulanahb` | `maulana ahmad bukhori` | `maulanahmad2287@gmail.com` | "Maulana" | "saya maulana"):**
  * **Tugas M13:** 
    1. **BUG-02 — Rate Limiting Endpoint /lookup & Upload Bukti Bayar** (*Status: Telah Selesai / Fixed* via PR #27 di `fix/tracking-lookup-security`).
    2. Project Manager, Code Review Pull Request, fasilitasi merge cabang perbaikan ke `develop`, pemantauan status deployment cloud AWS EC2, dan koordinasi dokumen QA Minggu 13.
  * **Setup Git:** `git config user.name "Maulanahb" && git config user.email "maulanahmad2287@gmail.com"`

---

## 8. Perkakas Headless Scraping & Web Automation (Lightpanda)

Lightpanda Browser (`~/.local/bin/lightpanda` di WSL2 Ubuntu) terdaftar sebagai MCP server `lightpanda` untuk agen AI:
- Gunakan saat membaca halaman dokumentasi JS-heavy, scraping konten web modern, atau ekstraksi markdown tanpa beban Chromium berat (~30–50 MB RAM vs 1–2 GB).
- Perintah CLI cepat: `wsl -d Ubuntu -- bash -lc 'lightpanda fetch "<URL>" --dump markdown'`.
- Jangan gunakan untuk screenshot visual pixel-perfect (tetap gunakan Playwright/Chromium).






