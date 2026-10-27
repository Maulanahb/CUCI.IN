---
trigger: always_on
description: Auto-mode engine combining Superpowers skill auto-routing with Caveman high-density execution.
---

# Autonomous Execution & Superpowers Routing (Auto-Mode)

## 1. Auto Skill Discovery & Invocation (Superpowers Protocol)
Pada **setiap prompt atau tugas**, sebelum menulis kode atau mengajukan pertanyaan berulang:
1. **Analisis Intent**: Evaluasi apakah tugas berhubungan dengan UI/UX, kueri database/P9, background queue, bug/error, copywriting, pengujian, atau alur tim CUCI.IN.
2. **Katalog & Penjodohan Skill**:
   - **Lokal (`.agents/skills/`)**:
     - `cuci-in-dev`: Alur kerja tim CUCI.IN, feature branching, git author, background queue (P9), streaming ekspor.
     - `impeccable` & `frontend-design`: Desain UI/UX modern, refactor visual Blade, micro-interactions, layout responsif.
     - `copywriting`: Penulisan teks UI, landing page, hero section, label, dan pesan validasi ramah/formal.
     - `systematic-debugging`: Investigasi dan isolasi akar masalah bug / error secara ilmiah.
     - `test-driven-development`: Red-Green-Refactor dan penulisan Feature/Unit Test PHPUnit.
     - `verification-before-completion`: Verifikasi bukti nyata sebelum klaim selesai.
   - **Global (`~/.gemini/config/skills/` & Built-in)**:
     - `ui-ux-pro-max`: Design systems, palette, typography pairings, stack-specific UI best practices.
     - `ponytail`: Solusi paling sederhana, zero-bloat, YAGNI, perubahan minimal yang menyelesaikan masalah.
     - `graphify`: Kueri arsitektur, relasi modul, dan dependensi kode via knowledge graph.
3. **Aktivasi Wajib**: Jika ada skill yang relevan (bahkan hanya 1% potensi relevansi), muat berkas `SKILL.md` dengan `view_file` sebelum eksekusi.
4. **Header Ringkas**: Umumkan skill yang aktif di baris pertama: `🎯 [Skill: <nama-skill>]`.

## 2. Gaya Komunikasi Caveman (Zero Fluff & High Signal)
- **Tanpa Basi-Basi**: Hapus kalimat pembuka ("Halo!", "Tentu saya bantu", "Baiklah...") dan penutup basa-basi ("Semoga bermanfaat!").
- **Action & Code First**: Berikan akar masalah, diff kode spesifik, link file yang dapat diklik, dan perintah terminal yang siap dijalankan.
- **Kepadatan Informasi Tinggi**: Gunakan poin-poin telegrafis daripada paragraf naratif panjang.
- **Evidence Before Assertions**: Jangan pernah mengklaim tugas selesai sebelum memverifikasi bukti eksekusi perintah (contoh: `php artisan test`, `vendor/bin/pint --format agent`).
