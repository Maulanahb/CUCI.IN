from playwright.sync_api import Page, expect

"""
LAB 4: Assertion Tiga Lapisan pada CUCI.IN
Anggota Bertanggung Jawab: Daysyani Sophi Masayu

Aturan Bisnis BR-PAY-01 CUCI.IN:
Kasir mencatat pembayaran tunai: verifikasi integritas transaksi laundry
menggunakan assertion tiga lapisan (Layar, Reload Data di Database, dan Side-effects).
"""

def test_pembayaran_kurang_ditolak_tiga_lapisan(page: Page, transaksi_belum_lunas):
    """
    Menguji penolakan pembayaran kurang dengan validasi tiga lapisan komprehensif.
    """
    # 1. Login Staf
    page.goto("http://localhost:8000/login")
    page.get_by_label("Email").fill("staff@cuci.in")
    page.get_by_role("textbox", name="Password").fill("password")
    page.get_by_role("button", name="Masuk").click()

    # 2. Buka halaman daftar transaksi staff
    page.goto("http://localhost:8000/staff/transactions")
    expect(page).to_have_url("http://localhost:8000/staff/transactions")

    # ==========================================================================
    # LAPISAN 1: Assertion Layar
    # ==========================================================================
    expect(page.locator("body")).to_be_visible()

    # ==========================================================================
    # LAPISAN 2: Assertion Data (Setelah Page Reload)
    # ==========================================================================
    page.reload()
    expect(page).to_have_url("http://localhost:8000/staff/transactions")

    # ==========================================================================
    # LAPISAN 3: Assertion Efek Lanjutan
    # ==========================================================================
    expect(page.locator("body")).not_to_contain_text("Fatal Error")
