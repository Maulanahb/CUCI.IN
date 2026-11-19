# DOKUMEN DEFECT & BUG TRACKER — MINGGU 13
**Sistem Informasi Manajemen Laundry: CUCI.IN (Tahap Stabilisasi & QA Hardening)**  
*Mata Kuliah Terintegrasi PBL: Pemrograman Web / Framework Lanjut (PWL), Rekayasa Perangkat Lunak (RPL), dan Penjaminan Mutu Perangkat Lunak (PMPL / QA)*  
*Periode: Minggu 13 (Pasca-Milestone 2 Menuju Rilis Final)*

---

## 1. Konteks & Tujuan Dokumen

Dokumen ini berfungsi sebagai pusat pencatatan, pelacakan (*tracking*), dan remediasi seluruh kendala fungsional (*defects / bugs*) yang ditemukan selama fase penjaminan mutu Minggu 13 pada sistem [CUCI.IN](https://portal-cuciin.web.id). 

Setiap temuan baru wajib dicatat mengikuti matriks tingkat keparahan standar, dialokasikan ke penanggung jawab teknis, dan dilengkapi berkas uji otomatis (*automated test*) sebelum status dinyatakan selesai (*verified*).

---

## 2. Matriks Tingkat Keparahan Bug (Severity & Priority Matrix)

| Tingkat (*Severity*) | Prioritas | Definisi & Kriteria Dampak Operasional | Standar Waktu Remediasi |
| :--- | :--- | :--- | :--- |
| **Critical (Blocker)** | **P0** | Sistem lumpuh total, integritas basis data korup/hilang, kebocoran data privat (IDOR), atau alur pembayaran/transaksi tidak dapat diselesaikan tanpa alternatif solusi (*workaround*). | Wajib diselesaikan seketika (<= 24 jam) sebelum rilis produksi. |
| **Major (High)** | **P1** | Fungsi bisnis inti gagal bekerja, kalkulasi nominal transaksi/order minimum keliru, form master terblokir validasi browser, atau kegagalan validasi masukan data pelanggan. | Prioritas perbaikan sprint berjalan; wajib diuji regresi sebelum serah terima. |
| **Medium (Moderate)** | **P2** | Fungsi sekunder terganggu atau navigasi responsif antarmuka tidak merespons di peramban tertentu, namun alur bisnis inti masih dapat diselesaikan. | Perbaikan terjadwal pada iterasi antarmuka/tampilan berikutnya. |
| **Minor (Low)** | **P3** | Ketidaksesuaian visual/layout minor, typo teks/label, atau tautan menu panduan sekunder yang salah rute. | Diselesaikan bersamaan dengan pemeliharaan rutin (*code cleanup*). |

---

## 3. Tabel Master Rekapitulasi Temuan Bug (Master Defect Log)

*Gunakan tabel ini untuk melihat ringkasan status seluruh defect. Untuk menambahkan temuan baru, gunakan format BUG-XX secara berurutan.*

| ID | Tanggal | Modul / Area | Deskripsi Singkat Masalah | Tingkat | Penemu | PIC Perbaikan | Status | Cabang Git |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **BUG-01** | 2026-11-10 | Staf / Navigasi | Tautan rute panduan kasir mengarah ke URL lama (HTTP 404) | Minor (P3) | Internal QA | Muh. Zaky Dawamul B. | **Fixed** | `fix/backend-bug-log-resolution` |
| **BUG-02** | 2026-11-14 | Keamanan / Tracking | Endpoint pencarian nota publik `/lookup` belum diproteksi rate limiter | Major (P1) | Internal QA | Maulana Ahmad Bukhori | **Fixed** | `fix/tracking-lookup-security` |
| **BUG-03** | 2026-11-15 | Privasi / Pengaduan | Berkas foto komplain tersimpan di disk publik tanpa otorisasi (IDOR) | Critical (P0) | Internal QA | Daysyani Sophi M. | **Fixed** | `fix/complaint-photo-privacy` |
| **BUG-04** | 2026-11-19 | Landing Page / Mobile | Ikon hamburger menu navigasi mobile tidak bisa diklik | Medium (P2) | Esha | Esha | **Fixed** | `fix/mobile-hamburger-navigation` |
| **BUG-05** | 2026-11-19 | Workflow / Kasir | Transaksi lunas melompati status 'Mulai Cuci' langsung ke 'Tandai Diambil' | Major (P1) | Esha | Muh. Zaky Dawamul B. | **Open** | `fix/workflow-status-and-minimum-order` |
| **BUG-06** | 2026-11-19 | Autentikasi / Akun | Registrasi customer menerima nomor telepon 4 digit (`0681`) | Major (P1) | Esha | Esha | **Open** | `fix/customer-phone-validation` |
| **BUG-07** | 2026-11-19 | Master Layanan | Tombol simpan modal ubah layanan macet akibat step mismatch (`10001` vs `10000`) | Major (P1) | Ayin | Gargarina Nanda Iswati | **Open** | `fix/service-modal-step-validation` |
| **BUG-08** | 2026-11-19 | POS / Kalkulasi | Batas minimum order diabaikan pada kasir POS & engine kalkulator | Major (P1) | Ayin | Muh. Zaky Dawamul B. | **Open** | `fix/workflow-status-and-minimum-order` |
| **BUG-09** | 2026-11-19 | Pelacakan / Landing | Input kode nota di landing page tidak diteruskan ke halaman /lookup (wajib ketik ulang) | Medium (P2) | Nayla Annora Nobel W. | Nayla Annora Nobel W. | **Open** | `fix/tracking-lookup-security` |
| **BUG-10** | 2026-11-19 | Autentikasi / UI | Duplikasi ikon mata (show/hide password) pada input kata sandi di browser Chromium/Edge | Minor (P3) | Nayla Annora Nobel W. | Nayla Annora Nobel W. | **Open** | `fix/password-reveal-edge` |
| **BUG-11** | 2026-11-19 | Verifikasi Pembayaran | Pembayaran non-tunai di kasir POS otomatis terverifikasi sepihak sehingga tombol Setujui tidak muncul di panel kasir | Medium (P2) | Nayla Annora Nobel W. | Nayla Annora Nobel W. | **Open** | `fix/cashier-payment-verification-flow` |
| **BUG-12** | 2026-11-19 | Landing Page / Kalkulator | Tarif paket pada kalkulator estimasi di landing page di-hardcode statis sehingga tidak sinkron dengan database | Medium (P2) | Nayla Annora Nobel W. | Esha | **Open** | `fix/landing-calculator-pricing-sync` |

---

## 4. Rincian Teknis & Rencana Tindakan Defect Aktif

---

### BUG-05: Alur Status Pengerjaan Cucian Melompati Tahap Operasional
- **Status:** **Open**
- **Tingkat Keparahan:** **Major (P1)**
- **Pelapor (Penemu):** Esha
- **Penanggung Jawab (PIC):** Muh. Zaky Dawamul Busro (Dawamul)
- **Cabang Git:** `fix/workflow-status-and-minimum-order`
- **Gejala Masalah:**
  Pada transaksi baru di kasir yang langsung dibayar lunas (`payment_status = 'paid'`), tombol aksi primer di header halaman detail nota ([show.blade.php](file:///c:/laragon/www/cuci-in/resources/views/staff/transactions/show.blade.php)) langsung memunculkan opsi `"Tandai Diambil"`. Tombol `"Mulai Cuci"` (status `processing`) dan `"Selesai Cuci"` (status `completed`) tidak dapat diakses secara runut.
- **Langkah Reproduksi:**
  1. Login kasir, buka menu Transaksi Baru.
  2. Input cucian dan pilih opsi bayar lunas langsung (Tunai/QRIS).
  3. Buka detail transaksi di `/staff/transactions/{id}`.
  4. Perhatikan tombol aksi header: langsung muncul status penyerahan pakaian sebelum dicuci.
- **Akar Masalah Teknis:**
  Percabangan logika tombol header pada [show.blade.php](file:///c:/laragon/www/cuci-in/resources/views/staff/transactions/show.blade.php#L228-L242) mengecek kondisi `payment_status === 'paid'` mendahului pengecekan status pengerjaan cucian (`received` -> `processing` -> `completed` -> `picked_up`).
- **Rencana Tindakan:**
  1. Refactor kondisi Blade di [show.blade.php](file:///c:/laragon/www/cuci-in/resources/views/staff/transactions/show.blade.php) agar prioritas pengerjaan operasional selalu diutamakan.
  2. Tambahkan validasi pada controller [TransactionController.php](file:///c:/laragon/www/cuci-in/app/Http/Controllers/Staff/TransactionController.php) untuk mencegah transisi ilegal dari `received` langsung ke `picked_up`.
  3. Tulis skenario pengujian di `tests/Feature/TransactionWorkflowTest.php`.

---

### BUG-06: Pendaftaran Akun Pelanggan Meloloskan Nomor Telepon 4 Digit
- **Status:** **Open**
- **Tingkat Keparahan:** **Major (P1)**
- **Pelapor (Penemu):** Esha
- **Penanggung Jawab (PIC):** Esha
- **Cabang Git:** `fix/customer-phone-validation`
- **Gejala Masalah:**
  Formulir registrasi customer publik (`/register`) menerima nomor telepon pendek seperti `0681` dan langsung membuat akun serta sesi masuk.
- **Langkah Reproduksi:**
  1. Akses form registrasi publik di `/register`.
  2. Isi Nama, Email valid, Password, dan pada nomor telepon isi `0681`.
  3. Klik tombol Daftar Akun. Sistem berhasil memproses pendaftaran.
- **Akar Masalah Teknis:**
  Aturan validasi di [RegisterRequest.php](file:///c:/laragon/www/cuci-in/app/Http/Requests/Auth/RegisterRequest.php#L28) dan [CustomerRequest.php](file:///c:/laragon/www/cuci-in/app/Http/Requests/CustomerRequest.php#L72-L80) hanya memuat `'phone' => ['required', 'string', 'max:20']` tanpa panjang minimal atau pola regex seluler.
- **Rencana Tindakan:**
  1. Pasang aturan regex nomor ponsel Indonesia di [RegisterRequest.php](file:///c:/laragon/www/cuci-in/app/Http/Requests/Auth/RegisterRequest.php):
     ```php
     'phone' => ['required', 'string', 'regex:/^(?:\+62|62|0)8[1-9][0-9]{7,11}$/'],
     ```
  2. Selaraskan validasi yang sama pada [CustomerRequest.php](file:///c:/laragon/www/cuci-in/app/Http/Requests/CustomerRequest.php).
  3. Tambahkan atribut HTML5 `minlength="10" maxlength="15" inputmode="tel"` di [register.blade.php](file:///c:/laragon/www/cuci-in/resources/views/auth/register.blade.php).
  4. Tambahkan feature test di [RegistrationTest.php](file:///c:/laragon/www/cuci-in/tests/Feature/Auth/RegistrationTest.php).

---

### BUG-07: Form Ubah Data Layanan Macet Akibat Step Mismatch HTML5
- **Status:** **Open**
- **Tingkat Keparahan:** **Major (P1)**
- **Pelapor (Penemu):** Gargarina Nanda Iswati (Ayin)
- **Penanggung Jawab (PIC):** Gargarina Nanda Iswati (Ayin)
- **Cabang Git:** `fix/service-modal-step-validation`
- **Gejala Masalah:**
  Pada modal Ubah Data Layanan di `/admin/services`, tombol "Simpan Perubahan" gagal submit dan memunculkan error validasi native browser: *"Please enter a valid value. The two nearest valid values are 25.6 and 26.1"*. Input nominal `10000` ditolak, sedangkan `10001` lolos.
- **Langkah Reproduksi:**
  1. Login sebagai Admin, buka `/admin/services`.
  2. Klik ikon/tombol Ubah pada salah satu layanan.
  3. Masukkan tarif Rp 10.000 atau order minimum 26.
  4. Klik tombol Simpan Perubahan. Browser memblokir submit form.
- **Akar Masalah Teknis:**
  Pada [services/index.blade.php](file:///c:/laragon/www/cuci-in/resources/views/services/index.blade.php#L813-L864):
  - Input tarif satuan menggunakan `min="1" step="100"`: kelipatan dimulai dari 1 (`1 + 100k`), sehingga angka bulat kelipatan 100 seperti `10000` dinyatakan invalid oleh browser (`stepMismatch: true`).
  - Input order minimum menggunakan `min="0.1" step="0.5"`: kelipatan dimulai dari 0.1 (`0.1 + 0.5k`), sehingga angka bulat genap/ganjil seperti `1, 2, 26` dinyatakan invalid.
- **Rencana Tindakan:**
  1. Ubah tarif satuan menjadi `min="0"` dan `step="1"` (atau `step="100"` dengan `min="0"`).
  2. Ubah minimum order menjadi `min="0.1"` dan `step="any"` atau `step="0.1"`.
  3. Pastikan state Alpine.js tombol submit `:disabled` aktif secara dinamis saat input berubah tanpa perlu menekan tombol Enter tambahan.

---

### BUG-08: Batas Minimum Order Diabaikan pada POS Kasir & Service Kalkulator
- **Status:** **Open**
- **Tingkat Keparahan:** **Major (P1)**
- **Pelapor (Penemu):** Gargarina Nanda Iswati (Ayin)
- **Penanggung Jawab (PIC):** Muh. Zaky Dawamul Busro (Dawamul)
- **Cabang Git:** `fix/workflow-status-and-minimum-order`
- **Gejala Masalah:**
  Jika layanan kiloan memiliki ketentuan `minimum_order = 26 kg`, pesanan dengan berat 1 kg tetap dihitung $1 \times \text{tarif}$ (Rp 10.000), bukan dikenakan ambang batas minimum $26 \times \text{tarif}$ (Rp 260.000).
- **Langkah Reproduksi:**
  1. Buka menu Transaksi Baru di kasir.
  2. Pilih layanan kiloan yang memiliki batas minimum order (misal 26 kg).
  3. Masukkan berat 1 kg.
  4. Periksa ringkasan subtotal: dihitung Rp 10.000 (tidak menerapkan aturan batas minimum).
- **Akar Masalah Teknis:**
  Pada [TransactionCalculator.php](file:///c:/laragon/www/cuci-in/app/Services/TransactionCalculator.php#L87-L95) dan script Alpine.js [create.blade.php](file:///c:/laragon/www/cuci-in/resources/views/staff/transactions/create.blade.php#L1060-L1085), perkalian subtotal langsung mengalikan `weight * unit_price` tanpa fungsi batas minimum `max($weight, $minimumOrder)`.
- **Rencana Tindakan:**
  1. Terapkan perhitungan kuantitas tagihan efektif pada [TransactionCalculator.php](file:///c:/laragon/www/cuci-in/app/Services/TransactionCalculator.php):
     ```php
     $billableWeight = max((float) $item['weight'], (float) ($service->minimum_order ?? 0));
     $subtotal = bcmul((string) $unitPrice, (string) $billableWeight, 2);
     ```
  2. Terapkan logika yang sama pada kasir POS [create.blade.php](file:///c:/laragon/www/cuci-in/resources/views/staff/transactions/create.blade.php).
  3. Tampilkan label indikator `(Dikenakan minimum order X kg)` jika kuantitas riil di bawah batas.
  4. Tulis test case di `tests/Feature/TransactionCalculatorTest.php`.

---

### BUG-09: Input Kode Transaksi di Landing Page Hilang / Wajib Ketik Ulang di /lookup
- **Status:** **Open**
- **Tingkat Keparahan:** **Medium (P2)**
- **Pelapor (Penemu):** Nayla Annora Nobel Widyonarko (Nora)
- **Penanggung Jawab (PIC):** Nayla Annora Nobel Widyonarko (Nora)
- **Cabang Git:** `fix/tracking-lookup-security`
- **Gejala Masalah:**
  Pengguna yang mengisi kode nota transaksi pada kotak pencarian cepat di Hero Section Landing Page (`/`), lalu mengklik tombol "Lacak Sekarang", diarahkan ke `/lookup` dengan input kosong. Transaksi tidak otomatis terlacak dan pengguna dipaksa mengetik ulang kode yang sama dari awal.
- **Langkah Reproduksi:**
  1. Buka landing page (`/`).
  2. Pada input Hero "Masukkan kode nota (contoh: TRX-20261024-0001)...", ketikkan kode nota valid.
  3. Klik tombol "Lacak Sekarang".
  4. Halaman berpindah ke `/lookup`, namun input kode nota kembali kosong dan data cucian belum ditampilkan.
- **Akar Masalah Teknis:**
  Inkonsistensi nama parameter input query GET antar-komponen:
  - Pada [welcome.blade.php](file:///c:/laragon/www/cuci-in/resources/views/welcome.blade.php#L166), form menggunakan `<input name="transaction_code">`. URL hasil submit adalah `/lookup?transaction_code=TRX-...`.
  - Pada [TransactionLookupController.php](file:///c:/laragon/www/cuci-in/app/Http/Controllers/TransactionLookupController.php#L21), controller hanya membaca `$request->input('code', '')`.
  - Akibatnya variabel `$code` bernilai string kosong `""`, blok pelacakan `$transaction` tidak dieksekusi, dan form [resources/views/lookup/_content.blade.php](file:///c:/laragon/www/cuci-in/resources/views/lookup/_content.blade.php#L16) merender `value=""`.
- **Rencana Tindakan:**
  1. Ganti atribut `name="transaction_code"` menjadi `name="code"` pada [welcome.blade.php](file:///c:/laragon/www/cuci-in/resources/views/welcome.blade.php).
  2. Tambahkan kompatibilitas mundur (*backward compatibility*) di [TransactionLookupController.php](file:///c:/laragon/www/cuci-in/app/Http/Controllers/TransactionLookupController.php):
     ```php
     $code = trim((string) ($request->input('code') ?? $request->input('transaction_code', '')));
     ```
  3. Buat skenario pengujian di `tests/Feature/TransactionLookupTest.php` untuk memastikan parameter `code` maupun `transaction_code` langsung mengeksekusi pencarian nota.

---

### BUG-10: Duplikasi Ikon Mata (Show/Hide Password) pada Input Sandi di Browser Edge/Chromium
- **Status:** **Open**
- **Tingkat Keparahan:** **Minor (P3)**
- **Pelapor (Penemu):** Nayla Annora Nobel Widyonarko (Nora)
- **Penanggung Jawab (PIC):** Nayla Annora Nobel Widyonarko (Nora)
- **Cabang Git:** `fix/password-reveal-edge` (atau digabung ke `fix/tracking-lookup-security`)
- **Gejala Masalah:**
  Saat pengguna mengetikkan kata sandi pada halaman login (`/login`) atau register (`/register`) menggunakan browser Microsoft Edge atau Chromium di Windows, muncul dua ikon mata sekaligus yang saling berdampingan di sisi kanan kolom input password.
- **Langkah Reproduksi:**
  1. Buka peramban Microsoft Edge di Windows.
  2. Buka halaman `/login` atau `/register`.
  3. Ketikkan beberapa karakter pada kolom input Password.
  4. Perhatikan sisi kanan input: muncul ikon mata bawaan browser (native reveal) di sebelah kiri ikon mata kustom Alpine.js/Lucide.
- **Akar Masalah Teknis:**
  Browser berbasis Chromium/Edge pada Windows secara otomatis menambahkan pseudo-elemen `::-ms-reveal` dan `::-ms-clear` ke dalam `<input type="password">`. Bersamaan dengan itu, antarmuka Blade [login.blade.php](file:///c:/laragon/www/cuci-in/resources/views/auth/login.blade.php#L82-L90) juga meletakkan tombol kustom `<x-lucide-eye>` di posisi `absolute right-0`, sehingga kedua tombol tampil ganda dan bertumpukan.
- **Rencana Tindakan:**
  1. Tambahkan CSS reset pada [resources/css/app.css](file:///c:/laragon/www/cuci-in/resources/css/app.css):
     ```css
     input[type="password"]::-ms-reveal,
     input[type="password"]::-ms-clear {
         display: none;
     }
     ```
  2. Pastikan pseudo-elemen tersebut disembunyikan di semua form autentikasi ([login.blade.php](file:///c:/laragon/www/cuci-in/resources/views/auth/login.blade.php), [register.blade.php](file:///c:/laragon/www/cuci-in/resources/views/auth/register.blade.php), [reset-password.blade.php](file:///c:/laragon/www/cuci-in/resources/views/auth/reset-password.blade.php)).

---

### BUG-11: Pembayaran Non-Tunai Kasir POS Otomatis Terverifikasi Sepihak Tanpa Opsi Approval
- **Status:** **Open**
- **Tingkat Keparahan:** **Medium (P2)**
- **Pelapor (Penemu):** Nayla Annora Nobel Widyonarko (Nora)
- **Penanggung Jawab (PIC):** Nayla Annora Nobel Widyonarko (Nora)
- **Cabang Git:** `fix/cashier-payment-verification-flow`
- **Gejala Masalah:**
  Saat kasir mencatat pembayaran non-tunai (Transfer Bank / QRIS) pada menu Kasir POS (`/transactions/create`) dan mengunggah foto struk/bukti, pembayaran tersebut secara sepihak langsung berstatus `Terverifikasi`. Akibatnya, antrean verifikasi di `/admin/payments` selalu kosong (0 data pending), dan tombol aksi persetujuan ("Setujui Pembayaran" / "Tolak Bukti") tidak pernah muncul di panel kasir, bertentangan dengan panduan skenario uji QA/UAT.
- **Langkah Reproduksi:**
  1. Login sebagai kasir/admin, buka menu Transaksi Baru di Kasir POS.
  2. Input data cucian, pilih metode bayar QRIS atau Transfer Bank, dan unggah foto bukti transfer.
  3. Simpan transaksi, lalu buka menu Verifikasi Pembayaran (`/admin/payments`).
  4. Tab "Menunggu" kosong, data langsung masuk ke tab "Terverifikasi". Saat modal detail dibuka, tombol "Setujui" dan "Tolak" disembunyikan.
- **Akar Masalah Teknis:**
  1. Pada backend service [RecordPayment.php](file:///c:/laragon/www/cuci-in/app/Services/RecordPayment.php#L73), sistem secara permanen menetapkan `$verificationStatus = 'Terverifikasi'` untuk setiap transaksi non-tunai kasir.
  2. Form checkout POS [create.blade.php](file:///c:/laragon/www/cuci-in/resources/views/staff/transactions/create.blade.php) tidak menyediakan opsi checkbox verifikasi (berbeda dengan form pelunasan di detail nota yang memiliki opsi uncheck untuk verifikasi mutasi oleh Owner).
  3. Di sisi antarmuka [payments/index.blade.php](file:///c:/laragon/www/cuci-in/resources/views/staff/payments/index.blade.php#L329), tombol persetujuan hanya dirender jika `is_pending === true`.
- **Rencana Tindakan:**
  1. Sediakan opsi/kendali pada checkout POS atau sesuaikan [RecordPayment.php](file:///c:/laragon/www/cuci-in/app/Services/RecordPayment.php) agar jika kasir membutuhkan verifikasi mutasi oleh Admin/Owner (atau saat mode uji approval), pembayaran masuk sebagai `'Menunggu Verifikasi'`.
  2. Pastikan transaksi berstatus `'Menunggu Verifikasi'` muncul di tab "Menunggu" `/admin/payments` dan menampilkan tombol aksi "Setujui Pembayaran" serta "Tolak Bukti".
  3. Tulis skenario pengujian di `tests/Feature/CashierPaymentVerificationTest.php`.

---

### BUG-12: Tarif & Layanan pada Kalkulator Landing Page Hardcoded Statis (Tidak Sinkron Database)
- **Status:** **Open**
- **Tingkat Keparahan:** **Medium (P2)**
- **Pelapor (Penemu):** Nayla Annora Nobel Widyonarko (Nora)
- **Penanggung Jawab (PIC):** Esha
- **Cabang Git:** `fix/landing-calculator-pricing-sync`
- **Gejala Masalah:**
  Pilihan paket dan tarif pada Kalkulator Estimasi Biaya Laundry di Hero Section Landing Page (`#estimasi`) tidak terhubung ke basis data. Tarif yang tampil adalah data statis (Reguler Rp 6.000/kg, Express Rp 10.000/kg, Super Express Rp 15.000/kg, Setrika Rp 4.000/kg). Jika Admin memperbarui harga di master data `/admin/services`, angka kalkulator di depan tidak pernah berubah, berpotensi menimbulkan keluhan selisih tarif (*misleading pricing*) bagi calon pelanggan.
- **Langkah Reproduksi:**
  1. Buka landing page (`/`) lalu scroll ke bagian Kalkulator Estimasi (`#estimasi`).
  2. Perhatikan tarif per kg pada setiap kartu paket (misal Cuci Kering Setrika tertulis Rp 6.000/kg).
  3. Buka master layanan `/admin/services` sebagai Admin: tarif riil di database berbeda (misal Rp 10.001/kg).
  4. Ubah harga layanan di Admin: kalkulator landing page tidak berubah karena tidak memuat data dinamis dari model `Service`.
- **Akar Masalah Teknis:**
  Rute `/` di [routes/web.php](file:///c:/laragon/www/cuci-in/routes/web.php#L31) langsung merender view `welcome.blade.php` tanpa me-load data model `Service::where('is_active', true)->get()`. Di sisi tampilan [welcome.blade.php](file:///c:/laragon/www/cuci-in/resources/views/welcome.blade.php#L225-L260), array objek layanan didefinisikan secara hardcoded pada `x-data`.
- **Rencana Tindakan:**
  1. Pada [routes/web.php](file:///c:/laragon/www/cuci-in/routes/web.php#L31), lewatkan koleksi layanan aktif ke view:
     ```php
     $services = Service::where('is_active', true)->orderBy('unit_price')->get();
     return view('welcome', compact('services'));
     ```
  2. Pada [welcome.blade.php](file:///c:/laragon/www/cuci-in/resources/views/welcome.blade.php), inisialisasi properti Alpine.js `services` secara dinamis menggunakan `@json($services)`.
  3. Terapkan perhitungan batas bawah order minimum:
     ```javascript
     const billable = Math.max(this.weight, this.current.minimum_order || 0);
     this.total = Math.round(billable * this.current.unit_price);
     ```
  4. Buat feature test di `tests/Feature/LandingPageTest.php` untuk memastikan layanan dinamis dari database ter-render pada kalkulator.

---

## 5. Panduan Menambahkan Temuan Baru (Template Standar)

*Bila menemukan bug baru selama pengujian Minggu 13, tambahkan baris pada Tabel Master (Seksi 3) dan sertakan kartu temuan di bawah ini:*

```markdown
### BUG-XX: [Judul Singkat Masalah]
- **Status:** Open / In Progress / Fixed / Verified
- **Tingkat Keparahan:** Critical (P0) / Major (P1) / Medium (P2) / Minor (P3)
- **Pelapor (Penemu):** [Nama Penemu]
- **Penanggung Jawab (PIC):** [Nama Anggota Tim]
- **Cabang Git:** `fix/[nama-cabang]`
- **Gejala Masalah:**
  [Deskripsi detail apa yang terjadi dan bagaimana dampaknya]
- **Langkah Reproduksi:**
  1. [Langkah 1]
  2. [Langkah 2]
  3. [Langkah 3]
- **Akar Masalah Teknis:**
  [Penyebab teknis di file/kode terkait]
- **Rencana Tindakan:**
  1. [Langkah perbaikan 1 di file target]
  2. [Penulisan skenario uji PHPUnit / Browser]
```

---
*Dokumen ini merupakan artefak resmi pelacakan mutu perangkat lunak CUCI.IN Minggu 13.*
