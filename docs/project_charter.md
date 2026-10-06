# PROJECT CHARTER

## CUCI.IN — Sistem Manajemen Operasional Laundry Berbasis Web

### 1. Pendekatan Proyek

**Pendekatan: Hybrid**

Proyek menggunakan pendekatan **Hybrid**, yaitu menggabungkan pendekatan predictive pada bagian yang kebutuhan dan ruang lingkupnya telah ditetapkan sejak awal dengan pendekatan adaptive pada bagian pengembangan, integrasi, pengujian, dan penyempurnaan sistem.

Pendekatan predictive digunakan untuk:

* Penetapan masalah dan tujuan sistem.
* Penetapan scope proyek.
* Penetapan aktor dan hak akses.
* Penetapan kebutuhan fungsional dan nonfungsional.
* Penyusunan Project Charter dan PRD.
* Penyusunan Use Case.
* Penyusunan ERD dan struktur data.
* Penyusunan WBS.
* Penyusunan jadwal dan pembagian pekerjaan.

Pendekatan adaptive digunakan untuk:

* Pengembangan fitur.
* Integrasi antarfitur.
* Penyesuaian implementasi terhadap kondisi teknis.
* Penyempurnaan UI/UX.
* Pengujian fitur.
* Perbaikan bug.
* Validasi hasil implementasi terhadap kebutuhan yang telah ditetapkan.

---

### 2. Latar Belakang

Proses operasional laundry yang masih dilakukan secara manual dapat menyebabkan pencatatan transaksi dan pembayaran menjadi kurang terstruktur, pencarian data menjadi lambat, terjadinya duplikasi atau kesalahan pencatatan, serta informasi status cucian sulit diperoleh secara cepat.

CUCI.IN dikembangkan sebagai sistem manajemen operasional laundry berbasis web yang mengintegrasikan pengelolaan pelanggan, transaksi, pembayaran, proses cucian, pengaduan, dashboard, dan laporan dalam satu sistem.

---

### 3. Tujuan Proyek

Membangun sistem manajemen operasional laundry berbasis web yang dapat:

1. Mengelola data pelanggan dan pengguna secara terstruktur.
2. Memproses transaksi laundry secara terintegrasi.
3. Mencatat pembayaran dan status pembayaran.
4. Memantau proses dan status cucian.
5. Memungkinkan pelanggan atau guest melakukan pengecekan transaksi.
6. Menangani pengaduan pelanggan.
7. Menyediakan dashboard operasional dan keuangan.
8. Menyediakan laporan yang mendukung monitoring dan pengambilan keputusan.

---

### 4. Ruang Lingkup Proyek

#### A. Authentication dan Access Control

* Login pengguna.
* Logout.
* Registrasi customer.
* Lupa password.
* Validasi autentikasi.
* Pengaturan hak akses berdasarkan role.
* Aktivasi/nonaktifkan akun pengguna.

#### B. Administrasi

* Pengelolaan data pengguna.
* Pengelolaan data pelanggan.
* Pengelolaan layanan laundry.
* Pengelolaan harga layanan.
* Pengelolaan estimasi durasi layanan.
* Monitoring transaksi dan operasional.

#### C. Transaksi/POS

* Pembuatan transaksi.
* Pemilihan pelanggan.
* Pemilihan beberapa layanan.
* Pencatatan berat.
* Perhitungan subtotal.
* Perhitungan total transaksi.
* Pengelolaan detail transaksi.
* Pencarian transaksi berdasarkan kode transaksi.

#### D. Pembayaran

* Pencatatan pembayaran.
* Metode pembayaran:

  * Tunai
  * Transfer
  * QRIS
* Pembayaran penuh.
* Pembayaran DP.
* Pelunasan.
* Perhitungan total pembayaran.
* Perhitungan sisa pembayaran.
* Status pembayaran.
* Pengelolaan bukti pembayaran.
* Verifikasi pembayaran untuk metode yang memerlukannya.

#### E. Operasional Laundry

* Pengelolaan status cucian.
* Status utama transaksi:

  * Diterima
  * Diproses
  * Selesai
  * Diambil
  * Dibatalkan
* Pencatatan riwayat perubahan status.
* Informasi estimasi selesai.
* Monitoring proses cucian.

**Catatan aturan pembatalan:** transaksi dapat dibatalkan hanya pada kondisi operasional awal, yaitu saat masih berada pada tahap Antrean, Baru Diterima, atau Baru Selesai Ditimbang. Ketiga kondisi tersebut merupakan **aturan bisnis**, bukan status yang disimpan sebagai status utama transaksi.

#### F. Customer dan Guest

* Customer terdaftar dapat login.
* Customer dapat melihat transaksi miliknya.
* Customer dapat melihat riwayat transaksi.
* Guest dapat melakukan pengecekan transaksi tanpa login menggunakan kode transaksi.
* Guest dapat melakukan registrasi menjadi customer.

#### G. Pengaduan

* Customer terdaftar dapat mengajukan pengaduan.
* Guest dapat mengajukan pengaduan terkait transaksi.
* Pengaduan harus terkait dengan transaksi.
* Pengelolaan pengaduan oleh Staff/Admin.
* Status pengaduan:

  * Menunggu
  * Diproses
  * Selesai
* Pencatatan respons pengaduan.
* Pencatatan handler pengaduan.
* Batas waktu penanganan maksimal 2 hari.

#### H. Dashboard

* Dashboard Admin.
* Dashboard Staff.
* Dashboard Customer.
* Informasi transaksi.
* Informasi status cucian.
* Informasi pembayaran.
* Informasi pengaduan.
* Ringkasan operasional dan keuangan.

#### I. Laporan

* Laporan transaksi.
* Laporan pembayaran.
* Laporan pendapatan.
* Laporan operasional laundry.

#### J. Dokumentasi dan Pengujian

* Penyusunan test case.
* Pengujian fitur.
* Pencatatan bug.
* Perbaikan bug.
* Dokumentasi sistem.
* Dokumentasi penggunaan sistem.

---

### 5. Aktor dan Hak Akses

| Aktor        | Hak Akses                          |
| ------------ | ---------------------------------- |
| **Admin**    | Manage + Monitor                   |
| **Staff**    | Operate                            |
| **Customer** | Access Own Transaction & Complaint |
| **Guest**    | Akses terbatas tanpa login         |

#### Admin

Admin berfungsi untuk mengelola master data, pengguna, pelanggan, layanan, monitoring transaksi dan operasional, dashboard, serta laporan.

#### Staff

Staff berfungsi menjalankan operasional harian seperti pelanggan, transaksi/POS, pembayaran, status cucian, dan pengaduan.

#### Customer

Customer dapat mengakses transaksi miliknya, riwayat transaksi, status cucian, serta pengaduan miliknya.

#### Guest

Guest tidak memiliki akun atau role yang disimpan sebagai data pengguna. Guest hanya memperoleh akses terbatas seperti pengecekan transaksi dan pengajuan pengaduan berdasarkan transaksi.

---

### 6. Arsitektur dan Teknologi

#### Arsitektur

**Full Laravel MPA (Multi-Page Application)**

Sistem menggunakan Laravel sebagai aplikasi utama dengan pola Multi-Page Application.

#### Frontend

* **Blade** sebagai server-side templating.
* **Tailwind CSS** sebagai framework styling.
* **Livewire** untuk interaksi halaman.
* **Alpine.js** untuk interaksi UI ringan di sisi client.

#### Backend

* **Laravel**
* Routing
* Controller
* Business logic
* Authentication
* Authorization
* Validation
* Session
* Database interaction

#### ORM

* **Eloquent ORM**

#### Database

* **PostgreSQL**

#### Version Control

* Git
* GitHub

#### Ketentuan Arsitektur

* Tidak menggunakan React sebagai frontend utama.
* Tidak menggunakan Vue sebagai frontend utama.
* Tidak menggunakan SPA sebagai arsitektur utama.
* Tidak membangun REST API sebagai arsitektur utama.

---

### 7. Tim dan Pembagian Peran

| Anggota                           | Peran/PIC Utama                                       |
| --------------------------------- | ----------------------------------------------------- |
| **Maulana Ahmad Bukhori**         | Project Manager, Authentication, Integration & Review |
| **Daysyani Sophi Masayu**         | System Analyst, Customer & Transaction Lookup         |
| **Gargarina Nanda Iswati**        | System Analyst, Service & Transaction Detail          |
| **Muh. Zaky Dawamul Busro**       | ERD/AD-SD, Transaction & Calculation                  |
| **Nayla Annora Nobel Widyonarko** | AD-SD Customer, Payment & Laundry Status              |

Pembagian menggunakan sistem **PIC per feature**, namun pengerjaan tetap bersifat kolaboratif. Anggota dapat membantu PIC lain apabila terdapat kendala teknis atau kebutuhan integrasi.

---

### 8. Milestone

#### M1 — Week 8: Validation Design

Target:

* Finalisasi kebutuhan.
* Project Charter.
* PRD.
* WBS.
* Use Case.
* ERD.
* Prototype.
* Pembagian pekerjaan.
* Jadwal pengembangan.
* MVP siap untuk dikembangkan/divalidasi.

Dokumen analisis seperti AD/SD dapat disempurnakan setelah implementasi agar mampu mendokumentasikan dan memvalidasi alur sistem yang benar-benar diterapkan.

#### M2 — Week 12: Functional Product

Target:

* Fitur utama dapat berjalan.
* Authentication dan authorization.
* Customer management.
* Service management.
* POS/transaksi.
* Payment.
* Laundry operation.
* Customer access.
* Complaint.
* Integrasi antarfitur.
* AD/SD berdasarkan implementasi.
* Test case.
* Initial testing.
* Bug log.

#### M3 — Week 16: Final Product

Target:

* Final testing.
* Bug fixing.
* Validasi akhir.
* Penyempurnaan UI/UX.
* Final documentation.
* Final AD/SD.
* User guide.
* Final demo.
* Final report.

---

### 9. MVP

MVP difokuskan pada alur operasional utama dari pembuatan transaksi sampai pelanggan dapat melakukan pengecekan transaksi.

Fitur MVP:

1. Staff Login.
2. Customer Management.
3. Service Management.
4. Create Transaction.
5. Transaction Detail.
6. Total Calculation.
7. Basic Payment.
8. Laundry Status.
9. Transaction Code Lookup.

Alur utama MVP:

**Staff Login → Customer → Service → Transaction → Calculation → Payment → Diterima → Diproses → Selesai → Diambil → Customer/Guest Lookup**

Fitur di luar alur inti tetap berada dalam scope proyek, tetapi memiliki prioritas pengembangan setelah MVP selesai.

---

### 10. Aturan Pembayaran

Metode pembayaran:

* Tunai
* Transfer
* QRIS

Jenis pembayaran:

* Pembayaran penuh
* DP
* Pelunasan

Satu transaksi dapat memiliki lebih dari satu record pembayaran.

Pembayaran yang valid akan diperhitungkan dalam total pembayaran. Pembayaran yang ditolak tidak diperhitungkan.

Status verifikasi pembayaran:

* Tidak Diperlukan
* Menunggu Verifikasi
* Terverifikasi
* Ditolak

Aturan umum:

* Cash tidak memerlukan verifikasi.
* Transfer dan QRIS dapat membutuhkan bukti pembayaran dan verifikasi.
* Nilai pembayaran tidak boleh melebihi sisa tagihan.
* Tidak menggunakan payment gateway seperti Midtrans atau Xendit pada scope saat ini.

---

### 11. Aturan Data dan Penghapusan

Data operasional dan historis harus dipertahankan.

* `users` menggunakan `is_active` untuk mengaktifkan/nonaktifkan akun.
* `services` menggunakan `is_active`.
* Data customer tetap dipertahankan untuk kebutuhan histori transaksi.
* Transaksi dan data turunannya tidak dihapus secara operasional.
* Pembatalan transaksi bukan merupakan penghapusan data.
* Tidak menggunakan Laravel `SoftDeletes` atau kolom `deleted_at` untuk kebutuhan operasional utama.
* Hard delete hanya diperbolehkan untuk kebutuhan maintenance/admin tertentu terhadap data yang tidak memiliki ketergantungan historis.

---

### 12. Development Agreement

1. Setiap feature memiliki PIC utama.
2. Pengerjaan tetap dapat dilakukan secara kolaboratif.
3. Pengembangan menggunakan Full Laravel MPA.
4. View menggunakan Blade.
5. Styling menggunakan Tailwind CSS.
6. Livewire digunakan untuk interaksi halaman.
7. Alpine.js digunakan untuk interaksi UI ringan.
8. Database diakses melalui Eloquent.
9. Setiap fitur harus memiliki validation dan testing.
10. Perubahan scope, waktu, atau fitur utama harus dibahas dan disepakati.
11. Artefak analisis harus disinkronkan dengan implementasi.
12. AD/SD dapat dibuat atau disesuaikan berdasarkan implementasi untuk mendokumentasikan alur aktual.
13. Bug dicatat dalam bug log.
14. Fitur yang belum masuk prioritas tidak dikerjakan pada iterasi berjalan tanpa kesepakatan tim.
15. Setiap fitur harus siap diintegrasikan ke branch `develop`.

---

### 13. Definition of Done

Sebuah feature dinyatakan selesai apabila:

* Logic Laravel telah diimplementasikan.
* Route dan proses backend berjalan.
* Blade view telah tersedia.
* Tailwind CSS telah diterapkan.
* Livewire digunakan apabila diperlukan.
* Alpine.js digunakan apabila diperlukan.
* Validation telah diterapkan.
* Relasi dan data Eloquent telah sesuai.
* Feature telah diuji.
* Tidak terdapat critical bug.
* Feature siap diintegrasikan.
* Dokumentasi terkait tersedia apabila diperlukan.

---

### 14. Branch Strategy

```text
main
└── Branch stabil

develop
└── Branch integrasi

feature/*
├── feature/auth
├── feature/customer
├── feature/service
├── feature/transaction
└── feature/payment
```

Alur pengembangan:

**feature → develop → testing/integration → main**

`main` hanya digunakan untuk versi yang telah stabil.

---

### 15. Kanban Development

Status pekerjaan:

1. **TODO**
2. **IN PROGRESS**
3. **READY FOR INTEGRATION**
4. **INTEGRATING**
5. **DONE**

Setiap task minimal mencantumkan:

* Task
* PIC
* Progress
* Laravel Logic/Controller
* Blade View
* Tailwind CSS
* Livewire jika diperlukan
* Alpine.js jika diperlukan
* Validation
* Database/Eloquent
* Testing
* Branch
* Route
* Dependency
* Notes

---

### 16. Risiko dan Mitigasi

| Risiko                                    | Mitigasi                                                                                   |
| ----------------------------------------- | ------------------------------------------------------------------------------------------ |
| Integrasi antarfitur terlambat            | Setiap feature menggunakan branch terpisah dan diintegrasikan melalui `develop`            |
| Perbedaan implementasi antaranggota       | Mengacu pada Charter, PRD, Use Case, dan ERD yang telah disepakati                         |
| Bug saat integrasi                        | Testing dilakukan sebelum feature masuk tahap integrasi                                    |
| Scope creep                               | Perubahan scope harus dibahas dan disepakati tim                                           |
| Ketidaksesuaian dokumentasi dengan sistem | AD/SD diperiksa kembali berdasarkan implementasi aktual                                    |
| Kendala teknis                            | PIC dapat meminta bantuan anggota lain dan dilakukan penyesuaian teknis secara kolaboratif |

---

### 17. Kriteria Keberhasilan

Proyek dinyatakan berhasil apabila:

1. Alur utama transaksi laundry dapat berjalan dari login Staff sampai transaksi dapat dicek oleh Customer/Guest.
2. Data pelanggan, layanan, transaksi, pembayaran, dan status cucian tersimpan secara terstruktur.
3. Hak akses Admin, Staff, Customer, dan Guest berjalan sesuai ketentuan.
4. Sistem mampu mempertahankan histori transaksi dan operasional.
5. Fitur utama telah melalui pengujian.
6. Tidak terdapat critical bug pada fitur utama.
7. Sistem dapat diintegrasikan pada environment yang ditentukan.
8. Dokumentasi dan artefak proyek sesuai dengan implementasi sistem.
