---
name: diagram-design
description: Create or redraw architecture, ERD/PDM, sequence, swimlane, flowchart, and technical diagrams as self-contained HTML/SVG/PNG
---

# Workflow: diagram-design

Ikuti langkah berikut saat membuat atau merapikan diagram dengan `.agents/skills/diagram-design/`:

1. **Cek Style Guide / Profil (`SKILL.md §0`)**:
   - Periksa `.diagram-design` atau `.agents/skills/diagram-design/references/style-guide.md`.
   - Bila masih default (`#f5f5f5`, `#2d3142`, `#bf4520`) dan belum disetel untuk proyek ini, konfirmasi ke pengguna opsi kustomisasi (termasuk opsi token CUCI.IN dari `resources/css/app.css`).

2. **Tentukan Sumber & Tipe Diagram (`§3` & `§11`)**:
   - **Dari nol:** Pilih pola semantik (`references/semantic-patterns.md`) dan tipe visual (`references/type-*.md`), lalu salin template terdekat di `.agents/skills/diagram-design/assets/`.
   - **Import berkas ada (`.drawio`, Mermaid, `.excalidraw`, PlantUML):** Ekstrak dulu memakai skrip Python terkait:
     ```powershell
     python .agents/skills/diagram-design/scripts/drawio_extract.py <path-ke-file.drawio>
     ```
     Tetapkan 4 dial (*Format*, *Size*, *Detail*, *Audience*) sesuai `references/output-spec.md`.

3. **Susun HTML + SVG Inline (`§6` & `§12`)**:
   - Pastikan `<svg>` memiliki `role="img"`, `aria-labelledby`, `<title>`, `<desc>`, konektor ortogonal `r=8`, dan pembungkus lokal `overflow-x: auto`.

4. **Validasi Geometri & Self-Check (`§9`)**:
   ```powershell
   python .agents/skills/diagram-design/scripts/verify_geometry.py <file.html>
   python .agents/skills/diagram-design/scripts/self_check.py <file.html>
   ```
   Perbaiki seluruh temuan hingga status `OK` (`0 finding(s)`).

5. **Ekspor SVG / PNG (Hanya Jika Diminta)**:
   ```powershell
   python .agents/skills/diagram-design/scripts/export_svg.py <file.html>
   python .agents/skills/diagram-design/scripts/export_png.py <file.html>
   ```
   (Jika `export_png.py` membutuhkan Playwright dan belum terpasang, laporkan ke pengguna sebelum menginstal paket apa pun.)
