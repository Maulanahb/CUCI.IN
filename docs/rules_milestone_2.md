# CUCI.IN — RULES & PANDUAN PENGEMBANGAN SISTEM MILESTONE 2 (MINGGU 9–12)

Dokumen ini memaparkan aturan kerja, pembagian tugas teknis, cabang Git fitur, roadmap mingguan, dan standar arsitektur sistem manajemen operasional laundry CUCI.IN untuk periode Minggu ke-9 hingga Minggu ke-12 menuju pencapaian **Milestone 2 (Functional Product)**.

Rencana kerja Milestone 2 disusun dengan prinsip kolaborasi proporsional dan pembagian beban kerja yang seimbang di antara seluruh anggota tim. **Maulana Ahmad Bukhori** dan **Muh. Zaky Dawamul Busro** memegang peranan krusial pada penguatan arsitektur backend, manajemen peran pengguna (*role authorization*), optimasi kalkulasi transaksi, mesin rekapitulasi data, serta penyelesaian kendala teknis backend. Di sisi lain, tanggung jawab teknis pada fitur-fitur fungsional—seperti modul tiket pengaduan pelanggan, pengelolaan katalog layanan lanjutan, komponen bukti pembayaran digital dan verifikasi kasir, pemodelan diagram rekayasa (*Activity & Sequence Diagram*), serta dokumentasi pengujian—didistribusikan secara terstruktur kepada **Daysyani Sophi Masayu**, **Gargarina Nanda Iswati (Ayin)**, dan **Nayla Annora Nobel Widyonarko (Nora)** agar beban teknis terbagi merata dan seluruh anggota memperoleh porsi kontribusi serta aktivitas komit repositori Git yang proporsional.

---

## 1. Roadmap Target Mingguan (Minggu 9–12)

Target pengembangan dirinci per minggu dengan pembagian tugas yang jelas guna memastikan alur integrasi berjalan berkesinambungan tanpa penumpukan pekerjaan di akhir periode:

### Tabel 1. Rencana Pengembangan Produk Mingguan Menuju Milestone 2 (Minggu 9–12)

| Minggu | Target Pengembangan Produk | Penanggung Jawab Teknis (PIC) |
|:---:|---|---|
| **9** | • **Modul Pengaduan (Complaint Handling):** Pembuatan formulir pengajuan komplain pelanggan terhubung nomor nota (`TRX-XXXX`), fitur unggah bukti foto noda pakaian, penyiapan job antrean kompresi berkas, dan pelacakan status tiket.<br>• **Modul Bukti Pembayaran Digital:** Komponen unggah bukti transfer/struk QRIS di sisi pelanggan beserta validasi keamanan berkas (*client & server*).<br>• **Pengelolaan Katalog Layanan Lanjutan:** Pengaturan tarif dinamis, estimasi durasi pengerjaan fleksibel, dan integrasi paket kilat kasir.<br>• **Manajemen Pengguna & Hak Akses:** Pengaturan otorisasi multi-peran (*Admin, Kasir, Owner*) dan pengamanan rute *middleware*. | • **Daysyani Sophi Masayu** *(Modul Komplain)*<br>• **Nayla Annora Nobel W.** *(Upload Bukti Bayar)*<br>• **Gargarina Nanda Iswati** *(Katalog Layanan Lanjutan)*<br>• **Maulana Ahmad Bukhori** *(Manajemen Peran & Autentikasi)* |
| **10** | • **Panel Verifikasi Pembayaran Kasir:** Antarmuka kasir untuk memeriksa pratinjau bukti bayar digital dan validasi status pesanan (*approve* / *reject*).<br>• **Mesin Rekapitulasi Pelaporan & Kueri Agregasi:** Kueri rekap transaksi harian/bulanan di tingkat database PostgreSQL, kalkulasi rincian pembayaran tunai vs non-tunai, akumulasi omset outlet, pencegahan tanggal terpotong (`endOfDay`), dan penambahan indeks komposit database.<br>• **Fitur Ekspor Laporan (PDF, Excel, & CSV Streaming):** Mesin unduh dokumen laporan operasional dan keuangan outlet berbasis rentang tanggal; penerapan DomPDF untuk ringkasan cetak nota, Laravel Excel (`FromQuery`) untuk analitis keuangan, dan CSV Streaming (`streamDownload` + `lazyById`) untuk arsip transaksi besar tanpa membebani memori server.<br>• **Optimasi POS & Struk Kasir:** Peningkatan performa keranjang kasir multi-layanan, penyempurnaan format cetak nota termal 58mm/80mm, dan integrasi dispatch asynchronous notifikasi status cucian. | • **Nayla Annora Nobel W.** *(Panel Verifikasi Kasir)*<br>• **Muh. Zaky Dawamul Busro** *(Mesin Kueri & Rekap Pelaporan)*<br>• **Gargarina Nanda Iswati** *(Fitur Ekspor PDF, Excel & CSV)*<br>• **Maulana Ahmad Bukhori** *(Optimasi POS & Cetak Struk)* |
| **11** | • **Pemodelan Diagram Rekayasa Sistem (AD & SD):** Rekonstruksi visual diagram alur kerja perangkat lunak (*Activity Diagram* dan *Sequence Diagram*) yang selaras dengan arsitektur kode program aktual (*reverse engineering*).<br>• **Skenario Kasus Uji (Test Cases):** Pembuatan dokumen pengujian fungsional modul komplain, pembayaran, dan transaksi kasir.<br>• **Penyusunan Data Seeder Uji M2:** Pembuatan dataset pengujian komprehensif (transaksi bervolume besar, bukti transfer, dan variasi pengerjaan) untuk uji beban dan simulasi operasional demo.<br>• **Integrasi Cabang Git & Peer Code Review:** Penggabungan cabang fitur ke cabang integrasi `develop` melalui *Pull Request*, pengujian otomatis PHPUnit, dan verifikasi antrean *queue worker*. | • **Gargarina Nanda Iswati** *(Activity & Sequence Diagram)*<br>• **Daysyani Sophi Masayu** *(Test Cases Komplain & Kasir)*<br>• **Nayla Annora Nobel W.** *(Penyusunan Data Seeder Uji)*<br>• **Muh. Zaky Dawamul Busro & Maulana Ahmad Bukhori** *(Integrasi Git, Code Review & Test Automation)* |
| **12** | • **Pengujian Terpadu (Black-Box & E2E):** Pelaksanaan pengujian fungsional menyeluruh pada seluruh alur operasional laundry dan pencatatan hasil uji.<br>• **Resolusi Defek & Penanganan Bug Log:** Penuntasan seluruh catatan perbaikan pada *bug log* hingga sistem stabil dan bebas dari *critical bug*.<br>• **Produksi Media Demonstrasi Produk:** Penyusunan naskah simulasi skenario operasional dan perekaman video demonstrasi fungsional (durasi maksimal 3 menit).<br>• **Finalisasi Laporan M2 & Live Demo:** Pengemasan berkas Laporan Kemajuan Milestone 2, validasi data demo di server cloud AWS Free Tier (termasuk Supervisor worker), dan pelaksanaan demonstrasi langsung di hadapan dosen pengampu. | • **Daysyani Sophi Masayu & Gargarina Nanda Iswati** *(Pengujian E2E & Video Demonstrasi)*<br>• **Muh. Zaky Dawamul Busro** *(Penuntasan Bug Log Backend)*<br>• **Nayla Annora Nobel W.** *(Validasi Data Demo M2)*<br>• **Maulana Ahmad Bukhori** *(Koordinasi Laporan & Live Demo)* |

---

## 2. Pembagian Peran dan Alokasi Modul Anggota Tim

Setiap anggota tim mengemban tanggung jawab modul spesifik beserta cabang Git fitur yang terisolasi untuk menghindari konflik penggabungan kode program (*merge conflict*):

### Tabel 2. Matriks Pembagian Tugas dan Cabang Git Milestone 2

| No | Anggota Tim & Peran | Fokus Modul & Fitur yang Dikoding | Cabang Git Utama |
|:---:|---|---|---|
| 1 | **Daysyani Sophi Masayu**<br>*(Front-End & QA)* | • Formulir tiket pengaduan pelanggan terhubung nomor nota transaksi (`TRX-XXXX`).<br>• Fitur unggah bukti foto pakaian bernoda/rusak terintegrasi background job kompresi berkas.<br>• Tampilan riwayat dan pelacakan status tiket (*Menunggu* $\rightarrow$ *Diproses* $\rightarrow$ *Selesai*).<br>• Penyusunan skenario uji fungsional dan video demonstrasi produk. | `feature/complaint-handling` |
| 2 | **Gargarina Nanda Iswati / Ayin**<br>*(Feature & System Modeling)* | • Pengelolaan katalog layanan lanjutan (tarif dinamis, estimasi durasi fleksibel, dan paket kilat).<br>• Mesin ekspor laporan kinerja operasional ke format dokumen PDF (DomPDF ringkas), spreadsheet Excel (`FromQuery`), dan CSV Streaming (`streamDownload` + `lazyById`) hemat memori.<br>• Pemodelan diagram rekayasa perangkat lunak (*Activity Diagram* & *Sequence Diagram*).<br>• Pengujian *End-to-End* dan pendampingan materi video demo. | `feature/advanced-service-catalog`<br>`feature/export-reports-pdf-excel` |
| 3 | **Nayla Annora Nobel W. / Nora**<br>*(Payment UI & Testing Datasets)* | • Komponen unggah bukti transfer bank dan struk QRIS oleh pelanggan beserta validasi berkas gambar dan optimasi upload via queue.<br>• Panel verifikasi kasir untuk meninjau pratinjau bukti bayar dan menetapkan status *approve* atau *reject*.<br>• Penyiapan dataset seeder pengujian (transaksi skala besar, komplain, dan bukti bayar digital).<br>• Pengujian modul pembayaran dan pencatatan temuan pada *bug log*. | `feature/payment-proof-upload`<br>`feature/cashier-payment-verification` |
| 4 | **Muh. Zaky Dawamul Busro**<br>*(Backend & Database Engine)* | • Mesin kueri agregasi pelaporan transaksi harian/bulanan di tingkat database PostgreSQL, rincian metode bayar, dan total omset outlet.<br>• Optimasi performa kueri Eloquent, pencegahan batas tanggal terpotong (`endOfDay`), dan penyesuaian indeks komposit basis data.<br>• Investigasi dan resolusi perbaikan catatan galat pada *bug log* backend.<br>• Pengawalan kestabilan *automated test suite* dan integrasi cabang fitur. | `feature/reporting-aggregation-engine`<br>`fix/backend-bug-log-resolution` |
| 5 | **Maulana Ahmad Bukhori**<br>*(Project Leader & Core POS)* | • Penguatan otorisasi pengguna berbasis multi-peran (*Admin, Kasir, Owner*).<br>• Optimasi logika keranjang transaksi kasir POS multi-layanan, format cetak nota termal browser, dan orkestrasi queue background job status cucian.<br>• Penegakan standar kode (*Laravel Pint*) dan *Peer Code Review* sebelum *merge Pull Request*.<br>• Koordinasi akhir dokumen laporan kemajuan dan persiapan evaluasi *live demo*. | `feature/user-role-management`<br>*(integrasi `develop` / `main`)* |

---

### Rincian Spesifikasi Pekerjaan Anggota Tim:

#### 1. Maulana Ahmad Bukhori (Project Leader & Full-Stack Core)
- **Fokus Modul:** Manajemen Otorisasi Pengguna (*Role & Permission*), Optimasi Alur Kasir & Cetak Struk, Arsitektur Sistem, dan Koordinasi Tim.
- **Rencana Pekerjaan & Komit Repositori:**
  - `feature/user-role-management`: Implementasi antarmuka dan *middleware* otorisasi multi-peran (Admin, Kasir, Owner).
  - `feature/pos-thermal-optimization`: Optimasi logika keranjang kasir multi-layanan dan tata letak cetak nota termal 58mm/80mm.
  - Penegakan standar kode program (*Laravel Pint*), peninjauan *Pull Request* tim, dan koordinasi akhir berkas Milestone 2.

#### 2. Muh. Zaky Dawamul Busro (Backend & Transaction Engine)
- **Fokus Modul:** Mesin Kueri Pelaporan, Rekapitulasi Data Keuangan/Operasional, Integrasi Cabang Git, dan Resolusi Defek Backend.
- **Rencana Pekerjaan & Komit Repositori:**
  - `feature/reporting-aggregation-engine`: Pengembangan kueri agregasi pendapatan (`selectRaw`), kalkulasi metode pembayaran, dan penanganan tanggal terpotong (`startOfDay` & `endOfDay`).
  - `feature/database-query-optimization`: Penyesuaian indeks komposit database pada `transactions` dan `payments` guna memastikan performa kueri pelaporan cepat.
  - `fix/backend-bug-log-resolution`: Investigasi dan penuntasan catatan galat pada *bug log* serta pengawalan stabilitas rangkaian *automated tests*.

#### 3. Daysyani Sophi Masayu (Front-End & Quality Assurance)
- **Fokus Modul:** Modul Tiket Pengaduan Pelanggan (*Complaint Handling*), Kasus Uji Fungsional, Pengujian E2E, dan Produksi Video Demonstrasi.
- **Rencana Pekerjaan & Komit Repositori:**
  - `feature/complaint-handling`: Pembuatan formulir pengajuan komplain pelanggan berbasis nomor transaksi (`TRX-XXXX`), unggah foto kerusakan/noda pakaian, integrasi job kompresi foto di background, dan tampilan status tiket.
  - `test/functional-complaint-cases`: Penyusunan skenario uji *black-box* untuk modul komplain dan alur transaksi pelanggan.
  - Kolaborasi pelaksanaan pengujian *End-to-End* serta penyusunan naskah simulasi dan perekaman video demo produk (durasi maks. 3 menit).

#### 4. Gargarina Nanda Iswati / Ayin (Feature Modules & System Modeling)
- **Fokus Modul:** Pengelolaan Katalog Layanan Lanjutan, Mesin Ekspor Laporan (PDF, Excel, & CSV Streaming), Pemodelan Diagram Rekayasa Sistem, dan Pengujian Pengguna.
- **Rencana Pekerjaan & Komit Repositori:**
  - `feature/advanced-service-catalog`: Implementasi antarmuka dan pengendali pengaturan tarif dinamis serta integrasi paket kilat ke kasir.
  - `feature/export-reports-pdf-excel`: Integrasi pustaka ekspor laporan keuangan dan operasional laundry; penerapan pola keran `response()->streamDownload()` + `lazyById()` untuk CSV dataset besar, `FromQuery` untuk Excel, dan DomPDF untuk nota ringkas.
  - `docs/activity-sequence-diagrams`: Rekonstruksi diagram visual *Activity Diagram* dan *Sequence Diagram* yang merefleksikan alur kode program aktual.
  - Kolaborasi eksekusi pengujian fungsional dan pendampingan materi video demo.

#### 5. Nayla Annora Nobel Widyonarko / Nora (Payment Components & Testing Datasets)
- **Fokus Modul:** Modul Upload Bukti Pembayaran Digital, Panel Verifikasi Pembayaran Kasir, Data Seeder Pengujian, dan Validasi Demo.
- **Rencana Pekerjaan & Komit Repositori:**
  - `feature/payment-proof-upload`: Implementasi komponen unggah bukti transfer bank/struk QRIS oleh pelanggan dengan validasi berkas gambar dan background job optimasi berkas.
  - `feature/cashier-payment-verification`: Pembuatan panel verifikasi kasir untuk meninjau pratinjau bukti bayar dan menetapkan status *approve* atau *reject*.
  - `database/seeders-demo-m2`: Penyiapan seeder data uji (transaksi lunas/belum lunas skala besar, komplain, dan bukti transfer) untuk keperluan pengujian performa dan demonstrasi.
  - Pengujian modul verifikasi pembayaran dan pencatatan temuan pada *bug log*.

---

## 3. Kriteria Keberhasilan Milestone 2 (*Definition of Done M2*)

Milestone 2 dinyatakan tercapai secara penuh apabila sistem memenuhi tolok ukur berikut:
1. **Fungsionalitas Penuh (100% Core Features):** Seluruh fitur pada kategori MVP, P1, dan P2 (meliputi operasional kasir, verifikasi pembayaran digital, penanganan komplain, dan pelaporan multi-format) beroperasi lancar tanpa galat sistem.
2. **Kestabilan Kode Program:** Seluruh rangkaian pengujian otomatis (*automated unit & feature tests*) lulus 100% dan sistem bebas dari *critical* maupun *high severity bug*.
3. **Traceability Dokumen Lengkap:** Berkas pendukung rekayasa perangkat lunak (Use Case, ERD, WBS, Activity Diagram, Sequence Diagram, dan Test Case) selaras dengan implementasi sistem aktual.
4. **Distribusi Kontribusi yang Seimbang:** Riwayat komit Git seluruh anggota tim mencerminkan kolaborasi yang sehat, merata, dan dapat dipertanggungjawabkan secara akademik.
5. **Kesiapan Demonstrasi & Bukti Produk:** Repositori Git terbarui dengan rapi, video demonstrasi berdurasi maksimal 3 menit siap diakses publik, dan aplikasi siap didemonstrasikan secara langsung di hadapan dosen pengampu.

---

## 4. Standar Arsitektur Teknis: Pengolahan Data, Ekspor/Impor & Queue (Materi P9 PWL)

Mengadaptasi materi *Pertemuan 9 Pemrograman Web Lanjut (ETL, Dataset Besar, Memory Management, Ekspor/Impor, dan Queue Background Jobs)*, seluruh anggota tim diwajibkan menerapkan kaidah arsitektur teknis berikut pada modul masing-masing:

### 4.1 Kueri Pelaporan & Penanganan Tanggal (Zaky Dawamul & Maulana)
* **Pencegahan Tanggal Terpotong (*End of Day*):** Filter rentang tanggal pada laporan omzet kasir/admin wajib menggunakan `$request->date('to')->endOfDay()` (23:59:59) agar transaksi cucian di penghujung hari tidak hilang dari hasil query.
* **Agregasi Tingkat Database (SQL Aggregation):** Hindari pemuatan seluruh data model ke memori PHP (`Transaction::all()->sum(...)`). Gunakan kalkulasi langsung di PostgreSQL (`selectRaw('COUNT(*), SUM(grand_total)')`) untuk mencegah *memory exhaustion* pada server berkapasitas 1–2 GB RAM.
* **Optimasi Indeks:** Tabel `transactions` dan `payments` wajib memiliki indeks komposit pada kolom filter tanggal (`created_at`, `paid_at`) dan status (`status`, `payment_status`).

### 4.2 Mesin Ekspor Dokumen Laporan (Ayin)
* **Distribusi Format:**
  * **PDF (DomPDF):** Dibatasi khusus untuk cetak nota individual kasir dan ringkasan eksekutif 1–2 halaman (karena DomPDF memuat seluruh struktur dokumen ke memori).
  * **Excel (Laravel Excel):** Digunakan untuk rekapitulasi analitis keuangan bulanan Owner dengan interface `FromQuery` dan `WithChunkReading`.
  * **CSV Streaming:** Untuk arsip transaksi ribuan baris, gunakan pola keran memori rendah bawaan Laravel: `response()->streamDownload()` dikombinasikan dengan `lazyById()`.

### 4.3 Eksekusi Asynchronous / Queue Background Jobs (Nora, Daysyani, & Maulana)
* **Beban Pekerjaan Antrean:** Pekerjaan dengan durasi tidak terprediksi—seperti pemotongan/kompresi foto resolusi tinggi (bukti bayar transfer dan foto noda pakaian komplain) serta pengiriman notifikasi status cucian—dialihkan ke queue (`make:job`).
* **Konfigurasi Ketahanan Job:** Setiap class job wajib menyertakan `$tries = 3;`, `$backoff = [10, 60];`, `$timeout = 60;`, dan payload hanya membawa ID model/path file.
* **Database Driver & Supervisor:** Antrean memanfaatkan `QUEUE_CONNECTION=database` bawaan tabel migrasi `jobs`, dipantau oleh Supervisor daemon di lingkungan produksi AWS, dan di-refresh dengan `php artisan queue:restart` setiap proses deployment.

---

## 5. Standar Arsitektur REST API, Keamanan, Otorisasi & Distribusi Tugas P10 PWL

Mengadaptasi materi *Pertemuan 10 Pemrograman Web Lanjut (REST API Architecture, Sanctum, Rate Limiting, Idempotency, Policies, API Resource, dan OpenAPI)*, sistem CUCI.IN diperkuat dengan standar keamanan dan arsitektur data terdistribusi. Asisten AI di IDE AGY **wajib mendeteksi secara otomatis** anggota tim yang sedang aktif dan langsung mengarahkan instruksi ke modul serta cabang fitur berikut:

### Tabel 3. Matriks Tugas P10 PWL & Deteksi Pengembang IDE AGY

| No | Anggota Tim | Git Author & Email | Tugas Spesifik P10 | Cabang Git Fitur | File / Komponen Target |
|:---:|---|---|---|---|---|
| 1 | **Maulana Ahmad Bukhori** | `Maulanahb`<br>`maulanahmad2287@gmail.com` | **Idempotency & Anti Double-Submit POS** | `feature/pos-idempotency-guard` | • Migration `create_idempotency_keys_table`<br>• Middleware `EnsureIdempotency`<br>• [`create.blade.php`](file:///c:/laragon/www/cuci-in/resources/views/staff/transactions/create.blade.php) (UUID & disable submit)<br>• `tests/Feature/IdempotencyTransactionTest.php` |
| 2 | **Muh. Zaky Dawamul Busro** | `Zakydawamul`<br>`zakydb091005@gmail.com` | **Rate Limiting Login & API Throttle** | `feature/auth-rate-limiting` | • [`routes/web.php`](file:///c:/laragon/www/cuci-in/routes/web.php) (`throttle:5,1` di `/login`)<br>• [`bootstrap/app.php`](file:///c:/laragon/www/cuci-in/bootstrap/app.php) (`RateLimiter::for('api', ...)` 60 req/min)<br>• Response 429 + header `Retry-After`<br>• `tests/Feature/AuthRateLimitTest.php` |
| 3 | **Daysyani Sophi Masayu** | `daysyani`<br>`syaniday@gmail.com` | **Laravel Policies (Anti-IDOR Terpusat)** | `feature/policy-authorization` | • Policy: `app/Policies/ComplaintPolicy.php`<br>• Policy: `app/Policies/TransactionPolicy.php`<br>• Refactor [`ComplaintController.php`](file:///c:/laragon/www/cuci-in/app/Http/Controllers/Customer/ComplaintController.php) (`Gate::authorize`)<br>• `tests/Feature/CustomerComplaintTest.php` (uji 403) |
| 4 | **Nayla Annora Nobel W. (Nora)** | `annora1304`<br>`naylaannora@gmail.com` | **Sanctum & API Tracking Publik** | `feature/sanctum-tracking-api` | • `php artisan install:api` (`routes/api.php`)<br>• Model [`User.php`](file:///c:/laragon/www/cuci-in/app/Models/User.php) (`HasApiTokens`)<br>• Controller `Api/TrackingController.php`<br>• `tests/Feature/Api/TrackingApiTest.php` |
| 5 | **Gargarina Nanda I. (Ayin)** | `ayinanda`<br>`ayiniswati22@gmail.com` | **Eloquent API Resource & OpenAPI Spec** | `feature/transaction-api-resource` | • Resource `app/Http/Resources/TransactionResource.php`<br>• Pencegahan N+1 dengan `whenLoaded()`<br>• Format uang integer & ISO 8601 tanggal<br>• Spesifikasi kontrak `docs/openapi.yaml` |

### Pedoman Teknis Implementasi P10:
1. **Idempotency Key (Maulana):** Request checkout POS menyertakan header `Idempotency-Key` (UUID). Middleware mengembalikan `409` jika request sedang diproses, `422` jika payload berubah untuk key yang sama, dan replay data lama (`Idempotent-Replayed: true`) jika request diulang karena koneksi putus.
2. **Brute Force Protection (Zaky):** Rute `POST /login` wajib dilindungi `throttle:5,1` untuk membatasi maksimal 5 percobaan gagal per menit per IP.
3. **Otorisasi Terpusat Anti-IDOR (Daysyani):** Semua pengecekan hak akses manual `abort(403)` di controller wajib direfactor ke Laravel Policy resmi dan dipanggil dengan `Gate::authorize()` atau `$this->authorize()`.
4. **Pondasi Token API (Nora):** Jalankan `install:api`, endpoint pelacakan publik `GET /api/v1/track/{trx}` merespons data cucian tanpa mengharuskan login session web.
5. **Data Shaper API (Ayin):** Seluruh response JSON API publik wajib dibungkus `JsonResource`. Dilarang mengembalikan model mentah yang mengekspos atribut sensitif basis data.

---

## 6. Riwayat Perubahan Dokumen (*Changelog*)

| Versi | Tanggal | Penulis / Kontributor | Deskripsi Perubahan |
| :---: | :---: | :--- | :--- |
| **v1.0** | 20 Oktober 2026 | Tim CUCI.IN (Maulana, Zaky, Daysyani, Ayin, Nora) | Inisialisasi dokumen Rencana Menuju Milestone 2: Target mingguan M9–M12, pembagian beban kerja tim, rencana cabang Git, dan kriteria *Definition of Done*. |
| **v1.1** | 24 Oktober 2026 | Maulana Ahmad Bukhori | **Penambahan Subbab Standar Teknis Data, Ekspor & Queue:** Integrasi materi Pertemuan 9 Pemrograman Web Lanjut ke dalam arsitektur sistem (standar query tanggal `endOfDay`, agregasi SQL PostgreSQL, pola streaming CSV, kompresi upload foto & notifikasi asynchronous via Laravel Queue). |
| **v1.2** | 24 Oktober 2026 | Maulana Ahmad Bukhori | **Penyelarasan Format Tabel:** Penambahan format tabel resmi *Matriks Pembagian Tugas dan Cabang Git Milestone 2* serta sinkronisasi penanggung jawab teknis (PIC) pada seluruh roadmap mingguan M9–M12 selaras dengan standar arsitektur P9. |
| **v2.0** | 24 Oktober 2026 | Maulana Ahmad Bukhori | **Restrukturisasi & Penamaan Dokumen:** Berkas resmi diubah namanya menjadi `rules_milestone_2.md` agar terlacak oleh Git (tidak terhalang *.gitignore*) sehingga dapat di-push dan dibaca oleh seluruh anggota tim di GitHub. |
| **v2.1** | 31 Oktober 2026 | Tim Pengembang CUCI.IN | **Integrasi Standar Arsitektur REST API & Keamanan (Materi P10):** Penambahan Seksi 5 berisi Matriks Pembagian Tugas P10 untuk 5 anggota (Idempotency POS, Rate Limiter Login, Laravel Policies, Sanctum Tracking API, dan API Resource/OpenAPI) serta aturan deteksi otomatis pengembang pada IDE AGY. |
