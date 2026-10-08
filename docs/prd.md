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
* Harga.
* Estimasi durasi.
* Deskripsi.
* Status layanan.

Layanan yang tidak aktif tidak dapat dipilih untuk transaksi baru.

---

# 7. Transaction/POS Requirements

## FR-05 — Pembuatan Transaksi

Staff dapat membuat transaksi baru.

Data transaksi:

* Customer.
* Kode transaksi.
* Layanan.
* Berat.
* Harga satuan.
* Subtotal.
* Total transaksi.

Satu transaksi dapat memiliki beberapa layanan.

## FR-06 — Perhitungan Transaksi

Sistem menghitung subtotal berdasarkan:

**Berat × Harga Satuan**

Total transaksi merupakan akumulasi seluruh subtotal detail transaksi.

Harga pada transaksi menggunakan nilai harga pada saat transaksi dibuat sehingga perubahan harga layanan tidak mengubah transaksi lama.

## FR-07 — Kode Transaksi

Setiap transaksi harus memiliki kode transaksi yang unik.

Kode transaksi digunakan untuk:

* Pencarian transaksi.
* Akses customer.
* Akses guest.
* Identifikasi transaksi.

## FR-08 — Detail Transaksi

Sistem harus menampilkan:

* Informasi customer.
* Kode transaksi.
* Daftar layanan.
* Berat.
* Harga satuan.
* Subtotal.
* Total transaksi.
* Status pembayaran.
* Status cucian.
* Estimasi selesai.

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

Transfer dan QRIS dapat menggunakan bukti pembayaran.

Bukti pembayaran dapat digunakan untuk proses verifikasi.

## FR-13 — Verifikasi Pembayaran

Pembayaran yang memerlukan verifikasi memiliki status:

* Menunggu Verifikasi.
* Terverifikasi.
* Ditolak.

Tunai tidak memerlukan verifikasi.

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

## FR-15 — Perubahan Status

Staff dapat memperbarui status cucian sesuai alur operasional.

Setiap perubahan status harus dicatat ke dalam riwayat status.

## FR-16 — Status History

Sistem menyimpan:

* Transaksi.
* Status.
* User yang melakukan perubahan.
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

## FR-18 — Cek Transaksi

Customer dan Guest dapat melakukan pengecekan transaksi berdasarkan kode transaksi.

Informasi yang dapat ditampilkan:

* Kode transaksi.
* Detail layanan.
* Total transaksi.
* Status pembayaran.
* Status cucian.
* Estimasi selesai.

## FR-19 — Riwayat Customer

Customer yang telah login dapat melihat transaksi yang terkait dengan akunnya.

## FR-20 — Registrasi dari Guest

Guest dapat berpindah menjadi Customer melalui proses registrasi.

Data customer yang sebelumnya tersimpan dapat dikaitkan dengan akun customer.

---

# 11. Complaint Requirements

## FR-21 — Pengajuan Pengaduan

Customer terdaftar dan Guest dapat mengajukan pengaduan.

Setiap pengaduan harus terkait dengan satu transaksi.

Data pengaduan:

* Transaksi.
* Subject.
* Description.
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

## FR-28 — Laporan Transaksi

Sistem menyediakan laporan transaksi berdasarkan data transaksi.

## FR-29 — Laporan Pembayaran

Sistem menyediakan laporan pembayaran berdasarkan:

* Transaksi.
* Metode pembayaran.
* Waktu pembayaran.
* Status verifikasi.

## FR-30 — Laporan Pendapatan

Sistem menyediakan informasi pendapatan berdasarkan pembayaran yang valid.

## FR-31 — Laporan Operasional

Sistem menyediakan informasi terkait status dan aktivitas operasional laundry.

---

# 14. Non-Functional Requirements

## NFR-01 — Usability

Antarmuka harus:

* Sederhana.
* Konsisten.
* Mudah dipahami.
* Tidak berlebihan secara visual.
* Responsif pada perangkat yang digunakan.

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

* Melindungi password dengan hashing.
* Menerapkan authentication.
* Menerapkan authorization berdasarkan role.
* Melakukan validation terhadap input.
* Membatasi akses data customer berdasarkan kepemilikan.

## NFR-03 — Performance

Sistem harus mampu memberikan respons yang wajar pada proses:

* Login.
* Pencarian data.
* Pembuatan transaksi.
* Pencatatan pembayaran.
* Pengecekan transaksi.

## NFR-04 — Data Integrity

Sistem harus menjaga:

* Keunikan kode transaksi.
* Keunikan email.
* Konsistensi foreign key.
* Konsistensi total transaksi.
* Konsistensi pembayaran.
* Konsistensi status transaksi.

## NFR-05 — Maintainability

Source code harus:

* Mengikuti struktur Laravel.
* Menggunakan Eloquent untuk ORM.
* Memisahkan logic sesuai tanggung jawabnya.
* Menggunakan component Livewire ketika diperlukan.
* Menggunakan Alpine.js untuk interaksi UI ringan.
* Menggunakan Git untuk version control.

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

REST API tidak digunakan sebagai arsitektur utama sistem.

React, Vue, dan SPA juga tidak digunakan sebagai frontend utama.

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

Relasi dan struktur data mengikuti ERD serta Kamus Data CUCI.IN yang telah ditetapkan.

Prinsip utama data:

* Customer Guest dapat memiliki `user_id = NULL`.
* Satu transaksi memiliki satu atau lebih detail transaksi.
* Satu transaksi dapat memiliki beberapa pembayaran.
* Riwayat status disimpan terpisah dari status transaksi saat ini.
* Pengaduan selalu terkait dengan transaksi.
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

## BR-06 — Status Transaksi

Status utama transaksi terdiri dari:

**Diterima → Diproses → Selesai → Diambil**

Status **Dibatalkan** hanya dapat digunakan sesuai aturan pembatalan.

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
