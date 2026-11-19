---
trigger: always_on
description: Standar penggunaan Lightpanda Browser (CLI & MCP) di WSL2 untuk fetching halaman web, scraping JavaScript modern, dan konversi ke Markdown.
---

## lightpanda

Lightpanda Browser adalah engine headless browser berbasis Zig + V8 yang terpasang di WSL2 Ubuntu (`~/.local/bin/lightpanda`). Terintegrasi sebagai MCP server di Antigravity dan dapat dijalankan via CLI maupun CDP.

### Kapan Digunakan
1. **Membaca Dokumentasi & Web JS-Heavy**: Gunakan saat perlu membaca dokumentasi teknis atau halaman web yang kontennya dirender JavaScript di browser klien (SPA, React, Vue, Next.js) yang tidak terbaca oleh `curl` biasa.
2. **Ekstraksi Markdown Cepat**: Konversi otomatis halaman web ke format Markdown bersih tanpa tag HTML, CSS, iklan, atau boilerplate yang mengotori context window.
3. **Pengganti Ringan Chromium/Puppeteer**: Menghemat RAM (~30-50 MB dibanding Chromium 1-2 GB).

### Kapan TIDAK Boleh Digunakan
1. **Screenshot Visual Pixel-Perfect**: Gunakan Playwright/Chromium jika butuh tangkapan layar tampilan UI CSS asli (Lightpanda merender representasi teks-layout PNG).
2. **Bypass Proteksi Anti-Bot**: DILARANG mencoba menembus Cloudflare Turnstile, reCAPTCHA, atau proteksi anti-bot sesuai panduan kepatuhan.

### Panduan Eksekusi

#### A. Melalui MCP Tools (Native Agent)
Saat MCP server `lightpanda` aktif, gunakan tool bawaan:
- `markdown`: Ambil konten URL langsung sebagai Markdown (`{ "url": "https://..." }`).
- `tree`: Analisis struktur semantik halaman sebelum interaksi form atau tombol.
- `extract`: Ekstraksi data terstruktur spesifik berbasis CSS selector.
- `goto`, `click`, `fill`, `waitForSelector`: Otomasi interaksi halaman secara bertahap.

#### B. Melalui Terminal (CLI via WSL2)
Jalankan perintah langsung dari Windows PowerShell:
```powershell
# Fetch halaman dan ubah langsung ke Markdown
wsl -d Ubuntu -- bash -lc 'lightpanda fetch "https://example.com" --dump markdown'

# Fetch dengan ekstraksi metadata / OpenGraph / JSON-LD
wsl -d Ubuntu -- bash -lc 'lightpanda fetch "https://example.com" --dump structured_data'

# Jalankan CDP Server di port 9222 (bisa diakses dari http://127.0.0.1:9222)
wsl -d Ubuntu -- bash -lc 'lightpanda serve --host 0.0.0.0 --port 9222'
```
