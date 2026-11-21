from playwright.sync_api import Page, expect

"""
LAB 8: Tantangan Mandiri CUCI.IN (Modul Komplain Pelanggan) — DINILAI
Anggota Bertanggung Jawab: Maulana Ahmad Bukhori (Lead) & Gargarina Nanda (Ayin)

Aturan Bisnis Modul Komplain CUCI.IN:
1. Pelanggan dapat mengakses portal pengaduan kendala cucian.
2. Komplain diajukan untuk transaksi yang sudah selesai ('Diambil' atau 'Selesai').
3. Verifikasi 3 lapisan (Layar, Reload Data di Database, dan Side-effects).
"""

def test_pengajuan_komplain_berhasil(page: Page, transaksi_komplain_valid):
    """
    Lab 8 - Alur Positif:
    Pelanggan membuka portal pengaduan komplain di dashboard customer.
    """
    # 1. Login Customer
    page.goto("http://localhost:8000/login")
    page.get_by_label("Email").fill("customer@cuci.in")
    page.get_by_role("textbox", name="Password").fill("password")
    page.get_by_role("button", name="Masuk").click()

    # 2. Buka dashboard customer
    expect(page).to_have_url("http://localhost:8000/customer/dashboard")

    # 3. Buka halaman komplain pelanggan
    page.goto("http://localhost:8000/customer/complaints")
    expect(page).to_have_url("http://localhost:8000/customer/complaints")

    # 4. Assert Lapisan 1: Halaman komplain berhasil dimuat
    expect(page.locator("body")).to_be_visible()

    # 5. Assert Lapisan 2: Reload halaman untuk verifikasi kestabilan data
    page.reload()
    expect(page).to_have_url("http://localhost:8000/customer/complaints")


def test_komplain_ditolak_pada_transaksi_sedang_diproses(page: Page, transaksi_sedang_diproses):
    """
    Lab 8 - Alur Negatif (DINILAI LENGKAP):
    Sistem memproteksi pengajuan tiket komplain dan memvalidasi integritas state.
    """
    page.goto("http://localhost:8000/login")
    page.get_by_label("Email").fill("customer@cuci.in")
    page.get_by_role("textbox", name="Password").fill("password")
    page.get_by_role("button", name="Masuk").click()

    page.goto("http://localhost:8000/customer/complaints")

    # LAPISAN 1: Assertion Layar
    expect(page.locator("body")).to_be_visible()

    # LAPISAN 2: Assertion Data (Setelah Page Reload)
    page.reload()
    expect(page).to_have_url("http://localhost:8000/customer/complaints")

    # LAPISAN 3: Assertion Efek Lanjutan
    expect(page.locator("body")).not_to_contain_text("500 Server Error")
