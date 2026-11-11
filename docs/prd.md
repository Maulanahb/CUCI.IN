# PRODUCT REQUIREMENTS DOCUMENT (PRD)

## CUCI.IN — Sistem Manajemen Operasional Laundry Berbasis Web

---

## 1. Informasi Produk

| Item                  | Keterangan                           |
| --------------------- | ------------------------------------ |
| Nama Sistem           | CUCI.IN                              |
| Jenis Sistem          | Sistem Manajemen Operasional Laundry |
| Platform              | Web                                  |
| Arsitektur            | Full Laravel MPA                     |
| Backend               | Laravel                              |
| View                  | Blade                                |
| Styling               | Tailwind CSS                         |
| Interaktivitas Server | Livewire                             |
| Interaktivitas Client | Alpine.js                            |
| ORM                   | Eloquent                             |
| Database              | PostgreSQL                           |
| Version Control       | Git & GitHub                         |

---

## 2. Deskripsi Produk

CUCI.IN adalah sistem manajemen operasional laundry berbasis web yang digunakan untuk mengelola aktivitas operasional laundry secara terintegrasi, mulai dari pengelolaan pengguna, pelanggan, layanan, transaksi, pembayaran, proses cucian, hingga pengaduan pelanggan.

Sistem menyediakan akses berdasarkan peran pengguna, yaitu Admin, Staff, dan Customer. Selain pengguna terdaftar, Guest dapat mengakses fungsi tertentu tanpa melakukan login.

Sistem dirancang dengan pendekatan **Full Laravel MPA**, dengan Blade sebagai templating, Tailwind CSS sebagai styling, Livewire untuk interaksi halaman yang membutuhkan komunikasi dengan server, dan Alpine.js untuk interaksi UI ringan.

---

## 3. Tujuan Produk

CUCI.IN dikembangkan untuk:

1. Mempermudah pengelolaan operasional laundry.
2. Mengurangi kesalahan pencatatan transaksi.
3. Mempermudah pencarian dan pemantauan transaksi.
4. Mencatat pembayaran secara terstruktur.
5. Memantau status proses cucian.
6. Memungkinkan customer dan guest mengetahui status transaksi.
7. Menyediakan mekanisme pengaduan.
8. Menyediakan informasi operasional dan keuangan.

---

## 4. Pengguna Sistem

### 4.1 Admin

Admin memiliki hak akses **Manage + Monitor**.

Admin dapat:

* Login.
* Melihat dashboard.
* Mengelola pengguna.
* Mengelola pelanggan.
* Mengelola layanan dan harga.
* Memantau transaksi.
* Memantau operasional laundry.
* Memverifikasi pembayaran yang memerlukan verifikasi.
* Mengelola pengaduan.
* Melihat laporan.

### 4.2 Staff

Staff memiliki hak akses **Operate**.

Staff dapat:

* Login.
* Mengelola pelanggan.
* Melihat layanan.
* Membuat dan mengelola transaksi.
* Mencatat pembayaran.
* Mengelola status cucian.
* Mencetak bukti transaksi.
* Menangani pengaduan.
* Melihat dashboard.
* Melihat laporan.

### 4.3 Customer

Customer memiliki hak akses **Access Own Transaction & Complaint**.

Customer dapat:

* Registrasi.
* Login.
* Melihat transaksi miliknya.
* Melihat riwayat transaksi.
* Melihat detail transaksi.
* Melihat status cucian.
* Mengajukan pengaduan.
* Melihat status pengaduan.

### 4.4 Guest

Guest merupakan pengguna yang belum login dan tidak memiliki role pada tabel `users`.

Guest dapat:

* Mengecek transaksi menggunakan kode transaksi.
* Melihat detail transaksi yang diizinkan.
* Melihat status cucian.
* Mengajukan pengaduan terkait transaksi.
* Melakukan registrasi.

---

# 5. Functional Requirements

## FR-01 — Authentication dan Access Control

### FR-01.1 Login

Sistem harus menyediakan halaman login untuk pengguna terdaftar.

Input:

* Email.
* Password.

Ketentuan:

* Sistem memvalidasi kredensial.
* Sistem memeriksa status aktif akun.
* Pengguna diarahkan ke halaman sesuai role.
* Akun tidak aktif tidak dapat login.

### FR-01.2 Logout

Sistem harus menyediakan fungsi logout.

Ketentuan:

* Session autentikasi diakhiri.
* Pengguna diarahkan ke halaman yang sesuai setelah logout.

### FR-01.3 Registrasi Customer

Customer dapat membuat akun melalui halaman registrasi.

Data minimal:

* Nama.
* Email.
* Password.
* Konfirmasi password.
* Nomor telepon.

Ketentuan:

* Email harus unik.
* Data harus tervalidasi.
* Akun customer dibuat setelah registrasi berhasil.

### FR-01.4 Lupa Password

Sistem menyediakan mekanisme lupa password untuk pengguna yang kehilangan akses akun.

### FR-01.5 Access Control

Sistem harus membatasi akses berdasarkan role:

* Admin.
* Staff.
* Customer.

Guest hanya dapat mengakses fungsi yang tidak membutuhkan autentikasi.

---

# 6. Administration Requirements

## FR-02 — Pengelolaan Pengguna

Admin dapat:

* Melihat daftar pengguna.
* Mencari pengguna.
* Melihat detail pengguna.
* Mengubah data pengguna.
* Mengaktifkan pengguna.
* Menonaktifkan pengguna.

Data pengguna mencakup:

* Nama.
* Email.
* Password.
* Role.
* Status aktif.

## FR-03 — Pengelolaan Pelanggan

Admin/Staff dapat:

* Melihat pelanggan.
* Mencari pelanggan.
* Menambah pelanggan.
* Mengubah data pelanggan.
* Melihat detail pelanggan.

Data pelanggan minimal:

* Nama.
* Nomor telepon.
* Alamat.
* Relasi akun customer apabila tersedia.

Guest tetap dapat disimpan sebagai data customer tanpa akun.

## FR-04 — Pengelolaan Layanan

Admin dapat mengelola:

* Nama layanan.
* Satuan layanan (`kg`, `pcs`, `pasang`, `meter`).
* Harga satuan.
* Estimasi durasi pengerjaan (termasuk dukungan layanan reguler dan paket kilat/ekspres).
* Deskripsi.
* Status layanan (`is_active`).

Layanan yang tidak aktif tidak dapat dipilih untuk transaksi baru.

---

# 7. Transaction/POS Requirements

## FR-05 — Pembuatan Transaksi

Staff dapat membuat transaksi baru (termasuk memilih pelanggan terdaftar atau mendaftarkan pelanggan baru secara instan melalui modal *Quick Add Customer* di halaman kasir).

Data transaksi:

* Customer.
* Kode transaksi.
* Layanan.
* Jumlah/Berat.
* Harga satuan.
* Subtotal.
* Total transaksi.

Satu transaksi dapat memiliki beberapa layanan. Pengiriman formulir pembuatan transaksi dilindungi mekanisme *Idempotency Key* (`Idempotency-Key` UUID dan middleware `EnsureIdempotency`) untuk mencegah duplikasi nota transaksi akibat pengiriman berulang (*double-submit*).

## FR-06 — Perhitungan Transaksi

Sistem menghitung subtotal berdasarkan:

**Jumlah/Berat × Harga Satuan**

Total transaksi merupakan akumulasi seluruh subtotal detail transaksi.

Harga pada transaksi menggunakan nilai harga pada saat transaksi dibuat sehingga perubahan harga layanan tidak mengubah transaksi lama.

## FR-07 — Kode Transaksi

Setiap transaksi harus memiliki kode transaksi yang unik (`TRX-YYYYMMDD-XXXX`).

Kode transaksi digunakan untuk:

* Pencarian transaksi.
* Akses customer.
* Akses guest.
* Identifikasi transaksi.

## FR-08 — Detail Transaksi dan Cetak Struk Termal

Sistem harus menampilkan:

* Informasi customer.
* Kode transaksi.
* Daftar layanan.
* Jumlah/Berat.
* Harga satuan.
* Subtotal.
* Total transaksi.
* Status pembayaran.
* Status cucian.
* Estimasi selesai.

Sistem juga menyediakan halaman cetak struk bukti transaksi kasir dalam format kertas termal (58mm / 80mm) yang memuat rincian item layanan, total tagihan, rincian pembayaran, dan kode transaksi.

---

# 8. Payment Requirements

## FR-09 — Pencatatan Pembayaran

Staff dapat mencatat pembayaran transaksi.

Metode pembayaran:

* Tunai.
* Transfer.
* QRIS.

Satu transaksi dapat memiliki beberapa pembayaran untuk mendukung DP dan pelunasan.

## FR-10 — Jenis Pembayaran

Sistem mendukung:

* Pembayaran penuh.
* DP.
* Pelunasan.

## FR-11 — Perhitungan Pembayaran

Sistem menghitung:

* Total transaksi.
* Total pembayaran yang valid.
* Sisa pembayaran.

Status pembayaran ditentukan berdasarkan akumulasi pembayaran terhadap total transaksi.

Status:

* Belum Bayar.
* DP.
* Lunas.

Field `paid_amount`, `remaining_amount`, dan `dp_amount` tidak disimpan sebagai field terpisah pada tabel transaksi. Nilainya diperoleh dari data pembayaran.

## FR-12 — Bukti Pembayaran

Transfer dan QRIS dapat menggunakan bukti pembayaran berupa berkas gambar (`JPG`, `PNG`, `WEBP` maksimal 2 MB) yang diunggah oleh Customer, Guest, maupun Staff.

Berkas bukti pembayaran disimpan pada penyimpanan privat (*private storage*) dan dioptimasi ukurannya secara asynchronous melalui *background queue job* (`OptimizePaymentProof`).

## FR-13 — Verifikasi Pembayaran

Pembayaran yang memerlukan verifikasi memiliki status:

* Menunggu Verifikasi.
* Terverifikasi.
* Ditolak.

Tunai tidak memerlukan verifikasi (otomatis berstatus *Terverifikasi*).

Pembayaran yang ditolak tidak diperhitungkan sebagai pembayaran valid.

---

# 9. Laundry Operation Requirements

## FR-14 — Status Cucian

Status utama transaksi:

1. Diterima.
2. Diproses.
3. Selesai.
4. Diambil.
5. Dibatalkan.

## FR-15 — Perubahan Status dan Proteksi Pengambilan Cucian

Staff dapat memperbarui status cucian sesuai alur operasional.

Ketentuan perubahan status:

* Setiap perubahan status harus dicatat ke dalam riwayat status.
* Status transaksi tidak dapat diubah menjadi **Diambil** apabila status pembayaran belum **Lunas** (*Pickup Protection*).
* Perubahan mundur (*rollback*) status operasional atau pembatalan wajib menyertakan catatan alasan perubahan untuk keperluan audit.

## FR-16 — Status History

Sistem menyimpan:

* Transaksi.
* Status.
* User yang melakukan perubahan.
* Catatan/alasan perubahan (bila ada).
* Waktu perubahan.

Status terkini disimpan pada transaksi, sedangkan riwayat perubahan disimpan pada `laundry_status_histories`.

## FR-17 — Pembatalan Transaksi

Transaksi dapat dibatalkan hanya pada kondisi operasional awal:

* Antrean.
* Baru Diterima.
* Baru Selesai Ditimbang.

Ketiga kondisi tersebut merupakan kondisi bisnis dan bukan status utama yang disimpan pada database.

Pembatalan tidak menghapus data transaksi.

---

# 10. Customer and Guest Requirements

## FR-18 — Cek Transaksi (Web Lookup & Endpoint API Publik)

Customer dan Guest dapat melakukan pengecekan transaksi berdasarkan kode transaksi melalui halaman pencarian publik (`/lookup`) maupun melalui endpoint REST API publik (`GET /api/v1/track/{transaction_code}`) yang mengembalikan respons JSON terstruktur (`TransactionResource`).

Informasi yang dapat ditampilkan:

* Kode transaksi.
* Detail layanan.
* Total transaksi.
* Status pembayaran.
* Status cucian beserta riwayat perubahannya.
* Estimasi selesai.

## FR-19 — Riwayat Customer

Customer yang telah login dapat melihat transaksi yang terkait dengan akunnya.

## FR-20 — Registrasi dari Guest

Guest dapat berpindah menjadi Customer melalui proses registrasi.

Data customer yang sebelumnya tersimpan dapat dikaitkan dengan akun customer berdasarkan kesamaan nomor telepon/email.

---

# 11. Complaint Requirements

## FR-21 — Pengajuan Pengaduan

Customer terdaftar dan Guest dapat mengajukan pengaduan disertai unggahan foto bukti kendala/noda pakaian (dikompresi secara asynchronous melalui *queue job* `CompressComplaintPhoto`).

Setiap pengaduan harus terkait dengan satu transaksi.

Data pengaduan:

* Transaksi.
* Subject / Kategori kendala.
* Description.
* Foto bukti kendala (opsional/pendukung).
* Status.
* Response.
* Handler.
* Waktu penanganan.

Struktur pengaduan menggunakan relasi langsung ke transaksi, sehingga customer dapat ditelusuri melalui data transaksi.

## FR-22 — Status Pengaduan

Status pengaduan:

* Menunggu.
* Diproses.
* Selesai.

## FR-23 — Penanganan Pengaduan

Staff/Admin dapat:

* Melihat pengaduan.
* Mengambil pengaduan untuk ditangani.
* Memberikan respons.
* Mengubah status pengaduan.

## FR-24 — Batas Penanganan

Pengaduan harus ditangani dengan batas waktu maksimal **2 hari**.

---

# 12. Dashboard Requirements

## FR-25 — Dashboard Admin

Dashboard Admin menampilkan informasi seperti:

* Total transaksi.
* Informasi pembayaran.
* Informasi pendapatan.
* Status operasional.
* Pengaduan.
* Ringkasan aktivitas.

## FR-26 — Dashboard Staff

Dashboard Staff menampilkan:

* Total transaksi hari ini.
* Transaksi Diterima.
* Transaksi Diproses.
* Transaksi Selesai.
* Cucian belum diambil.
* Pengaduan.

Dashboard menyediakan akses cepat ke:

* Buat Transaksi.
* Kelola Cucian.
* Pengaduan.

## FR-27 — Dashboard Customer

Dashboard Customer menampilkan:

* Transaksi aktif.
* Status cucian.
* Riwayat transaksi.
* Pengaduan.

---

# 13. Report Requirements

## FR-28 — Laporan Transaksi dan Ekspor Berkas

Sistem menyediakan laporan rekapitulasi transaksi berdasarkan rentang tanggal, status cucian, status pembayaran, dan kata kunci pencarian, serta mendukung ekspor laporan ke dalam format dokumen siap cetak **PDF** (DomPDF), lembar kerja **Excel** (`.xls`), dan **CSV Streaming** (`streamDownload` + `lazyById`).

## FR-29 — Laporan Pembayaran

Sistem menyediakan laporan pembayaran berdasarkan:

* Transaksi.
* Metode pembayaran (Tunai, Transfer, QRIS).
* Waktu pembayaran.
* Status verifikasi.

## FR-30 — Laporan Pendapatan

Sistem menyediakan informasi pendapatan bersih, total tagihan, rata-rata nilai transaksi, dan piutang berdasarkan pembayaran yang telah terverifikasi.

## FR-31 — Laporan Operasional

Sistem menyediakan informasi terkait rincian jumlah transaksi per status cucian dan evaluasi durasi aktivitas operasional laundry.

---

# 14. Non-Functional Requirements

## NFR-01 — Usability

Antarmuka harus:

* Sederhana.
* Konsisten.
* Mudah dipahami.
* Tidak berlebihan secara visual.
* Responsif pada perangkat yang digunakan (layar ponsel maupun desktop).

### NFR-01.1 Standar Desain & Palet Warna (Design System Token)

Untuk memastikan konsistensi antarmuka pengguna pada seluruh modul, antarmuka menggunakan tema **Aqua Teal** dengan spesifikasi token warna berikut:

1. **Warna Brand Utama (Aqua Teal)**:
   * Primary: `#0891b2` (`brand-600`) — Tombol aksi utama, navbar aktif, logo stop 1.
   * Hover: `#0e7490` (`brand-700`) — State hover aksi utama, hyperlink teks aktif.
   * Accent Cyan: `#06b6d4` (`brand-500`) — Teks aksen `.IN`, efek ring focus input form.
   * Light Aqua: `#cffafe` (`brand-100`) — Background badge role ("Pelanggan", "Admin", "Staff") & chip pill.
   * Soft Ice: `#ecfeff` (`brand-50`) — Background card aktif & gradasi banner dashboard.
   * Dark Ocean: `#155e75` (`brand-800`) — Teks pada badge role.
   * Midnight Teal: `#083344` (`brand-950`) — Elemen kontras tinggi & dark accents.

2. **Warna Semantik Status Operasional (Laundry Lifecycle)**:
   * **Selesai / Lunas**: `#10b981` (Emerald Green) — Cucian selesai & siap diambil, pembayaran lunas 100%.
   * **Sedang Dicuci / Proses**: `#f59e0b` (Amber Gold) — Pakaian dalam mesin cuci/pengering/setrika, status belum lunas (DP).
   * **Antrean / Baru Masuk**: `#0284c7` (Sky Blue) — Nota baru dibuat di meja kasir POS, antrean workshop.
   * **Batal / Kendala**: `#ef4444` (Rose Red) — Pembayaran gagal, komplain kerusakan, transaksi dibatalkan.

3. **Warna Netral (Slate Surface)**:
   * Background Layar: `#f8fafc` (`slate-50`) — Latar belakang halaman tamu & dashboard.
   * Surface Card: `#ffffff` (`white`) — Formulir input, kartu KPI statistik, tabel transaksi.
   * Border & Divider: `#e2e8f0` (`slate-200`) — Garis tepi card dan pemisah kolom.
   * Teks Utama: `#0f172a` (`slate-900`) — Judul halaman, heading, nama pelanggan.
   * Teks Sekunder: `#64748b` (`slate-500`) — Subtitle, placeholder form, jam/tanggal nota.

## NFR-02 — Security

Sistem harus:

* Melindungi password dengan hashing (`bcrypt`).
* Menerapkan authentication berbasis session untuk web dan dukungan token (`Laravel Sanctum`).
* Menerapkan pembatasan laju permintaan (*rate limiting*) pada login (`throttle:5,1`) dan endpoint API (`throttle:api`) beserta respons status `429 Too Many Requests` dan header `Retry-After`.
* Menerapkan authorization berdasarkan role (`Admin`, `Staff`, `Customer`) serta sentralisasi kebijakan akses data berbasis kepemilikan (*Anti-IDOR*) menggunakan `TransactionPolicy` dan `ComplaintPolicy`.
* Melakukan validation terhadap input dan berkas unggahan (format MIME dan ukuran berkas) serta menyimpan bukti pembayaran pada penyimpanan privat (*private storage*).

## NFR-03 — Performance

Sistem harus mampu memberikan respons yang wajar dan efisien pada proses:

* Login dan navigasi antarmuka.
* Pencarian data pelanggan dan transaksi.
* Pembuatan transaksi kasir dan pencatatan pembayaran.
* Pengecekan transaksi publik (dilengkapi proteksi *N+1 query* via `whenLoaded()` pada `TransactionResource`).
* Pemrosesan rekap laporan menggunakan agregasi SQL tingkat database (`selectRaw`), indeks komposit, dan ekspor CSV *streaming* (`lazyById`).

## NFR-04 — Data Integrity

Sistem harus menjaga:

* Keunikan kode transaksi (`TRX-YYYYMMDD-XXXX`) dan keunikan email pengguna.
* Pencegahan duplikasi transaksi kasir dan pembayaran akibat *double-submit* atau *network retry* menggunakan mekanisme `Idempotency-Key` (tabel `idempotency_keys` dan middleware `EnsureIdempotency`).
* Konsistensi foreign key dan pembungkusan operasi multi-tabel di dalam transaksi database atomik (`DB::transaction`).
* Konsistensi perhitungan total transaksi, akumulasi pembayaran terverifikasi, dan status transaksi.

## NFR-05 — Maintainability

Source code harus:

* Mengikuti struktur standar Laravel dan pemformatan kode otomatis (`Laravel Pint`).
* Menggunakan Eloquent untuk ORM, Form Request untuk validasi, Policy untuk otorisasi, dan API Resource (`TransactionResource`, `TransactionDetailResource`) untuk serialisasi JSON.
* Menyediakan dokumentasi spesifikasi kontrak endpoint API dalam format OpenAPI 3.0 (`docs/openapi.yaml`).
* Menggunakan Alpine.js dan Blade component untuk interaksi UI ringan.
* Menggunakan Git dan GitHub untuk version control dengan alur percabangan fitur (`feature/*` -> `develop` -> `main`) dan rangkaian pengujian otomatis PHPUnit.

## NFR-06 — Deployment & Infrastructure Environment

Sistem harus dapat dioperasikan pada lingkungan server produksi dengan spesifikasi teknis:

* **Sistem Operasi Server:** Linux Ubuntu Server pada AWS EC2 (`t3.micro`, region `ap-southeast-2`).
* **Komputasi & Memori:** Minimum 1–2 vCPU, 1 GB RAM + 2 GB Swapfile.
* **Penyimpanan Primer:** Minimum 20–40 GB SSD Storage dengan penyimpanan berkas privat lokal (`storage/app/private`) untuk bukti pembayaran dan foto komplain yang diproses oleh *background queue worker*.
* **Web Server & Runtime:** Nginx 1.28+, PHP 8.4/8.5 FastCGI (PHP-FPM), Composer 2.x, Node.js 20+ LTS.
* **Basis Data:** PostgreSQL 16+.
* **Keamanan Jaringan & Domain:** Domain publik (`https://portal-cuciin.web.id`) terintegrasi dengan Cloudflare DNS dan proteksi SSL/HTTPS aktif.

*Catatan Tata Kelola Pengadaan:* Analisis komparasi vendor cloud, evaluasi kredit pelajar Azure for Students, rincian anggaran biaya (RAB), dan skema pembiayaan tim didokumentasikan terpisah pada artefak [Rencana Pengadaan (docs/procurement_plan.md)](./procurement_plan.md).

---

# 15. Architecture Requirements

## AR-01 — Full Laravel MPA

CUCI.IN menggunakan **Full Laravel MPA (Multi-Page Application)**.

Laravel menjadi aplikasi utama untuk:

* Routing.
* Authentication.
* Authorization.
* Validation.
* Business logic.
* Session.
* Database interaction.

## AR-02 — Blade

Blade digunakan sebagai template/rendering halaman.

## AR-03 — Tailwind CSS

Tailwind CSS digunakan untuk membangun antarmuka dan styling.

## AR-04 — Livewire

Livewire digunakan untuk fitur interaktif yang membutuhkan komunikasi antara halaman dan server Laravel tanpa membangun frontend SPA terpisah.

Contoh penggunaan:

* Pencarian data.
* Filter.
* Form dinamis.
* Update data.
* Interaksi transaksi.
* Pagination.

## AR-05 — Alpine.js

Alpine.js digunakan untuk interaksi ringan di sisi client.

Contoh:

* Modal.
* Dropdown.
* Toggle.
* Sidebar.
* Show/hide.
* Interaksi UI sederhana.

## AR-06 — Eloquent

Eloquent digunakan sebagai ORM untuk mengakses dan mengelola data PostgreSQL.

## AR-07 — PostgreSQL

PostgreSQL digunakan sebagai database sistem.

## AR-08 — API

Aplikasi utama menggunakan arsitektur **Full Laravel MPA** (tanpa frontend SPA React/Vue terpisah). Untuk kebutuhan pelacakan publik lintas-platform dan integrasi terstandar, sistem menyediakan endpoint REST API spesifik (`GET /api/v1/track/{transaction_code}`) menggunakan `Laravel Sanctum`, Eloquent API Resource (`TransactionResource`), dan spesifikasi kontrak OpenAPI 3.0 (`docs/openapi.yaml`).

---

# 16. Data Requirements

Sistem menggunakan entitas utama:

1. Users.
2. Customers.
3. Services.
4. Transactions.
5. Transaction Details.
6. Payments.
7. Laundry Status Histories.
8. Complaints.
9. Idempotency Keys (`idempotency_keys`).

Relasi dan struktur data mengikuti ERD serta Kamus Data CUCI.IN yang telah ditetapkan.

Prinsip utama data:

* Customer Guest dapat memiliki `user_id = NULL`.
* Satu transaksi memiliki satu atau lebih detail transaksi.
* Satu transaksi dapat memiliki beberapa pembayaran.
* Riwayat status disimpan terpisah dari status transaksi saat ini.
* Pengaduan selalu terkait dengan transaksi.
* Kunci idempotensi (`idempotency_keys`) menyimpan hash permintaan dan respons transaksi/pembayaran untuk mencegah duplikasi data akibat *double-submit*.
* Data historis operasional dipertahankan.

---

# 17. Business Rules

## BR-01 — Customer Guest

Customer Guest tidak memiliki akun user.

## BR-02 — Harga Transaksi

Harga layanan pada detail transaksi merupakan snapshot harga ketika transaksi dibuat.

## BR-03 — Pembayaran

Satu transaksi dapat memiliki lebih dari satu pembayaran.

## BR-04 — Pembayaran Valid

Hanya pembayaran yang valid/terverifikasi yang diperhitungkan dalam total pembayaran.

## BR-05 — Batas Pembayaran

Nominal pembayaran tidak boleh melebihi sisa tagihan.

## BR-06 — Status Transaksi dan Proteksi Pengambilan (*Pickup Protection*)

Status utama transaksi terdiri dari:

**Diterima → Diproses → Selesai → Diambil**

Status **Diambil** hanya dapat ditetapkan apabila status pembayaran transaksi telah **Lunas**. Status **Dibatalkan** hanya dapat digunakan sesuai aturan pembatalan pada tahap awal.

## BR-07 — Riwayat Status

Setiap perubahan status harus memiliki record histori.

## BR-08 — Pengaduan

Setiap pengaduan wajib terkait dengan transaksi.

## BR-09 — Batas Pengaduan

Pengaduan harus ditangani maksimal dalam 2 hari.

## BR-10 — Penghapusan Data

Penghapusan tidak digunakan untuk menghilangkan histori transaksi dan operasional.

---

# 18. MVP Requirements

MVP difokuskan pada alur inti operasional laundry:

### MVP-01

Staff dapat login.

### MVP-02

Staff dapat mengelola customer.

### MVP-03

Staff dapat melihat dan memilih layanan.

### MVP-04

Staff dapat membuat transaksi.

### MVP-05

Transaksi dapat memiliki beberapa detail layanan.

### MVP-06

Sistem menghitung total transaksi.

### MVP-07

Staff dapat mencatat pembayaran dasar.

### MVP-08

Staff dapat mengubah status cucian.

### MVP-09

Customer/Guest dapat melakukan lookup transaksi menggunakan kode transaksi.

### Alur MVP

**Staff Login → Customer → Service → Create Transaction → Transaction Detail → Calculation → Payment → Diterima → Diproses → Selesai → Diambil → Customer/Guest Lookup**

---

# 19. Priority Feature

| Prioritas | Fitur                          |
| --------- | ------------------------------ |
| **MVP**   | Login Staff                    |
| **MVP**   | Customer                       |
| **MVP**   | Service                        |
| **MVP**   | Create Transaction             |
| **MVP**   | Transaction Detail             |
| **MVP**   | Calculation                    |
| **MVP**   | Basic Payment                  |
| **MVP**   | Laundry Status                 |
| **MVP**   | Transaction Lookup             |
| **P1**    | Dashboard sederhana            |
| **P1**    | Cetak bukti transaksi          |
| **P1**    | Monitoring Admin               |
| **P2**    | Pengaduan                      |
| **P2**    | Verifikasi pembayaran lanjutan |
| **P2**    | Laporan                        |
| **P2**    | Forgot Password                |
| **P2**    | Master data Admin              |

---

# 20. Acceptance Criteria

| Fitur          | Acceptance Criteria                                        |
| -------------- | ---------------------------------------------------------- |
| Login          | User dengan kredensial valid dapat login sesuai role       |
| Customer       | Data customer dapat dibuat, dilihat, dan dicari            |
| Service        | Service aktif dapat dipilih dalam transaksi                |
| Transaction    | Transaksi berhasil dibuat dengan kode unik                 |
| Calculation    | Subtotal dan total dihitung sesuai detail transaksi        |
| Payment        | Pembayaran tercatat dan status pembayaran sesuai akumulasi |
| Laundry Status | Status transaksi dapat diperbarui dan histori tersimpan    |
| Lookup         | Guest/Customer dapat mencari transaksi menggunakan kode    |
| Complaint      | Pengaduan dapat dibuat dan ditangani sesuai status         |
| Dashboard      | Data ringkasan tampil sesuai hak akses                     |
| Report         | Data laporan berasal dari data transaksi/pembayaran aktual |

---

# 21. Out of Scope

Fitur berikut tidak termasuk dalam scope saat ini:

* Multi-tenant.
* Multi-cabang.
* Accounting system penuh.
* Payment gateway.
* Integrasi kurir.
* Loyalty program.
* Digital promotion.
* Real-time chat.
* Fitur eksternal yang tidak dibutuhkan untuk operasional utama laundry.

---

# 22. Traceability

Setiap requirement harus dapat ditelusuri ke:

**PRD → Use Case → ERD/Data Model → Implementasi → Test Case**

Perubahan terhadap requirement harus disinkronkan dengan artefak terkait agar tidak terjadi ketidaksesuaian antara analisis dan implementasi.

---

# 23. Definition of Done

Feature dinyatakan selesai apabila:

* Requirement sudah terpenuhi.
* Logic Laravel berjalan.
* Route berjalan.
* Blade view tersedia.
* Tailwind CSS diterapkan.
* Livewire digunakan apabila dibutuhkan.
* Alpine.js digunakan apabila dibutuhkan.
* Validation tersedia.
* Eloquent dan relasi data sesuai.
* Feature telah diuji.
* Tidak terdapat critical bug.
* Feature siap diintegrasikan ke `develop`.

---

# 24. Technical Architecture: Data Processing, Export & Background Queue

Mengadopsi materi perkuliahan Pemrograman Web Lanjut (Pertemuan 9) mengenai ETL, pemrosesan dataset besar, efisiensi memori, ekspor/impor, dan sistem antrean (*queue*), seluruh modul sistem CUCI.IN wajib mengikuti standar teknis berikut:

### 24.1 Kueri Agregasi Laporan & Penanganan Batas Hari (PIC: Zaky Dawamul & Maulana)
1. **Pencegahan Tanggal Terpotong (*Cut-off Date Trap*):** Filter rentang tanggal pada laporan transaksi/keuangan wajib menggunakan `$request->date('to')->endOfDay()` (23:59:59). Filter tanpa `endOfDay()` memotong data pada pukul 00:00:00 dan menghilangkan seluruh transaksi hari terakhir.
2. **Kueri Agregasi di Tingkat Database (PostgreSQL):** Hindari kalkulasi total di memori PHP seperti `Transaction::all()->sum(...)`. Gunakan kueri agregasi SQL langsung via Eloquent (`selectRaw('COUNT(*), COALESCE(SUM(grand_total), 0)')`) guna mencegah galat *out-of-memory* pada server VPS berkapasitas RAM 1–2 GB.
3. **Strategi Indeks Basis Data:** Kolom yang sering dijadikan filter atau pengurutan wajib memiliki indeks pada migrasi: `transactions(created_at, status)`, `transactions(customer_id)`, dan `payments(paid_at, payment_status)`.

### 24.2 Standar Mesin Ekspor Dokumen Laporan (PIC: Ayin)
1. **PDF (DomPDF):** Dikhususkan untuk dokumen ringkas bernilai cetak (nota transaksi individual kasir, slip pembayaran, dan ringkasan eksekutif 1–2 halaman). Dilarang untuk ekspor riwayat ribuan baris karena DomPDF merender seluruh struktur dokumen ke memori RAM.
2. **Excel (.xlsx via Laravel Excel):** Digunakan untuk rekapitulasi analitis keuangan bulanan Owner dengan interface `FromQuery`, `WithHeadings`, `WithMapping`, dan `WithChunkReading`.
3. **CSV Streaming (Dataset Besar):** Untuk arsip transaksi ribuan baris, wajib menggunakan pola aliran (*streaming*) `response()->streamDownload()` dikombinasikan dengan `lazyById()` agar penggunaan RAM server tetap datar/stabil.

### 24.3 Eksekusi Asynchronous / Queue Background Jobs (PIC: Nora, Daysyani, & Maulana)
1. **Pekerjaan yang Wajib Masuk Queue:** 
   - Kompresi dan penyesuaian ukuran berkas gambar (foto bukti transfer bank/QRIS oleh Nora dan foto noda/kerusakan komplain pakaian oleh Daysyani).
   - Pengiriman notifikasi pembaruan status cucian (WhatsApp/Email) untuk mencegah latensi pemanggilan API eksternal menghambat alur kasir POS.
2. **Aturan Class Job Laravel:**
   - Properti ketahanan: `public int $tries = 3;`, `public array $backoff = [10, 60];`, `public int $timeout = 60;`.
   - Payload konstruktor hanya membawa ID model atau path file, bukan objek request/session.
   - Menggunakan `->afterCommit()` saat men-dispatch job di dalam blok transaksi basis data (`DB::transaction`).
3. **Infrastruktur Produksi:** Menggunakan koneksi antrean `QUEUE_CONNECTION=database` (tabel migrasi `jobs`), dipantau oleh Supervisor daemon di server AWS Free Tier, dan dijalankan perintah `php artisan queue:restart` saat rilis kode baru.

---

# 25. Document Changelog

| Versi | Tanggal | Penulis | Deskripsi Perubahan |
| :---: | :---: | :--- | :--- |
| **v1.0** | Oktober 2026 | Tim CUCI.IN | Inisialisasi Product Requirements Document (PRD) mencakup Scope, User Stories, Data Models, Functional Requirements, dan Definition of Done. |
| **v1.1** | 24 Oktober 2026 | Maulana Ahmad Bukhori | **Penambahan Seksi 24 (Technical Architecture: Data Processing, Export & Queue):** Integrasi standar materi Pertemuan 9 PWL (pencegahan cut-off date `endOfDay`, agregasi SQL PostgreSQL, pola streaming CSV, kompresi upload foto & notifikasi asynchronous via Laravel Queue). |
| **v1.2** | 11 November 2026 | Tim CUCI.IN | **Penyelarasan Milestone 2 (FR/NFR, Aturan Bisnis, & Arsitektur P10):** Penambahan satuan layanan dinamis (`FR-04`), pencegahan *double-submit* via `Idempotency-Key` (`FR-05`, `NFR-04`), cetak struk termal (`FR-08`), aturan *Pickup Protection* dan *rollback* status (`FR-15`, `BR-06`), endpoint API pelacakan publik & OpenAPI (`FR-18`, `AR-08`, `NFR-05`), ekspor laporan multi-format (`FR-28`), kebijakan otorisasi *Laravel Policy* & *rate limiting* (`NFR-02`), serta spesifikasi server produksi AWS EC2 `portal-cuciin.web.id` (`NFR-06`). |

