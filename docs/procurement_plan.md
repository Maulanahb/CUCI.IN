# RENCANA PENGADAAN (PROCUREMENT PLAN)

## CUCI.IN — Sistem Manajemen Operasional Laundry Berbasis Web

> **Mata Kuliah:** Manajemen Proyek Sistem Informasi (MPSI)  
> **Program Studi:** D4 Sistem Informasi Bisnis  
> **Pertemuan / Domain:** Minggu 9 — Performance Domain: Resources & Procurement  
> **Tahun Akademik:** 2026/2027  

---

## 1. Latar Belakang & Kebijakan Pengadaan

CUCI.IN dikembangkan sebagai proyek rekayasa perangkat lunak mandiri (*self-funded internal venture*) oleh tim mahasiswa D4 Sistem Informasi Bisnis untuk memecahkan tantangan operasional usaha laundry. Karena proyek ini tidak didanai oleh toko mitra eksternal, strategi pengadaan dirancang dengan prinsip **efisiensi biaya maksimal tanpa mengorbankan stabilitas sistem**.

Kebijakan utama pengadaan proyek:
1. **Prioritas Fasilitas Akademik:** Memaksimalkan penawaran kemitraan pendidikan aktif (GitHub Student Developer Pack, lisensi institusi Notion, dan repositori organisasi GitHub).
2. **Penyediaan Server Mandiri yang Terukur:** Menyediakan lingkungan hosting online aktif 24 jam untuk pengujian, staging, dan demonstrasi evaluasi, dengan pembiayaan patungan yang terjangkau bagi mahasiswa.
3. **Penundaan Perangkat Keras Fisik:** Menunda pembelian alat kasir fisik (printer thermal) selama masa pengembangan, dan memprioritaskan fitur cetak nota digital peramban (*browser print*).

---

## 2. Evaluasi Lingkungan Server & Basis Data

Dalam menentukan infrastruktur komputasi untuk menjalankan aplikasi Laravel (PHP 8.4) dan basis data PostgreSQL, tim mengevaluasi dua alternatif lingkungan cloud:

### 2.1 Opsi Baseline (Acuan Utama): Cloud VPS Lokal
- **Penyedia:** IDCloudHost / Biznet Gio (Data Center Indonesia).
- **Spesifikasi:** 1 vCPU, 1–2 GB RAM, 20–40 GB SSD Storage.
- **Estimasi Biaya:** Rp 50.000 – Rp 85.000 per bulan.
- **Justifikasi:** Menjamin latensi rendah untuk akses kasir lokal, tidak memiliki risiko kejutan tagihan di luar kuota (*unforeseen egress billing*), dan memberikan kontrol penuh (*root access*) untuk mengonfigurasi PHP 8.4, PostgreSQL, serta background process (queue worker).

### 2.2 Opsi Uji Coba (Staging): Microsoft Azure for Students
- **Penyedia:** Microsoft Azure (didukung kredit $100 via GitHub Student Developer Pack).
- **Justifikasi Evaluasi:** Kredit $100 dapat dimanfaatkan untuk hosting uji coba dan staging secara gratis selama kredit masih aktif dan memenuhi ketentuan kelayakan akun pelajar.
- **Batasan & Mitigasi Risiko:** Layanan terkelola seperti *Azure Database for PostgreSQL Flexible Server* dapat menguras kredit dengan cepat jika tidak dikonfigurasi secara cermat, serta memiliki batasan waktu aktif. Oleh sebab itu, Azure for Students diposisikan sebagai **calon lingkungan uji coba tambahan**, sementara skema patungan VPS lokal tetap disiapkan sebagai cadangan agar operasional sistem tidak terputus.

---

## 3. Tabel Rencana Pengadaan (*Procurement Item List*)

| No | Kebutuhan Pengadaan | Kategori | Sifat Kebutuhan | Status Pengadaan | Rekomendasi Vendor | Estimasi Biaya | Alasan Kebutuhan & Justifikasi Teknis |
| :-: | :--- | :--- | :---: | :---: | :--- | :--- | :--- |
| 1 | **Cloud VPS (1 vCPU, 1–2 GB RAM, 20–40 GB SSD)** | Server Web | Wajib | Perlu Disewa (Opsi Uji Coba: Azure) | IDCloudHost / Biznet Gio *(Alternatif: Azure for Students)* | Rp 50.000 – Rp 85.000 / bulan | Menjalankan aplikasi Laravel dan database PostgreSQL agar web bisa diakses online kapan saja untuk pengujian dan demo. Kredit $100 Azure for Students dievaluasi untuk staging; namun VPS lokal tetap disiapkan sebagai acuan utama agar kebutuhan stack tidak terbentur batasan kuota. |
| 2 | **Domain Kustom (`cuci-in.tech` atau `cuci-in.me`)** | Alamat Web | Wajib | Gratis (GitHub Education) | Radix (.tech) / Namecheap (.me) | Rp 0 (1 tahun pertama) | Alamat web resmi agar pengujian sistem dan pemasangan sertifikat SSL lebih mudah. Menggunakan voucher domain gratis 1 tahun dari GitHub Student Developer Pack. |
| 3 | **Sertifikat SSL/TLS (HTTPS)** | Keamanan | Wajib | Gratis | Let's Encrypt / Certbot | Rp 0 | Protokol HTTPS untuk mengamankan data transaksi dan kredensial akun pengguna secara gratis. |
| 4 | **Notion & GitHub Organization** | Kolaborasi | Wajib | Gratis (Lisensi Edukasi) | Notion Labs & GitHub Inc. | Rp 0 | Manajemen proyek kelompok, dokumentasi jobsheet, pelacakan RACI, dan repositori kode tim. |
| 5 | **Cloud Storage (Foto Komplain & Bukti Bayar)** | Penyimpanan | Dianjurkan | Gratis (Free Tier) | Cloudflare R2 / AWS S3 | Rp 0 (Free Tier 10 GB) | Penyimpanan berkas foto noda komplain dan struk transfer agar tidak membebani ruang simpan VPS. |
| 6 | **Mini Thermal Printer 58mm (USB/Bluetooth)** | Perangkat Kasir | Opsional | Ditunda | Toko Perlengkapan Kasir | Rp 0 saat ini | Selama pengembangan dan pengujian, kasir mencetak nota digital melalui browser. Pengadaan fisik ditunda dan akan meminjam perangkat lab jika diwajibkan saat evaluasi akhir. |
| 7 | **Kertas Thermal Roll 58x30mm** | Habis Pakai | Opsional | Ditunda | Toko Alat Tulis | Rp 35.000 (jika demo fisik) | Pembelian kertas thermal baru dilakukan apabila unit printer fisik benar-benar digunakan saat demo. |

---

## 4. Rincian Anggaran Biaya (RAB) & Sumber Pendanaan

### 4.1 Rekapitulasi Biaya Pengadaan
- **Biaya Awal (Domain & Setup):** Rp 0 (memanfaatkan jatah domain gratis mahasiswa 1 tahun dari GitHub Education).
- **Biaya Operasional Bulanan:** Rp 50.000 – Rp 85.000 per bulan untuk sewa Cloud VPS lokal (dapat menjadi Rp 0 jika kredit Azure for Students berhasil dimanfaatkan penuh selama masa uji coba).
- **Biaya Layanan Penunjang (SSL, Storage R2, Tool Kolaborasi):** Rp 0 (menggunakan tier gratis dan lisensi pendidikan kampus).
- **Biaya Perangkat Kasir Fisik:** Rp 0 saat ini (pengadaan ditunda / meminjam unit lab kampus jika diperlukan untuk demonstrasi).

### 4.2 Skema Pendanaan Mandiri Tim
Biaya operasional sewa VPS cloud lokal ditanggung secara patungan bersama oleh 5 anggota tim pengembang:
$$\text{Iuran per Anggota} = \frac{\text{Rp } 50.000 \text{ s.d. Rp } 85.000}{5 \text{ orang}} \approx \text{\textbf{Rp 10.000 – Rp 17.000 per orang / bulan}}$$

---

## 5. Kesimpulan Kelayakan Pengadaan (*Procurement Feasibility*)

Rencana pengadaan ini dinilai sangat realistis dan layak (*feasible*) untuk kapasitas tim mahasiswa. Kebutuhan utama berupa server produksi online aktif 24/7 dan domain publik resmi dapat terpenuhi dengan baik. Pengeluaran kelompok dapat ditekan seminimal mungkin dengan mengoptimalkan fasilitas gratis akun mahasiswa, serta menunda pembelian perangkat keras fisik kasir yang belum mendesak pada tahap pengembangan saat ini.
