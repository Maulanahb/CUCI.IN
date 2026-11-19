# Panduan Pembagian Peran & Skenario Video Demo CUCI.IN (Milestone 2)

Dokumen ini memuat pembagian tugas perekaman video demonstrasi produk aplikasi **CUCI.IN** untuk evaluasi Milestone 2 (Mata Kuliah PWL, MPSI, dan PMPL). Setiap anggota tim mendemonstrasikan modul dan fitur yang secara aktual telah dikembangkan pada repositori Git.

---

## 1. Ringkasan Matriks Pembagian

| No | Anggota Tim | Fokus Modul | Halaman / Rute Web | Aksi Utama di Layar |
|:---|:---|:---|:---|:---|
| 1 | **Maulana Ahmad Bukhori** | Peran Pengguna, Kasir POS & Cetak Nota | `/login`<br>`/admin/transactions/create`<br>`/admin/transactions/{id}/receipt` | Login peran Kasir, input transaksi cucian kiloan/satuan, demokan tombol checkout anti double-submit (*Idempotency Guard*), cetak struk thermal kasir. |
| 2 | **Gargarina Nanda Iswati (Ayin)** | Katalog Layanan & Mesin Ekspor Laporan | `/admin/services`<br>`/admin/reports` | Ubah harga/estimasi paket kilat, unduh laporan PDF siap cetak, unduh laporan Excel/CSV streaming berfitur sanitasi anti-formula injection. |
| 3 | **Nayla Annora Nobel W. (Nora)** | Pembayaran Digital & Pelacakan Publik | `/lookup`<br>`/admin/payments/verification` | Lacak status nota via kode & scan barcode struk, unggah bukti transfer/QRIS pelanggan, verifikasi (*approve/reject*) di panel kasir. |
| 4 | **Daysyani Sophi Masayu** | Modul Komplain & Dashboard Customer | `/customer/dashboard`<br>`/complaints/create`<br>`/admin/complaints` | Masuk Dashboard Customer, buat tiket komplain noda pakaian (unggah foto, queue background), tindak lanjuti tiket di panel kasir berproteksi Laravel Policy. |
| 5 | **Muh. Zaky Dawamul Busro** | Agregasi Database, Keamanan & Testing | `/admin/dashboard`<br>`/login`<br>Terminal CLI | Paparkan dashboard metrik omset/metode bayar berkecepatan tinggi, simulasikan rate limiting login 5x gagal, tunjukkan 100% kelulusan feature test di PHPUnit. |

---

## 2. Rincian Skenario Demo per Anggota

```
                   ALUR DEMONSTRASI SISTEM CUCI.IN (E2E)
  ┌───────────────────────────────────────────────────────────────────────┐
  │ 1. KASIR POS & NOTA TERMAL (Maulana)                                  │
  │    Input pesanan baru -> Checkout anti-double submit -> Cetak struk   │
  └──────────────────────────────────┬────────────────────────────────────┘
                                     ▼
  ┌───────────────────────────────────────────────────────────────────────┐
  │ 2. KATALOG LAYANAN & EKSPOR LAPORAN (Ayin)                            │
  │    Kelola tarif dinamis & paket express -> Ekspor PDF & Excel/CSV     │
  └──────────────────────────────────┬────────────────────────────────────┘
                                     ▼
  ┌───────────────────────────────────────────────────────────────────────┐
  │ 3. PELACAKAN PUBLIK & PEMBAYARAN DIGITAL (Nora)                       │
  │    Lacak nota / scan barcode -> Upload transfer -> Verifikasi kasir   │
  └──────────────────────────────────┬────────────────────────────────────┘
                                     ▼
  ┌───────────────────────────────────────────────────────────────────────┐
  │ 4. TIKET KOMPLAIN & DASHBOARD CUSTOMER (Daysyani)                     │
  │    Lihat histori cucian -> Ajukan komplain noda -> Tindak lanjut staf │
  └──────────────────────────────────┬────────────────────────────────────┘
                                     ▼
  ┌───────────────────────────────────────────────────────────────────────┐
  │ 5. AGREGASI OMSET, HARDENING KEAMANAN & TESTING (Zaky)                │
  │    Dashboard kueri database -> Uji rate limiting 429 -> Terminal test │
  └───────────────────────────────────────────────────────────────────────┘
```

---

### Bagian 1: Maulana Ahmad Bukhori
* **Modul:** Project Leader, Autentikasi Peran, Kasir POS & Cetak Struk
* **Halaman Web yang Dibuka:**
  1. Halaman Login: `https://portal-cuciin.web.id/login`
  2. Kasir POS: `https://portal-cuciin.web.id/admin/transactions/create`
  3. Struk Kasir: `https://portal-cuciin.web.id/admin/transactions/{id}/receipt`
* **Langkah Aksi Layar:**
  1. Tunjukkan halaman login dan masuk menggunakan akun kasir.
  2. Buka menu **Transaksi Baru (POS)**:
     - Pilih nama pelanggan atau tambah data pelanggan cepat.
     - Tambahkan layanan (contoh: Cuci Kiloan Reguler 3 Kg dan Cuci Kemeja Satuan).
     - Masukkan jumlah uang tunai yang diterima (tunjukkan kembalian terhitung otomatis).
  3. Tekan tombol **Proses Transaksi / Bayar**:
     - Sorot tombol checkout yang otomatis terkunci (`:disabled="loading"`) dengan header *Idempotency-Key* agar pesanan tidak terkirim dua kali saat jaringan lambat.
  4. Halaman otomatis mengarahkan ke pratinjau struk thermal kasir:
     - Tunjukkan tata letak struk roll 58mm/80mm lengkap dengan barcode/QR transaksi.
     - Tekan tombol **Cetak Nota** untuk memicu dialog cetak browser.
* **Poin Narasi Rekaman:**
  > "Sistem CUCI.IN mendukung multi-peran: Admin, Kasir, dan Owner. Pada antarmuka kasir POS, staf dapat memproses pesanan kiloan maupun satuan secara simultan. Transaksi diamankan dengan Idempotency Guard untuk mencegah duplikasi nota jika terjadi gangguan jaringan, serta langsung terintegrasi dengan cetak struk termal."

---

### Bagian 2: Gargarina Nanda Iswati (Ayin)
* **Modul:** Katalog Layanan Lanjutan, Mesin Ekspor Laporan & API Resource
* **Halaman Web yang Dibuka:**
  1. Pengelolaan Layanan: `https://portal-cuciin.web.id/admin/services`
  2. Pelaporan Laundry: `https://portal-cuciin.web.id/admin/reports`
* **Langkah Aksi Layar:**
  1. Masuk ke menu **Katalog Layanan**:
     - Tunjukkan daftar paket cucian aktif.
     - Buka modal edit salah satu layanan (tunjukkan pengaturan tarif dinamis, estimasi durasi selesai dalam jam/hari, dan status paket kilat/express).
  2. Pindah ke menu **Laporan Transaksi**:
     - Pilih filter rentang tanggal (misal: 1 bulan terakhir atau hari ini).
     - Klik tombol **Ekspor PDF**: buka berkas PDF yang terunduh dan tunjukkan tabel rekapitulasi ringkas siap cetak.
     - Klik tombol **Ekspor Excel / CSV**: buka berkas spreadsheet yang terunduh, perlihatkan sanitasi keamanan formula injection (tidak ada eksekusi formula berbahaya pada nama atau catatan).
* **Poin Narasi Rekaman:**
  > "Pada panel katalog layanan, pengelola outlet dapat menyesuaikan tarif dinamis dan durasi estimasi cucian kilat. Mesin pelaporan CUCI.IN memfasilitasi ekspor rekap harian ke PDF siap cetak, serta fitur streaming unduhan Excel dan CSV berkinerja tinggi yang telah diproteksi dari celah formula injection."

---

### Bagian 3: Nayla Annora Nobel Widyonarko (Nora)
* **Modul:** Pelacakan Publik, Unggah Bukti Bayar Digital & Verifikasi Kasir
* **Halaman Web yang Dibuka:**
  1. Pelacakan Nota Publik: `https://portal-cuciin.web.id/lookup`
  2. Panel Verifikasi Kasir: `https://portal-cuciin.web.id/admin/payments/verification`
* **Langkah Aksi Layar:**
  1. Buka halaman publik `/lookup` (tanpa login):
     - Masukkan salah satu kode nota transaksi (`TRX-...`).
     - Tunjukkan status cucian yang sedang diproses.
     - Klik tombol scanner kamera untuk memperagakan fitur scan barcode struk (`html5-qrcode`).
  2. Tunjukkan bagian bawah rincian nota yang belum lunas:
     - Pilih metode transfer bank atau QRIS.
     - Unggah file gambar foto struk transfer/bukti bayar, lalu klik **Kirim Bukti Pembayaran**.
  3. Beralih ke layar Kasir pada menu **Verifikasi Pembayaran**:
     - Tunjukkan daftar transaksi yang menunggu verifikasi.
     - Klik tombol pratinjau untuk melihat foto bukti transfer yang diunggah pelanggan tadi.
     - Klik tombol **Setujui / Approve** sehingga status transaksi berubah menjadi Lunas secara instan.
* **Poin Narasi Rekaman:**
  > "Pelanggan dapat melacak status cucian secara mandiri melalui halaman lookup publik atau memindai barcode struk fisik. Pelanggan juga dapat langsung mengunggah foto bukti transfer bank atau QRIS. Di sisi kasir, staf dapat meninjau pratinjau bukti bayar dan menyetujuinya secara instan pada panel verifikasi."

---

### Bagian 4: Daysyani Sophi Masayu
* **Modul:** Modul Tiket Pengaduan Pelanggan (*Complaint Handling*) & Dashboard Customer
* **Halaman Web yang Dibuka:**
  1. Dashboard Customer: `https://portal-cuciin.web.id/customer/dashboard`
  2. Formulir Komplain: `https://portal-cuciin.web.id/complaints/create`
  3. Manajemen Komplain Kasir: `https://portal-cuciin.web.id/admin/complaints`
* **Langkah Aksi Layar:**
  1. Masuk ke **Dashboard Customer** (login sebagai pelanggan):
     - Tunjukkan ringkasan cucian aktif dan riwayat pemesanan pelanggan yang rapi.
  2. Buka menu **Ajukan Pengaduan / Komplain**:
     - Pilih transaksi selesai yang bermasalah.
     - Ketik deskripsi kendala (misal: noda pakaian luntur atau tertinggal).
     - Unggah foto bukti noda pakaian, lalu tekan **Kirim Pengaduan** (sistem otomatis menjalankan kompresi gambar di antrean background).
     - Tunjukkan tiket komplain baru yang muncul dengan status *Menunggu*.
  3. Beralih ke layar Kasir/Admin pada menu **Daftar Komplain**:
     - Buka tiket komplain tersebut, periksa foto noda yang tersimpan aman di disk privat.
     - Ubah status tiket dari *Diproses* menjadi *Selesai* disertai catatan resolusi kasir.
* **Poin Narasi Rekaman:**
  > "Untuk menjamin kepuasan layanan, pelanggan memiliki akses ke Dashboard Customer mandiri dan formulir tiket komplain. Pelanggan cukup memilih nota terkait dan mengunggah foto noda pakaian. Otorisasi data dijaga ketat oleh Laravel Policy agar data komplain dan foto pakaian hanya dapat diakses oleh pemilik transaksi dan staf berwenang."

---

### Bagian 5: Muh. Zaky Dawamul Busro
* **Modul:** Agregasi Database, Hardening Keamanan Sistem & Automated Testing
* **Halaman Web yang Dibuka:**
  1. Dashboard Ringkasan Eksekutif: `https://portal-cuciin.web.id/admin/dashboard`
  2. Halaman Login: `https://portal-cuciin.web.id/login`
  3. Terminal / Command Line Interface (CLI)
* **Langkah Aksi Layar:**
  1. Tunjukkan halaman **Dashboard Admin**:
     - Paparkan kartu ringkasan omset total, jumlah cucian selesai, dan grafik proporsi pembayaran tunai vs nontunai.
     - Tunjukkan respons kueri agregasi database PostgreSQL yang instan dan bebas error tanggal terpotong.
  2. Buka halaman **Login** di tab incognito untuk simulasi keamanan:
     - Masukkan kredensial salah sebanyak 5 kali berturut-turut.
     - Tunjukkan aktivasi pembatasan akses Rate Limiting (*HTTP 429 Too Many Requests*) yang melindungi server dari serangan brute-force.
  3. Buka **Terminal**:
     - Jalankan perintah: `php artisan test`
     - Perlihatkan seluruh rangkaian Feature dan Unit Test (100% Passed / Hijau tanpa galat).
* **Poin Narasi Rekaman:**
  > "Seluruh data operasional diolah melalui kueri agregasi PostgreSQL yang telah dioptimasi dengan indeks komposit. Dari segi keamanan, endpoint autentikasi diproteksi dengan rate limiting ketat untuk mencegah serangan brute force. Kestabilan arsitektur aplikasi divalidasi oleh serangkaian pengujian otomatis PHPUnit yang lulus seratus persen."

---

## 3. Checklist Persiapan Perekaman Layar

1. **Format Perekaman**: Resolusi monitor disetel ke 1080p (1920x1080), zoom browser 100%, rekam menggunakan OBS Studio atau browser capture bawaan (format `.mp4`).
2. **Kesiapan Data Akun (Database)**:
   - Akun Admin: Siap digunakan untuk Ayin & Zaky.
   - Akun Kasir: Siap digunakan untuk Maulana & Nora.
   - Akun Customer: Siap digunakan untuk Daysyani.
   - Siapkan minimal 1 nota status *Belum Lunas* (untuk demo unggah transfer Nora) dan 1 nota status *Selesai* (untuk demo komplain Daysyani).
3. **Penyatuan Video**: Setiap anggota merekam potongan video layar dan audio masing-masing (durasi sekitar 30-40 detik per orang), lalu mengumpulkan berkas rekaman ke Daysyani untuk dirangkai menjadi video demonstrasi utuh berdurasi maksimal 3 menit.
