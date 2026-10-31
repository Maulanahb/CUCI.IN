---
name: cuci-in-dev
description: Panduan dan SOP alur kerja pengembangan fitur, pengujian otomatis, dan manajemen komit Git untuk tim pengembang CUCI.IN (Milestone 2) pada Antigravity IDE. Gunakan saat anggota tim (Daysyani, Ayin, Nora, Maulana, Dawamul) mulai mengoding, membuat fitur, menulis test, atau mempersiapkan commit.
---

# Skill: CUCI.IN Milestone 2 Development Workflow

Skill ini memandu asisten AI di Antigravity IDE (AGY) untuk mendampingi anggota tim CUCI.IN mengembangkan fitur Milestone 2 secara presisi, terstruktur, dan sesuai standar rekayasa perangkat lunak.

---

## 1. Langkah Pertama: Identifikasi Anggota Tim

Saat sesi dimulai, kenali siapa pengembang yang sedang berinteraksi:
- **Daysyani Sophi Masayu** (`daysyani` | `syaniday@gmail.com`)
  - **Tugas Utama:** Modul Tiket Pengaduan Pelanggan (*Complaint Handling*) & Dashboard Customer.
  - **Cabang Git:** `feature/complaint-handling`
- **Gargarina Nanda Iswati / Ayin** (`ayinanda` | `ayiniswati22@gmail.com`)
  - **Tugas Utama:** Katalog Layanan Lanjutan & Mesin Ekspor Laporan (PDF/Excel).
  - **Cabang Git:** `feature/advanced-service-catalog` & `feature/export-reports-pdf-excel`
- **Nayla Annora Nobel Widyonarko / Nora** (`nayla annora nobel widyonarko` / `annora1304` | `naylaannora@gmail.com`)
  - **Tugas Utama:** Komponen Upload Bukti Bayar Digital & Panel Verifikasi Kasir.
  - **Cabang Git:** `feature/payment-proof-upload` & `feature/cashier-payment-verification`
- **Maulana Ahmad Bukhori** (`Maulanahb` | `maulanahmad2287@gmail.com`)
  - **Tugas Utama:** Project Leader, Manajemen Hak Akses/Role, Optimasi POS & Koordinasi.
- **Muh. Zaky Dawamul Busro** (`Zakydawamul` | `zakydb091005@gmail.com`)
  - **Tugas Utama:** Mesin Kueri Pelaporan, Optimasi Basis Data & Resolusi Bug Log Backend.

*Jika nama pengembang belum diketahui, tanyakan dengan ramah di awal turn: "Halo! Dengan siapa saya berpasangan hari ini (Daysyani, Ayin, Nora, Dawamul, atau Maulana)?"*

---

## 2. Alur Kerja Standar Pengembangan Fitur (Feature Lifecycle)

Setiap pengembangan fitur harus melalui 5 tahap berurutan:

### Tahap 1: Setup Branch & Identitas Git
Pastikan cabang Git terpisah dan identitas author sesuai:
```bash
# Contoh untuk Daysyani
git checkout develop
git pull origin develop
git checkout -b feature/complaint-handling

git config user.name "daysyani"
git config user.email "syaniday@gmail.com"
```

### Tahap 2: Implementasi Backend & UI
1. **Migration & Model:** Gunakan `php artisan make:migration` dan `php artisan make:model`. Pastikan tipe data dan relasi Eloquent terdefinisi jelas.
2. **Form Request & Controller:** Selalu validasi input menggunakan Form Request terpisah di `app/Http/Requests/`.
3. **Tampilan (Blade + Tailwind + Alpine.js):**
   - Wajib arsitektur **Laravel MPA** (Multi-Page Application).
   - Gunakan layout yang sudah ada (`layouts/app.blade.php`, `layouts/staff.blade.php`).
   - Gunakan komponen Blade dari `resources/views/components/` (misal `<x-badge>`, `<x-button>`).
   - Gunakan Alpine.js untuk interaktivitas ringan (modal, dropdown, tab, pratinjau upload file).

### Tahap 3: Penulisan Pengujian Otomatis (PHPUnit)
Setiap fitur baru WAJIB memiliki pengujian fitur di `tests/Feature/`:
```bash
php artisan make:test --phpunit NamaFiturTest
```
Uji skenario:
- Pengguna yang belum login diarahkan ke login (302 redirect).
- Pengguna dengan role yang salah dilarang akses (403 forbidden).
- Pengguna yang sah berhasil memproses form dan data tersimpan di database.
- Validasi input (misal: format file salah atau kolom wajib kosong).

Jalankan test:
```bash
php artisan test --filter=NamaFiturTest
```

### Tahap 4: Format Kode Otomatis (Laravel Pint)
Sebelum melakukan komit, jalankan linter agar format kode rapi:
```bash
vendor/bin/pint --format agent
```

### Tahap 5: Komit Terstruktur (Conventional Commits)
Pandu pengembang untuk melakukan komit secara modular dan sering (*frequent commits*):
- `feat(complaint): add complaint submission form with transaction code lookup`
- `feat(complaint): handle image upload for damaged clothing proof`
- `feat(complaint): implement complaint status tracking badge`
- `test(complaint): add feature tests for complaint validation and storage`
- `refactor(complaint): clean up complaint controller using form request`

---

## 3. Checklist Sebelum Push / Pull Request

Sebelum cabang fitur digabung ke `develop`:
- [ ] Seluruh test lulus (`php artisan test`).
- [ ] Kode terformat rapi (`vendor/bin/pint --format agent`).
- [ ] Tidak ada berkas sampah atau kredensial rahasia (.env) yang terikut ke Git.
- [ ] Riwayat komit memiliki author yang sesuai dengan anggota tim.
- [ ] Buat Pull Request ke cabang `develop` untuk di-review oleh Maulana / Dawamul.

---

## 4. Standar Arsitektur Pengolahan Data, Ekspor, & Queue (Materi P9)

Bila mendampingi pengembang pada modul pelaporan, ekspor dokumen, upload media, atau pemrosesan antrean, pastikan menerapkan aturan berikut:

1. **Filter Tanggal & Agregasi Laporan (Zaky & Maulana):**
   - Wajib gunakan `$request->date('to')->endOfDay()` agar transaksi di akhir hari tidak terpotong.
   - Hitung nilai rekap/agregasi di database (`selectRaw('SUM(...)')` / `count()`), bukan di memori PHP.
   - Pastikan tabel `transactions` dan `payments` memiliki indeks pada kolom filter tanggal dan status.

2. **Ekspor Laporan (Ayin):**
   - **PDF:** Gunakan `laravel-dompdf` hanya untuk nota cetak atau dokumen ringkas (1–2 halaman).
   - **Excel:** Gunakan `FromQuery` pada Laravel Excel (`maatwebsite/excel`).
   - **CSV (Dataset Besar):** Wajib gunakan pola aliran (*streaming*) `response()->streamDownload()` + `lazyById()` untuk menjaga memori RAM server tetap stabil.

3. **Background Job / Queue (Maulana, Nora, Daysyani):**
   - Tugas berat (kompresi foto bukti transfer, kompresi foto komplain noda, notifikasi status cucian) wajib dikirim ke Queue Job (`make:job`).
   - Terapkan properti: `public int $tries = 3;`, `public array $backoff = [10, 60];`, `public int $timeout = 60;`.
   - Hanya bawa ID model atau path file pada constructor job.
   - Gunakan `->afterCommit()` saat men-dispatch job di dalam transaksi basis data.
   - Panduan alokasi modul tim dan standar arsitektur tersedia di [rules_milestone_2.md](file:///c:/laragon/www/cuci-in/docs/rules_milestone_2.md) dan [prd.md](file:///c:/laragon/www/cuci-in/docs/prd.md) (Seksi 24).

---

## 5. Deployment ke Server Production AWS EC2 (sistem.md)

Dokumentasi lengkap topologi cloud dan server tercatat pada [docs/sistem.md](file:///c:/laragon/www/cuci-in/docs/sistem.md):

- **Domain Utama:** `https://portal-cuciin.web.id` (Cloudflare SSL Flexible $\rightarrow$ AWS EC2 `3.25.167.8`).
- **Path Proyek Server:** `/var/www/cuci-in` (branch `develop`).
- **Basis Data Server:** PostgreSQL 16+ (`cuci_in`), user `postgres`.
- **Perintah Sinkronisasi Kode ke Server (One-Liner):**
  ```bash
  cd /var/www/cuci-in && \
  git pull origin develop && \
  composer install --no-dev --optimize-autoloader && \
  php artisan migrate --force && \
  npm install && \
  npm run build && \
  php artisan optimize && \
  sudo chown -R www-data:www-data storage bootstrap/cache
  ```
- **Eksekusi Queue Worker Background:**
  ```bash
  nohup php /var/www/cuci-in/artisan queue:work --tries=3 --timeout=60 > /dev/null 2>&1 &
  ```

