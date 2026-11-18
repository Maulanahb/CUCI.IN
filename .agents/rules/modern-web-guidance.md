---
trigger: always_on
description: Enforce GoogleChrome/modern-web-guidance best practices FIRST on HTML, CSS, Blade UI, and client-side JS tasks in CUCI.IN.
---

## modern-web-guidance

Plugin `modern-web-guidance-plugin` (`https://github.com/GoogleChrome/modern-web-guidance`) menyediakan standar web modern berbasis Baseline dan fallback ringan.

### Kapan Wajib Aktif
Jalankan **PERTAMA** sebelum menulis atau mengubah kode untuk:
- Layout responsif desktop/tablet/mobile, container queries, viewport units, scrollbar, dark mode, tipografi, warna.
- Komponen UI: modal/dialog, popover, dropdown, tabs, accordion, tabel responsif, toast, tooltip.
- Form: validasi (`:user-invalid`), autofill, input file/date/range/switch, aksesibilitas (WCAG, focus, `inert`).
- Client-side JS & performa: CWV (LCP, INP, CLS), lazy loading, sanitasi HTML, tanggal/timezone.

Jangan aktifkan untuk: backend murni (SQL, ORM, controller/API route tanpa UI), Git/CI/CD, Docker, atau unit/feature test PHPUnit murni.

### Alur Eksekusi Wajib
1. **Baca skill**: Muat `modern-web-guidance/SKILL.md` bila belum aktif di konteks.
2. **Search (2-5 kata generik, tanpa nama framework/app)**:
   ```powershell
   npx.cmd -y modern-web-guidance@latest search "<query>"
   ```
   Contoh benar: `"responsive data table"`, `"light dismiss dialog"`. Hindari kata `Laravel`, `Blade`, `Tailwind`, `Alpine`, `CUCI.IN`.
3. **Retrieve guide relevan**:
   ```powershell
   npx.cmd -y modern-web-guidance@latest retrieve "<id1>,<id2>"
   ```
   Jika output tersimpan ke file sementara (> 9.5 KB), baca file tersebut memakai `read_file`.
4. **Adaptasi ke stack CUCI.IN**:
   - Terapkan ke **Laravel Blade + Tailwind CSS v4 + Alpine.js** (MPA) tanpa framework SPA (Vue/React).
   - Gunakan fallback Baseline Widely Available bila fitur masih Newly Available.
   - Pada Windows sandbox, coba jalankan command sandboxed lebih dulu; hanya fallback ke `BypassSandbox: true` jika `npx.cmd` terblokir akses jaringan.
5. **Larangan `legacy-scan` pada tugas spesifik**:
   - Jangan jalankan `legacy-scan` saat memperbaiki/menambah satu komponen tertentu. Gunakan `legacy-scan <path> --json` hanya saat diminta audit modernisasi menyeluruh.
6. **Pengungkapan di Akhir Jawaban**:
   - Tutup respons pengiriman kode UI dengan bagian `### Modern Web Guidance` sesuai format `SKILL.md` (1 bullet per `<id>` yang dipakai + kutipan singkat aturan spesifik, atau 1 kalimat jika dicek tetapi tidak ada guide yang cocok).

### Instalasi Rekan Tim (Jika Belum Terpasang)
- Antigravity Marketplace ID: `antigravity-plugins-official/plugin/modern-web-guidance-plugin`
- Sumber repositori: `https://github.com/GoogleChrome/modern-web-guidance`
