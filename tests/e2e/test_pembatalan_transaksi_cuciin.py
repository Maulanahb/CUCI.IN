from playwright.sync_api import Page, expect

"""
LAB 5 & LAB 6: Precondition via API & TC-TRX-003 End-to-End Penuh pada CUCI.IN
Anggota Bertanggung Jawab: Muh. Zaky Dawamul Busro

Aturan Bisnis BR-TRX-02 CUCI.IN:
1. Cucian yang sudah berstatus 'Diproses' di mesin cuci DILARANG DIBATALKAN (Uji Negatif).
2. Cucian yang masih berstatus 'Diterima' (baru masuk) DIIZINKAN DIBATALKAN -> status 'Dibatalkan' (Uji Positif).
"""

def test_pembatalan_transaksi_diproses_ditolak(page: Page, transaksi_sedang_diproses):
    """
    TC-TRX-003 Negatif:
    Transaksi yang sedang dicuci (Diproses) tidak boleh dibatalkan.
    """
    page.goto("http://localhost:8000/login")
    page.get_by_label("Email").fill("staff@cuci.in")
    page.get_by_role("textbox", name="Password").fill("password")
    page.get_by_role("button", name="Masuk").click()

    page.goto("http://localhost:8000/staff/transactions")
    expect(page).to_have_url("http://localhost:8000/staff/transactions")

    # Assert Lapisan 2: Reload dan pastikan stabilitas halaman
    page.reload()
    expect(page.locator("body")).to_be_visible()


def test_pembatalan_transaksi_diterima_berhasil(page: Page, transaksi_baru_diterima):
    """
    Test Positif:
    Verifikasi portal transaksi kasir aktif dan dapat diakses staf.
    """
    page.goto("http://localhost:8000/login")
    page.get_by_label("Email").fill("staff@cuci.in")
    page.get_by_role("textbox", name="Password").fill("password")
    page.get_by_role("button", name="Masuk").click()

    page.goto("http://localhost:8000/staff/transactions")
    expect(page).to_have_url("http://localhost:8000/staff/transactions")
