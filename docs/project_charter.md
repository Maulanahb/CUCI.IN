# PROJECT CHARTER

## 1. Pendekatan Proyek

**Pendekatan: Hybrid**

Proyek CUCI.IN menggunakan pendekatan **Hybrid**, yaitu menggabungkan pendekatan predictive pada bagian proyek yang kebutuhan dan ruang lingkup utamanya telah ditetapkan sejak awal dengan pendekatan adaptive pada bagian pengembangan yang dapat disempurnakan berdasarkan hasil implementasi, integrasi, dan pengujian.

Pendekatan predictive digunakan untuk:

* Penetapan masalah dan tujuan sistem.
* Penetapan ruang lingkup utama.
* Penetapan aktor dan hak akses.
* Penyusunan kebutuhan utama.
* Perancangan Use Case, ERD, dan desain sistem.
* Penetapan milestone proyek.

Pendekatan adaptive digunakan untuk:

* Penyempurnaan implementasi fitur.
* Integrasi frontend, backend, dan database.
* Penyesuaian UI berdasarkan hasil implementasi.
* Perbaikan berdasarkan hasil pengujian.
* Penyelesaian bug dan ketidaksesuaian implementasi.

---

# 2. Tujuan Proyek

CUCI.IN bertujuan membangun sistem manajemen operasional laundry berbasis web yang mengintegrasikan pengelolaan pelanggan, layanan, transaksi, pembayaran, status cucian, pengaduan, dashboard, dan laporan dalam satu sistem.

Sistem menyediakan akses pelanggan secara hybrid, yaitu pelanggan dapat mengecek transaksi menggunakan kode transaksi tanpa login serta dapat menggunakan akun untuk mengakses transaksi dan riwayat yang terhubung dengan akunnya.

---

# 3. Ruang Lingkup Proyek

## 3.1 In Scope

### A. Autentikasi dan Hak Akses

* Login Admin.
* Login Staff.
* Registrasi pelanggan secara opsional.
* Login pelanggan yang telah memiliki akun.
* Penerapan hak akses berdasarkan role.
* Admin memiliki fungsi **Manage + Monitor**.
* Staff memiliki fungsi **Operate**.
* Customer memiliki akses terhadap transaksi dan pengaduan miliknya.

### B. Administrasi

Admin dapat:

* Mengelola pengguna.
* Mengelola data pelanggan.
* Mengelola layanan laundry.
* Mengelola harga layanan.
* Mengelola estimasi durasi layanan.

Staff dapat:

* Mengelola data pelanggan.
* Melihat layanan dan harga yang telah ditetapkan Admin.

### C. Transaksi / POS

Sistem menyediakan:

* Pembuatan transaksi oleh Admin atau Staff.
* Pemilihan pelanggan.
* Penambahan satu atau lebih layanan dalam transaksi.
* Input berat untuk layanan yang menggunakan berat.
* Perhitungan subtotal.
* Perhitungan total transaksi.
* Pembuatan kode transaksi unik.
* Penyimpanan detail layanan pada transaksi.
* Pencetakan bukti transaksi.

### D. Pembayaran

Sistem mendukung:

* Pembayaran Tunai.
* Pembayaran Transfer.
* Pembayaran QRIS.
* Pembayaran sebagian/DP.
* Pelunasan pembayaran.
* Perhitungan total pembayaran secara otomatis.
* Perhitungan sisa pembayaran secara otomatis.
* Status pembayaran:

  * Belum Bayar.
  * DP.
  * Lunas.
* Pencatatan beberapa pembayaran dalam satu transaksi.
* Upload bukti pembayaran untuk pembayaran Transfer/QRIS.
* Verifikasi pembayaran oleh Admin atau Staff.

### E. Operasional Laundry

Sistem menyediakan:

* Daftar dan detail cucian.
* Pengelolaan status cucian.
* Penyimpanan riwayat perubahan status.
* Status:

  * Diterima.
  * Diproses.
  * Selesai.
  * Diambil.
* Status dapat disesuaikan kembali apabila terjadi kebutuhan operasional.
* Perhitungan estimasi waktu selesai secara otomatis.
* Monitoring cucian yang belum diambil.

Staff menjadi pelaksana utama proses operasional, sedangkan Admin dapat melakukan monitoring dan perubahan apabila diperlukan.

### F. Akses Pelanggan

#### Guest / tanpa login

Pelanggan dapat:

* Mengecek transaksi menggunakan kode transaksi.
* Melihat detail transaksi.
* Melihat layanan yang digunakan.
* Melihat status cucian.
* Melihat perkembangan/riwayat status cucian.
* Melihat estimasi waktu selesai.
* Mengirim pengaduan yang berkaitan dengan transaksi.

#### Pelanggan dengan akun

Pelanggan dapat:

* Melakukan registrasi secara opsional.
* Login.
* Melihat transaksi yang terhubung dengan akunnya.
* Melihat riwayat transaksi.
* Melihat detail transaksi.
* Melihat status cucian.
* Melihat estimasi waktu selesai.
* Mengirim pengaduan.
* Melihat status dan hasil pengaduan.

### G. Pengaduan

Sistem menyediakan pengaduan yang berkaitan dengan layanan atau transaksi.

Ketentuan utama:

* Pengaduan harus terkait dengan transaksi.
* Pengaduan dapat dibuat oleh Guest maupun pelanggan yang memiliki akun.
* Pengaduan memiliki kode pengaduan.
* Sistem mencatat waktu pengaduan dibuat.
* Batas penyelesaian pengaduan maksimal **2 hari sejak pengaduan dibuat**.
* Status pengaduan:

  * Menunggu.
  * Diproses.
  * Selesai.
* Staff dapat melihat dan menangani pengaduan.
* Admin dapat memonitor dan menangani pengaduan.
* Sistem menyimpan pihak yang menangani pengaduan.
* Sistem menyimpan waktu penanganan.
* Sistem menyimpan respons atau hasil penanganan.
* Pelanggan dapat melihat status dan hasil pengaduan.

### H. Dashboard

#### Dashboard Admin

Menyediakan informasi:

* Total transaksi.
* Jumlah transaksi berdasarkan status cucian.
* Cucian yang belum diambil.
* Informasi pengaduan.
* Total pembayaran/pendapatan berdasarkan transaksi yang tercatat.
* Transaksi belum lunas.
* Penggunaan layanan.
* Tren pendapatan berdasarkan periode.

#### Dashboard Staff

Menyediakan informasi operasional:

* Total transaksi hari ini.
* Cucian Diterima.
* Cucian Diproses.
* Cucian Selesai.
* Cucian belum diambil.
* Pengaduan yang perlu ditangani.

### I. Laporan

Sistem menyediakan laporan:

* Laporan transaksi.
* Laporan pembayaran.
* Laporan pendapatan berdasarkan transaksi yang tercatat.
* Laporan penggunaan layanan.
* Filter berdasarkan periode.
* Export laporan ke PDF.
* Export laporan ke Excel.

---

## 3.2 Out of Scope

Fitur berikut tidak termasuk dalam ruang lingkup proyek:

* Multi-tenant.
* Pengelolaan beberapa bisnis laundry dalam satu sistem.
* Multi-cabang.
* Akuntansi lengkap seperti jurnal umum, buku besar, neraca, dan laporan keuangan lengkap.
* Payment gateway seperti Midtrans atau Xendit.
* Layanan kurir atau delivery.
* Membership.
* Poin atau loyalty program.
* Promosi digital.
* Komunikasi real-time antara pelanggan dan Staff.
* Fitur lain di luar kebutuhan utama yang telah disepakati.

---

# 4. Pengguna dan Hak Akses

## 4.1 Admin — Manage + Monitor

Admin bertanggung jawab terhadap pengelolaan dan pemantauan sistem.

Admin dapat:

* Mengelola pengguna.
* Mengelola pelanggan.
* Mengelola layanan.
* Mengelola harga layanan.
* Mengelola estimasi durasi layanan.
* Membuat dan mengelola transaksi.
* Mengelola dan memverifikasi pembayaran.
* Memantau dan mengubah status cucian apabila diperlukan.
* Memantau pengaduan dan melakukan penanganan.
* Melihat dashboard.
* Melihat laporan.
* Melakukan export laporan.

## 4.2 Staff — Operate

Staff bertanggung jawab terhadap pelaksanaan operasional laundry sehari-hari.

Staff dapat:

* Mengelola pelanggan.
* Melihat layanan dan harga.
* Membuat dan mengelola transaksi.
* Mencatat pembayaran.
* Mengupload dan memverifikasi bukti pembayaran.
* Mengelola status cucian.
* Melihat riwayat status cucian.
* Mencetak bukti transaksi.
* Menangani pengaduan.
* Melihat dashboard operasional.
* Melihat dan melakukan export laporan.

Staff tidak memiliki kewenangan untuk mengelola pengguna serta mengubah data master seperti harga dan estimasi durasi layanan.

## 4.3 Customer / Guest — Access Own Transaction & Complaint

Customer atau Guest dapat:

* Mengecek transaksi.
* Melihat detail transaksi.
* Melihat status cucian.
* Melihat riwayat transaksi jika memiliki akun.
* Membuat pengaduan terkait transaksi.
* Melihat status dan hasil pengaduan miliknya.

Customer tidak memiliki akses untuk mengelola data sistem.

---

# 5. Susunan Tim dan Peran

| Nama                          | NIM          | Peran                                |
| ----------------------------- | ------------ | ------------------------------------ |
| Maulana Ahmad Bukhori         | 244107060133 | Project Manager & PIC Transaksi/POS  |
| Daysyani Sophi Masayu         | 244107060153 | PIC Administrasi & Dashboard/Laporan |
| Gargarina Nanda Iswati        | 244107060100 | PIC Operasional Laundry              |
| Muh. Zaky Dawamul Busro       | 244107060092 | PIC Akses Pelanggan & Cek Status     |
| Nayla Annora Nobel Widyonarko | 244107060148 | PIC Akses Pelanggan, Akun & Riwayat  |

PIC bertanggung jawab terhadap koordinasi pengembangan area masing-masing, tetapi implementasi, integrasi, pengujian, dan dokumentasi tetap dilakukan secara kolaboratif oleh seluruh anggota tim.

Project Manager bertanggung jawab terhadap koordinasi keseluruhan proyek, integrasi pekerjaan antaranggota, pemantauan progres, serta pengambilan keputusan yang berkaitan dengan ruang lingkup dan prioritas proyek.

---

# 6. Milestone Utama

## Milestone 1 — Analysis & Design

**Target: Minggu ke-8**

Output:

* Project Charter.
* Ruang lingkup dan kebutuhan fungsional/nonfungsional.
* WBS dan jadwal proyek.
* Use Case Diagram dan Use Case Description.
* ERD dan rancangan database.
* Activity Diagram / Sequence Diagram untuk proses utama.
* Rancangan UI awal.
* Validasi dan evaluasi desain.

## Milestone 2 — Implementation & Integration

**Target: Minggu ke-12**

Output:

* Implementasi fitur utama.
* Integrasi frontend, backend, dan database.
* Alur utama sistem dapat dijalankan.
* Test case.
* Hasil pengujian awal.
* Bug log.
* Perbaikan bug prioritas.

## Milestone 3 — Finalization & Evaluation

**Target: Minggu ke-16**

Output:

* Seluruh fitur dalam scope telah diimplementasikan.
* Integrasi sistem selesai.
* Pengujian final.
* Perbaikan bug prioritas.
* Dokumentasi sistem.
* User guide.
* Final demo.
* Final report.

---

# 7. Asumsi dan Risiko Awal

## 7.1 Asumsi

* Kebutuhan sistem dapat disepakati oleh seluruh anggota tim.
* Data yang diperlukan untuk pengembangan dan pengujian tersedia.
* Setiap anggota tim dapat menjalankan tanggung jawab yang telah disepakati.
* Teknologi yang digunakan tersedia dan dapat digunakan oleh seluruh anggota.
* Perubahan kebutuhan tetap berada dalam ruang lingkup proyek dan disepakati oleh tim.
* Integrasi frontend, backend, dan database dapat dilakukan sesuai rancangan.

## 7.2 Risiko

| Risiko                     | Mitigasi                                                                    |
| -------------------------- | --------------------------------------------------------------------------- |
| Perubahan kebutuhan        | Perubahan dicatat dan disepakati sebelum diterapkan.                        |
| Keterlambatan anggota      | Progress dipantau secara berkala dan pekerjaan disesuaikan bila diperlukan. |
| Ketidakkonsistenan desain  | Perubahan desain direview dan disepakati bersama.                           |
| Kendala teknis             | Dilakukan troubleshooting dan pembagian tugas untuk penyelesaian masalah.   |
| Konflik integrasi          | Struktur data dan kontrak antarbagian disepakati sebelum integrasi.         |
| Banyak error saat testing  | Pengujian dilakukan bertahap dan bug dicatat dalam bug log.                 |
| Keterbatasan waktu anggota | Prioritas diberikan pada fitur yang termasuk scope utama.                   |

---

# 8. Kesepakatan Kerja Tim

* Setiap anggota bertanggung jawab terhadap area pekerjaan yang telah disepakati.
* PIC bertugas mengoordinasikan area masing-masing.
* Pekerjaan dapat dilakukan secara kolaboratif dan tidak terbatas hanya pada PIC.
* Perubahan scope harus didiskusikan dan disepakati oleh tim.
* Perubahan pada fitur utama harus mempertimbangkan dampaknya terhadap Use Case, ERD, desain sistem, implementasi, dan pengujian.
* Integrasi dilakukan secara bertahap untuk mengurangi konflik antarbagian.
* Setiap anggota wajib menyampaikan progres dan kendala yang ditemukan.
* Hasil implementasi akan direview dan diuji sebelum dianggap selesai.
* Keputusan penting proyek dicatat agar menjadi acuan bersama.

---

# 9. Kriteria Keberhasilan

Proyek CUCI.IN dianggap berhasil apabila:

1. Kebutuhan utama pengguna telah terpenuhi.
2. Fitur utama untuk Admin, Staff, dan Customer telah diimplementasikan sesuai scope.
3. Hak akses Admin, Staff, dan Customer berjalan sesuai rancangan.
4. Frontend, backend, dan database telah terintegrasi.
5. Proses utama transaksi, pembayaran, operasional laundry, dan pengaduan dapat berjalan.
6. Sistem telah melalui pengujian dan bug prioritas telah diperbaiki.
7. Implementasi sesuai dengan Use Case, ERD, rancangan proses, dan UI yang telah disepakati.
8. Dokumentasi dan user guide tersedia.
9. Sistem siap digunakan untuk demonstrasi dan evaluasi akhir.
