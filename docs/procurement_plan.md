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
3. **Realisasi Domain & Keamanan Publik:** Mengintegrasikan domain resmi `https://portal-cuciin.web.id` (serta domain demo pendukung) menggunakan DNS & CDN Cloudflare dengan enkripsi HTTPS (SSL Flexible) tanpa biaya tambahan.
4. **Penundaan Perangkat Keras Fisik:** Menunda pembelian alat kasir fisik (printer thermal) selama masa pengembangan, dan memprioritaskan fitur cetak nota digital peramban (*browser print*) serta ekspor dokumen PDF.

---

## 2. Evaluasi Lingkungan Server & Basis Data

Dalam menentukan infrastruktur komputasi untuk menjalankan aplikasi Full Laravel MPA (PHP 8.5) dan basis data PostgreSQL 16+, tim mengevaluasi dan menetapkan alternatif lingkungan cloud sebagai berikut:

### 2.1 Lingkungan Utama Terpilih: AWS Cloud Free Tier (EC2 t3.micro)
- **Penyedia Layanan:** Amazon Web Services (AWS Cloud, Region Asia Pacific `ap-southeast-2`).
- **Status Akun:** Berhasil Diaktivasi, Terverifikasi Aktif & Live Production (Billing Total: **USD 0.00**).
- **Spesifikasi Teknis:**
  - Tipe Instance: `t3.micro` (1 vCPU, 1 GB RAM, 30 GB EBS gp3 storage).
  - Alamat IP Publik: `3.25.167.8` (IPv4 EC2).
  - Sistem Operasi: Ubuntu Linux 26.04 LTS (*Resolute*) 64-bit.
  - Memori Tambahan: Swap Memory 2 GB (`/swapfile`) untuk menjaga stabilitas memori saat proses build asset Vite dan worker background job.
- **Estimasi Biaya:** **Rp 0 / bulan** (selama masa promo free tier 6–12 bulan).
- **Justifikasi Teknis:** Menghilangkan 100% beban biaya server kelompok selama masa pengerjaan dan penilaian PBL. Menyediakan akses *root* penuh pada sistem operasi Linux untuk mengonfigurasi web stack lengkap (Nginx 1.28+, PHP 8.5-FPM, PostgreSQL 16+, dan background queue worker `php artisan queue:work`) secara terisolasi dan mandiri.

### 2.2 Opsi Cadangan (Contingency / Fallback): Cloud VPS Lokal & Azure
- **Penyedia:** IDCloudHost / Biznet Gio (Data Center Indonesia) atau Microsoft Azure for Students ($100 credit).
- **Estimasi Biaya:** Rp 50.000 – Rp 85.000 per bulan (jika menyewa VPS lokal).
- **Justifikasi:** Disiapkan sebagai skema kontingensi apabila masa gratis AWS berakhir atau jika diperlukan latensi domestik ultra-rendah pasca-milestone evaluasi.

---

## 3. Tabel Rencana Pengadaan (*Procurement Item List*)

| No | Kebutuhan Pengadaan | Kategori | Sifat Kebutuhan | Status Realisasi | Rekomendasi Vendor | Estimasi Biaya | Alasan Kebutuhan & Justifikasi Teknis |
| :-: | :--- | :--- | :---: | :---: | :--- | :--- | :--- |
| 1 | **Cloud VPS (AWS EC2 t3.micro: 1 vCPU, 1 GB RAM, 30 GB EBS, Swap 2 GB)** | Server Web & DB | Wajib | **Aktif & Live Production** | **Amazon Web Services (AWS)** *(Cadangan: IDCloudHost / Biznet / Azure)* | **Rp 0 (AWS Free Plan)** | Menjalankan aplikasi Laravel dan database PostgreSQL agar web bisa diakses online 24/7 untuk pengujian dan demo dosen. Akun AWS Free Tier telah diverifikasi aktif dengan tagihan USD 0.00 (Region `ap-southeast-2`, IP `3.25.167.8`), membebaskan tim dari biaya sewa server. |
| 2 | **Domain Kustom (`portal-cuciin.web.id`) & Subdomain Cadangan** | Alamat Web | Wajib | **Aktif & Terhubung DNS** | Cloudflare DNS & Registrar Partner | **Rp 0** | Alamat web resmi publik (`https://portal-cuciin.web.id`) agar pengujian sistem, demo evaluasi, dan fitur pelacakan status nota publik (`/lookup`) mudah diakses. Didukung domain cadangan demo/staging. |
| 3 | **Sertifikat SSL/TLS & Proteksi Web (HTTPS)** | Keamanan | Wajib | **Aktif Terenkripsi** | Cloudflare Edge (SSL Flexible) / Let's Encrypt | **Rp 0** | Protokol HTTPS untuk mengamankan data transaksi kasir, kata sandi pengguna, dan integritas transmisi data secara gratis. |
| 4 | **Notion & GitHub Organization** | Kolaborasi | Wajib | **Aktif Digunakan** | Notion Labs & GitHub Inc. | **Rp 0** | Manajemen proyek kelompok, dokumentasi jobsheet MPSI, pelacakan RACI, board kanban, dan repositori kode tim. |
| 5 | **Cloud Storage (Foto Komplain & Bukti Bayar)** | Penyimpanan | Dianjurkan | **Tersedia (Free Tier)** | Cloudflare R2 / AWS S3 | **Rp 0 (Free Tier 10 GB)** | Penyimpanan berkas foto noda komplain dan struk transfer agar tidak membebani ruang simpan server EC2. |
| 6 | **Mini Thermal Printer 58mm (USB/Bluetooth)** | Perangkat Kasir | Opsional | **Ditunda (Browser Print)** | Toko Perlengkapan Kasir | **Rp 0 saat ini** | Selama pengembangan dan pengujian, kasir mencetak nota digital melalui browser atau unduh PDF. Pengadaan fisik ditunda dan akan meminjam perangkat lab jika diwajibkan saat evaluasi akhir. |
| 7 | **Kertas Thermal Roll 58x30mm** | Habis Pakai | Opsional | **Ditunda** | Toko Alat Tulis | **Rp 0 (Rp 35.000 jika demo fisik)** | Pembelian kertas thermal baru dilakukan apabila unit printer fisik benar-benar digunakan saat demo langsung. |

---

## 4. Rincian Anggaran Biaya (RAB) & Sumber Pendanaan

### 4.1 Rekapitulasi Biaya Pengadaan
- **Biaya Server Web & Basis Data:** **Rp 0** (didukung penuh oleh AWS Cloud Free Tier selama masa promo 6–12 bulan).
- **Biaya Alamat Domain & Setup DNS:** **Rp 0** (terintegrasi via domain resmi dan manajemen DNS gratis Cloudflare).
- **Biaya Layanan Penunjang (SSL, Cloud Storage, Tool Kolaborasi):** **Rp 0** (menggunakan tier gratis dan lisensi pendidikan kampus).
- **Biaya Perangkat Kasir Fisik:** **Rp 0 saat ini** (pengadaan ditunda / meminjam unit lab kampus jika diperlukan untuk demonstrasi).
- **Total Pengeluaran Riil Tim:** **Rp 0 / bulan**.

### 4.2 Skema Pendanaan Mandiri Tim
Karena server utama dan domain publik berhasil diperoleh dan diaktifkan secara gratis:
$$\text{Iuran Riil per Anggota} = \frac{\text{Rp 0}}{5 \text{ orang}} = \text{\textbf{Rp 0 (Hemat 100\%)}}$$

*(Catatan Kontingensi: Apabila di masa mendatang server AWS habis masa promonya dan tim memutuskan migrasi ke VPS lokal berbayar, skema iuran patungan cadangan adalah Rp 10.000 – Rp 17.000 / orang / bulan).*

---

## 5. Kesimpulan Kelayakan Pengadaan (*Procurement Feasibility*)

Rencana pengadaan ini dinilai **sangat unggul, realistis, dan layak (*highly feasible*)** untuk kapasitas tim mahasiswa. Seluruh kebutuhan utama sistem (server online 24/7, database PostgreSQL terisolasi, sertifikat keamanan SSL, dan domain publik) berhasil dipenuhi dengan **biaya aktual Rp 0**. Keberhasilan mengaktivasi fasilitas AWS Free Tier dan Cloudflare membuktikan inisiatif tim dalam manajemen biaya proyek (*cost management*) yang efisien tanpa mengorbankan kualitas dan ketersediaan sistem aplikasi CUCI.IN.

---

## 6. Riwayat Perubahan Dokumen (*Document Changelog*)

| Versi | Tanggal | Penanggung Jawab | Deskripsi Perubahan Dokumen | Status Validasi |
| :---: | :---: | :--- | :--- | :---: |
| **v1.0** | 13 Oktober 2026 | Maulana Ahmad Bukhori | Penyusunan awal dokumen rencana pengadaan (*Procurement Plan*) MPSI Minggu 9. Skema hosting server mengasumsikan sewa Cloud VPS lokal (IDCloudHost / Biznet Gio estimasi Rp 50.000 – Rp 85.000/bulan) dengan skema patungan anggota tim Rp 10.000 – Rp 17.000/orang/bulan. | Approved (Baseline) |
| **v1.1** | 22 Oktober 2026 | Maulana Ahmad Bukhori | Pembaruan status aktivasi akun AWS Cloud Free Tier (USD 0.00) sebagai lingkungan hosting utama. Iuran per anggota disesuaikan menjadi Rp 0 (efisiensi 100%), dan sewa VPS lokal diposisikan sebagai rencana cadangan (*fallback*). | Approved |
| **v2.0** | 29 Oktober 2026 | Maulana Ahmad Bukhori & Tim CUCI.IN | Realisasi infrastruktur live production: penyesuaian spesifikasi server AWS EC2 `t3.micro` (Region `ap-southeast-2`, IP Publik: `3.25.167.8`, Swap Memory 2 GB), pengesahan domain resmi aktif `https://portal-cuciin.web.id` via Cloudflare Flexible SSL, penegasan cetak nota digital peramban (*browser print*) tanpa pembelian printer fisik, serta penyelarasan menyeluruh dengan dokumen arsitektur `sistem.md`. | Approved (Live Production) |
