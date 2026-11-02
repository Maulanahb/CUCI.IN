from playwright.sync_api import Page, expect

"""
LAB 3: Membuang Sleep, Memakai Auto-Waiting pada CUCI.IN
Anggota Bertanggung Jawab: Nayla Annora (Nora)

Kasus: Halaman Riwayat Transaksi Kasir (/staff/transactions)
Daftar transaksi dimuat dari database dan dirender dengan tabel dinamis.
Penggunaan time.sleep() diganti dengan expect auto-waiting.
"""

def test_riwayat_transaksi_tanpa_sleep(page: Page):
    """
    Test menggunakan Playwright web-first assertion expect(...).to_be_visible()
    tanpa jeda kaku time.sleep().
    """
    page.goto("http://localhost:8000/login")
    page.get_by_label("Email").fill("staff@cuci.in")
    page.get_by_role("textbox", name="Password").fill("password")
    page.get_by_role("button", name="Masuk").click()

    page.goto("http://localhost:8000/staff/transactions")

    # Auto-waiting: Playwright menunggu elemen tabel transaksi siap
    expect(page.locator("table, main").first).to_be_visible()
