# PRODUCT REQUIREMENTS DOCUMENT (PRD)

## 1. Informasi Dokumen

| Item            | Detail                               |
| --------------- | ------------------------------------ |
| Nama Produk     | CUCI.IN                              |
| Jenis Produk    | Sistem Manajemen Operasional Laundry |
| Platform        | Web Application                      |
| Pendekatan      | Hybrid                               |
| Arsitektur      | SPA + REST API                       |
| Backend         | Laravel                              |
| Frontend        | React                                |
| ORM             | Eloquent ORM                         |
| Database        | PostgreSQL                           |
| Target Pengguna | Admin, Staff, Customer               |

---

# 2. Ringkasan Produk

**CUCI.IN** adalah sistem manajemen operasional laundry berbasis web yang digunakan untuk mengelola data pelanggan, layanan, transaksi, pembayaran, proses cucian, serta pengaduan pelanggan dalam satu sistem terintegrasi.

Sistem menyediakan akses berdasarkan peran:

* **Admin** sebagai pihak yang mengelola dan memonitor sistem.
* **Staff** sebagai pihak yang menjalankan operasional laundry.
* **Customer** sebagai pihak yang mengakses transaksi dan pengaduannya sendiri.
* **Guest** dapat melakukan pengecekan transaksi dan pengaduan tanpa harus memiliki akun.

---

# 3. Problem Statement

Proses operasional laundry yang masih dilakukan secara manual dapat menyebabkan:

1. Pencatatan transaksi dan pembayaran membutuhkan waktu lebih lama.
2. Data transaksi berpotensi mengalami kesalahan atau duplikasi.
3. Pencarian transaksi dan status cucian kurang efisien.
4. Perubahan harga layanan dapat menyebabkan ketidaksesuaian nilai transaksi lama apabila harga tidak disimpan sebagai snapshot.
5. Pelanggan harus menghubungi laundry untuk mengetahui status cucian.
6. Pengelolaan pengaduan pelanggan belum terdokumentasi secara terstruktur.
7. Pengelola kesulitan memantau aktivitas operasional dan pembayaran secara terpusat.

---

# 4. Tujuan Produk

CUCI.IN bertujuan untuk:

1. Mengintegrasikan proses pengelolaan pelanggan, layanan, transaksi, pembayaran, dan operasional laundry.
2. Mempermudah Staff dalam menjalankan proses transaksi dan pengelolaan cucian.
3. Mempermudah Admin dalam mengelola master data dan memonitor sistem.
4. Memberikan akses kepada pelanggan untuk mengetahui detail dan status transaksi.
5. Menyediakan mekanisme pengaduan pelanggan yang terstruktur.
6. Menyediakan pencatatan pembayaran dan verifikasi pembayaran.
7. Menyediakan dashboard dan laporan untuk membantu monitoring operasional dan keuangan.
8. Menjaga konsistensi data historis transaksi meskipun terjadi perubahan harga layanan.

---

# 5. Pengguna dan Aktor

## 5.1 Admin

Admin memiliki hak akses **Manage + Monitor**.

Admin dapat:

* Mengelola pengguna.
* Mengelola pelanggan.
* Mengelola layanan dan harga.
* Mengelola transaksi.
* Mengelola pengaduan.
* Memonitor sistem.
* Melihat dashboard.
* Melihat laporan.
* Mengelola status akun pengguna.

## 5.2 Staff

Staff memiliki hak akses **Operate**.

Staff dapat:

* Mengelola pelanggan.
* Melihat layanan dan harga.
* Mengelola transaksi.
* Mencatat pembayaran.
* Memverifikasi pembayaran.
* Mengelola status cucian.
* Mencetak bukti transaksi.
* Menangani pengaduan.
* Melihat dashboard operasional.
* Melihat laporan.

## 5.3 Customer Terdaftar

Customer terdaftar dapat:

* Login.
* Melihat transaksi miliknya.
* Melihat riwayat transaksi.
* Melihat detail transaksi.
* Melihat status cucian.
* Melihat status pengaduan.
* Mengajukan pengaduan.

## 5.4 Guest

Guest dapat:

* Mengecek transaksi menggunakan kode transaksi.
* Melihat detail transaksi.
* Melihat status cucian.
* Melakukan registrasi.
* Mengajukan pengaduan berdasarkan transaksi.
* Melihat status pengaduan.

Guest tidak memiliki akun pada tabel `users`.

---

# 6. Ruang Lingkup

## 6.1 Authentication dan Access Control

Sistem mencakup:

* Login Admin dan Staff.
* Login Customer terdaftar.
* Lupa password.
* Registrasi Customer.
* Pembatasan akses berdasarkan role.
* Aktivasi dan deaktivasi akun pengguna oleh Admin.

## 6.2 Administrasi

Admin dapat:

* Mengelola data pengguna.
* Mengaktifkan atau menonaktifkan pengguna.
* Mengelola data pelanggan.
* Mengelola layanan.
* Mengelola harga layanan.
* Mengelola estimasi durasi layanan.

Staff dapat:

* Mengelola data pelanggan.
* Melihat layanan dan harga.

## 6.3 Transaksi / POS

Sistem mencakup:

* Pembuatan transaksi.
* Pemilihan pelanggan.
* Pemilihan satu atau beberapa layanan.
* Pencatatan berat cucian.
* Perhitungan subtotal.
* Perhitungan total transaksi.
* Pembuatan kode transaksi unik.
* Penyimpanan harga layanan sebagai snapshot transaksi.
* Perhitungan estimasi selesai.
* Pencetakan bukti transaksi.
* Pembatalan transaksi sesuai aturan bisnis.

## 6.4 Pembayaran

Sistem mencakup:

* Pembayaran Tunai.
* Pembayaran Transfer.
* Pembayaran QRIS.
* Pembayaran penuh.
* Pembayaran DP.
* Pelunasan.
* Pencatatan beberapa pembayaran dalam satu transaksi.
* Perhitungan total pembayaran.
* Perhitungan sisa pembayaran.
* Status pembayaran:

  * Belum Bayar
  * DP
  * Lunas
* Upload bukti pembayaran untuk metode yang membutuhkan bukti.
* Verifikasi pembayaran.

Sistem tidak menggunakan payment gateway.

## 6.5 Operasional Laundry

Sistem mencakup pengelolaan status:

* `Diterima`
* `Diproses`
* `Selesai`
* `Diambil`
* `Dibatalkan`

Setiap perubahan status dicatat dalam riwayat status.

Tahapan seperti **Antrean**, **Baru Diterima**, dan **Baru Selesai Ditimbang** digunakan sebagai tahapan/proses operasional dan **bukan sebagai nilai status yang disimpan pada database**.

## 6.6 Customer Access

Customer dapat:

* Mengecek transaksi menggunakan kode transaksi.
* Melihat detail layanan.
* Melihat status cucian.
* Melihat progress transaksi.
* Melihat estimasi selesai.
* Melihat riwayat transaksi apabila telah memiliki akun.
* Mengajukan pengaduan terkait transaksi.
* Melihat status dan hasil penanganan pengaduan.

## 6.7 Pengaduan

Sistem mencakup:

* Pengajuan pengaduan oleh Guest maupun Customer terdaftar.
* Pengaduan harus berkaitan dengan transaksi.
* Pembuatan kode pengaduan.
* Pencatatan subjek dan deskripsi pengaduan.
* Batas waktu penanganan maksimal 2 hari sejak pengaduan dibuat.
* Status:

  * Menunggu
  * Diproses
  * Selesai
* Penanganan oleh Staff atau Admin.
* Pencatatan pihak yang menangani.
* Pencatatan waktu penanganan.
* Pencatatan respons atau hasil penanganan.
* Customer dapat melihat status dan hasil pengaduan.

## 6.8 Dashboard

### Dashboard Admin

Menampilkan informasi yang berkaitan dengan:

* Aktivitas transaksi.
* Status cucian.
* Pembayaran.
* Pendapatan berdasarkan transaksi yang tercatat.
* Pengaduan.
* Monitoring sistem.

### Dashboard Staff

Menampilkan informasi operasional seperti:

* Total transaksi hari ini.
* Cucian Diterima.
* Cucian Diproses.
* Cucian Selesai.
* Cucian belum diambil.
* Pengaduan.

## 6.9 Laporan

Sistem menyediakan:

* Laporan transaksi.
* Laporan pembayaran.
* Laporan pendapatan berdasarkan transaksi yang tercatat.
* Laporan penggunaan layanan.
* Filter berdasarkan periode.
* Export PDF/Excel.

---

# 7. Functional Requirements

## 7.1 User & Authentication

| ID         | Requirement                                                     |
| ---------- | --------------------------------------------------------------- |
| FR-USER-01 | Sistem harus menyediakan login berdasarkan kredensial pengguna. |
| FR-USER-02 | Admin dapat mengaktifkan atau menonaktifkan akun pengguna.      |
| FR-USER-03 | Pengguna yang tidak aktif tidak dapat melakukan login.          |
| FR-USER-04 | Sistem menyediakan fitur lupa password.                         |
| FR-USER-05 | Customer dapat melakukan registrasi akun.                       |
| FR-USER-06 | Sistem menerapkan hak akses berdasarkan role.                   |

## 7.2 Customer

| ID         | Requirement                                                |
| ---------- | ---------------------------------------------------------- |
| FR-CUST-01 | Admin dan Staff dapat mengelola data pelanggan.            |
| FR-CUST-02 | Guest dapat mengecek transaksi menggunakan kode transaksi. |
| FR-CUST-03 | Customer terdaftar dapat melihat transaksi miliknya.       |
| FR-CUST-04 | Customer terdaftar dapat melihat riwayat transaksi.        |

## 7.3 Service

| ID        | Requirement                                                          |
| --------- | -------------------------------------------------------------------- |
| FR-SVC-01 | Admin dapat mengelola layanan.                                       |
| FR-SVC-02 | Admin dapat menentukan harga layanan.                                |
| FR-SVC-03 | Admin dapat menentukan estimasi durasi layanan.                      |
| FR-SVC-04 | Staff dapat melihat layanan dan harga yang aktif.                    |
| FR-SVC-05 | Layanan yang tidak aktif tidak dapat digunakan untuk transaksi baru. |

## 7.4 Transaction

| ID        | Requirement                                                              |
| --------- | ------------------------------------------------------------------------ |
| FR-TRX-01 | Admin dan Staff dapat membuat transaksi.                                 |
| FR-TRX-02 | Satu transaksi dapat memiliki beberapa detail layanan.                   |
| FR-TRX-03 | Sistem menghitung subtotal berdasarkan berat, harga satuan, dan layanan. |
| FR-TRX-04 | Sistem menghitung total transaksi secara otomatis.                       |
| FR-TRX-05 | Sistem menghasilkan kode transaksi unik.                                 |
| FR-TRX-06 | Sistem menyimpan harga layanan pada detail transaksi sebagai snapshot.   |
| FR-TRX-07 | Sistem menghitung estimasi waktu selesai.                                |
| FR-TRX-08 | Admin dan Staff dapat membatalkan transaksi sesuai aturan pembatalan.    |
| FR-TRX-09 | Pembatalan transaksi dicatat dalam riwayat perubahan status.             |

## 7.5 Payment

| ID        | Requirement                                                         |
| --------- | ------------------------------------------------------------------- |
| FR-PAY-01 | Admin dan Staff dapat mencatat pembayaran.                          |
| FR-PAY-02 | Sistem mendukung pembayaran DP dan pelunasan.                       |
| FR-PAY-03 | Sistem mendukung beberapa pembayaran dalam satu transaksi.          |
| FR-PAY-04 | Sistem menghitung total pembayaran yang valid.                      |
| FR-PAY-05 | Sistem menghitung sisa pembayaran secara otomatis.                  |
| FR-PAY-06 | Sistem menyediakan status Belum Bayar, DP, dan Lunas.               |
| FR-PAY-07 | Sistem dapat menyimpan bukti pembayaran.                            |
| FR-PAY-08 | Admin dan Staff dapat melakukan verifikasi pembayaran.              |
| FR-PAY-09 | Sistem mencatat pengguna yang membuat dan memverifikasi pembayaran. |

## 7.6 Laundry Operation

| ID         | Requirement                                                                             |
| ---------- | --------------------------------------------------------------------------------------- |
| FR-LDRY-01 | Staff dapat mengelola status cucian.                                                    |
| FR-LDRY-02 | Sistem menyimpan riwayat perubahan status cucian.                                       |
| FR-LDRY-03 | Sistem mencatat pengguna yang mengubah status.                                          |
| FR-LDRY-04 | Customer dapat melihat status cucian.                                                   |
| FR-LDRY-05 | Sistem menampilkan estimasi selesai.                                                    |
| FR-LDRY-06 | Sistem dapat memonitor transaksi yang belum diambil.                                    |
| FR-LDRY-07 | Status Dibatalkan merupakan status akhir dan tidak dapat kembali ke status operasional. |

## 7.7 Complaint

| ID        | Requirement                                                                   |
| --------- | ----------------------------------------------------------------------------- |
| FR-CMP-01 | Guest dan Customer terdaftar dapat mengajukan pengaduan terkait transaksi.    |
| FR-CMP-02 | Sistem menghasilkan kode pengaduan unik.                                      |
| FR-CMP-03 | Sistem mencatat subjek dan deskripsi pengaduan.                               |
| FR-CMP-04 | Sistem menentukan deadline penanganan maksimal 2 hari sejak pengaduan dibuat. |
| FR-CMP-05 | Staff dan Admin dapat melihat dan menangani pengaduan.                        |
| FR-CMP-06 | Sistem mencatat pihak yang menangani pengaduan.                               |
| FR-CMP-07 | Sistem mencatat waktu penanganan.                                             |
| FR-CMP-08 | Sistem menyimpan respons atau hasil penanganan.                               |
| FR-CMP-09 | Customer dapat melihat status dan hasil pengaduan.                            |

---

# 8. Use Case

Use case utama sistem meliputi:

### Admin

* Login
* Lupa Password
* Lihat Dashboard
* Monitoring Sistem
* Kelola Data Pengguna
* Kelola Data Pelanggan
* Kelola Layanan & Harga
* Kelola Transaksi
* Lihat Laporan
* Kelola Pengaduan

### Staff

* Login
* Lupa Password
* Lihat Dashboard
* Kelola Data Pelanggan
* Lihat Layanan
* Kelola Transaksi
* Verifikasi Pembayaran
* Kelola Status Cucian
* Cetak Bukti Transaksi
* Lihat Laporan
* Kelola Pengaduan

### Customer Terdaftar

* Login
* Lupa Password
* Cek Transaksi
* Lihat Riwayat Transaksi
* Ajukan Pengaduan
* Lihat Status Pengaduan

### Guest

* Cek Transaksi
* Registrasi
* Ajukan Pengaduan
* Lihat Status Pengaduan

Pada proses `Cek Transaksi`, sistem mencakup:

* Lihat Status Cucian.
* Lihat Detail Transaksi.

Pada proses `Kelola Transaksi`, proses verifikasi pembayaran dapat dilakukan sesuai kebutuhan transaksi.

---

# 9. Business Rules

| ID    | Business Rule                                                                                                                                                                                                           |
| ----- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| BR-01 | Setiap pengguna memiliki satu role: Admin, Staff, atau Customer.                                                                                                                                                        |
| BR-02 | Pengguna yang berstatus tidak aktif tidak dapat login.                                                                                                                                                                  |
| BR-03 | Guest bukan merupakan data pada tabel users.                                                                                                                                                                            |
| BR-04 | Satu Customer dapat memiliki atau tidak memiliki akun users.                                                                                                                                                            |
| BR-05 | Satu transaksi harus memiliki satu Customer.                                                                                                                                                                            |
| BR-06 | Satu transaksi dapat memiliki satu atau lebih detail layanan.                                                                                                                                                           |
| BR-07 | Harga pada detail transaksi disimpan sebagai snapshot dan tidak mengikuti perubahan harga layanan setelah transaksi dibuat.                                                                                             |
| BR-08 | Layanan tidak aktif tidak dapat dipilih untuk transaksi baru.                                                                                                                                                           |
| BR-09 | Satu transaksi dapat memiliki beberapa pembayaran.                                                                                                                                                                      |
| BR-10 | Total pembayaran tidak boleh melebihi total transaksi.                                                                                                                                                                  |
| BR-11 | Sisa pembayaran dihitung dari total transaksi dikurangi pembayaran yang valid.                                                                                                                                          |
| BR-12 | Pembayaran transfer atau QRIS dapat membutuhkan bukti pembayaran dan verifikasi.                                                                                                                                        |
| BR-13 | Pembayaran yang ditolak tidak dihitung sebagai pembayaran valid.                                                                                                                                                        |
| BR-14 | Setiap perubahan status cucian dicatat pada riwayat status.                                                                                                                                                             |
| BR-15 | Perubahan status mencatat pengguna dan waktu perubahan.                                                                                                                                                                 |
| BR-16 | Pengguna dapat dinonaktifkan tanpa menghapus riwayat aktivitasnya.                                                                                                                                                      |
| BR-17 | `Dibatalkan` merupakan status akhir transaksi.                                                                                                                                                                          |
| BR-18 | Transaksi berstatus `Dibatalkan` tidak dapat kembali ke status operasional.                                                                                                                                             |
| BR-19 | Transaksi berstatus `Diambil` tidak dapat dibatalkan.                                                                                                                                                                   |
| BR-20 | Pembatalan transaksi harus dicatat dalam riwayat status beserta pengguna dan waktu pembatalan.                                                                                                                          |
| BR-21 | Pembatalan hanya dapat dilakukan ketika transaksi masih berada pada tahap operasional awal, yaitu Antrean, Baru Diterima, atau Baru Selesai Ditimbang. Tahapan tersebut bukan nilai status yang disimpan pada database. |
| BR-22 | Transaksi yang telah memasuki proses pencucian tidak dapat dibatalkan.                                                                                                                                                  |
| BR-23 | Deadline pengaduan adalah maksimal 2 hari sejak pengaduan dibuat.                                                                                                                                                       |
| BR-24 | Pengaduan harus berkaitan dengan transaksi.                                                                                                                                                                             |
| BR-25 | Penanganan pengaduan dicatat melalui status, pihak yang menangani, waktu penanganan, dan respons/hasil penanganan.                                                                                                      |
| BR-26 | Perubahan harga layanan tidak mengubah nilai transaksi yang telah dibuat sebelumnya.                                                                                                                                    |

---

# 10. Non-Functional Requirements

## 10.1 Security

* Sistem menerapkan autentikasi pengguna.
* Sistem menerapkan authorization berdasarkan role.
* Password disimpan dalam bentuk hash.
* Pengguna tidak aktif tidak dapat login.
* Data transaksi hanya dapat diakses sesuai hak akses.

## 10.2 Performance

* Sistem harus memberikan respons yang wajar pada proses transaksi dan pencarian.
* Pencarian transaksi berdasarkan kode harus dapat dilakukan secara efisien.
* Dashboard harus menampilkan data berdasarkan data transaksi yang tersimpan.

## 10.3 Usability

* Antarmuka harus sederhana dan konsisten.
* Informasi status transaksi harus mudah dipahami.
* Form transaksi harus meminimalkan input yang tidak diperlukan.
* Sistem harus memberikan informasi validasi ketika terjadi kesalahan input.

## 10.4 Maintainability

* Backend dan frontend menggunakan struktur yang terorganisasi.
* Database menggunakan relasi yang konsisten.
* Perubahan fitur tidak boleh menghilangkan data historis.
* Pengembangan mengikuti struktur repository dan branch yang disepakati tim.

## 10.5 Data Integrity

* Kode transaksi harus unik.
* Kode pengaduan harus unik.
* Harga transaksi lama harus tetap tersimpan.
* Riwayat status harus tetap tersedia.
* Data pengguna yang dinonaktifkan tidak dihapus hanya untuk mengubah status akses.

---

# 11. Data Utama Sistem

Sistem menggunakan 8 tabel utama:

1. `users`
2. `customers`
3. `services`
4. `transactions`
5. `transaction_details`
6. `payments`
7. `laundry_status_histories`
8. `complaints`

### Data penting

**users**

* identitas pengguna
* role
* status aktif/nonaktif

**customers**

* identitas pelanggan
* hubungan opsional dengan akun pengguna

**services**

* nama layanan
* harga
* estimasi durasi
* status aktif

**transactions**

* pelanggan
* kode transaksi
* total transaksi
* status
* estimasi selesai

**transaction_details**

* layanan
* berat
* harga snapshot
* subtotal

**payments**

* jumlah pembayaran
* jenis pembayaran
* metode pembayaran
* bukti pembayaran
* status verifikasi
* pencatat pembayaran
* pihak yang memverifikasi

**laundry_status_histories**

* status
* transaksi
* pengguna yang mengubah status
* waktu perubahan

**complaints**

* kode pengaduan
* transaksi
* subjek
* deskripsi
* status
* deadline
* respons
* pihak yang menangani
* waktu penanganan

---

# 12. Acceptance Criteria

## Authentication

* Admin, Staff, dan Customer terdaftar dapat login sesuai hak akses.
* Pengguna yang dinonaktifkan tidak dapat login.
* Fitur lupa password tersedia.

## Transaction

* Staff/Admin dapat membuat transaksi dengan satu atau beberapa layanan.
* Sistem menghitung subtotal dan total secara otomatis.
* Kode transaksi unik.
* Harga historis transaksi tetap sama meskipun harga layanan berubah.
* Transaksi dapat dibatalkan hanya pada tahap operasional yang diizinkan.
* Transaksi yang telah memasuki proses pencucian tidak dapat dibatalkan.
* Transaksi yang dibatalkan tercatat pada riwayat status.

## Payment

* Sistem dapat mencatat pembayaran penuh, DP, dan pelunasan.
* Satu transaksi dapat memiliki beberapa pembayaran.
* Total pembayaran dan sisa pembayaran dihitung otomatis.
* Pembayaran tidak dapat melebihi total transaksi.
* Pembayaran yang membutuhkan verifikasi memiliki status verifikasi.

## Laundry

* Staff dapat mengubah status cucian.
* Sistem menyimpan histori perubahan status.
* Customer dapat melihat status cucian.
* Transaksi yang telah `Dibatalkan` tidak dapat kembali ke status operasional.

## Complaint

* Guest maupun Customer terdaftar dapat mengajukan pengaduan.
* Pengaduan harus memiliki transaksi terkait.
* Sistem memberikan kode pengaduan.
* Sistem menetapkan deadline maksimal 2 hari.
* Staff/Admin dapat menangani pengaduan.
* Customer dapat melihat status dan hasil penanganan.

## Reporting

* Admin dan Staff dapat melihat laporan sesuai hak akses.
* Laporan dapat difilter berdasarkan periode.
* Laporan dapat diekspor.

---

# 13. Traceability

| Area            | Use Case                                                   | Data Utama                        |
| --------------- | ---------------------------------------------------------- | --------------------------------- |
| Authentication  | Login, Lupa Password, Registrasi                           | users                             |
| User Management | Kelola Data Pengguna                                       | users                             |
| Customer        | Kelola Data Pelanggan                                      | customers                         |
| Service         | Kelola Layanan & Harga, Lihat Layanan                      | services                          |
| Transaction     | Kelola Transaksi, Cek Transaksi                            | transactions, transaction_details |
| Payment         | Kelola Transaksi, Verifikasi Pembayaran                    | payments                          |
| Laundry         | Kelola Status Cucian, Lihat Status Cucian                  | laundry_status_histories          |
| Complaint       | Ajukan Pengaduan, Kelola Pengaduan, Lihat Status Pengaduan | complaints                        |
| Reporting       | Lihat Laporan                                              | transactions, payments, services  |

---

# 14. Batasan dan Asumsi

## 14.1 Batasan

Sistem tidak mencakup:

* Multi-tenant.
* Multi-cabang.
* Akuntansi penuh.
* Payment gateway.
* Layanan kurir.
* Membership atau loyalty point.
* Sistem promosi digital.
* Komunikasi real-time antara customer dan Staff.
* Fitur lain di luar ruang lingkup yang telah disepakati.

## 14.2 Asumsi

* Sistem digunakan oleh satu bisnis laundry.
* Admin bertanggung jawab terhadap pengelolaan master data dan pengguna.
* Staff menjalankan operasional transaksi dan cucian.
* Customer dapat menggunakan akses Guest maupun akun terdaftar.
* Data transaksi yang telah dibuat harus tetap dapat dipertanggungjawabkan secara historis.
* Perubahan harga layanan tidak mengubah transaksi yang telah dibuat.
* Tahapan operasional seperti Antrean, Baru Diterima, dan Baru Selesai Ditimbang digunakan untuk menentukan proses operasional, tetapi tidak menjadi enum/status baru pada database.
* Pembatalan transaksi hanya diperbolehkan pada tahap operasional awal sesuai aturan bisnis yang telah ditetapkan.

#15 Data Deletion & Deactivation

1. Data **pengguna** dan **layanan** tidak dihapus secara permanen melalui fungsi operasional sistem apabila masih memiliki keterkaitan dengan data historis.
2. Pengguna yang tidak lagi digunakan dinonaktifkan melalui atribut `users.is_active = false` sehingga pengguna tidak dapat melakukan login, tetapi data dan relasi historisnya tetap dipertahankan.
3. Layanan yang tidak lagi tersedia dinonaktifkan melalui atribut `services.is_active = false` sehingga layanan tidak dapat digunakan pada transaksi baru, tetapi tetap dapat direferensikan oleh transaksi historis.
4. Data **transaksi, detail transaksi, pembayaran, riwayat status cucian, dan pengaduan** tidak dapat dihapus melalui fungsi operasional sistem karena merupakan data historis.
5. Pembatalan transaksi tidak dilakukan dengan menghapus data transaksi, tetapi menggunakan status `Dibatalkan` dan tetap dicatat pada `laundry_status_histories`.
6. **Hard delete** hanya diperbolehkan untuk kebutuhan administratif/maintenance pada data yang tidak memiliki keterkaitan dengan data historis dan tidak dilakukan melalui fungsi operasional utama sistem.
