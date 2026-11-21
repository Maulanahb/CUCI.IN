---
name: lightpanda
description: Alur kerja penelusuran web modern, scraping dokumentasi JS-heavy, dan ekstraksi data teks hemat memori menggunakan Lightpanda.
---

# Workflow: Lightpanda Web Reader & Scraper

Gunakan workflow ini saat perlu membaca halaman web modern atau mengotomasi interaksi browser headless tanpa overhead Chromium.

## Langkah Eksekusi

### 1. Evaluasi Kebutuhan Halaman
- Jika halaman statis / teks umum: Cukup gunakan pencarian web atau `curl` standar.
- Jika halaman memerlukan eksekusi JavaScript (SPA, docsite dinamis): Gunakan Lightpanda.

### 2. Pengambilan Konten (CLI / MCP)
- **Opsi Ringkas (Markdown Dump)**:
  ```powershell
  wsl -d Ubuntu -- bash -lc 'lightpanda fetch "<URL>" --dump markdown'
  ```
- **Opsi Halaman Dinamis dengan Waktu Render**:
  ```powershell
  wsl -d Ubuntu -- bash -lc 'lightpanda fetch "<URL>" --dump markdown --wait-ms 2000'
  ```
- **Opsi Metadata Terstruktur (OpenGraph/JSON-LD)**:
  ```powershell
  wsl -d Ubuntu -- bash -lc 'lightpanda fetch "<URL>" --dump structured_data'
  ```

### 3. Interaksi Multi-Langkah (Form / Navigasi Berkelanjutan)
Gunakan tool MCP `lightpanda`:
1. Navigasi: Panggil tool `goto` dengan target URL.
2. Analisis elemen: Panggil tool `tree` untuk membaca hierarki node dan ID target.
3. Aksi: Panggil `fill` atau `click` menggunakan CSS selector dari node.
4. Pembacaan hasil: Panggil tool `markdown` untuk mengekstrak output final.

### 4. Penyajian Hasil
- Sajikan informasi yang diekstrak langsung dalam format poin telegrafis atau dokumen yang diminta pengguna tanpa membawa noise HTML mentah.
