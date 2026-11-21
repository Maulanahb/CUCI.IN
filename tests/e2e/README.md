# Panduan Eksekusi Playwright E2E Testing — CUCI.IN
Mata Kuliah: Penjaminan Mutu Perangkat Lunak (SIB216101) — Pertemuan 9

---

## 1. Persiapan Lingkungan

Pastikan server web lokal CUCI.IN sedang aktif di port 8000:
```bash
# Di terminal 1 (Jalankan server Laravel)
php artisan serve
```

Pastikan dependensi Python dan Chromium sudah terpasang:
```bash
# Di terminal 2 (Pengujian Playwright)
pip install pytest-playwright requests
playwright install chromium
```

---

## 2. Menjalankan Skrip Pengujian per Anggota Tim

```bash
# Daysyani (Lab 1 & Lab 4)
pytest tests/e2e/test_login_cuciin.py -v
pytest tests/e2e/test_pembayaran_kurang_cuciin.py -v

# Nora (Lab 2 & Lab 3)
pytest tests/e2e/test_rapuh_pos_cuciin.py -v
pytest tests/e2e/test_riwayat_cuciin.py -v --count=10

# Zaky (Lab 5 & Lab 6)
pytest tests/e2e/test_pembatalan_transaksi_cuciin.py -v

# Maulana & Ayin (Lab 7 & Lab 8 Tantangan Mandiri DINILAI)
pytest tests/e2e/test_complaint_cuciin.py -v --tracing=retain-on-failure --video=retain-on-failure

# Menjalankan seluruh suite pengujian sekaligus
pytest tests/e2e -v
```

---

## 3. Format Pengumpulan ke LMS (Masing-Masing Mahasiswa)

Sesuai instruksi modul (halaman 14), masing-masing anggota mengumpulkan arsip ZIP dengan format:
`PMPL-M9-<NIM>-<Nama>.zip`

Isi dalam file ZIP:
1. Folder `tests/e2e/` (berisi skrip Python di atas).
2. Berkas Word Laporan: `docs/PMPL-M9-Laporan-Praktikum-E2E-CUCIIN.docx` (sesuaikan Nama & NIM masing-masing).
3. Berkas `trace.zip` (dari folder `test-results/`).
4. Tangkapan layar (*screenshot*) terminal saat test lulus hijau (`PASSED`).
