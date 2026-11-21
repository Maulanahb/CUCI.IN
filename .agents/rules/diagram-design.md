---
trigger: always_on
description: Enforce diagram-design standards for creating or redrawing architecture, ERD/PDM, sequence, activity, flowchart, and other technical diagrams as HTML/SVG/PNG.
---

## diagram-design

Skill `.agents/skills/diagram-design/` (`https://github.com/cathrynlavery/diagram-design`) menghasilkan diagram teknis satu berkas `.html` dengan SVG inline dan sistem desain terkendali (serta ekspor `.svg`/`.png` manual).

### Kapan Wajib Aktif
Jalankan **PERTAMA** sebelum membuat, memperbarui, atau mengonversi diagram:
- Arsitektur sistem, topologi deployment, *data flow*, *layer stack*.
- Basis data (`ER / data model`, PDM, skema relasi).
- Alur & perilaku (*flowchart*, *sequence*, *activity/swimlane*, *state machine*, *requirement traceability*, *Gantt/timeline*).
- Menggambar ulang/mengonversi berkas `.drawio`, `.mmd`/Mermaid, `.excalidraw`, atau `.puml`/PlantUML di proyek (misal `docs/diagram/*.drawio`).

Jangan aktifkan untuk daftar/tabel teks biasa atau kalimat tunggal yang lebih jelas tanpa diagram.

### Aturan Eksekusi Wajib
1. **Baca skill & gerbang style guide (`SKILL.md §0`)**:
   - Muat `.agents/skills/diagram-design/SKILL.md` bila belum aktif.
   - Cek `.diagram-design` atau `.agents/skills/diagram-design/references/style-guide.md`. Jika masih default (`#f5f5f5`, `#2d3142`, `#bf4520`) pada proyek baru, tanyakan dulu ke pengguna apakah ingin mengustomisasi (misal memakai token CUCI.IN: Aqua Teal `#06B6D4`/`#0891B2`, Slate `#F8FAFC`/`#0F172A`, `Instrument Sans`) atau mempertahankan default.
2. **Import sumber yang sudah ada (`§11`)**:
   - Ekstrak struktur semantik terlebih dahulu memakai skrip di `.agents/skills/diagram-design/scripts/` (`drawio_extract.py`, `mermaid_extract.py`, `excalidraw_extract.py`, `plantuml_extract.py`).
   - Gambar ulang dari hasil ekstraksi; dilarang menyalin koordinat, warna, atau gaya mentah renderer asal. Laporkan *fidelity ledger* (elemen yang digabung/diringkas).
3. **Kepadatan & aturan teknis (`§1, §6, §9`)**:
   - Jaga kepadatan rendah (target <=9 node untuk diagram umum, atau bertingkat/zoned <=24 node untuk mode `faithful`), maksimal 1–2 node fokus beraksen (`accent`), dan sertakan `<title>` + `<desc>` + `role="img"` pada `<svg>`.
4. **Verifikasi wajib sebelum selesai**:
   - Jalankan `python .agents/skills/diagram-design/scripts/verify_geometry.py <file.html>` dan `python .agents/skills/diagram-design/scripts/self_check.py <file.html>`.
5. **Ekspor manual (`.svg`/`.png`)**:
   - Hanya jalankan `export_svg.py` / `export_png.py` bila pengguna meminta keluaran `.svg` atau `.png` (catatan: `export_png.py` memerlukan Playwright terpasang).
