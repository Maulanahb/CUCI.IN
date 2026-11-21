from playwright.sync_api import Page, expect

"""
LAB 2: Memperbaiki Locator yang Rapuh pada CUCI.IN (Form Kasir POS & Cek Nota)
Anggota Bertanggung Jawab: Nayla Annora (Nora)

Prinsip Pemilihan Locator Tangguh:
1. get_by_role (Peran & Nama Aksesibilitas)
2. get_by_label (Label Form)
3. get_by_test_id (Atribut data-testid khusus testing)

Hindari locator rapuh seperti path CSS panjang atau teks tombol yang rawan typo/terjemahan.
"""

def test_pencarian_nota_dengan_locator_tangguh(page: Page):
    """
    Menguji fitur pencarian nota fisik (/lookup) tanpa menggunakan selector rapuh.
    """
    page.goto("http://localhost:8000/lookup")

    # ==========================================================================
    # LOCATOR LAMA (RAPUH - JANGAN DIPAKAI):
    # page.click("div.max-w-2xl > form > div > div > input") # Patah saat div flex diubah
    # page.click("#code")                                   # Rawan bentrok jika id diganti
    # page.click("text=Cek Transaksi")                       # Patah jika tombol diganti ikon
    # ==========================================================================

    # LOCATOR PENGGANTI (TANGGUH & TAHAN BANTING):
    # 1. Menggunakan semantic label
    input_nota = page.get_by_label("Masukkan Kode Transaksi / Nomor Nota")
    input_nota.fill("TRX-0001")

    # 2. Menggunakan get_by_role untuk tombol submit
    tombol_cari = page.get_by_role("button", name="Cek Transaksi")
    expect(tombol_cari).to_be_visible()
    tombol_cari.click()
