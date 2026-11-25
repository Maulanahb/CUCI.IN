from playwright.sync_api import Page, expect
import pytest

from tests.e2e.conftest import BASE_URL, E2eDbFactory

"""
LAB 2: Memperbaiki Locator yang Rapuh pada CUCI.IN (Cek Nota Publik & Modal QR)
Anggota Bertanggung Jawab: Nayla Annora (Nora)

Prinsip Pemilihan Locator Tangguh:
1. get_by_role (Peran & Nama Aksesibilitas)
2. get_by_label (Label Form)
3. Locator semantik terikat elemen bermakna
"""


@pytest.mark.smoke
def test_pencarian_nota_dengan_locator_tangguh(guest_page: Page, e2e_db: E2eDbFactory):
    """Menguji pencarian nota fisik (/lookup) dan modal QR scanner dengan locator semantik."""
    customer = e2e_db.create_customer(name="E2E Pelanggan Lookup", phone="089955667788")
    trx = e2e_db.create_transaction(
        customer_id=customer["customer_id"],
        weight=2.0,
        total_amount=20000.0,
        status="Selesai",
        payment_mode="paid",
    )

    guest_page.goto(f"{BASE_URL}/lookup")

    # 1. Buka dan tutup modal Pindai QR dengan locator semantik role dialog
    tombol_qr = guest_page.get_by_role("button", name="Pindai QR")
    expect(tombol_qr).to_be_visible()
    tombol_qr.click()

    dialog_qr = guest_page.get_by_role("dialog", name="Pindai QR Nota")
    expect(dialog_qr).to_be_visible()
    dialog_qr.get_by_role("button", name="Tutup pemindai").click()
    expect(dialog_qr).to_be_hidden()

    # 2. Cari transaksi nyata menggunakan semantic label dan role button
    input_nota = guest_page.get_by_label("Masukkan Kode Transaksi / Nomor Nota")
    input_nota.fill(trx["transaction_code"])

    tombol_cari = guest_page.get_by_role("button", name="Cek Transaksi")
    expect(tombol_cari).to_be_visible()
    tombol_cari.click()

    # 3. Verifikasi hasil pencarian nota nyata tampil utuh
    expect(guest_page.locator("body")).to_contain_text(trx["transaction_code"])
    expect(guest_page.locator("body")).to_contain_text("Selesai")
    expect(guest_page.locator("body")).to_contain_text("Lunas")
