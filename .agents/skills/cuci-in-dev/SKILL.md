---
name: cuci-in-dev
description: Panduan dan SOP alur kerja pengembangan fitur, pengujian otomatis, dan manajemen komit Git untuk tim pengembang CUCI.IN (Milestone 2) pada Antigravity IDE. Gunakan saat anggota tim (Daysyani, Ayin, Nora, Maulana, Dawamul) mulai mengoding, membuat fitur, menulis test, atau mempersiapkan commit.
---

# Skill: CUCI.IN Milestone 2 Development Workflow

Skill ini memandu asisten AI di Antigravity IDE (AGY) untuk mendampingi anggota tim CUCI.IN mengembangkan fitur Milestone 2 secara presisi, terstruktur, dan sesuai standar rekayasa perangkat lunak.

---

## 1. Langkah Pertama: Identifikasi Anggota Tim & Tugas P10

Saat sesi dimulai atau ketika pengembang menyebutkan nama maupun tugasnya, kenali dan langsung arahkan ke konfigurasi git author serta cabang fitur yang tepat:
- **Daysyani Sophi Masayu** (`daysyani` | `syaniday@gmail.com`)
  - **Tugas Utama:** Modul Tiket Pengaduan Pelanggan (*Complaint Handling*) & Dashboard Customer.
  - **Tugas P10 PWL:** Laravel Policies & Sentralisasi Otorisasi Anti-IDOR (`ComplaintPolicy` & `TransactionPolicy`).
  - **Cabang Git:** `feature/complaint-handling` dan `feature/policy-authorization`
- **Gargarina Nanda Iswati / Ayin** (`ayinanda` | `ayiniswati22@gmail.com`)
  - **Tugas Utama:** Katalog Layanan Lanjutan & Mesin Ekspor Laporan (PDF/Excel).
  - **Tugas P10 PWL:** Eloquent API Resource (`TransactionResource`) & Kontrak OpenAPI Spec (`docs/openapi.yaml`).
  - **Cabang Git:** `feature/advanced-service-catalog`, `feature/export-reports-pdf-excel`, dan `feature/transaction-api-resource`
- **Nayla Annora Nobel Widyonarko / Nora** (`nayla annora nobel widyonarko` / `annora1304` | `naylaannora@gmail.com`)
  - **Tugas Utama:** Komponen Upload Bukti Bayar Digital & Panel Verifikasi Kasir.
  - **Tugas P10 PWL:** Pemasangan Laravel Sanctum (`install:api`) & API Endpoint Pelacakan Nota Publik (`/api/v1/track/{trx}`).
  - **Cabang Git:** `feature/payment-proof-upload`, `feature/cashier-payment-verification`, dan `feature/sanctum-tracking-api`
- **Maulana Ahmad Bukhori** (`Maulanahb` | `maulanahmad2287@gmail.com`)
  - **Tugas Utama:** Project Leader, Manajemen Hak Akses/Role, Optimasi POS & Koordinasi.
  - **Tugas P10 PWL:** Idempotency Key & Anti Double-Submit pada Transaksi POS (`idempotency_keys` & `EnsureIdempotency`).
  - **Cabang Git:** `feature/user-role-management`, `feature/pos-idempotency-guard`, `develop`, `main`
- **Muh. Zaky Dawamul Busro** (`Zakydawamul` | `zakydb091005@gmail.com`)
  - **Tugas Utama:** Mesin Kueri Pelaporan, Optimasi Basis Data & Resolusi Bug Log Backend.
  - **Tugas P10 PWL:** Rate Limiting Login (`throttle:5,1`) & Konfigurasi Throttle Global (`RateLimiter::for('api')`).
  - **Cabang Git:** `feature/reporting-aggregation-engine`, `feature/auth-rate-limiting`, `fix/backend-bug-log-resolution`

*Aturan Deteksi Otomatis:* Jika pengembang menyebutkan nama (misal: "saya Nora", "saya Zaky") atau menyebutkan kata kunci tugasnya (misal: "mau buat policy", "mau pasang rate limit", "mau setup idempotency", "mau bikin tracking api", "mau bikin resource"), asisten IDE AGY harus langsung menyapa dengan nama mereka, mengingatkan pengaturan git author, dan memandu pengerjaan pada cabang fitur terkait.

### 1A. Sprint Remediasi Audit Keamanan (Cabang `fix/...`)
Catatan: Mitigasi `Lead 1–5` (`b719351`, `dcd6f20`, `d2c829c`, `c3285ed`, plus hardening EC2 & `SERVER_SSH_FINGERPRINT`) sudah selesai. Jika anggota tim menyebut ingin mengerjakan bug keamanan dari hasil audit, arahkan langsung ke cabang & fokus berikut (rincian lengkap di `docs/rules_milestone_2.md` Seksi 6):
- **Ayin (`fix/api-resource-and-export-sanitization`):** Masking `customer.name` & hapus/masking penuh `customer.phone` di `TransactionResource.php` + `openapi.yaml` + `TransactionResourceTest.php` (`HIGH #1`); sanitasi formula CSV/Excel di `ReportController::exportCsv` & `excel.blade.php` (`LOW #5`).
- **Nora (`fix/tracking-lookup-security`):** Throttle `/lookup` & `/lookup/payment-proof` di `routes/web.php` + penyesuaian `TrackingApiTest.php` (`HIGH #1`); pasang SRI (`integrity` + `crossorigin`) atau bundle Vite untuk `html5-qrcode@2.3.8` di `lookup/_content.blade.php`.
- **Daysyani (`fix/complaint-photo-privacy`):** Pindahkan foto komplain dari disk `'public'` ke `'local'` (`ComplaintController`, `CompressComplaintPhoto`), buat endpoint ber-`Gate::authorize('view', $complaint)`, perbarui `Complaint::getPhotoUrlAttribute()` & `CustomerComplaintTest.php` (`MEDIUM #3`); tambah `'bail'` + filter `customer_id` di `StoreComplaintRequest`.
- **Zaky (`fix/proxy-trust-and-auth-hardening`):** Ganti wildcard `trustProxies(at: '*')` di `bootstrap/app.php` (whitelist proxy + kecualikan `HEADER_X_FORWARDED_HOST`) + `URL::forceRootUrl`/`forceScheme` di `AppServiceProvider` (`HIGH #2`); tambah regex telepon di `RegisterRequest`/`CustomerRequest`, hapus `sessions` di `ResetPasswordController`, & pasang `throttle:6,1` pada auth tamu di `routes/web.php`.
- **Maulana (`fix/idempotency-scoping-and-role-reports`):** Migrasi composite unique `idempotency_keys` (`user_id`, `route`, `key`) + scoping & `$request->path()` di `EnsureIdempotency` (`LOW #4`); pindahkan `/admin/reports*` ke `role:admin` + update `docs/prd.md` & `ReportExportTest` (`LOW #6`); ganti `.innerHTML` -> `.textContent` di `create.blade.php`. *(Cek EC2 & `SERVER_SSH_FINGERPRINT` Lead 1–5 sudah beres).*

### 1B. Sprint Stabilisasi & Remediasi Bug Minggu 13 (docs/qa_bug_tracker_minggu_13.md)
Saat memasuki fase QA Minggu 13, jika anggota tim menyebut nama mereka atau tugas defect baru dari penemuan Esha/Ayin, asisten AI di AGY wajib langsung memandu ke cabang dan file berikut:
- **Nora (`fix/tracking-lookup-security` & `fix/password-reveal-edge` | `git config user.name "annora1304"` & `naylaannora@gmail.com`):**
  - **Tugas 1:** BUG-09 — Sinkronisasi parameter kode nota landing page (`name="code"`) ke `TransactionLookupController.php` agar pencarian langsung tampil otomatis di `/lookup` tanpa ketik ulang. Berkas: `resources/views/welcome.blade.php`, `app/Http/Controllers/TransactionLookupController.php`.
  - **Tugas 2:** BUG-10 — Duplikasi ikon mata show/hide password pada input kata sandi di Microsoft Edge/Chromium. Berkas: `resources/css/app.css`.
  - **Tugas 3:** BUG-11 — Alur verifikasi pembayaran kasir POS agar bukti transfer/QRIS dapat masuk antrean 'Menunggu Verifikasi' dan memunculkan tombol 'Setujui / Approve' di `/admin/payments`. Berkas: `app/Services/RecordPayment.php`, `resources/views/staff/transactions/create.blade.php`, `resources/views/staff/payments/index.blade.php`.
  - **Tugas 4:** BUG-12 — Sinkronisasi tarif kalkulator landing page `#estimasi` dari database `Service::where('is_active', true)->get()` pada `routes/web.php` dan `welcome.blade.php`.
- **Dawamul / Zaky (`fix/workflow-status-and-minimum-order` | `git config user.name "Zakydawamul"` & `zakydb091005@gmail.com`):**
  - **Tugas 1:** BUG-05 — Perbaiki urutan tombol aksi header `show.blade.php` agar status pengerjaan cucian (`received` -> `processing` -> `completed`) diproses tuntas sebelum tombol `picked_up` ('Tandai Diambil') muncul pada transaksi lunas.
  - **Tugas 2:** BUG-08 — Terapkan perhitungan ambang batas `minimum_order` (`max($weight, $minimumOrder)`) pada kalkulasi subtotal POS `create.blade.php` & service `TransactionCalculator.php`.
  - **Berkas:** `resources/views/staff/transactions/show.blade.php`, `app/Http/Controllers/Staff/TransactionController.php`, `app/Services/TransactionCalculator.php`, `resources/views/staff/transactions/create.blade.php`.
- **Ayin (`fix/service-modal-step-validation` | `git config user.name "ayinanda"` & `ayiniswati22@gmail.com`):**
  - **Tugas:** BUG-07 — Perbaiki step mismatch HTML5 modal ubah layanan di `/admin/services` agar angka nominal bulat seperti Rp 10.000 (`min="0" step="1"`) dan order minimum bulat 26 (`min="0.1" step="any"`) tidak diblokir validasi native browser.
  - **Berkas:** `resources/views/services/index.blade.php`, `app/Http/Requests/ServiceRequest.php`.
- **Esha (`fix/customer-phone-validation` | `fix/mobile-hamburger-navigation`):**
  - **Tugas 1:** BUG-04 — Hamburger menu mobile view landing page (Status: *Fixed* di `welcome.blade.php`).
  - **Tugas 2:** BUG-06 — Validasi panjang (min 10-13 digit) & regex nomor HP seluler Indonesia pada pendaftaran akun customer (cegah `0681` lolos). Berkas: `app/Http/Requests/Auth/RegisterRequest.php`, `app/Http/Requests/CustomerRequest.php`, `resources/views/auth/register.blade.php`.
  - **Tugas 3:** Pengujian responsif tampilan mobile peramban seluler dan pelaporan defect baru di `docs/qa_bug_tracker_minggu_13.md`.
- **Daysyani (`daysyani` | `syaniday@gmail.com`):**
  - **Tugas:** Lead QA Regression Testing, validasi pengujian alur tiket komplain anti-IDOR, dan verifikasi status kelulusan bug fix yang diajukan anggota tim di `docs/qa_bug_tracker_minggu_13.md` & Notion QA.
- **Maulana (`Maulanahb` | `maulanahmad2287@gmail.com`):**
  - **Tugas:** BUG-02 — Rate limiting endpoint `/lookup` & upload bukti bayar (*Status: Fixed* via PR #27 di `fix/tracking-lookup-security`), Project Manager, Code Review Pull Request, fasilitasi merge cabang perbaikan ke `develop`, pemantauan status deployment cloud AWS EC2, dan koordinasi dokumen QA Minggu 13.

---

## 2. Alur Kerja Standar Pengembangan Fitur (Feature Lifecycle)

Setiap pengembangan fitur harus melalui 5 tahap berurutan:

### Tahap 1: Setup Branch & Identitas Git
Pastikan cabang Git terpisah dan identitas author sesuai:
```bash
# Contoh untuk Daysyani
git checkout develop
git pull origin develop
git checkout -b feature/complaint-handling

git config user.name "daysyani"
git config user.email "syaniday@gmail.com"
```

### Tahap 2: Implementasi Backend & UI
1. **Migration & Model:** Gunakan `php artisan make:migration` dan `php artisan make:model`. Pastikan tipe data dan relasi Eloquent terdefinisi jelas.
2. **Form Request & Controller:** Selalu validasi input menggunakan Form Request terpisah di `app/Http/Requests/`.
3. **Tampilan (Blade + Tailwind + Alpine.js):**
   - Wajib arsitektur **Laravel MPA** (Multi-Page Application).
   - Gunakan layout yang sudah ada (`layouts/app.blade.php`, `layouts/staff.blade.php`).
   - Gunakan komponen Blade dari `resources/views/components/` (misal `<x-badge>`, `<x-button>`).
   - Gunakan Alpine.js untuk interaktivitas ringan (modal, dropdown, tab, pratinjau upload file).

### Tahap 3: Penulisan Pengujian Otomatis (PHPUnit)
Setiap fitur baru WAJIB memiliki pengujian fitur di `tests/Feature/`:
```bash
php artisan make:test --phpunit NamaFiturTest
```
Uji skenario:
- Pengguna yang belum login diarahkan ke login (302 redirect).
- Pengguna dengan role yang salah dilarang akses (403 forbidden).
- Pengguna yang sah berhasil memproses form dan data tersimpan di database.
- Validasi input (misal: format file salah atau kolom wajib kosong).

Jalankan test:
```bash
php artisan test --filter=NamaFiturTest
```

### Tahap 4: Format Kode Otomatis (Laravel Pint)
Sebelum melakukan komit, jalankan linter agar format kode rapi:
```bash
vendor/bin/pint --dirty --format agent
# Jika di Windows (PowerShell / cmd.exe), gunakan:
php vendor/bin/pint --dirty --format agent
```

### Tahap 5: Komit Terstruktur (Conventional Commits)
Pandu pengembang untuk melakukan komit secara modular dan sering (*frequent commits*):
- `feat(complaint): add complaint submission form with transaction code lookup`
- `feat(complaint): handle image upload for damaged clothing proof`
- `feat(complaint): implement complaint status tracking badge`
- `test(complaint): add feature tests for complaint validation and storage`
- `refactor(complaint): clean up complaint controller using form request`

---

## 3. Checklist Sebelum Push / Pull Request

Sebelum cabang fitur digabung ke `develop`:
- [ ] Seluruh test lulus (`php artisan test`).
- [ ] Kode terformat rapi (`vendor/bin/pint --format agent`).
- [ ] Tidak ada berkas sampah atau kredensial rahasia (.env) yang terikut ke Git.
- [ ] Riwayat komit memiliki author yang sesuai dengan anggota tim.
- [ ] Buat Pull Request ke cabang `develop` untuk di-review oleh Maulana / Dawamul.

---

## 4. Standar Arsitektur Pengolahan Data, Ekspor, & Queue (Materi P9)

Bila mendampingi pengembang pada modul pelaporan, ekspor dokumen, upload media, atau pemrosesan antrean, pastikan menerapkan aturan berikut:

1. **Filter Tanggal & Agregasi Laporan (Zaky & Maulana):**
   - Wajib gunakan `$request->date('to')->endOfDay()` agar transaksi di akhir hari tidak terpotong.
   - Hitung nilai rekap/agregasi di database (`selectRaw('SUM(...)')` / `count()`), bukan di memori PHP.
   - Pastikan tabel `transactions` dan `payments` memiliki indeks pada kolom filter tanggal dan status.

2. **Ekspor Laporan (Ayin):**
   - **PDF:** Gunakan `laravel-dompdf` hanya untuk nota cetak atau dokumen ringkas (1–2 halaman).
   - **Excel:** Gunakan `FromQuery` pada Laravel Excel (`maatwebsite/excel`).
   - **CSV (Dataset Besar):** Wajib gunakan pola aliran (*streaming*) `response()->streamDownload()` + `lazyById()` untuk menjaga memori RAM server tetap stabil.

3. **Background Job / Queue (Maulana, Nora, Daysyani):**
   - Tugas berat (kompresi foto bukti transfer, kompresi foto komplain noda, notifikasi status cucian) wajib dikirim ke Queue Job (`make:job`).
   - Terapkan properti: `public int $tries = 3;`, `public array $backoff = [10, 60];`, `public int $timeout = 60;`.
   - Hanya bawa ID model atau path file pada constructor job.
   - Gunakan `->afterCommit()` saat men-dispatch job di dalam transaksi basis data.
   - Panduan alokasi modul tim dan standar arsitektur tersedia di [rules_milestone_2.md](file:///c:/laragon/www/cuci-in/docs/rules_milestone_2.md) dan [prd.md](file:///c:/laragon/www/cuci-in/docs/prd.md) (Seksi 24).

---

## 5. Deployment ke Server Production AWS EC2 (sistem.md)

Dokumentasi lengkap topologi cloud dan server tercatat pada [docs/sistem.md](file:///c:/laragon/www/cuci-in/docs/sistem.md):

- **Domain Utama:** `https://portal-cuciin.web.id` (Cloudflare SSL Full / Full (strict) + Origin CA Nginx port 443 $\rightarrow$ AWS EC2 `3.25.167.8`).
- **Path Proyek Server:** `/var/www/cuci-in` (branch `develop`).
- **Basis Data Server:** PostgreSQL 16+ (`cuci_in`), user `postgres`.
- **Perintah Sinkronisasi Kode ke Server (One-Liner):**
  ```bash
  cd /var/www/cuci-in && \
  git pull origin develop && \
  composer install --no-dev --optimize-autoloader && \
  php artisan migrate --force && \
  npm install && \
  npm run build && \
  php artisan optimize && \
  sudo chown -R www-data:www-data storage bootstrap/cache
  ```
- **Eksekusi Queue Worker & Scheduler Harian (`model:prune`):**
  ```bash
  nohup php /var/www/cuci-in/artisan queue:work --tries=3 --timeout=60 > /dev/null 2>&1 &
  # Cron scheduler (routes/console.php menjalankan model:prune harian):
  # * * * * * cd /var/www/cuci-in && php artisan schedule:run >> /dev/null 2>&1
  ```

