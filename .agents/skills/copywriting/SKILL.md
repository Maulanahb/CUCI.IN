---
name: copywriting
description: Use when writing, rewriting, or improving marketing copy, landing pages, UI microcopy, and customer- or staff-facing text for CUCI.IN and web applications. Applies to headlines, CTAs, hero sections, value propositions, feature descriptions, form labels, empty states, flash messages, and status updates. Enforces strict anti-AI tone, dual-voice standards (formal for staff/admin, friendly for customer/guest), and zero-hallucinated-claims.
---

# Copywriting Skill

Keahlian ini memandu pembuatan dan penyuntingan naskah (copywriting) yang jelas, persuasif, manusiawi, dan bebas dari klise AI (*anti-AI tells*), baik untuk antarmuka pengguna (UI microcopy) maupun halaman publik CUCI.IN.

---

## 1. Konteks Produk & Ground Truth

Sebelum mulai menulis, baca dokumen konteks produk utama:
- **Wajib baca:** [PRODUCT.md](file:///c:/laragon/www/cuci-in/PRODUCT.md) pada root proyek.
- Jika relevan, rujuk dokumen tim: `docs/prd.md` dan `docs/rules_milestone_2.md`.

### Batasan Bukti & Fakta (Zero Hallucinated Claims)
- **DILARANG mengarang statistik:** Jangan membuat angka palsu seperti "Dipercaya oleh 10.000+ pelanggan" atau "Menghemat waktu hingga 80%".
- **DILARANG mengarang testimoni:** Belum ada laundry mitra atau testimoni nyata di tahap ini.
- **Gunakan fakta fungsional sebagai daya tarik:**
  - Pelacakan status cucian instan tanpa perlu login cukup dengan nomor nota (`TRX-XXXX`).
  - Pencatatan transaksi POS meja kasir cepat dan terhubung langsung ke printer thermal struk.
  - Transparansi status pembayaran (Tunai, Transfer, QRIS) dengan verifikasi bukti bayar.
  - Penanganan tiket pengaduan pelanggan terhubung langsung dengan nomor nota.

---

## 2. Standar Suara & Nada (Dual-Voice Framework)

Sistem CUCI.IN memiliki dua kelompok pengguna dengan kebutuhan komunikasi yang berbeda:

### A. Internal (Staff / Kasir / Admin)
- **Karakter:** Formal, ringkas, operasional, berorientasi aksi (*actionable*).
- **Alasan:** Kasir melayani antrean langsung di depan meja; pesan harus terbaca sekilas tanpa distraksi atau basa-basi.
- **Pedoman Bahasa:** Bahasa Indonesia baku dan lugas.
- **Contoh:**
  - ✅ *"Data pelanggan berhasil disimpan."*
  - ✅ *"Sisa tagihan: Rp25.000 (Belum Lunas)"*
  - ✅ *"Verifikasi Pembayaran"*
  - ✅ *"Cetak Nota Thermal"*
  - ❌ *"Hore! Pelanggan barumu berhasil didaftarkan ke sistem kami yang canggih!"* (Terlalu santai & bertele-tele)

### B. Eksternal (Customer / Guest)
- **Karakter:** Santai-ramah, sopan, menenangkan (*reassuring*), transparan.
- **Alasan:** Pelanggan mengakses via ponsel (mobile web) untuk memastikan pakaian mereka aman dan mengetahui kapan pakaian siap diambil.
- **Pedoman Bahasa:** Bahasa Indonesia komunikatif, akrab namun tetap santun.
- **Contoh:**
  - ✅ *"Cucianmu lagi dicuci dengan teliti."*
  - ✅ *"Pantau cucianmu kapan saja tanpa perlu login. Cukup masukkan nomor nota."*
  - ✅ *"Pakaianmu sudah rapi dan siap diambil di outlet!"*
  - ✅ *"Ada kendala pada pakaianmu? Unggah foto dan beri tahu kami di sini."*
  - ❌ *"Pengguna diwajibkan untuk menginputkan kredensial nota guna memeriksa status entitas cucian."* (Terlalu kaku & robotik)

---

## 3. Diagnosis Situasi Sebelum Menulis

Tentukan salah satu dari tiga situasi kerja sebelum menyusun teks:

| Situasi | Masukan yang Diterima | Peran & Tindakan Copywriter |
|---|---|---|
| **A. Blank / Dari Awal** | Instruksi singkat atau kebutuhan fitur baru tanpa draf | Bangun narasi dan struktur teks dari fakta di `PRODUCT.md`. Fokus pada manfaat nyata dan alur pengguna. |
| **B. Teks Sudah Ada (Refinement)** | Draf kasar yang sudah memiliki poin pikiran | Pertahankan kosakata dan sudut pandang pengembang/klien. Rapikan struktur, buang kata bertele-tele, dan bersihkan dari AI tells. |
| **C. Point Edit (Microcopy)** | Permintaan spesifik ("Perbaiki label tombol ini", "Buat teks empty state") | Ubah hanya elemen yang diminta dengan perubahan minimal di sekitarnya. Jangan mengubah bagian lain yang tidak diminta. |

---

## 4. Gerbang Tiga Pertanyaan (The Three-Question Gate)

Uji setiap kalimat penting, judul, atau CTA dengan 3 pertanyaan ini:
1. **Dapatkah pembaca membayangkan bentuk fisiknya?** (Konkret > Abstrak. *"Nota struk tercetak dari printer thermal"* lebih kuat daripada *"Solusi integrasi output fisik"*).
2. **Dapatkah klaim ini dibuktikan benar atau salah?** (Falsifiable. *"Cek status cucian lewat HP tanpa login"* dapat diuji; *"Layanan terbaik berstandar dunia"* hanyalah hiasan hampa).
3. **Apakah kompetitor bisa menulis kalimat yang persis sama?** (*Swap Test*. Jika kalimat tersebut bisa ditempel di sembarang situs SaaS lain tanpa terasa bedanya, ubah dan tambahkan pembeda spesifik CUCI.IN).

---

## 5. Larangan Pola AI (Strict Anti-AI Tells)

Hindari pola kalimat klise buatan mesin, baik dalam Bahasa Indonesia maupun Bahasa Inggris:

### Pola Terlarang (Banned Patterns)
1. **Formula Kontras Semu (*Contrast Reveals*):**
   - ❌ *"Bukan sekadar aplikasi laundry, melainkan mitra operasional bisnis Anda."*
   - ❌ *"It's not just a tool, it's a partner."*
   - ✅ Nyatakan langsung fungsinya: *"Catat transaksi kasir dan pantau cucian dalam satu sistem."*
2. **Daftar Negasi Beruntun (*Negation Lists*):**
   - ❌ *"Tanpa repot, tanpa ribet, tanpa antrean, tanpa buku nota."*
   - ✅ Nyatakan apa yang terjadi secara positif: *"Cukup ketik nomor nota di HP untuk melihat progres cucianmu."*
3. **Kalimat Berekor Panjang (*Trailing Pile-ons*):**
   - ❌ *"...memudahkan kasir mencatat cucian, menghadirkan transparansi, efisiensi waktu, dan kepuasan pelanggan."*
   - ✅ Putus di klaim utama: *"Kasir mencatat transaksi kiloan dan cetak struk nota dalam hitungan detik."*
4. **Pertanyaan Retoris Terjawab Sendiri (*Self-Answered Colon Reveals*):**
   - ❌ *"Hasilnya? Operasional 2x lebih cepat."*
   - ❌ *"Yang terbaik: pelanggan senang."*
   - ✅ Tulis langsung datanya atau buang format tanya-jawab buatan.
5. **Kata-Kata Klise AI (*High-Frequency AI Vocabulary*):**
   - **Bahasa Indonesia:** `di era digital yang serba cepat ini`, `solusi terdepan`, `menyelami`, `lanskap`, `bukti nyata`, `tak dapat dipungkiri bahwa`, `berperan krusial`, `membuka potensi`, `menghadirkan pengalaman tak terlupakan`, `secara keseluruhan`, `kesimpulannya`.
   - **English:** `in today's fast-paced world`, `seamless`, `unlock the power`, `delve into`, `testament to`, `game-changer`, `robust`, `revolutionize`.
6. **Penghindaran Kata Kerja Biasa (*Copula Avoidance*):**
   - ❌ *"Aplikasi ini berdiri sebagai bukti inovasi digital..."*
   - ✅ *"CUCI.IN mencatat transaksi laundry kiloan dan satuan secara rapi."*

---

## 6. Standar Penulisan UI Microcopy

Microcopy antarmuka adalah teks yang paling sering digunakan dalam pengembangan web CUCI.IN:

### A. Tombol Aksi (CTA Buttons)
- **Rumus:** `[Kata Kerja Aksi] + [Objek Spesifik]`
- **Hindari label ambigu:** *"Kirim"*, *"Submit"*, *"Klik Di Sini"*, *"Proses"*, *"OK"*.
- **Contoh Tombol Meja Kasir (Staff):**
  - ✅ `"Simpan Transaksi"`
  - ✅ `"Cetak Nota Thermal"`
  - ✅ `"Verifikasi Pembayaran"`
  - ✅ `"Tolak Bukti Bayar"`
- **Contoh Tombol Pelanggan (Customer/Guest):**
  - ✅ `"Cek Cucian Sekarang"`
  - ✅ `"Unggah Bukti Transfer"`
  - ✅ `"Ajukan Pengaduan"`
  - ✅ `"Lihat Riwayat Cucian"`

### B. Status Kosong (Empty States)
Empty state yang baik terdiri dari 3 hal:
1. **Kondisi saat ini** (apa yang kosong).
2. **Konteks penjelas** (mengapa kosong atau apa artinya).
3. **Aksi pemandu** (tombol atau tautan langkah berikutnya).
- **Contoh (Riwayat Pengaduan Customer):**
  - *Judul:* *"Belum ada pengaduan aktif"*
  - *Deskripsi:* *"Semua cucianmu berjalan lancar tanpa kendala. Jika ada pakaian yang bernoda atau keluhan layanan, kamu bisa mengajukan pengaduan kapan saja."*
  - *Tombol:* `[Ajukan Pengaduan Baru]`
- **Contoh (Daftar Antrean Cucian Kasir):**
  - *Judul:* *"Tidak ada antrean cucian hari ini"*
  - *Deskripsi:* *"Belum ada transaksi baru yang masuk. Klik tombol di bawah untuk mencatat pelanggan baru."*
  - *Tombol:* `[Buat Transaksi Baru]`

### C. Pesan Notifikasi (Toast / Flash Messages)
- Ringkas, jelas objeknya, dan nyatakan status tindakan.
- **Berhasil:**
  - ✅ *"Status transaksi TRX-1024 diperbarui menjadi Diproses."*
  - ✅ *"Bukti transfer berhasil diverifikasi."*
- **Gagal / Validasi:**
  - ✅ *"Ukuran foto bukti bayar melebihi 2MB. Silakan gunakan foto yang lebih kecil."*
  - ✅ *"Nomor nota tidak ditemukan. Periksa kembali kode pada strukmu."*

### D. Modal Konfirmasi (Confirmation Modals)
- Judul modal harus berupa pertanyaan langsung yang menyebutkan aksinya.
- Teks penjelas menyebutkan konsekuensinya.
- Tombol aksi utama menggunakan kata kerja yang sesuai (bukan hanya "Ya" / "Batal").
- **Contoh:**
  - *Judul:* *"Batalkan transaksi TRX-0042?"*
  - *Deskripsi:* *"Transaksi yang dibatalkan tidak dapat dikembalikan ke antrean cuci. Pastikan pelanggan telah mengetahui pembatalan ini."*
  - *Tombol Batal:* `"Kembali"` | *Tombol Aksi:* `"Ya, Batalkan Transaksi"` (Warna destruktif/merah).

---

## 7. Struktur Teks Halaman (Page Structure Framework)

### Bagian Atas (Hero / Above the Fold)
- **Headline (H1):** Sampaikan nilai utama secara jelas dan terukur.
  - Formula 1: `{Hasil yang diinginkan} tanpa {hambatan utama}`
    - *"Pantau cucian selesai tepat waktu tanpa bolak-balik tanya kasir."*
  - Formula 2: `Langsung ke solusi & tindakan`
    - *"Cek Progres Cucianmu dengan Satu Kode Nota."*
- **Subheadline:** 1–2 kalimat penjelasan konkret.
  - *"Masukkan nomor nota transaksi untuk mengetahui apakah pakaianmu sedang dicuci, disetrika, atau sudah siap diambil di outlet."*
- **Input / CTA Utama:**
  - Field input dengan placeholder yang jelas: `Contoh: TRX-2024-0012`
  - Tombol CTA: `"Lacak Cucian"`

### Bagian Fitur / Layanan
- **Manfaat > Fitur:**
  - Fitur: *Kalkulasi otomatis kiloan dan paket kilat.*
  - Manfaat: *Total harga dan estimasi waktu selesai langsung terhitung otomatis di kasir tanpa kalkulator manual.*
- **Langkah Kerja (How It Works):**
  - Maksimal 3–4 langkah berurutan:
    1. Kasir timbang dan catat cucian.
    2. Pelanggan terima nota struk dengan kode `TRX-XXXX`.
    3. Pantau status cucian dari HP.
    4. Ambil cucian bersih saat status sudah *Selesai*.

---

## 8. Format Output Rekomendasi Copywriting

Saat memberikan rekomendasi teks atau menyunting halaman, susun respons dalam format berikut:

1. **Diagnosis Situasi (1 Baris):** Sebutkan kategori (A, B, atau C) dan alasannya.
2. **Naskah Rekomendasi (Draf):** Dikelompokkan rapi per elemen atau per bagian (Headline, Subheadline, CTA, Microcopy).
3. **Pilihan Alternatif (Opsional jika diminta):** Berikan 2 alternatif headline atau CTA beserta alasan perbedaannya.
4. **Catatan Alasan (Rationale):** Jelaskan mengapa pilihan kata tersebut sesuai dengan prinsip keterbacaan, suara merek CUCI.IN, dan bebas klise AI.

---

## 9. Hubungan dengan Skill Lain di Workspace

- **humanizer**: Gunakan untuk mendeteksi dan membersihkan gaya tulisan yang terlalu kaku atau beraroma terjemahan mesin.
- **frontend-design**: Gunakan untuk memastikan hierarki teks, tipografi (Inter / Outfit), spasi, dan keterbacaan visual diimplementasikan secara optimal pada komponen Blade/Tailwind.
- **impeccable**: Gunakan saat menata alur antarmuka pengguna, micro-copy UX, modal dialog, dan status interaktif.
- **web-design-guidelines**: Pastikan struktur semantik tag (`<h1>`, `<label>`, `<button>`, accessibility WCAG) sesuai standar.
- **cuci-in-dev**: SOP alur kerja pengembangan fitur dan komit Git untuk tim CUCI.IN.