# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

- **Admin (pemilik laundry / owner):** satu peran yang sama. Mengelola master data (pengguna, pelanggan, layanan, harga, estimasi durasi), memantau transaksi dan operasional, membaca dashboard dan laporan keuangan.
- **Staff (kasir / pegawai):** menjalankan operasional harian di meja kasir: mencatat pelanggan, membuat transaksi POS, menimbang, menerima pembayaran, mencetak nota thermal, memperbarui status cucian, menangani pengaduan.
- **Customer (terdaftar):** melihat transaksi miliknya, riwayat, status cucian, dan pengaduan miliknya.
- **Guest (tanpa akun):** mengecek transaksi dengan kode nota (`TRX-XXXX`) tanpa login, mengajukan pengaduan terkait transaksi, dan dapat mendaftar menjadi customer.

Kode menyimpan role sebagai `admin`, `staff`, `customer`. Guest tidak disimpan sebagai data pengguna. Tidak ada role Owner terpisah; "Owner" dan "Kasir" di dokumen tim berarti Admin dan Staff.

## Product Purpose

CUCI.IN adalah sistem manajemen operasional laundry berbasis web yang menggantikan pencatatan manual (buku nota). Tujuannya: transaksi dan pembayaran tercatat terstruktur, pencarian data cepat, tidak ada duplikasi atau salah catat, dan status cucian bisa diketahui pelanggan kapan saja.

Sukses berarti alur utama berjalan utuh: Staff login → pelanggan → layanan → transaksi → perhitungan → pembayaran → Diterima → Diproses → Selesai → Diambil → Customer/Guest cek status.

## Positioning

Semua dalam satu sistem: kasir mencatat transaksi, status cucian diperbarui di sistem yang sama, dan pelanggan atau guest langsung bisa mengecek status cuciannya dengan kode nota tanpa perlu login.

## Operating Context

- **Satu outlet** laundry kiloan kecil.
- **Kasir** bekerja di meja depan memakai PC/laptop yang terhubung ke **printer thermal** untuk nota struk.
- **Pelanggan** mengecek status dan riwayat lewat **HP** (mobile web).
- **Admin/owner** memantau dashboard dan laporan, termasuk ekspor laporan ke PDF dan Excel.
- Proyek akademik D4 Sistem Informasi Bisnis dengan milestone M1 (validasi desain), M2 (produk fungsional, minggu 12), M3 (produk final, penyempurnaan UI/UX, minggu 16).

## Capabilities and Constraints

- **Stack tetap:** Laravel MPA, Blade, Tailwind CSS, Alpine.js (Livewire diizinkan oleh Charter). Tanpa React, Vue, SPA, atau REST API sebagai arsitektur utama. Database PostgreSQL.
- **Status transaksi:** Diterima, Diproses, Selesai, Diambil, Dibatalkan. Pembatalan hanya pada tahap awal (aturan bisnis, bukan status).
- **Pembayaran:** Tunai, Transfer, QRIS; penuh, DP, pelunasan; satu transaksi boleh punya beberapa pembayaran. Status verifikasi: Tidak Diperlukan, Menunggu Verifikasi, Terverifikasi, Ditolak. Tunai tidak perlu verifikasi. Nilai bayar tidak boleh melebihi sisa tagihan. Tidak memakai payment gateway.
- **Bukti pembayaran:** pelanggan mengunggah bukti transfer/QRIS; kasir/admin menyetujui atau menolak.
- **Pengaduan:** wajib terkait transaksi; status Menunggu, Diproses, Selesai; boleh menyertakan foto kendala; batas penanganan maksimal 2 hari.
- **Data historis tidak dihapus:** akun dan layanan dinonaktifkan via `is_active`; transaksi tidak dihapus; tanpa SoftDeletes.
- **Terminologi:** gunakan istilah bahasa Indonesia yang sudah dipakai di kode (nota, kode transaksi, status cucian, pengaduan, kasir).

## Brand Commitments

- Nama produk: **CUCI.IN**.
- **Voice:** bahasa Indonesia. **Formal** untuk Staff/Admin ("Data pelanggan berhasil disimpan"), **santai-ramah** untuk Customer/Guest ("Cucianmu lagi diproses").
- Aset logo yang sudah ada: `public/logo.svg`, `public/logos/`.

## Evidence on Hand

- Dokumen produk: `docs/project_charter.md`, `docs/prd.md`, `docs/rules_milestone_2.md`.
- Belum ada laundry mitra, testimoni, data pelanggan nyata, statistik penggunaan, atau harga resmi. Jangan mengarang klaim, jumlah pelanggan, atau testimoni.

## Product Principles

1. **Meja kasir dulu.** Alur POS harus cepat dan minim langkah, karena kasir melayani pelanggan yang sedang menunggu di depan meja.
2. **Status selalu jujur dan terbaca.** Pelanggan harus bisa tahu posisi cuciannya dalam beberapa detik dari HP, tanpa login.
3. **Uang harus jelas.** Total, sudah dibayar, sisa tagihan, dan status verifikasi selalu terlihat, tidak boleh ambigu.
4. **Riwayat tidak hilang.** Setiap perubahan status, pembayaran, dan pengaduan tercatat; tidak ada penghapusan yang mengaburkan histori.
5. **Satu sistem, satu sumber data.** Kasir, admin, dan pelanggan melihat data yang sama dari tempat yang sama.
