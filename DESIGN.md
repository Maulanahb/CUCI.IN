---
name: CUCI.IN
description: Sistem manajemen operasional laundry kiloan — meja kasir yang rapi, status cucian yang jelas.
colors:
  laundry-teal: "#0e7490"
  laundry-teal-deep: "#155e75"
  laundry-teal-ink: "#164e63"
  fresh-aqua: "#06b6d4"
  aqua-mist: "#ecfeff"
  aqua-foam: "#cffafe"
  aqua-edge: "#a5f3fc"
  counter-white: "#ffffff"
  floor-slate: "#f8fafc"
  hairline-slate: "#e2e8f0"
  quiet-slate: "#64748b"
  body-slate: "#1e293b"
  heading-slate: "#0f172a"
  status-info: "#0369a1"
  status-info-bg: "#f0f9ff"
  status-warning: "#b45309"
  status-warning-bg: "#fffbeb"
  status-success: "#047857"
  status-success-bg: "#ecfdf5"
  status-danger: "#be123c"
  status-danger-bg: "#fff1f2"
  danger-solid: "#e11d48"
typography:
  display:
    fontFamily: "Instrument Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.875rem"
    fontWeight: 800
    lineHeight: 1.2
    letterSpacing: "-0.025em"
  headline:
    fontFamily: "Instrument Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 800
    lineHeight: 1.55
  title:
    fontFamily: "Instrument Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1rem"
    fontWeight: 600
    lineHeight: 1.5
  body:
    fontFamily: "Instrument Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 400
    lineHeight: 1.43
  label:
    fontFamily: "Instrument Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 700
    lineHeight: 1.33
    letterSpacing: "0.05em"
  code:
    fontFamily: "ui-monospace, SFMono-Regular, Menlo, Consolas, monospace"
    fontSize: "1.125rem"
    fontWeight: 800
rounded:
  lg: "8px"
  xl: "12px"
  2xl: "16px"
  full: "9999px"
spacing:
  xs: "4px"
  sm: "8px"
  md: "12px"
  lg: "16px"
  xl: "20px"
  2xl: "24px"
components:
  button-primary:
    backgroundColor: "{colors.laundry-teal}"
    textColor: "{colors.counter-white}"
    typography: "{typography.body}"
    rounded: "{rounded.xl}"
    padding: "0 16px"
    height: "44px"
  button-primary-hover:
    backgroundColor: "{colors.laundry-teal-deep}"
  button-secondary:
    backgroundColor: "{colors.floor-slate}"
    textColor: "{colors.body-slate}"
    rounded: "{rounded.xl}"
    padding: "0 16px"
    height: "44px"
  button-soft:
    backgroundColor: "{colors.aqua-mist}"
    textColor: "{colors.laundry-teal}"
    rounded: "{rounded.xl}"
    padding: "0 16px"
    height: "44px"
  button-danger:
    backgroundColor: "{colors.danger-solid}"
    textColor: "{colors.counter-white}"
    rounded: "{rounded.xl}"
    padding: "0 16px"
    height: "44px"
  button-sm:
    rounded: "{rounded.lg}"
    padding: "0 12px"
    height: "36px"
  input-text:
    backgroundColor: "{colors.floor-slate}"
    textColor: "{colors.heading-slate}"
    rounded: "{rounded.xl}"
    padding: "10px 14px"
  card:
    backgroundColor: "{colors.counter-white}"
    rounded: "{rounded.2xl}"
    padding: "20px"
  badge-brand:
    backgroundColor: "{colors.aqua-mist}"
    textColor: "{colors.laundry-teal}"
    typography: "{typography.label}"
    rounded: "{rounded.full}"
    padding: "4px 10px"
  nav-item-active:
    backgroundColor: "{colors.aqua-mist}"
    textColor: "{colors.laundry-teal}"
    rounded: "{rounded.lg}"
    padding: "8px 12px"
---

# Design System: CUCI.IN

## Overview

**Creative North Star: "The Clean Counter"**

CUCI.IN harus terasa seperti meja kasir laundry yang rapi: setiap barang punya tempatnya, tidak ada yang menumpuk, dan siapa pun yang berdiri di depannya langsung tahu apa yang terjadi. Latar lantai slate yang sangat terang menampung kartu putih bersih; satu warna teal laundry menandai apa yang bisa ditekan dan di mana kita sedang berada. Sisanya tenang.

Kepadatan mengikuti peran. Layar staff dan admin padat dan bisa dipindai cepat (teks data 12–14px, tabel ringkas, label kecil berhuruf kapital), karena kasir bekerja sambil melayani pelanggan yang menunggu. Layar publik (cek transaksi) lebih lapang dan terpusat, dibaca di HP dalam beberapa detik.

Komponen rapi dan percaya diri: sudut lembut, warna solid, tanpa efek berlebihan. Status (cucian, pembayaran, pengaduan) selalu disampaikan dengan lencana berwarna semantik, bukan hanya teks.

**Key Characteristics:**
- Satu warna brand (Laundry Teal) untuk aksi utama dan navigasi aktif.
- Kartu putih di atas latar slate terang, dipisahkan border tipis dan bayangan nyaris tak terlihat.
- Instrument Sans untuk semua teks; monospace hanya untuk kode transaksi.
- Label bagian kecil, tebal, kapital, berjarak huruf lebar.
- Warna status semantik yang konsisten di seluruh peran.

## Colors

Satu keluarga Aqua Teal (air, kebersihan, kesegaran) di atas netral slate yang dingin, ditambah empat nada status semantik.

### Primary
- **Laundry Teal** (laundry-teal): tombol utama, item navigasi aktif, tautan, garis progres stepper, cincin fokus. Warna yang berarti "di sini kamu bertindak".
- **Laundry Teal Deep** (laundry-teal-deep): status hover tombol utama dan tautan.
- **Laundry Teal Ink** (laundry-teal-ink): teks penekanan pada langkah aktif stepper.
- **Fresh Aqua** (fresh-aqua): aksen kecil saja, seperti ".IN" pada wordmark. Kontrasnya terlalu rendah untuk teks isi di atas putih.
- **Aqua Mist / Aqua Foam / Aqua Edge** (aqua-mist, aqua-foam, aqua-edge): permukaan lembut untuk navigasi aktif, tombol `soft`, lencana brand, avatar inisial, dan cincin langkah stepper.

### Neutral
- **Counter White** (counter-white): permukaan kartu, sidebar, header.
- **Floor Slate** (floor-slate): latar halaman dan latar input saat diam.
- **Hairline Slate** (hairline-slate): border kartu, pemisah, border input.
- **Quiet Slate** (quiet-slate): teks sekunder, label bagian, keterangan bantuan.
- **Body Slate** (body-slate): teks isi.
- **Heading Slate** (heading-slate): judul, angka total, nilai penting.

### Status
- **Info** (status-info / status-info-bg): status cucian Diterima.
- **Warning** (status-warning / status-warning-bg): Diproses, pembayaran DP.
- **Success** (status-success / status-success-bg): Selesai, Lunas, pesan sukses.
- **Danger** (status-danger / status-danger-bg / danger-solid): Dibatalkan, Belum Bayar, sisa tagihan, error, tombol hapus/tolak.

### Named Rules
**The One Teal Rule.** Laundry Teal hanya untuk aksi utama, navigasi aktif, dan progres. Jangan jadikan latar besar atau dekorasi; kelangkaannya yang membuat tombol utama mudah ditemukan.

**The Status Means Status Rule.** Hijau, kuning, biru, dan merah hanya dipakai untuk status dan umpan balik. Jangan pakai sebagai warna dekoratif atau aksen kartu.

## Typography

**Display Font:** Instrument Sans (fallback ui-sans-serif, system-ui)
**Body Font:** Instrument Sans
**Label/Mono Font:** ui-monospace untuk kode transaksi

**Character:** Satu keluarga sans yang bersih dan sedikit humanis; hierarki dibangun dari bobot (400 → 800) dan ukuran, bukan dari pergantian font.

### Hierarchy
- **Display** (800, 30px di desktop / 24px di HP, line-height 1.2, tracking rapat): judul utama halaman publik seperti "Cek Status Cucian Anda".
- **Headline** (800, 18px): nilai status utama, total transaksi.
- **Title** (600, 16–18px): judul halaman di header aplikasi.
- **Body** (400–600, 14px): teks isi, item navigasi, tombol ukuran md. Paragraf deskripsi maksimal sekitar 65ch.
- **Label** (700, 12px, tracking 0.05em, KAPITAL): label bagian seperti "STATUS CUCIAN", "KODE TRANSAKSI", kepala tabel.
- **Code** (800, monospace, 16–18px): kode transaksi `TRX-XXXX`, selalu monospace agar mudah dicocokkan dengan nota fisik.

### Named Rules
**The Receipt Code Rule.** Kode transaksi selalu tampil dalam monospace tebal. Itu jembatan antara layar dan nota thermal.

## Layout

Aplikasi staff/admin/customer memakai sidebar tetap 256px di desktop yang berubah menjadi laci geser dengan overlay di bawah breakpoint `lg` (1024px). Header lengket 64px berisi judul halaman dan slot aksi. Konten dibatasi lebar maksimum 1280px dengan padding 16px (HP) dan 24px (≥640px).

Halaman publik (cek transaksi) memakai kolom tunggal terpusat, maksimal 896px, dengan kartu formulir 672px dan hasil 768px. Halaman auth memakai kolom 440px di tengah layar.

Ritme spasi berbasis kelipatan 4px: 12px antar elemen dalam kartu, 20–24px padding kartu, 24–32px antar bagian.

## Elevation & Depth

Datar dan tenang. Kedalaman disampaikan lewat perbedaan latar (Floor Slate vs Counter White) dan border hairline, bukan bayangan. Bayangan hanya `shadow-xs` sangat tipis untuk memisahkan kartu dari latar. Satu-satunya efek blur adalah header lengket semi-transparan (putih 80–90% dengan backdrop-blur) agar konten yang digulir tetap terbaca di bawahnya.

### Shadow Vocabulary
- **Card rest** (`box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05)`): semua kartu dan panel saat diam.
- **Hairline lift** (`box-shadow: 0 1px rgb(0 0 0 / 0.05)`): tombol sekunder putih di header publik.

### Named Rules
**The Flat Counter Rule.** Permukaan datar saat diam. Jangan tambah bayangan besar atau mengangkat kartu saat hover; umpan balik hover cukup lewat perubahan warna latar atau border.

## Shapes

Sudut lembut yang membesar mengikuti ukuran elemen: 8px untuk elemen kecil (item navigasi, tombol kecil, tombol ikon), 12px untuk tombol utama dan input, 16px untuk kartu dan panel. Lencana dan avatar berbentuk pil/bulat penuh. Border selalu 1px hairline; tidak ada border tebal dekoratif.

## Components

### Buttons
Rapi dan percaya diri: warna solid, satu bobot semibold, transisi warna saja.
- **Shape:** sudut 12px (md, tinggi 44px) atau 8px (sm 36px, xs/row 32px, icon 32×32).
- **Primary:** latar Laundry Teal, teks putih, semibold. Hover ke Laundry Teal Deep.
- **Secondary:** latar Floor Slate, border hairline, teks Body Slate. Hover menggelapkan latar dan border satu tingkat.
- **Soft:** latar Aqua Mist, border Aqua Edge, teks Laundry Teal. Untuk aksi pendukung yang tetap bernuansa brand.
- **Soft warning / Danger outline:** versi lembut amber dan rose untuk aksi peringatan.
- **Danger:** latar rose solid, teks putih. Hanya untuk aksi destruktif atau penolakan.
- **Ghost:** tanpa latar, teks slate, hover latar slate terang.
- **Disabled:** latar slate-200, teks slate-500, opasitas 60%, tidak bisa diklik.
- **Focus:** outline 2px Laundry Teal dengan offset 2px (global).
- Komponen: `<x-ui.button variant="" size="">`.

### Badges
- **Style:** pil penuh, border 1px, latar nada-50, teks nada-700, 12px semibold.
- **Tones:** neutral, info, warning, success, danger, brand.
- **Status cucian** (`<x-ui.status-badge>`): Diterima → info, Diproses → warning, Selesai → success, Diambil → neutral, Dibatalkan → danger.
- **Status pembayaran** (`<x-ui.payment-badge>`): Lunas → success, DP → warning, lainnya → danger.

### Cards / Containers
- **Corner Style:** 16px.
- **Background:** Counter White.
- **Shadow Strategy:** Card rest (lihat Elevation & Depth).
- **Border:** 1px Hairline Slate (80% opasitas).
- **Internal Padding:** 20px default, 24px pada layar ≥640px untuk kartu publik.
- Panel ringkasan di dalam kartu (mis. ringkasan pembayaran) memakai latar Floor Slate, sudut 12px, padding 16px.
- Komponen: `<x-ui.card>`.

### Inputs / Fields
- **Style:** border 1px Hairline Slate, latar Floor Slate semi-transparan, sudut 12px, padding 10px 14px.
- **Focus:** border berubah ke Laundry Teal, latar menjadi putih, cincin 2px Fresh Aqua 20%.
- **Label:** 12px semibold Body Slate di atas field.
- **Ikon awalan:** ikon Lucide 16px slate-400 di dalam field (mis. ikon nota di input kode transaksi).

### Navigation
- **Sidebar:** putih, border kanan hairline, wordmark CUCI.IN di atas (logo 32px + teks semibold teal).
- **Item:** ikon 20px + label 14px medium, sudut 8px, padding 8px 12px.
- **Aktif:** latar Aqua Mist, teks Laundry Teal. **Diam:** teks slate-600, hover latar slate-100.
- **Mobile:** sidebar menjadi laci dari kiri dengan overlay slate-900 40%; tombol hamburger di header.
- **Footer sidebar:** avatar inisial bulat (Aqua Foam + teks teal), nama, role, dan tombol Keluar yang memerah saat hover.

### Status Stepper (signature)
Penanda progres cucian empat langkah (Diterima → Diproses → Selesai → Diambil) di halaman cek transaksi. Lingkaran 32px bernomor; langkah yang sudah dilalui berwarna Laundry Teal dengan cincin Aqua Foam 4px, langkah berikutnya slate-200. Garis penghubung 2px terisi teal sesuai progres. Status Dibatalkan menyembunyikan stepper dan menampilkan lencana danger.

## Do's and Don'ts

### Do:
- **Do** pakai komponen yang sudah ada (`x-ui.button`, `x-ui.card`, `x-ui.badge`, `x-ui.status-badge`, `x-ui.payment-badge`) sebelum menulis kelas Tailwind baru.
- **Do** pakai token `brand-*` dari `app.css` untuk warna brand, bukan `cyan-*` atau hex mentah.
- **Do** tampilkan setiap status sebagai lencana semantik, dan nilai uang dengan format `Rp 12.500`.
- **Do** tulis kode transaksi dalam monospace tebal.
- **Do** pakai ikon Lucide (`<x-lucide-*>`) berukuran 16–20px dengan stroke konsisten.
- **Do** jaga target sentuh minimal 44px untuk aksi utama di layar publik/HP.

### Don't:
- **Don't** pakai Fresh Aqua (brand-500) untuk teks isi di atas putih; kontrasnya tidak cukup.
- **Don't** pakai teks bergradien atau latar gradien warna-warni; sistem ini warna solid.
- **Don't** pakai glassmorphism atau blur dekoratif; blur hanya untuk header lengket.
- **Don't** tambahkan bayangan besar atau efek angkat saat hover pada kartu.
- **Don't** pakai warna status (hijau, kuning, merah, biru) sebagai dekorasi.
- **Don't** pakai emoji sebagai ikon antarmuka.
- **Don't** tambahkan varian `dark:`; mode gelap belum didukung.
