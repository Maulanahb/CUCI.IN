from playwright.sync_api import Page, expect
import pytest

from tests.e2e.conftest import BASE_URL

"""
LAB 4 + ALUR 7: Assertion Tiga Lapisan Pembayaran Kasir & Proteksi Pengambilan
Anggota Bertanggung Jawab: Daysyani Sophi Masayu

Menguji:
1. Guard kurang bayar (cash_received < amount) pada modal pembayaran (#payment-modal).
2. Pembayaran DP parsial dan proteksi pengambilan: transaksi belum lunas DILARANG status 'Diambil'.
3. Pelunasan Tunai dengan uang kembalian -> status menjadi 'Lunas', tercatat di riwayat, persisten pasca-reload.
"""


@pytest.mark.smoke
@pytest.mark.payment
def test_pembayaran_kurang_ditolak_tiga_lapisan(staff_page: Page, transaksi_belum_lunas: dict):
    """Validasi pembayaran kurang ditolak, DP memblokir status Diambil, dan pelunasan dengan kembalian sukses."""
    detail_url = f"{BASE_URL}/staff/transactions/{transaksi_belum_lunas['transaction_id']}"
    staff_page.goto(detail_url)
    expect(staff_page.locator("body")).to_contain_text(transaksi_belum_lunas["transaction_code"])
    expect(staff_page.locator("body")).to_contain_text("Belum Bayar")

    # ==========================================================================
    # LAPISAN 1: Guard Kurang Bayar di Modal Pembayaran
    # ==========================================================================
    staff_page.get_by_role("button", name="Catat Pembayaran").first.click()
    modal = staff_page.locator("#payment-modal")
    expect(modal).to_be_visible()

    modal.locator("#payment-amount").fill("50000")
    modal.locator("#cash-received").fill("30000")

    expect(modal.locator("#cash-help")).to_contain_text("Kurang Rp 20.000")
    submit_btn = modal.get_by_role("button", name="Konfirmasi & Simpan")
    expect(submit_btn).to_be_disabled()

    # ==========================================================================
    # LAPISAN 2: Catat Pembayaran DP Rp 20.000 & Uji Proteksi Status 'Diambil'
    # ==========================================================================
    modal.locator("#payment-amount").fill("20000")
    modal.locator("#cash-received").fill("20000")
    expect(modal.locator("#cash-help")).to_contain_text("Uang pas.")
    expect(submit_btn).to_be_enabled()
    submit_btn.click()

    expect(staff_page.locator("body")).to_contain_text("Pembayaran sebesar Rp 20.000 (Tunai) berhasil dicatat.")
    expect(staff_page.locator("body")).to_contain_text("DP")
    expect(staff_page.locator("body")).to_contain_text("Rp 30.000")

    # Coba ubah status ke 'Diambil' sebelum lunas -> harus ditolak server
    staff_page.locator("#status").select_option("Diambil")
    staff_page.get_by_role("button", name="Perbarui Status").click()
    expect(staff_page.locator("body")).to_contain_text("pembayaran belum lunas")

    # ==========================================================================
    # LAPISAN 3: Pelunasan Sisa Rp 30.000 (Uang Diterima Rp 50.000, Kembali Rp 20.000) & Reload Persistence
    # ==========================================================================
    staff_page.get_by_role("button", name="Catat Pembayaran").first.click()
    modal_pelunasan = staff_page.locator("#payment-modal")
    expect(modal_pelunasan).to_be_visible()

    modal_pelunasan.locator("#payment-amount").fill("30000")
    modal_pelunasan.locator("#cash-received").fill("50000")
    expect(modal_pelunasan.locator("#cash-help")).to_contain_text("Kembalian Rp 20.000")
    modal_pelunasan.get_by_role("button", name="Konfirmasi & Simpan").click()

    expect(staff_page.locator("body")).to_contain_text("Pembayaran sebesar Rp 30.000 (Tunai) berhasil dicatat.")
    expect(staff_page.locator("body")).to_contain_text("Lunas")
    expect(staff_page.locator("body")).to_contain_text("Kembali Rp 20.000")

    staff_page.reload()
    expect(staff_page.locator("body")).to_contain_text("Lunas")
    expect(staff_page.locator("body")).to_contain_text("Pelunasan")
    expect(staff_page.locator("body")).not_to_contain_text("Fatal Error")
