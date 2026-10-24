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
1. **Prioritas Fasilitas Akademik & Cloud Free Tier:** Memaksimalkan penawaran kemitraan pendidikan aktif (GitHub Student Developer Pack, lisensi institusi Notion, repositori organisasi GitHub, dan aktivasi AWS Cloud Free Tier).
2. **Penyediaan Server Mandiri Bebas Biaya (Rp 0):** Menyediakan lingkungan hosting online aktif 24 jam untuk pengujian, staging, dan demonstrasi evaluasi dosen menggunakan akun AWS Free Tier yang telah berhasil diverifikasi aktif (USD 0.00). Skema patungan sewa VPS lokal disiapkan sebagai opsi cadangan (*fallback*).
3. **Penundaan Perangkat Keras Fisik:** Menunda pembelian alat kasir fisik (printer thermal) selama masa pengembangan, dan memprioritaskan fitur cetak nota digital peramban (*browser print*).

---

## 2. Evaluasi Lingkungan Server & Basis Data

Dalam menentukan infrastruktur komputasi untuk menjalankan aplikasi Laravel (PHP 8.4) dan basis data PostgreSQL, tim mengevaluasi dan menetapkan alternatif lingkungan cloud sebagai berikut:

### 2.1 Lingkungan Utama Terpilih: AWS Cloud Free Tier
- **Penyedia:** Amazon Web Services (AWS Cloud, Region `us-east-1`).
- **Status Akun:** Berhasil Diaktivasi & Terverifikasi Aktif (Billing Total: **USD 0.00**).
- **Spesifikasi:** 1 vCPU, 1–2 GB RAM, 20–40 GB SSD Storage (paket AWS Free Tier / Lightsail / EC2).
- **Estimasi Biaya:** **Rp 0 / bulan** (selama masa free plan 6 bulan).
- **Justifikasi Teknis:** Menghilangkan 100% beban biaya server kelompok selama masa pengerjaan dan penilaian PBL. Menyediakan akses *root* penuh pada sistem operasi Linux (Ubuntu 24.04 LTS) untuk mengonfigurasi stack Laravel (Nginx, PHP 8.4-FPM, PostgreSQL 15+, dan background queue worker) secara optimal.

### 2.2 Opsi Cadangan (Contingency / Fallback): Cloud VPS Lokal & Azure
- **Penyedia:** IDCloudHost / Biznet Gio (Data Center Indonesia) atau Microsoft Azure for Students ($100 credit).
- **Estimasi Biaya:** Rp 50.000 – Rp 85.000 per bulan (jika menyewa VPS lokal).
- **Justifikasi:** Disiapkan sebagai skema kontingensi apabila masa gratis AWS berakhir atau jika diperlukan latensi domestik ultra-rendah pasca-milestone evaluasi.

---

## 3. Tabel Rencana Pengadaan (*Procurement Item List*)

| No | Kebutuhan Pengadaan | Kategori | Sifat Kebutuhan | Status Pengadaan | Rekomendasi Vendor | Estimasi Biaya | Alasan Kebutuhan & Justifikasi Teknis |
| :-: | :--- | :--- | :---: | :---: | :--- | :--- | :--- |
| 1 | **Cloud VPS (1 vCPU, 1–2 GB RAM, 20–40 GB SSD)** | Server Web & DB | Wajib | **Berhasil Diaktifkan (Free Tier)** | **Amazon Web Services (AWS)** *(Cadangan: IDCloudHost / Biznet / Azure)* | **Rp 0 (AWS Free Plan 6 Bulan)** | Menjalankan aplikasi Laravel dan database PostgreSQL agar web bisa diakses online 24/7 untuk pengujian dan demo dosen. Akun AWS Free Tier telah diverifikasi aktif dengan tagihan USD 0.00, membebaskan tim dari biaya sewa server. |
| 2 | **Domain Kustom (`cuci-in.tech` atau `cuci-in.me`)** | Alamat Web | Wajib | Gratis (GitHub Education) | Radix (.tech) / Namecheap (.me) | Rp 0 (1 tahun pertama) | Alamat web resmi agar pengujian sistem dan pemasangan sertifikat SSL lebih mudah. Menggunakan voucher domain gratis 1 tahun dari GitHub Student Developer Pack. |
| 3 | **Sertifikat SSL/TLS (HTTPS)** | Keamanan | Wajib | Gratis | Let's Encrypt / Certbot | Rp 0 | Protokol HTTPS untuk mengamankan data transaksi dan kredensial akun pengguna secara gratis. |
| 4 | **Notion & GitHub Organization** | Kolaborasi | Wajib | Gratis (Lisensi Edukasi) | Notion Labs & GitHub Inc. | Rp 0 | Manajemen proyek kelompok, dokumentasi jobsheet, pelacakan RACI, dan repositori kode tim. |
| 5 | **Cloud Storage (Foto Komplain & Bukti Bayar)** | Penyimpanan | Dianjurkan | Gratis (Free Tier) | Cloudflare R2 / AWS S3 | Rp 0 (Free Tier 10 GB) | Penyimpanan berkas foto noda komplain dan struk transfer agar tidak membebani ruang simpan VPS. |
| 6 | **Mini Thermal Printer 58mm (USB/Bluetooth)** | Perangkat Kasir | Opsional | Ditunda | Toko Perlengkapan Kasir | Rp 0 saat ini | Selama pengembangan dan pengujian, kasir mencetak nota digital melalui browser. Pengadaan fisik ditunda dan akan meminjam perangkat lab jika diwajibkan saat evaluasi akhir. |
| 7 | **Kertas Thermal Roll 58x30mm** | Habis Pakai | Opsional | Ditunda | Toko Alat Tulis | Rp 35.000 (jika demo fisik) | Pembelian kertas thermal baru dilakukan apabila unit printer fisik benar-benar digunakan saat demo. |

---

## 4. Rincian Anggaran Biaya (RAB) & Sumber Pendanaan

### 4.1 Rekapitulasi Biaya Pengadaan
- **Biaya Server Web & Basis Data:** **Rp 0** (didukung penuh oleh AWS Cloud Free Tier selama 6 bulan).
- **Biaya Awal (Domain & Setup):** Rp 0 (memanfaatkan jatah domain gratis mahasiswa 1 tahun dari GitHub Education).
- **Biaya Layanan Penunjang (SSL, Storage R2, Tool Kolaborasi):** Rp 0 (menggunakan tier gratis dan lisensi pendidikan kampus).
- **Biaya Perangkat Kasir Fisik:** Rp 0 saat ini (pengadaan ditunda / meminjam unit lab kampus jika diperlukan untuk demonstrasi).
- **Total Pengeluaran Riil Tim:** **Rp 0 / bulan**.

### 4.2 Skema Pendanaan Mandiri Tim
Karena server utama berhasil diperoleh secara gratis melalui aktivasi akun AWS Free Tier:
$$\text{Iuran Riil per Anggota} = \frac{\text{Rp 0}}{5 \text{ orang}} = \text{\textbf{Rp 0 (Hemat 100\%)}}$$

*(Catatan Kontingensi: Apabila di masa mendatang server AWS habis masa promonya dan tim memutuskan migrasi ke VPS lokal berbayar, skema iuran patungan cadangan adalah Rp 10.000 – Rp 17.000 / orang / bulan).*

---

## 5. Kesimpulan Kelayakan Pengadaan (*Procurement Feasibility*)

Rencana pengadaan ini dinilai **sangat unggul, realistis, dan layak (*highly feasible*)** untuk kapasitas tim mahasiswa. Seluruh kebutuhan utama sistem (server online 24/7, database PostgreSQL terisolasi, sertifikat keamanan SSL, dan domain publik) berhasil dipenuhi dengan **biaya aktual Rp 0**. Keberhasilan mengaktivasi fasilitas AWS Free Tier membuktikan inisiatif tim dalam manajemen biaya proyek (*cost management*) yang efisien tanpa mengorbankan kualitas dan ketersediaan sistem aplikasi CUCI.IN.
