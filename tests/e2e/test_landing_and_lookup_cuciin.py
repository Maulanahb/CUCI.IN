from pathlib import Path

from playwright.sync_api import BrowserContext, Page, expect
import pytest

from tests.e2e.conftest import BASE_URL, E2eDbFactory

"""
ALUR 1 & 2: Landing Page, Kalkulator Tarif, Lookup Publik, & Upload Bukti Tamu
Mencakup TC-LND-001, TC-MOB-001, TC-LKP-001, TC-LKP-002, TC-LKP-003, TC-PAY-004.
"""


@pytest.mark.smoke
def test_landing_page_desktop_dan_kalkulator_tarif(guest_page: Page):
    """Landing page tampil benar dan kalkulator #estimasi menghitung tarif sesuai paket & berat."""
    guest_page.goto(f"{BASE_URL}/")

    expect(guest_page.get_by_role("heading", level=1)).to_contain_text("Laundry Bersih")
    estimasi = guest_page.locator("#estimasi")
    expect(estimasi).to_be_visible()

    # Pilih paket Kilat Express 24 Jam (Rp 10.000/kg) dan geser slider ke indeks 2 (10 kg)
    estimasi.get_by_role("button", name="Kilat Express 24 Jam").click()
    estimasi.locator('input[type="range"]').fill("2")

    expect(estimasi).to_contain_text("Cuci Kilat Express 24 Jam")
    expect(estimasi).to_contain_text("Rp 100.000")
    expect(estimasi).to_contain_text("24 Jam")


@pytest.mark.mobile
def test_landing_page_mobile_hamburger_dan_tanpa_overflow(new_context):
    """Viewport mobile 375x812 tidak overflow horizontal dan tombol hamburger membuka menu."""
    context: BrowserContext = new_context(viewport={"width": 375, "height": 812})
    page = context.new_page()
    page.goto(f"{BASE_URL}/")

    mobile_menu_btn = page.get_by_role("button", name="Toggle Navigation Menu")
    expect(mobile_menu_btn).to_be_visible()
    mobile_menu_btn.click()

    mobile_nav = page.locator("#mobile-nav")
    expect(mobile_nav).to_be_visible()
    expect(mobile_nav.get_by_role("link", name="Lacak Status Nota")).to_be_visible()

    has_no_overflow = page.evaluate(
        "() => document.documentElement.scrollWidth <= document.documentElement.clientWidth"
    )
    assert has_no_overflow, "Terdeteksi horizontal overflow pada viewport mobile 375px."


@pytest.mark.smoke
def test_lookup_publik_nota_valid_dan_tidak_ditemukan(guest_page: Page, e2e_db: E2eDbFactory):
    """Pelacakan /lookup case-insensitive menampilkan nota valid dan menangani nota fiktif."""
    customer = e2e_db.create_customer(name="E2E Pelanggan Lacak", phone="089911223344")
    trx = e2e_db.create_transaction(
        customer_id=customer["customer_id"],
        weight=4.0,
        total_amount=40000.0,
        status="Diproses",
        payment_mode="unpaid",
    )

    guest_page.goto(f"{BASE_URL}/lookup")
    guest_page.get_by_label("Masukkan Kode Transaksi / Nomor Nota").fill(trx["transaction_code"].lower())
    guest_page.get_by_role("button", name="Cek Transaksi").click()

    expect(guest_page).to_have_url(f"{BASE_URL}/lookup?code={trx['transaction_code'].lower()}")
    expect(guest_page.locator("body")).to_contain_text(trx["transaction_code"])
    expect(guest_page.locator("body")).to_contain_text("Diproses")
    expect(guest_page.locator("body")).to_contain_text(trx["service_name"])
    expect(guest_page.locator("#btn-copy-link")).to_be_visible()

    # Uji kode nota fiktif -> pesan tidak ditemukan
    guest_page.get_by_label("Masukkan Kode Transaksi / Nomor Nota").fill("TRX-TIDAK-ADA-9999")
    guest_page.get_by_role("button", name="Cek Transaksi").click()
    expect(guest_page.locator("body")).to_contain_text("Transaksi Tidak Ditemukan")


@pytest.mark.payment
def test_guest_upload_bukti_pembayaran_validasi_4_digit_dan_persisten(
    guest_page: Page,
    e2e_db: E2eDbFactory,
    sample_png_path: Path,
):
    """Guest wajib lolos verifikasi 4 digit terakhir HP saat unggah bukti bayar di /lookup."""
    customer = e2e_db.create_customer(name="E2E Tamu Upload", phone="089912345678")
    trx = e2e_db.create_transaction(
        customer_id=customer["customer_id"],
        weight=3.0,
        total_amount=30000.0,
        status="Diterima",
        payment_mode="unpaid",
    )

    guest_page.goto(f"{BASE_URL}/lookup?code={trx['transaction_code']}")

    # Buka form unggah bukti pembayaran
    guest_page.get_by_role("button", name="Kirim Bukti Pembayaran").click()
    form = guest_page.locator('form[action$="/lookup/payment-proof"]')
    expect(form).to_be_visible()

    # 1. Uji 4 digit HP salah -> ditolak server dengan pesan error
    form.locator("#guest-phone-last-4").fill("0000")
    form.locator("#guest-proof-file").set_input_files(str(sample_png_path))
    form.locator('button[type="submit"]').click()

    expect(guest_page.locator("body")).to_contain_text(
        "4 digit terakhir nomor telepon tidak cocok"
    )

    # 2. Uji 4 digit HP benar ('5678') -> berhasil & persisten setelah reload
    form_retry = guest_page.locator('form[action$="/lookup/payment-proof"]')
    form_retry.locator("#guest-payment-method").select_option("Transfer")
    form_retry.locator("#guest-phone-last-4").fill("5678")
    form_retry.locator("#guest-proof-file").set_input_files(str(sample_png_path))
    form_retry.locator('button[type="submit"]').click()

    expect(guest_page.locator("body")).to_contain_text("Bukti Pembayaran Terkirim!")
    expect(guest_page.locator("body")).to_contain_text("Bukti Pembayaran Sedang Diverifikasi")

    guest_page.reload()
    expect(guest_page.locator("body")).to_contain_text("Bukti Pembayaran Sedang Diverifikasi")


@pytest.mark.smoke
def test_hero_landing_lacak_sekarang_otomatis_tampil_di_lookup(
    guest_page: Page,
    e2e_db: E2eDbFactory,
):
    """Submit kode nota dari form hero '/' langsung menampilkan detail transaksi di '/lookup' (BUG-09 fixed)."""
    trx = e2e_db.create_transaction(weight=2.0, total_amount=20000.0, status="Diproses")

    guest_page.goto(f"{BASE_URL}/")
    guest_page.locator('form[action$="/lookup"] input[name="code"]').fill(trx["transaction_code"])
    guest_page.get_by_role("button", name="Lacak Sekarang").click()

    expect(guest_page.locator("#code")).to_have_value(trx["transaction_code"])
    expect(guest_page.locator("body")).to_contain_text("Status Cucian")
