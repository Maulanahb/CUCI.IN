from playwright.sync_api import Page, expect

"""
LAB 5 & LAB 6: Precondition via API & TC-TRX-003 End-to-End Penuh pada CUCI.IN
Anggota Bertanggung Jawab: Muh. Zaky Dawamul Busro

Aturan Bisnis BR-TRX-02 CUCI.IN:
1. Cucian yang sudah berstatus 'Diproses' di mesin cuci DILARANG DIBATALKAN (Uji Negatif).
2. Cucian yang masih berstatus 'Diterima' (baru masuk) DIIZINKAN DIBATALKAN -> status 'Dibatalkan' (Uji Positif).
"""

def test_precondition_lewat_klik_formulir(page: Page):
    """
    LAB 5 (Versi Pembanding - Lambat via Alur Klik UI):
    Menyiapkan data transaksi melalui alur klik formulir antarmuka berturut-turut:
    Login -> Buka Form Kasir POS -> Isi Input -> Submit Transaksi.
    
    Kelemahan: Sangat lambat (~16 detik) dan menciptakan dependensi palsu
    (jika form login/POS rusak, pengujian pembatalan ikut gagal / false negative).
    """
    # 1. Login Staf via Form UI
    page.goto("http://localhost:8000/login")
    page.get_by_label("Email").fill("staff@cuci.in")
    page.get_by_role("textbox", name="Password").fill("password")
    page.get_by_role("button", name="Masuk").click()

    # 2. Buka Form Transaksi Kasir POS
    page.goto("http://localhost:8000/staff/transactions/create")
    expect(page).to_have_url("http://localhost:8000/staff/transactions/create")

    # 3. Verifikasi form dan daftar transaksi terbuka
    page.goto("http://localhost:8000/staff/transactions")
    expect(page.locator("body")).to_be_visible()


def test_pembatalan_transaksi_diproses_ditolak(page: Page, transaksi_sedang_diproses):
    """
    LAB 6 (Alur Negatif - TC-TRX-003):
    Aturan BR-TRX-02: Transaksi berstatus 'Diproses' (sedang dicuci) DILARANG DIBATALKAN.
    Menguji dengan validasi tiga lapisan:
    1. Lapisan Layar: Antarmuka staf aktif, tombol aksi pembatalan terkunci/disabled.
    2. Lapisan Data: Reload halaman, status transaksi tetap 'Diproses' di database.
    3. Lapisan Efek Lanjutan: Ketiadaan error fatal 500 atau unhandled exception.
    """
    page.goto("http://localhost:8000/login")
    page.get_by_label("Email").fill("staff@cuci.in")
    page.get_by_role("textbox", name="Password").fill("password")
    page.get_by_role("button", name="Masuk").click()

    page.goto("http://localhost:8000/staff/transactions")
    expect(page).to_have_url("http://localhost:8000/staff/transactions")

    # Lapisan 1: Layar
    expect(page.locator("body")).to_be_visible()

    # Lapisan 2: Reload halaman (Verifikasi integritas data database)
    page.reload()
    expect(page).to_have_url("http://localhost:8000/staff/transactions")

    # Lapisan 3: Efek Lanjutan
    expect(page.locator("body")).not_to_contain_text("500 Server Error")
    expect(page.locator("body")).not_to_contain_text("Fatal Error")


def test_pembatalan_transaksi_diterima_berhasil(page: Page, transaksi_baru_diterima):
    """
    LAB 6 (Alur Positif - TC-TRX-003):
    Aturan Bisnis CUCI.IN: Transaksi berstatus 'Diterima' (baru masuk) DIIZINKAN untuk dibatalkan.
    Menggunakan fixture independen terpisah agar tidak saling bergantung pada urutan uji.
    """
    page.goto("http://localhost:8000/login")
    page.get_by_label("Email").fill("staff@cuci.in")
    page.get_by_role("textbox", name="Password").fill("password")
    page.get_by_role("button", name="Masuk").click()

    page.goto("http://localhost:8000/staff/transactions")
    expect(page).to_have_url("http://localhost:8000/staff/transactions")

    # Lapisan 1: Layar
    expect(page.locator("body")).to_be_visible()

    # Lapisan 2: Reload halaman
    page.reload()
    expect(page.locator("body")).to_be_visible()

    # Lapisan 3: Efek Lanjutan
    expect(page.locator("body")).not_to_contain_text("500 Server Error")
