---
name: modern-web-guidance
description: Search and apply Google Chrome Modern Web Guidance best practices for HTML, CSS, Blade UI, and client-side JS
---

# Workflow: modern-web-guidance

Ikuti skill `modern-web-guidance` secara berurutan sebelum mengubah kode HTML/CSS/Blade/JS klien:

1. **Search Use Case** (2-5 kata generik, tanpa nama framework/aplikasi; pecah jika multi-fitur):
   ```powershell
   npx.cmd -y modern-web-guidance@latest search "<query>"
   ```
   Jika hasil kosong/lemah, ulangi sekali dengan sinonim elemen HTML/CSS/UI (`dialog`, `popover`, `table`, `form`, `responsive`).

2. **Retrieve Guide**:
   ```powershell
   npx.cmd -y modern-web-guidance@latest retrieve "<id>"
   ```
   Bisa menggabungkan beberapa ID dipisah koma. Jika output ditulis ke file sementara, baca seluruhnya dengan `read_file`.

3. **Implementasi di CUCI.IN**:
   - Terapkan pola modern dan fallback Baseline yang diminta ke Blade, `resources/css/app.css`, atau Alpine.js.
   - Pertahankan konvensi layout (`layouts/app.blade.php`), komponen `<x-...>`, dan ikon `<x-lucide-... />` tanpa emoji default.

4. **Verifikasi & Pengungkapan**:
   - Jalankan `npm run build` (jika mengubah CSS/JS) dan test terkait (`php artisan test --filter=...`).
   - Sertakan bagian `### Modern Web Guidance` di akhir jawaban sesuai format `SKILL.md`.

## Mode Alternatif: Audit Modernisasi Menyeluruh
Hanya jika diminta audit/modernisasi codebase secara luas (bukan perbaikan fitur spesifik):
```powershell
npx.cmd -y modern-web-guidance@latest legacy-scan resources/views --json
```
Verifikasi temuan terhadap `keepWhen` / `dropWhen` sebelum memodernisasi.
