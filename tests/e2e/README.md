# Panduan Suite E2E Playwright + Pytest — CUCI.IN (Milestone 3)

Suite pengujian End-to-End (E2E) berbasis `pytest-playwright` yang menguji seluruh **16 alur fungsional CUCI.IN** langsung terhadap browser Chromium, database PostgreSQL lokal (`db_bridge.php`), dan cleanup data uji otomatis saat teardown.

---

## 1. Persiapan Lingkungan

1. Pastikan server lokal CUCI.IN aktif di port `8000` (atau set env `E2E_BASE_URL`):
   ```powershell
   php artisan serve --host=127.0.0.1 --port=8000
   ```
2. Pastikan dependensi Python dan browser Chromium Playwright sudah terpasang:
   ```powershell
   python -m pip install pytest pytest-playwright requests
   python -m playwright install chromium
   ```

---

## 2. Daftar Berkas & Pemetaan Alur / Test Case

| Berkas E2E | Cakupan Alur & Test Case | Marker Utama |
| :--- | :--- | :--- |
| `conftest.py` + `helpers/db_bridge.php` | Health-check `/up`, fixture DB nyata (`TRX-E2E-*`), multi-actor context terisolasi, PNG 240x240 generator, teardown cleanup otomatis | - |
| `test_landing_and_lookup_cuciin.py` | Landing desktop/mobile (`375px`), kalkulator `#estimasi`, `/lookup` valid/fiktif, upload bukti bayar guest & validasi 4 digit HP (`TC-LND-001`, `TC-MOB-001`, `TC-LKP-001..003`, `TC-PAY-004`) | `smoke`, `mobile`, `payment` |
| `test_rapuh_pos_cuciin.py` | Locator semantik pencarian nota `/lookup` & modal QR scanner (`Lab 2`) | `smoke` |
| `test_login_cuciin.py` | Login Admin/Staff/Customer, password salah, akun nonaktif ditolak, logout (`TC-AUT-001..003`) | `smoke`, `auth` |
| `test_auth_and_rbac_cuciin.py` | Registrasi customer `/register` (`TC-AUT-004`), guard RBAC Guest/Staff/Customer 302/403 (`TC-RBAC-001..002`) | `auth` |
| `test_pos_and_receipt_cuciin.py` | Kasir POS, Quick-Add pelanggan, multi-item, DP Tunai, `Bayar Nanti`, idempotency key, & struk thermal Code128 + QR (`TC-POS-001..004`) | `smoke`, `pos` |
| `test_riwayat_cuciin.py` | Pencarian, filter status, drawer, & detail `/staff/transactions` tanpa `sleep` (`Lab 3`, `TC-TRX-001`) | `pos` |
| `test_pembayaran_kurang_cuciin.py` | Guard kurang bayar kasir, DP parsial memblokir status `Diambil`, pelunasan kembalian, persistensi reload (`TC-PAY-001..003`, `TC-STS-003`) | `smoke`, `payment` |
| `test_payment_verification_cuciin.py` | Multi-aktor Customer upload Transfer/QRIS -> Staff review preview -> Approve (`Lunas`) & Reject beralasan (`TC-PAY-005`, `TC-VER-001..002`) | `smoke`, `payment` |
| `test_pembatalan_transaksi_cuciin.py` | Siklus status `Diterima -> Diproses -> Selesai -> Diambil`, rollback wajib alasan, pembatalan beralasan, imutabilitas terminal (`TC-STS-001..005`) | `smoke`, `lifecycle` |
| `test_complaint_cuciin.py` | Pengaduan Customer + unggah foto, penanganan & tanggapan Staf, proteksi nota batal, Anti-IDOR 403 Customer B (`TC-CMP-001..002`, `TC-SEC-001`) | `smoke`, `complaint` |
| `test_admin_operations_cuciin.py` | Admin Users CRUD + toggle nonaktif, Master Layanan + toggle, Direktori Pelanggan, filter & ekspor PDF/Excel/CSV (`TC-ADM-001..003`, `TC-RPT-001..002`) | `admin` |
| `test_known_bugs_m3_cuciin.py` | Regresi `@pytest.mark.xfail` untuk defect terbuka M3 (`BUG-03`, `BUG-05..08`, `BUG-11..12`) agar otomatis terdeteksi saat fixed (`XPASS`) | `bug_open` |

---

## 3. Perintah Eksekusi Pengujian

```powershell
# Jalankan seluruh suite E2E (membuat trace jika ada gagal)
python -m pytest tests/e2e -v --tracing=retain-on-failure

# Jalankan smoke suite cepat saja
python -m pytest tests/e2e -m smoke -v

# Jalankan hanya suite fungsional utama (tanpa daftar bug terbuka xfail)
python -m pytest tests/e2e -m "not bug_open" -v

# Jalankan hanya tracker bug terbuka M3
python -m pytest tests/e2e -m bug_open -v

# Jalankan berkas tertentu dengan mode headed (melihat browser)
python -m pytest tests/e2e/test_pos_and_receipt_cuciin.py -v --headed
```

Catatan:
- Jangan pernah menjalankan suite E2E tulis-menulis terhadap server production (`https://portal-cuciin.web.id`). Gunakan server lokal atau staging khusus testing.
- Untuk melihat berkas `trace.zip` hasil kegagalan di `test-results/`:
  ```powershell
  python -m playwright show-trace test-results/<nama-folder>/trace.zip
  ```
